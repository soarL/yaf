<?php
namespace models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;
use plugins\ancun\ACTool;
use models\Sms;
use tools\Banks;

class Recharge extends Model {

	protected $table = 'user_moneyrecharge';

	public $timestamps = false;

	public static $payTypes = [
		'yemadai' => '汇潮支付',
		'baofoo' => '宝付支付',
		'lianlian' => '连连支付',
		'minsheng' => '民生支付',
		'fuiou' => '富友支付',
        'custody' => '江西银行',
	];

    public static $payWays = [
        'A' => '平台充值',
        'T' => '转账充值',
        'SWIFT' => '快捷支付',
        'WEB' => '网银支付',
        'deduct' => '代扣支付',
    ];
	
	public static $userTypes = ['未处理', '新', '旧'];
	
	public function user() {
  		return $this->belongsTo('models\User', 'userId');
  	}

	public static function getListBuilder($queries) {
		$builder = self::with('user');
		$builder = self::setListCondition($builder, $queries);
		return $builder;
	}

	public static function setListCondition($builder, $queries) {
		$searchType = $queries->searchType;
        $searchContent = $queries->searchContent;
        $status = $queries->status;
        $payStatus = $queries->payStatus;
        $type = $queries->type;
        $beginTime = $queries->beginTime;
        $endTime = $queries->endTime;
        $payWay = $queries->payWay;

        $statusList = ['success'=>1, 'fail'=>-1, 'unfinished'=>0];
        $payStatusList = ['success'=>1, 'fail'=>-1, 'unfinished'=>0];
        
        if($searchContent!='') {
	        $searchCol = '';
	        $searchValue = '';
	        if($searchType=='username') {
	        	$searchCol = 'userId';
	        	$user = User::where('username', $searchContent)->first(['userId']);
	        	$searchValue = $user?$user->userId:'';
	        }

	        if($searchType=='userId') {
	        	$searchCol = 'userId';
	        	$searchValue = $searchContent;
	        }

	        if($searchType=='serialNumber') {
	        	$searchCol = 'serialNumber';
	        	$searchValue = $searchContent;
	        }

	        if($searchValue!='') {
	        	$builder->where($searchCol, $searchValue);
			}
		}

        if($status!='all') {
        	$builder->where('status', $statusList[$status]);
        }

        if($payStatus!='all') {
        	$builder->where('payStatus', $payStatusList[$payStatus]);
        }

        if($type!='all') {
        	if($type=='yemadai') {
        		$builder->whereRaw('(payType=? or payType=?)', [$type, '']);
        	} else {
        		$builder->where('payType', $type);
        	}
        }

        if($payWay!='all') {
        	$builder->where('payWay', $payWay);
        }

        if($beginTime!='') {
            $beginTime .= ' 00:00:00';
            $builder->where('time', '>=', $beginTime);
        }

        if($endTime!='') {
            $endTime .= ' 23:59:59';
            $builder->where('time', '<=', $endTime);
        }

        return $builder;
	}

    public static function after($return) {
        DB::beginTransaction();
        $result = false;
        try {
            $trade = self::where('serialNumber', $return['tradeNo'])->lock()->first();
            if($trade) {
                if($return['status']==1) {
                    $result = $trade->afterSuccess($return);
                } else {
                    $result = $trade->afterFail($return);
                }
            } else {
                DB::rollback();
            }
        } catch(\Exception $e) {
            \Log::write('充值通知：'.$e->getMessage(), 'sqlError');
            DB::rollback();
        }
        if($result['status']==1) {
            DB::commit();
        } else {
            DB::rollback();
        }
        return $result;
    }

	public function afterFail($return) {
		$rdata = [];
		$this->status = -1;
        $this->result = $return['result'];
        $this->validTime = date('Y-m-d H:i:s');
        if($this->save()) {
            $rdata['status'] = 1;
            $rdata['info'] = '充值失败！';
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '系统异常！';
        }
        return $rdata;
	}

