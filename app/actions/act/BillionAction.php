<?php

use models\BillionPrize;
use models\OddMoney;
use traits\handles\ModeHandle;
use traits\handles\DisplayHandle;
use traits\ActCommon;
use models\User;
use Illuminate\Database\Capsule\Manager as DB;
use models\MoneyLog;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/22 0022
 * Time: 9:15
 */
class BillionAction extends Action
{
    use ModeHandle, DisplayHandle, ActCommon;

    public $actBegin = '2017-07-25 00:00:00';
    public $actEnd = '2017-08-06 00:00:00';

    /**
     *活动时间
     */
    private function checkTime() {
        $beginTime = strtotime($this->actBegin);
        $endTime = strtotime($this->actEnd);
        if($beginTime>time()) {
            $this->backJson(['status'=>0, 'info'=>date('Y年m月d日', $beginTime).'零点开放！']);
        }
        if($endTime<time()) {
            $this->backJson(['status'=>0, 'info'=>'活动已经结束！']);
        }
    }

    public function indexMode(){
        echo '1111';
    }


    /**
     * 进行抽奖
     */
    public function getPrizeMode(){
//        $this->checkTime();
//        $user = $this->getUser();
//        if(!$user) {
//            $this->backJson([
//                'status'=>0,
//                'info'=>'请先登录！'
//            ]);
//        }
        $money = OddMoney::where('type','invest')->where('userId',1000012020)->where('time','>',date('Y-m-d : 00:00:00',time()))->orderBy('money','desc')->value('money');
        if($money){
            $probability = $this->judgePrize($money);
            $prizeResult = $this->getPrizeResult($probability);
            if($prizeResult['prize'] == '提现券1张'){
//                $data = [];
//                $data['type'] = 'withdraw';
//                $data['useful_day'] = '';
//                $data['remark'] = '[活动]破10亿';
//                $data['userId'] = $user->userId;
//                $status = Lottery::generate($data);
//                $params=[];
//                  $params['prizeId'] = $prizeResult['prizeId'];
//                  BillionPrize::addOne($params,$user);

            }else{
//                $data = [];
//                $data['money'] = $prizeResult['money'];
//                $data['userId'] = $user->userId;
//                $data['remark'] = '破10亿活动红包抽奖获得'.$prizeResult;
//                $status = API::addMoney($data);
//                if($status) {
//                    User::where('userId', $user->userId)->update([
//                        'fundMoney'=>DB::raw('fundMoney+'.$prizeResult['money'])
//                    ]);
//
//                    $tradeNo = date('Ymd').substr(md5($user->userId), 8, 16);
//
//                    $log = [];
//                    $log['serialNumber'] = $tradeNo;
//                    $log['type'] = 'act_money';
//                    $log['mode'] = 'in';
//                    $log['mvalue'] = $prizeResult['money'];
//                    $log['remark'] = '破10亿活动红包抽奖获得'.$prizeResult;
//
//                    $user->fundMoney = $user->fundMoney + $prizeResult['money'];
//                    MoneyLog::addOne($log, $user);
//                    $params=[];
//                    $params['prizeId'] = $prizeResult['prizeId'];
//                    BillionPrize::addOne($params,$user);
//
//                }
            }

            $this->backJson([
                'status'=>1,
                'info'=>'恭喜您获得'.$prizeResult['prize'],
            ]);

        }else {
            $this->backJson([
                'status' => 0,
                'info' => '很抱歉，您不具备抽奖资格！'
            ]);
        }

    }

    /**
     **随机抽奖算法
     * @param $arr
     * @return int|string
     */
    private function getRandResult($arr) {
        $result = '';
        $arrSum = array_sum($arr);
        foreach ($arr as $key => $value) {
            $randNum = mt_rand(1, $arrSum);
            if ($randNum <= $value) {
                $result = $key;
                break;
            } else {
                $arrSum -= $value;
            }
        }
        unset ($arr);
        return $result;
    }

    /**
     **获取抽奖结果
     * @param $probability
     * @return mixed
     */
    private function  getPrizeResult($probability){
        $prize_arr = array(
            array('id'=>1,'money'=>2,'prize'=>'提现券1张','probability'=>$probability['withdraw']),
            array('id'=>2,'money'=>5,'prize'=>'5元现金','probability'=>$probability['five']),
            array('id'=>3,'money'=>10,'prize'=>'10元现金','probability'=>$probability['ten']),
            array('id'=>4,'money'=>20,'prize'=>'20元现金','probability'=>$probability['twenty']),
            array('id'=>5,'money'=>50,'prize'=>'50元现金','probability'=>$probability['fifty']),
            array('id'=>6,'money'=>100,'prize'=>'100元现金','probability'=>$probability['hundred']),
        );
        $arr=[];
        foreach ($prize_arr as $key => $val) {
            $arr[$val['id']] = $val['probability'];
        }
        $rid = $this->getRandResult($arr); //获取奖项id
        $result['prizeId'] = $prize_arr[$rid-1]['id'];
        $result['money'] = $prize_arr[$rid-1]['money'];
        $result['prize'] = $prize_arr[$rid-1]['prize'];
        return $result;
    }



    /**
     *  判断用户抽奖权限
     * @param $money
     * @return array
     */
    private function judgePrize($money){

        $probability = [];
        $num = DB::table('act_billion_prize')->get(['prizeName','num']);
        $number=[];
        $number['withdraw'] = $num[0]->num;
        $number['five'] = $num[1]->num;
        $number['ten'] = $num[2]->num;
        $number['twenty'] = $num[3]->num;
        $number['fifty'] = $num[4]->num;
        $number['hundred'] = $num[5]->num;
        if($money<10000) {
            $this->backJson([
                'status'=>0,
                'info'=>'很抱歉，您不具备抽奖资格！'
            ]);
        } else if($money>=10000 && $money<20000) {

            $probability = ['withdraw'=>$number['withdraw'],'five'=>$number['five'],'ten'=>0,'twenty'=>0,'fifty'=>0,'hundred'=>0];

        } else if($money>=20000 && $money<50000) {

            $probability = ['withdraw'=>$number['withdraw'],'five'=>$number['five'],'ten'=>$number['ten'],'twenty'=>0,'fifty'=>0,'hundred'=>0];

        } else if($money>=50000 && $money<100000) {

            $probability = ['withdraw'=>0,'five'=>$number['five'],'ten'=>$number['ten'],'twenty'=>$number['twenty'],'fifty'=>0,'hundred'=>0];

        } else if($money>=100000 && $money<200000) {

            $probability = ['withdraw'=>0,'five'=>0,'ten'=>$number['ten'],'twenty'=>$number['twenty'],'fifty'=>$number['fifty'],'hundred'=>0];

        } else if($money>=200000) {

            $probability = ['withdraw'=>0,'five'=>0,'ten'=>0,'twenty'=>$number['twenty'],'fifty'=>$number['fifty'],'hundred'=>$number['hundred']];

        }
        return $probability;
    }
}