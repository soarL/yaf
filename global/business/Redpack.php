<?php
namespace business;

use models\Redpack as Pack;
use models\WorkInfo;
use models\Recharge;
use models\User;
use Illuminate\Database\Capsule\Manager as DB;

class Redpack {
    use Common;

    // 商家ID
    public static $account = '10022';

    protected $user;

    protected $account;


    var $key = FALSE;

    public static function postRedPack($userId, $workId) {
        /* 查询用户是否已发红包 */
        $money = WorkInfo::where('oddkey','redpack')->first(['oddvalue'])->oddvalue;
        $num = Pack::where('userId',$userId)->where(function($query){$query->where('status', '=', '1')->orWhere('status', '=', '0');})->count();
        $remark = '用户[' . $userId . ']红包';
        if (empty($num)) {
            /* 添加发红包记录 */
            DB::beginTransAction();
            $this->user = User::where('userId',$userId)->first();
            $this->account = User::where('userId',self::$account)->first();
            $time = date("Y-m-d H:i:s");
            $data = ['userId'=>$userId,'addtime'=>$time,'giftMoney'=>$money,'status'=>0];
            Pack::insert($data);
            $moneyid = DB::getPdo()->lastInsertId();

            /* 1、添加充值记录 */
            $data = ['serialNumber'=>$workId,'userId'=>$userId,'mode'=>'in','money'=>$money,'fee'=>'0','status'=>'1','time'=>$time,'operator'=>'admin','remark'=>'系统发红包','source'=>'1'];
            $re = Recharge::insert($data);
            if (!$re) {
                $msg['status'] = 'error';
                $msg['msg'] = $remark . ",添加充值记录失败";
                $msg['data'] = $workId;
                DB::rollBack();
                Pack::where('id',$moneyid)->where('userId',$userId)->update(['status'=>'-1']);
                return $msg;
            }
            /* 2、更新用户金额 */
            $this->user->fundMoney = $this->user->fundMoney + $money;
            if (!$re) {
                $msg['status'] = 'error';
                $msg['msg'] = $remark . ",更新用户金额失败";
                $msg['data'] = $workId;
                DB::rollBack();
                Pack::where('id',$moneyid)->where('userId',$userId)->update(['status'=>'-1']);
                return $msg;
            }
            /* 添加资金记录 */
                $tradeNo = $this->getSystemNumber('serialNumber', 'user_moneylog');
                $moneylog = [];
                $moneylog['serialNumber'] = $tradeNo;
                $moneylog['type'] = 'addmoney';
                $moneylog['mode'] = 'in';
                $moneylog['mvalue'] = $money;
                $moneylog['userId'] = $userId;
                $moneylog['remark'] = $remark;
                $moneylog['remain'] = $this->user->fundMoney;
                $moneylog['frozen'] = $this->user->frozenMoney;
                $this->logData[] = $moneylog;

            /* 3、扣除商家金额 */
            if ($this->account->fundMoney < $money) {
                $msg['status'] = 'error';
                $msg['msg'] = $remark . ",商家金额不够";
                $msg['data'] = $workId;
                DB::rollBack();
                Pack::where('id',$moneyid)->where('userId',$userId)->update(['status'=>'-1']);
                return $msg;
            } else {
                $this->account->fundMoney = $this->account->fundMoney - $money;
                /* 添加资金记录 */
                    $moneylog['mode'] = 'out';
                    $moneylog['userId'] = $this->account->userId;
                    $moneylog['remain'] = $this->account->fundMoney;
                    $moneylog['remark'] = '用户:'.$this->user->userId.$remark;
                    $this->logData[] = $moneylog;
                    if(!$this->save()){
                        $msg['status'] = 'error';
                        $msg['msg'] = $remark . "保存用户和商家资金与日志变动失败!!";
                        $msg['data'] = $workId;
                        DB::rollBack();
                        Pack::where('id',$moneyid)->where('userId',$userId)->update(['status'=>'-1']);
                        return $msg;
                    }
                /*------ 扣除金额到用户 */
                $result = $this->api($this->user->userId, $money, $remark);
                if ('0000' == $result['status']) {
                    $msg['status'] = 'success';
                    $msg['msg'] = $remark . ",发送成功";
                    $msg['data'] = $workId;
                    DB::commit();
                    Pack::where('id',$moneyid)->where('userId',$userId)->update(['status'=>'1']);
                } else {
                    $msg['status'] = 'error';
                    $msg['msg'] = $remark;
                    $msg['data'] = $workId;
                    DB::rollBack();
                    Pack::where('id',$moneyid)->where('userId',$userId)->update(['status'=>'-1']);
                    return $msg;
                }
            }
        } else {
            $msg['status'] = 'error';
            $msg['msg'] = $remark . ",已送过红包";
            $msg['data'] = $workId;
        }
        return $msg;
    }


    /**
     * 红包借口
     * @param  [type] $userId [description]
     * @param  [type] $money  [description]
     * @param  [type] $remark [description]
     * @return [type]         [description]
     */
    protected function api($custody_id, $money, $remark){
        $data['accountId'] = self::$accountId;
        $data['amount'] = $money;
        $data['userNo'] = $custody_id;
        $data['desLineFlag'] = '0';
        if(!empty($remark)){
            $data['desLineFlag'] = '1';
            $data['desLine'] = $remark;
        }
        $handler = new Handler('RED_PACKET',$data);
        $retData = $handler->api();
        Log::write('红包信息:'.$handler->getJson().'结果:', $retData, 'custody');

        if($retData['retCode']==Handler::SUCCESS) {
            return TRUE;
        }else{
            return FALSE;
        }
    }
}
