<?php
use helpers\DateHelper;
use helpers\NumberHelper;
use models\Attribute;
use models\Ranking;
use models\RankingDay;
use models\RankingMonth;
use models\RankingWeek;
use models\UserBid;
use models\Odd;
use models\LookVote;
use models\LookOdd;
use models\UserCrtr;
use models\OddMoney;
use models\Crtr;
use models\GQLottery;
use models\Lottery;
use models\User;
use models\OddInfo;
use models\Queue;
use models\Interest;
use models\Invest;
use models\OddTrace;
use models\MoneyLog;
use models\ActUserPacket;
use models\TranLog;
use models\UserDuein;
use business\RehearHandler;
use tools\Log;
use custody\API;
use Illuminate\Database\Capsule\Manager as DB;
use forms\admin\LotteryForm;
use custody\Handler;
use tools\Redis;
use task\Task;

/**
 * TaskController
 * 任务控制器
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class TaskController extends Controller {
	public function changeOddAction() {
		$re = Odd::where('oddTrialTime','<',date('Y-m-d H:i:s',strtotime('-5days')))->where('progress','start')->where('oddStyle','newhand')->update(['oddStyle'=>'normal']);
		if($re){
			$this->export('改变新手标状态成功,标的'.$re.'条成功');
		}
	}

	public function oddTraceAction(){
        $traces = OddTrace::with(['odd'=>function($q){$q->where('progress','run')->with(['user'=>function($q){$q->with(['withdraw'=>function($q){$q->where('validTime','>',date('Y-m-d'))->where('status','1');}]);}]);}])->groupBy('oddNumber')->get();
        foreach ($traces as $key => $value) {
            $user =  $value->odd->user;
            if(isset($user->withdraw[0])){
                foreach ($user->withdraw as $key => $withdraw) {
                    $oddTrace[] = ['addtime'=>$withdraw->validTime,'oddNumber'=>$value->odd->oddNumber,'type'=>'withdraw','info'=>'借款人提现'.$withdraw->outMoney.'元'];
                }
            }
        }

        $stime = date('Y-m-d',strtotime('+3 day'));
        $etime = date('Y-m-d',strtotime('+4 day'));
        $interests = Interest::where('endtime','>=',$stime)->where('endtime','<',$etime)->where('status','0')->get();
        foreach ($interests as $key => $value) {
            $oddTrace[] = ['addtime'=>date('Y-m-d 09:00:00'),'oddNumber'=>$value->oddNumber,'type'=>'msg','info'=>'第'.$value->qishu.'期还款短信提醒发送借款人'];
        }

        $interests = Interest::where('endtime','>',date('Y-m-d'))->where('status','<>','0')->get();
        foreach ($interests as $key => $value) {
            if($value->benJin){
                $info = '借款人偿还第'.$value->qishu.'期本金'.$value->benJin.'元，利息'.$value->realinterest.'元。';
            }else{
                $info = '借款人偿还第'.$value->qishu.'期利息'.$value->realinterest.'元';
            }
            if($value->status == '3'){
                $info = '借款人偿还第'.$value->qishu.'期本金'.$value->benJin.'元，利息'.$value->realinterest.'元，支付逾期罚息'.$value->subsidy.'元。';
            }
            $oddTrace[] = ['addtime'=>$value->operatetime,'oddNumber'=>$value->oddNumber,'type'=>'repay','info'=>'第'.$value->qishu.'期还款短信提醒发送借款人'];
        }

        $stime = date('Y-m-d');
        $etime = date('Y-m-d',strtotime('+1 day'));
        $odds = Odd::where('progress','run')->where('finishTime','>=',$stime)->where('finishTime','<',$etime)->get();
        foreach ($odds as $key => $value) {
            $oddTrace[] = ['addtime'=>$value->finishTime,'oddNumber'=>$value->oddNumber,'type'=>'end','info'=>'本笔借款项目结清'];
        }

        $traces = OddTrace::with(['odd'=>function($q){$q->where('progress','run')->with(['user'=>function($q){$q->with(['recharge'=>function($q){$q->where('validTime','>',date('Y-m-d'))->where('status','1');}]);}]);}])->groupBy('oddNumber')->get();
        foreach ($traces as $key => $value) {
            $user =  $value->odd->user;
            if(isset($user->recharge[0])){
                foreach ($user->recharge as $key => $recharge) {
                    $oddTrace[] = ['addtime'=>$recharge->validTime,'oddNumber'=>$value->odd->oddNumber,'type'=>'recharge','info'=>'借款人充值'.$recharge->outMoney.'元'];
                }
            }
        }

        OddTrace::insert($oddTrace);
	}

	public function yearActivityAction(){
        if(time() > strtotime('2018-02-29')){
            exit('活动结束');
        }

		$packets = ActUserPacket::with('user')->where('status',0)->get();
		foreach ($packets as $key => $value) {
			if($value->user->custody_id){
				$remark = '用户['.$value->user->userId.']新年送红包';
				$result = API::redpack($value->user->userId, $value->money, 'rpk-normal', $remark);
				if($result['status']) {
					$value->status = 1;
					User::where('userId',$result)->update(['investMoney'=>DB::raw('investMoney+'.$value->money)]);
					// $value->updated_at = date('Y-m-d H:i:s');
					$value->save();
					$this->export('发放红包,'.$value->id.'成功');
				}
			}
		}

		$packet = ActUserPacket::where('userId','10000')->orderBy('id','desc')->first();
		if(!isset($packet) || (time() - (strtotime($packet->created_at)) > 10*60)){
			$packetes = [
			    1=>1.6, 
			    2=>1.8, 
			    3=>2,
			    4=>2.2, 
			    5=>2.6, 
			    6=>2.8,
			    7=>3, 
			    8=>3.8,
			    9=>5,
			];

			$packet = new ActUserPacket();
            $packet->userId = '10000';
            $item = rand(1,9);
            $packet->money = $packetes[$item];
            $packet->status = 1;
            $count = User::whereNotNull('name')->count();
            $user = User::whereNotNull('name')->offset(rand(1,$count))->first();
            $packet->name = $user->name;
            $packet->phone = $user->phone;
            $packet->save();
		}
	}

	public function dueinLogAction(){
		$users = User::get();
		$date = date('Ymd');
		foreach ($users as $key => $value) {
			$stay = Invest::getStayPrincipalByUser($value->userId);
			if($stay > 0){
				$duein['userId'] = $value->userId;
				$duein['date'] = $date;
				$duein['stay'] = $stay;
				$dueins[] = $duein;
			}
		}
		if($dueins){
			UserDuein::insert($dueins);
			$this->export($date.'待收统计,共' . count($dueins).'条');
		}
	}

	public function testAction() {
		Log::write('test crontab', [], 'crontab');
		$this->export('你好', self::CONSOLE_NONE_SUF);
		$this->export('where');
	}

	/**
	 * 添加虚拟用户
	 * @return mixed
	 */
	public function addVirtualUserAction() {
		$num = rand(0, 3);
		$value = Attribute::getByIdentity('addUserNum');
		Attribute::updateByIdentity('addUserNum', $value + $num);
		$this->export('添加虚拟用户：' . $num);
	}

	/**
	 * 生成总榜单（需要每日生成）
	 * @return mixed
	 */
	public function generateAction() {
		Ranking::generate();
		$this->export('生成总榜成功！');
	}

	/**
	 * 生成日榜单（需要每日生成）
	 * @return mixed
	 */
	public function generateDayAction() {
		$day = date('Y-m-d', time()-24*60*60);
		$dayBegin = $day.' 00:00:00';
		$dayEnd = $day.' 23:59:59';
		RankingDay::generate($dayBegin, $dayEnd);
		$this->export('生成日榜成功！');
	}

	/**
	 * 生成周榜单（需要每周一生成）
	 * @return mixed
	 */
	public function generateWeekAction() {
		$lastWeek = DateHelper::getLastWeek();
		RankingWeek::generate($lastWeek[0], $lastWeek[1]);
		$this->export('生成周榜成功！');
	}

	/**
	 * 生成月榜单（需要每月1号生成）
	 * @return mixed
	 */
	public function generateMonthAction() {
		$lastMonth = DateHelper::getLastMonth();
		RankingMonth::generate($lastMonth[0], $lastMonth[1]);
		$this->export('生成月榜成功！');
	}

	/**
	 * 生成查标数据
	 * @return mixed
	 */
	public function lookOddGenerateAction() {
		$votes = LookVote::with('odd')
            ->groupBy('oddNumber')
            ->orderBy('voteNum', 'desc')
            ->limit(2)
            ->get([DB::raw('count(userId) as voteNum'), 'oddNumber']);
        $last = LookOdd::orderBy('period', 'desc')->first();
        $period = 1;
        if($last) {
        	$period = $last->period+1;
        }
        $num = 0;
        foreach ($votes as $vote) {
       		$odd = new LookOdd();
       		$odd->oddNumber = $vote->oddNumber;
       		$odd->num = $vote->voteNum;
       		$odd->period = $period;
       		$odd->save();
       		Odd::where('oddNumber', $vote->oddNumber)->update(['isUserLook'=>'y']);
       		$num ++;
        }
        LookVote::truncate();
		$this->export('更新'.$num.'记录！');
	}

	/**
	 * 国庆节生成活动投资金额
	 * @return mixed
	 */
	public function gqCashMoneyAction() {
		set_time_limit(0);
		$this->export('请勿重新生成！', self::CONSOLE_FULL_SUF, true);
		$begin = '2016-09-27 00:00:00';
		$end = '2016-10-16 00:00:00';
		$credits = OddMoney::with('pcrtr')
			->where('type', 'credit')
			->whereHas('pcrtr', function($q) use($begin) {
				$q->where('addtime', '>', $begin);
			})
			->where('time', '>=', $begin)
			->where('time', '<', $end)
			->whereIn('status', [0, 1])
			->groupBy('userId')
			->get(['userId', DB::raw('sum(money) totalMoney')]);
		$invests = OddMoney::where('type', 'invest')
			->whereIn('status', [0, 1])
			->where('time', '>=', $begin)
			->where('time', '<', $end)
			->groupBy('userId')
			->get(['userId', DB::raw('sum(money) totalMoney')]);
		$list = [];
		foreach ($credits as $row) {
			$key = $row->userId;
			$list[$key] = $row->totalMoney;
		}
		foreach ($invests as $row) {
			$key = $row->userId;
			if(isset($list[$key])) {
				$list[$key] = $list[$key] + $row->totalMoney;
			} else {
				$list[$key] = $row->totalMoney;
			}
		}
		Log::write('list-cash', $list, 'guoqing');
		foreach ($list as $userId => $money) {
			User::where('userId', $userId)->update(['cashMoney' => $money]);
		}
		$this->export('生成完成！');
	}

	/**
	 * 国庆节生成幸运币
	 * @return mixed
	 */
	public function gqImiMoneyAction() {
		set_time_limit(0);
		$this->export('请勿重新生成！', self::CONSOLE_FULL_SUF, true);

		$begin = '2016-09-27 00:00:00';
		$end = '2016-10-16 00:00:00';
		$credits = OddMoney::with('pcrtr', 'odd')
			->where('type', 'credit')
			->whereHas('pcrtr', function($q) use($begin) {
				$q->where('addtime', '>', $begin);
			})
			->where('time', '>=', $begin)
			->where('time', '<', $end)
			->whereIn('status', [0, 1])
			->get();
		$invests = OddMoney::with('odd')
			->where('type', 'invest')
			->whereIn('status', [0, 1])
			->where('time', '>=', $begin)
			->where('time', '<', $end)
			->get();
		$list = [];
		foreach ($credits as $row) {
			$key = $row->userId;
			$money = $this->computeImiMoney($row);
			if(isset($list[$key])) {
				$list[$key] = $list[$key] + $money;
			} else {
				$list[$key] = $money;
			}
		}
		foreach ($invests as $row) {
			$key = $row->userId;
			$money = $this->computeImiMoney($row);
			if(isset($list[$key])) {
				$list[$key] = $list[$key] + $money;
			} else {
				$list[$key] = $money;
			}
		}
		Log::write('list-imi', $list, 'guoqing');
		foreach ($list as $userId => $money) {
			User::where('userId', $userId)->update(['imiMoney' => intval($money)]);
		}
		$this->export('生成完成！');
	}

	/**
	 * 计算可兑换的幸运币
	 * @param  models\OddMoney $oddMoney 投资
	 * @return double           幸运币数量
	 */
	private function computeImiMoney($oddMoney) {
		$money = 0;
		$odd = $oddMoney->odd;
		if($odd->oddBorrowPeriod==1||$odd->oddBorrowPeriod==2||$odd->oddBorrowPeriod==3) {
			$money = $oddMoney->money/50*50;
		} else if($odd->oddBorrowPeriod==6) {
			$money = $oddMoney->money/50*75;
		} else if($odd->oddBorrowPeriod==12) {
			$money = $oddMoney->money/50*100;
		} else if($odd->oddBorrowPeriod==24) {
			$money = $oddMoney->money/50*200;
		}
		return $money;
	}

	/**
	 * 自动分配抽奖券
	 * @return mixed
	 */
	public function assignImiMoneyAction() {
		set_time_limit(0);
		if(time()<strtotime('2016-10-18 00:00:00')) {
			$this->export('时间未到，暂时无法分配！', self::CONSOLE_FULL_SUF, true);
		}
		$types = [
			'A'=>['need'=>50000],
			'B'=>['need'=>20000],
			'C'=>['need'=>10000],
		];
		$attributes = Attribute::whereIn('identity', ['gq_num_A', 'gq_num_B', 'gq_num_C'])->get();

		$nums = [];
		foreach ($attributes as $attribute) {
			if($attribute->identity=='gq_num_A') {
				$nums['A'] = $attribute->value;
			} else if($attribute->identity=='gq_num_B') {
				$nums['B'] = $attribute->value;
			} else if($attribute->identity=='gq_num_C') {
				$nums['C'] = $attribute->value;
			}
		}

		$k = 0;
		$has = true;
		$limit = 500;
		while ($has) {
			DB::beginTransaction();
			$users = User::where('imiMoney', '>=', 10000)->orderBy('id', 'asc')->skip($k)->limit($limit)->get(['userId', 'imiMoney']);
			if(count($users)==0) {
				$has = false;
				DB::rollback();
				break;
			}
			foreach ($users as $user) {
				$k++;
				$imiMoney = $user->imiMoney;
				$failMoney = 0;
				foreach ($types as $key => $type) {
					$num = intval($imiMoney/$type['need']);
					$imiMoney = $imiMoney - $num*$type['need'];
					for ($i=0; $i < $num; $i++) {
						$gqLottery = new GQLottery();
						$gqLottery->userId = $user->userId;
						$gqLottery->type = $key;
						$no = $nums[$key] + 1;
						$mo = $key . NumberHelper::zeroPrefix($no, 4);
						$gqLottery->num = $mo;
						if($gqLottery->save()) {
							$nums[$key] = $no;
							Log::write($key.' => '.$user->userId, [], 'asign_gq_loggery');
						} else {
							$failMoney = $failMoney + $type['need'];
							Log::write($key.' => '.$user->userId.' [分配失败]', [], 'asign_gq_loggery_error');
						}
					}
				}
				$user->imiMoney = $imiMoney + $failMoney;
				$user->save();
			}
			DB::commit();
		}

		Attribute::where('identity', 'gq_num_A')->update(['value'=>$nums['A']]);
		Attribute::where('identity', 'gq_num_B')->update(['value'=>$nums['B']]);
		Attribute::where('identity', 'gq_num_C')->update(['value'=>$nums['C']]);

		$this->export('生成完成！');
	}

	/**
	 * 自动分配加息券
	 * @return mixed
	 */
	public function assignCashMoneyAction() {
		set_time_limit(0);
		if(time()<strtotime('2016-10-19 00:00:00')) {
			$this->export('时间未到，暂时无法分配！', self::CONSOLE_FULL_SUF, true);
		}
		$types = [
			6=>['need'=>100000, 'name'=>'1%加息券', 'money_rate'=>'0.01', 'type'=>'interest'],
			5=>['need'=>80000, 'name'=>'0.8%加息券', 'money_rate'=>'0.008', 'type'=>'interest'],
			4=>['need'=>60000, 'name'=>'0.6%加息券', 'money_rate'=>'0.006', 'type'=>'interest'],
			3=>['need'=>40000, 'name'=>'0.4%加息券', 'money_rate'=>'0.004', 'type'=>'interest'],
			2=>['need'=>20000, 'name'=>'0.2%加息券', 'money_rate'=>'0.002', 'type'=>'interest'],
			1=>['need'=>10000, 'name'=>'0.1%加息券', 'money_rate'=>'0.001', 'type'=>'interest'],
		];

		$k = 0;
		$has = true;
		$limit = 500;
		while ($has) {
			DB::beginTransaction();
			$users = User::where('cashMoney', '>=', 10000)->orderBy('id', 'asc')->skip($k)->limit($limit)->get(['userId', 'cashMoney']);
			if(count($users)==0) {
				$has = false;
				DB::rollback();
				break;
			}
			foreach ($users as $user) {
				$k++;
				$cashMoney = $user->cashMoney;
				$failMoney = 0;
				foreach ($types as $key => $type) {
					$num = intval($cashMoney/$type['need']);
					$cashMoney = $cashMoney - $num * $type['need'];
					for ($i=0; $i < $num; $i++) {
						$lottery = Lottery::where('type', $type['type'])
							->where('status', Lottery::STATUS_NOGET)
							->where('money_rate', $type['money_rate'])
							->first();
						if($lottery) {
							if($lottery->assign($user)) {
								Log::write($key.' => '.$user->userId, [], 'asign_loggery');
							} else {
								$failMoney = $failMoney + $type['need'];
								Log::write($key.' => '.$user->userId.' [分配失败]', [], 'asign_loggery_error');
							}	
						} else {
							$failMoney = $failMoney + $type['need'];
							Log::write($key.' => '.$user->userId.' [奖券不足]', [], 'asign_loggery_error');
						}
					}
				}
				$user->cashMoney = $cashMoney + $failMoney;
				$user->save();
			}
			DB::commit();
		}
		$this->export('生成完成！');
	}

	/**
	 * 发送提现券[0 1 1 * *]
	 * @return mixed
	 */
	public function sendWithdrawLotteryAction() {
		set_time_limit(0);
		$users = User::where('integral', '>', 15000)->get(['userId', 'integral']);

		$list = [];
		foreach ($users as $user) {
			$integralRow = $user->getTenderGrade();
			if($integralRow['grade']==3||$integralRow['grade']==4) {
				$list[] = $user->userId;
			} else if($integralRow['grade']==5||$integralRow['grade']==6) {
				$list[] = $user->userId;
				$list[] = $user->userId;
			} else if($integralRow['grade']>=7) {
				$list[] = $user->userId;
				$list[] = $user->userId;
				$list[] = $user->userId;
			}
		}
		$item = DateHelper::getCurrentMonth(date('Y-m-d'));
		$params = [
			'type' => 'withdraw',
        	'num' => 0,
        	'money_rate' => 2,
        	'useful_day' => '',
        	'money_lower' => '',
        	'money_uper' => '',
        	'period_lower' => '',
        	'period_uper' => '',
        	'remark'=>'vip用户送提现券['.date('Ym').']',
        	'assign_users'=>implode(',', $list),
        	'endtime'=>_date('Y-m-d', $item[1]),
        ];
        $user = User::where('userId', '9001')->first();
        $form = new LotteryForm($params);
        $form->setUser($user);
        if($form->generate()) {
            $this->export('发送提现券成功！');
        } else {
        	$this->export($form->posError());
        }
	}

	/**
	 * 完结债权转让[30 * * * *]
	 * @return mixed
	 */
	public function finishCrtrAction() {
		$crtrs = Crtr::with(['odd'=>function($q) {
			$q->select(['oddNumber', 'oddBorrowStyle', 'oddRepaymentStyle', 'oddBorrowPeriod', 'oddYearRate', 'oddReward']);
		}, 'oddMoney'=>function($q) {
			$q->select(['id', 'money', 'status']);
		}, 'user'=>function($q) {
			$q->select(['userId', 'custody_id']);
		}])->where('progress', 'start')->get();

		foreach ($crtrs as $crtr) {
			
			$isFull = ($crtr->successMoney==$crtr->money);
			if(!$isFull&&time()<strtotime($crtr->outtime)) {
				continue;
			}

			if($crtr->finish($isFull)) {
				$this->export('完结债权转让执行成功！执行债转编号：'.$crtr->getSN());
			} else {
				$this->export('完结债权转让执行异常！');
			}
		}
	}

	/**
	 * 更新redis中的用户数据
	 * @return mixed
	 */
	public function refreshRedisUserAction() {
		User::select(['userId', 'username', 'phone', 'cardnum', 'custody_id', 'email'])->chunk(500, function ($users) {
			foreach ($users as $user) {
				Redis::hMset('user:'.$user->userId, [
					'userId' => $user->userId, 
					'username' => $user->username, 
					'phone' => $user->phone, 
					'cardnum' => $user->cardnum, 
					'custody_id' => $user->custody_id, 
					'email' => $user->email, 
				]);
				$this->export('用户：'.$user->userId);
			}
		});
	}

	public function indexAction() {
		Task::run(0, function($task, $result) {
			$this->export('执行任务['.$task.']结果：'.$result['msg']);
		});
	}

	public function oddSeparateAction() {
		$columns = ['oddNumber', 'oddExteriorPhotos', 'oddPropertyPhotos', 'bankCreditReport', 'otherPhotos', 'oddLoanRemark', 'oddLoanControlList', 'oddLoanControl', 'controlPhotos', 'validateCarPhotos', 'contractVideoUrl'];
		Odd::select($columns)->chunk(200, function ($odds) {
			$records = [];
            foreach ($odds as $odd) {
                $records[] = [
					'oddNumber' => $odd->oddNumber,
					'oddExteriorPhotos' => $odd->oddExteriorPhotos,
					'oddPropertyPhotos' => $odd->oddPropertyPhotos,
					'bankCreditReport' => $odd->bankCreditReport,
					'otherPhotos' => $odd->otherPhotos,
					'oddLoanRemark' => $odd->oddLoanRemark,
					'oddLoanControlList' => $odd->oddLoanControlList,
					'oddLoanControl' => $odd->oddLoanControl,
					'controlPhotos' => $odd->controlPhotos,
					'validateCarPhotos' => $odd->validateCarPhotos,
					'contractVideoUrl' => $odd->contractVideoUrl,
                ];
            }
            $num = OddInfo::insert($records);
            $this->export('分离数据:'.$num.'条!');
        });
	}

	/**
	 * 更新自动投标队列[* /10 * * * *]
	 * @return mixed
	 */
	public function refreshQueueAction() {
		$key = Redis::getKey('autoInvesting');
        $ing = Redis::get($key);
        if($ing) {
            $this->export('自动投标中，暂不更新');
        };
        $key = Redis::getKey('autoInvestQueue');
        $list = Redis::lRange($key, 0, -1);
        $size = count($list);
        $this->export($size);

        $list = array_unique($list);
        $size = count($list);
        $this->export($size);

        $list = array_reverse($list);

        Queue::truncate();

        $location = 0;
        $rows = [];
        foreach ($list as $userId) {
        	$location ++;
        	$rows[] = ['userId'=>$userId, 'location'=>$location];
        }
        Queue::insert($rows);
        $this->export('[FINISH]更新完成');
	}

	public function deleteTransferAction() {
		$time = date('Y-m-d H:i:s', time()-3*24*3600);
		$crtrs = Crtr::where('progress', 'start')->whereRaw('oddMoneyLast=money')->where('addtime', '<', $time)->get();
		$deleteList = [];
		foreach ($crtrs as $crtr) {
			$status = Crtr::where('id', $crtr->id)->where('progress', 'start')->whereRaw('oddMoneyLast=money')->update(['progress'=>'fail', 'endtime' => date('Y-m-d H:i:s')]);
			if($status) {
				OddMoney::where('id', $crtr->oddmoneyId)->update(['ckclaims'=>'0']);
				$deleteList[] = $crtr->id;
			}
		}
		$this->export('delete crtrs: ' . implode(',', $deleteList));
	}

	public function handleTranMoneyAction() {
		$logs = TranLog::where('status', 1)->where('payStatus', 0)
			->where('handleStatus', '<>', 2)->get();
		foreach ($logs as $log) {
			$remark = '用户['.$log->userId.']资金迁移';
			$result = API::redpack($log->userId, $log->money, 'rpk-tran', $remark);
			if($result['status']) {
				$log->payStatus = 1;
				$log->result = $result['code'];
				$log->save();
				$this->export('user['.$log->userId.']success['.$log->money.']');
			} else {
				$log->payStatus = 2;
				$log->result = $result['code'];
				$log->save();
				$this->export('user['.$log->userId.']fail['.$log->money.']');
			}
		}
	}

	/**
	 * 自动复审[* /10 * * * *]
	 */
	public function autoRehearAction() {
		$key = Redis::getKey('rehearIngQueue');
		$odds = Odd::where('progress', 'start')->whereRaw('successMoney=oddMoney')->get(['oddNumber']);

		foreach ($odds as $odd) {
			if(!Redis::sAdd($key, $odd->oddNumber)) {
	            $this->export('odd['.$odd->oddNumber.'] is rehearing');
	            continue;
	        }
	        $handler = new RehearHandler(['oddNumber'=>$odd->oddNumber, 'step'=>1]);
        	$result = $handler->handle();
	        if($result['status']) {
	        	$this->export('odd['.$odd->oddNumber.'] rehear success');
	        } else {
	        	$this->export('odd['.$odd->oddNumber.'] rehear error['.$result['msg'].']');
	        }
		}
	}
}