	public function afterSuccess($return) {
		$rdata = [];
		if($this->status==1) {
			$rdata['status'] = 1;
			$rdata['info'] = '充值成功！';
		} else {
            if($this->money != $return['money']/100){
                \Log::write('充值异常:'.json_encode($return),'baofoo');
                return false;
            }
            $this->status = 1;
            $this->result = $return['result'];
            $this->validTime = date('Y-m-d H:i:s');
            $status1 = $this->save();

			$status2 = User::where('userId', $this->userId)->update(['fundMoney'=>DB::raw('fundMoney+'.$this->money),'withdrawMoney'=>DB::raw('withdrawMoney+'.$this->money*1.1)]);

			$remark = '在线充值'.$this->money.'元。';

            // 用户资金日志
            MoneyLog::log($this->userId, 'nor-recharge', 'in', $this->money, $remark);

            $acctfee = User::where('userId', User::ACCT_RP)->first();
            $bffee = $this->getBFFee();
            User::where('userId', User::ACCT_RP)->update(['fundMoney'=>DB::raw('fundMoney-'.$bffee)]);
            $feeRemark = $this->userId.'宝付充值费'.$bffee.'元。';
            $logs[] = [
                'type' => 'fee-recharge',
                'mode' => 'out',
                'mvalue' => $bffee,
                'userId' => User::ACCT_RP,
                'remark' => $feeRemark,
                'remain' => $acctfee->fundMoney,
                'frozen' => $acctfee->frozenMoney,
                'time' => $this->validTime,
            ];
            MoneyLog::insert($logs);

	        $acTool = new ACTool($this, 'recharge');
	        $acTool->send();

            if($status1 && $status2) {
                $rdata['status'] = 1;
                $rdata['info'] = '充值成功！';

                $user = $this->user;
                $msg['phone'] = $user->phone;
                $msg['msgType'] = 'recharge';
                $msg['userId'] = $user->userId;
                $msg['params'] = [
                                    $user->getPName(),
                                    date('Y-m-d H:i:s'),
                                    $this->money,
                                ];
                Sms::send($msg);

            } else {
                $rdata['status'] = 0;
                $rdata['info'] = '系统异常！';
            }
		}
        return $rdata;
	}

	public function getPayTypeName() {
		if($this->payType=='') {
			return '汇潮支付';
		}
		if(isset(self::$payTypes[$this->payType])) {
			return self::$payTypes[$this->payType];
		}
		return '其他';
	}

	public function getPayWayName() {
		if($this->payType==''||$this->payType=='yemadai') {
			return '网银支付';
		} else if($this->payType=='baofoo') {
			if($this->payWay==1) {
				return '网银支付';
			} else if($this->payWay==3){
				return '认证支付';
			}
		} else if($this->payType=='lianlian') {
			if($this->payWay==1) {
				return '网银支付';
			} else if($this->payWay=='D'){
				return '认证支付';
			}
		} else if($this->payType=='minsheng') {
			if($this->payWay==1) {
				return '借记卡';
			} else if($this->payWay==2){
				return '信用卡';
			} else if($this->payWay==3){
				return '混合通道';
			}
		}
		return '其他';
	}

    public function getBFFee(){
        $fee = $this->money * 0.0018;
        if($fee<2 && $this->payWay!='WEB'){
            $fee = 2;
        }
        return round($fee,2);
    }
	
	/**
	 * 获取某日的充值统计数据
	 * @param string $time 日期
	 * @return array
	 */
	public static function getDateDs($time) {
		$recharge = self::where('time', 'like', $time.'%')
	    	->where('status', 1)
	    	->get();
    	// 新用户充值人数
    	$data['newRechargeUserNum'] = 0;
    	// 老用户充值人数
    	$data['oldRechargeUserNum'] = 0;
    	// 充值金额
    	$data['rechargeMoney'] = 0;
    	// pc充值金额
    	$data['pcRechargeMoney'] = 0;
    	// app充值金额
    	$data['appRechargeMoney'] = 0;
    	
    	if ($recharge->isEmpty()) {
    		return $data;
    	}	
    	foreach ($recharge as $value) {
    		$userIdArr[] = $value->userId;
    		$data['rechargeMoney'] += $value->money;
    		if ($value->media == 'pc') {
    			$data['pcRechargeMoney'] += $value->money;
    		} elseif ($value->media == 'app') {
    			$data['appRechargeMoney'] += $value->money;
    		}
    	}
    	$userIdArr = array_unique($userIdArr);
    	// 查询今日充值用户的第一次充值时间
    	$userFirst = self::whereIn('userId', $userIdArr)
    		->where('status', 1)
    		->orderBy('time', 'asc')
    		->groupBy('userId')
    		->select('userId', 'time')
    		->get()
    		->toArray();
    	$userFirst = array_column($userFirst, 'time', 'userId');
    	foreach ($recharge as $value) {
    		if ($value->time <= $userFirst[$value['userId']]) {
    			self::where('id', $value->id)->update(['userType' => 1]);
    			$data['newRechargeUserNum'] ++;
    		} else {
    			self::where('id', $value->id)->update(['userType' => 2]);
    			$data['oldRechargeUserNum'] ++;
    		}
    	}
    	return $data;
	}
}
