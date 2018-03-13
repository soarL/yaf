<?php
use models\User;
use models\Sms;
use forms\app\RegisterForm;
use factories\RedisFactory;
use helpers\NetworkHelper;
use Yaf\Registry;
use tools\DuoZhuan;
use models\ActPrize;
use models\ActUserPrize;
use models\OddMoney;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * ActivityController
 * 活动接口
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class ActivityController extends Controller {

    public function dzRegisterAction() {
        $params = $this->getAllPost();
        $params['pm_key'] = 'duozhuan201706';
        $form = new RegisterForm($params);
        if($form->register()) {
            $channel = isset($params['channel'])?$params['channel']:'';
            $result = DuoZhuan::actReg($params['phone'], 2, $channel);
            $result = json_decode($result, true);

            $redis = RedisFactory::create();
            $redis->sAdd('dz_act_users', $params['phone']);

            $rdata['status'] = 1;
            $rdata['msg'] = '注册成功';
            $rdata['data']['send'] = $result;
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = $form->posError();
            $this->backJson($rdata);
        }
    }

    public function dzSmsAction() {
        $params = $this->getAllPost();

        $rdata = [];
        $count = User::whereRaw('phone=? or username=?', [$params['phone'], $params['phone']])->count();
        if($count>0) {
            $rdata['status'] = 0;
            $rdata['msg'] = '手机号已经注册！';
            $this->backJson($rdata);
        }

        $data = [];
        $data['userId'] = '';
        $data['phone'] = $params['phone'];
        $data['msgType'] = $params['msgType'];
        $data['code'] = Sms::generateCode(Sms::CODE_LENGTH);
        $data['params'] = [$data['code'],Sms::$msg[$data['msgType']],15];
        $result = Sms::send($data);

        if($result['status']==1) {
            $rdata['status'] = 1;
            $rdata['msg'] = '发送成功';
            $rdata['data']['code'] = '';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = $result['info'];
            $this->backJson($rdata);
        }
    }

    public function dzClickAction() {
        $channel = $this->getQuery('channel', '');
        $from = $this->getQuery('from', 1);

        setcookie('dz_channel', $channel, time()+3600, '/', WEB_DOMAIN);

        $result = DuoZhuan::actClick($from, $channel);
        $result = json_decode($result, true);

        $rdata['status'] = 1;
        $rdata['msg'] = '访问成功！';
        $rdata['data']['send'] = $result;
        $this->backJson($rdata);
    }


    public function act201706Action() {
        $prizes = ActPrize::whereRaw('1=1')->get();
        
        $user = $this->getUser();

        $money = 0;
        if($user) {
            $money = OddMoney::where('userId', $user->userId)
                ->whereHas('odd', function($q){
                    $q->where('oddBorrowStyle', 'month')->whereIn('oddBorrowPeriod', [6, 12, 24]);
                })
                ->where('time', '>=', '2017-06-21 00:00:00')
                ->where('time', '<', '2017-07-06 00:00:00')
                ->where('type', 'invest')
                ->sum('money');
        }

        $list = [];
        foreach ($prizes as $prize) {
            $row = ['id'=>$prize->id, 'num'=>$prize->num];
            if($prize->prizeCash>$money||$prize->num==0) {
                $row['abled'] = false;
            } else {
                $row['abled'] = true;
            }
            $list[] = $row;
        }

        // $money = 2110980;

        $money = strval(intval($money));
        $money = str_repeat('0', 9-strlen($money)) . $money;
        $money1 = '';
        $money2 = '';
        for ($i=0; $i < strlen($money); $i++) {
            if($i==3 || $i==6) {
                $money2 .= ',' . $money[$i];
                $money1 .= '<span class="doubleLeft">'.$money[$i].'</span>';
            } else {
                $money2 .= $money[$i];
                $money1 .= '<span>'.$money[$i].'</span>';
            }
        }

        $rdata['status'] = 1;
        $rdata['info'] = '访问成功！';
        $rdata['data']['prizes'] = $list;
        $rdata['data']['money1'] = $money1;
        $rdata['data']['money2'] = $money2;
        $rdata['data']['isLogin'] = $user?true:false;
        $this->backJson($rdata);
    }

    public function exchange201706Action() {
        $rdata['status'] = 0;
        $rdata['info'] = '活动已关闭！';
        $this->backJson($rdata);

        $prize = $this->getPost('prize', 0);
        
        $user = $this->getUser();

        $prize = ActPrize::where('id', $prize)->first();

        if(time()<strtotime('2017-06-21 00:00:00')) {
            $rdata['status'] = 0;
            $rdata['info'] = '活动未开始！';
            $this->backJson($rdata);
        }

        if(time()>strtotime('2017-07-09 00:00:00')) {
            $rdata['status'] = 0;
            $rdata['info'] = '活动已结束！';
            $this->backJson($rdata);
        }

        if(!$user) {
            $rdata['status'] = 0;
            $rdata['info'] = '请先登录！';
            $this->backJson($rdata);
        }

        if(!$prize) {
            $rdata['status'] = 0;
            $rdata['info'] = '奖品不存在！';
            $this->backJson($rdata);
        }

        if($prize->num==0) {
            $rdata['status'] = 0;
            $rdata['info'] = '奖品已被兑换完！';
            $this->backJson($rdata);
        }

        $money = OddMoney::where('userId', $user->userId)
            ->whereHas('odd', function($q){
                $q->where('oddBorrowStyle', 'month')->whereIn('oddBorrowPeriod', [6, 12, 24]);
            })
            ->where('time', '>=', '2017-06-21 00:00:00')
            ->where('time', '<', '2017-07-06 00:00:00')
            ->where('type', 'invest')
            ->sum('money');

        if($money<$prize->prizeCash) {
            $rdata['status'] = 0;
            $rdata['info'] = '您的投资额度不足！';
            $this->backJson($rdata);
        }

        $count = ActUserPrize::where('userId', $user->userId)->count();
        if($count==1) {
            $rdata['status'] = 0;
            $rdata['info'] = '您已经兑换过了！';
            $this->backJson($rdata);
        }

        $result = ActPrize::where('id', $prize->id)->update(['num'=>DB::raw('num-1')]);
        if($result==0) {
            $rdata['status'] = 0;
            $rdata['info'] = '兑换失败！';
            $this->backJson($rdata);
        }
        
        $data = [];
        $data['userId'] = $user->userId;
        $data['prizeId'] = $prize->id;
        $data['status'] = 1;
        $data['addtime'] = date('Y-m-d H:i:s');
        ActUserPrize::insert($data);

        $rdata['status'] = 1;
        $rdata['info'] = '兑换成功！';
        $this->backJson($rdata);
    }

    public function ajaxLoginAction() {
        $phone = $this->getPost('phone', '');
        $password = $this->getPost('password', '');

        $result = User::loginNormal($phone, $password, false);
        
        if($result['status']==1) {
            $rdata['status'] = 1;
            $rdata['info'] = '登录成功!';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = $result['info'];
            $this->backJson($rdata);
        }
    }
}

