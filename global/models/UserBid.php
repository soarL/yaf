<?php
namespace models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;
use custody\API;
use models\Sms;
use models\OddTrace;
use tools\Log;

/**
 * UserBid|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class UserBid extends Model {
    const TIME_LIMIT = 300;

    protected $table = 'user_bid';

    public $timestamps = false;

    public function user() {
        return $this->belongsTo('models\User', 'userId');
    }

    public function odd() {
        return $this->belongsTo('models\Odd', 'oddNumber');
    }

    public function oddMoney() {
        return $this->belongsTo('models\OddMoney', 'tradeNo', 'tradeNo');
    }

    public static function after($return) {
        $tradeNo = $return['tradeNo'];
        $result = [];

        DB::beginTransaction();
        try {
            $trade = self::where('tradeNo', $tradeNo)->lock()->first();
            if($trade) {
                if($return['status']==1) {
                    $result = $trade->afterSuccess($return);
                } else {
                    $result = $trade->afterFail($return);
                }
            } else {
                $result['status'] = 0;
                $result['msg'] = '订单不存在！';
            }
        } catch(\Exception $e) {
            Log::write('投标回调：'.$e->getMessage(), [], 'sqlError');
            $result['status'] = 0;
            $result['msg'] = '系统异常，请联系客服！';
        }

        if($result['status']==1) {
            DB::commit();
        } else {
            DB::rollback();
        }

        return $result;
    }

    public function afterSuccess($return) {
        $rdata = [];

        if($this->status==1) {
            $rdata['status'] = 1;
            $rdata['msg'] = '投标成功！';
            return $rdata;
        }

        $result = (isset($return['result']) && $return['result'])?$return['result']:'UNKNOWN';
        $count = self::where('tradeNo', $this->tradeNo)->where('status', '0')->update([
            'status' => 1,
            'validTime' => date('Y-m-d H:i:s'),
            'result' => $result,
        ]);

        if($count && $this->handle()) {
            $rdata['status'] = 1;
            $rdata['msg'] = '投标成功！';
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = '系统异常，请联系客服！';
        }
        return $rdata;
    }

    public function afterFail($return) {
        $rdata = [];

        $result = (isset($return['result']) && $return['result'])?$return['result']:'UNKNOWN';
        $count = self::where('tradeNo', $this->tradeNo)->where('status', '0')->update([
            'status' => 2,
            'validTime' => date('Y-m-d H:i:s'),
            'result' => $result,
        ]);

        if($this->lotteryId) {
            Lottery::where('id', $this->lotteryId)->update(['status'=>Lottery::STATUS_NOUSE]);
        }

        if($count) {
            Odd::disBid($this->oddNumber, $this->bidMoney);
        }

        $rdata['status'] = 1;
        $rdata['msg'] = '投标失败！';
        
        return $rdata;
    }

    private function handle() {
        $oddMoney = new OddMoney();
        $oddMoney->oddNumber = $this->oddNumber;
        $oddMoney->type = 'invest';
        $oddMoney->money = $this->bidMoney;
        $oddMoney->remain = $this->bidMoney;
        $oddMoney->userId = $this->userId;
        $oddMoney->remark = '手动投标';
        $oddMoney->time = date('Y-m-d H:i:s');
        $oddMoney->status = 0;
        $oddMoney->tradeNo = $this->tradeNo;
        $oddMoney->media = $this->media;
        $oddMoney->lotteryId = $this->lotteryId;
        if($oddMoney->save()) {

            $time = date('Y-m-d H:i:s');
            $user = User::where('userId', $this->userId)->first(['userId', 'fundMoney', 'frozenMoney', 'phone', 'name', 'sex']);

            $bonus = 0;
            $logs = [];
            if($this->lotteryId) {
                $lottery = Lottery::where('id', $this->lotteryId)->first();
                if($lottery) {
                    Lottery::where('id', $this->lotteryId)->update(['status'=>Lottery::STATUS_USED, 'used_at'=>date('Y-m-d H:i:s')]);
                }
            }

            $remark = '投资标的@oddNumber{'.$this->oddNumber.'},冻结'.$this->bidMoney.'元';
            $log = [];
            $log['userId'] = $user->userId;
            $log['type'] = 'nor-tender';
            $log['mode'] = 'freeze';
            $log['mvalue'] = $this->bidMoney;
            $log['remark'] = $remark;
            $log['remain'] = $user->fundMoney - $this->bidMoney;
            $log['frozen'] = $user->frozenMoney + $this->bidMoney;
            $log['time'] = $time;
            $logs[] = $log;

            MoneyLog::insert($logs);
            Odd::where('oddNumber', $this->oddNumber)->update(['successMoney'=>DB::raw('successMoney+'.$this->bidMoney)]);
            $res = Odd::where('oddNumber', $this->oddNumber)->whereRaw('successMoney=oddMoney')->update(['fullTime'=>date('Y-m-d H:i:s')]);
            if($res){
                $phone = ['15159634716','15396099909','18805907512'];
                foreach ($phone as $key => $value) {
                    $msg['phone'] = $value;
                    $msg['msgType'] = 'goRehear';
                    $msg['params'] = [
                                        $this->oddNumber,
                                        $time,
                                    ];
                    Sms::send($msg);
                }

                $oddTrace[] = ['addtime'=>$time,'oddNumber'=>$oddMoney->odd->oddNumber,'type'=>'full','info'=>'借款项目满标'];
                OddTrace::insert($oddTrace);
            }

            $status = User::where('userId', $this->userId)->update([
                'frozenMoney'=>DB::raw('frozenMoney+'.$this->bidMoney), 
                'fundMoney'=>DB::raw('fundMoney-'.($this->bidMoney - $bonus))
            ]);

            if($lottery && $lottery->type == 'money'){
                $msg = [];
                $msg['phone'] = $user->phone;
                $msg['msgType'] = 'bidRedpack';
                $msg['userId'] = $user->userId;
                $msg['params'] = [
                                    $user->getPName(),
                                    $oddMoney->remark,
                                    $oddMoney->oddNumber,
                                    $oddMoney->money,
                                    $oddMoney->odd->oddBorrowPeriod,
                                    $oddMoney->odd->getRepayTypeName(),
                                    ($oddMoney->odd->oddYearRate*100) .'%'.  ($oddMoney->odd->oddReward? '+'.$oddMoney->odd->oddReward*100 . '%' :'' ),
                                    $lottery->money_rate,
                                ];
                Sms::send($msg);
            }else{
                $msg = [];
                $msg['phone'] = $user->phone;
                $msg['msgType'] = 'userbid';
                $msg['userId'] = $user->userId;
                $msg['params'] = [
                                    $user->getPName(),
                                    $oddMoney->remark,
                                    $oddMoney->oddNumber,
                                    $oddMoney->money,
                                    $oddMoney->odd->oddBorrowPeriod,
                                    $oddMoney->odd->getRepayTypeName(),
                                    ($oddMoney->odd->oddYearRate*100) .'%'.  ($oddMoney->odd->oddReward? '+'.$oddMoney->odd->oddReward*100 . '%' :'' ),
                                ];
                Sms::send($msg);
            }

            return $status;
        }
        return false;
    }
}