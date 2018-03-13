<?php
use Yaf\Registry;
use models\OddMoney;
use models\Invest;
use models\User;
use models\Lottery;
use models\ActUserPrize;
use models\ActPrize;
use models\ActUserAddress;
use models\ActUserPacket;
use models\Sms;
use forms\app\RegisterForm;
use factories\RedisFactory;
use traits\ActCommon;
use traits\handles\ModeHandle;
use traits\handles\DisplayHandle;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Redpack201706Action
 * 201706红包活动
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Activity201709Action extends Action {
    use ModeHandle, DisplayHandle, ActCommon;

    public $actBegin = '2017-08-24 00:00:00';
    public $actEnd = '2017-09-24 00:00:00';
    public $exchangeBegin = '2017-08-24 00:00:00';
    public $exchangeEnd = '2017-09-26 00:00:00';



    private function checkTime() {
        $beginTime = strtotime($this->exchangeBegin);
        $endTime = strtotime($this->exchangeEnd);
        if($beginTime>time()) {
            $this->backJson(['status'=>0, 'info'=>date('Y年m月d日', $beginTime).'零点开放！']);
        }
        if($endTime<time()) {
            $this->backJson(['status'=>0, 'info'=>'兑换已经结束！']);
        }
    }

    public function indexMode() {
        $from = $this->getPost('from', 1);
        $channel = $this->getPost('channel', 1);
        $siteinfo = Registry::get('siteinfo');

        $redis = RedisFactory::create();
        $redis->lpush('activity201709_act_view', json_encode(['time'=>date('Y-m-d H:i:s'), 'ip'=>$siteinfo['clientIp'], 'from'=>$from, 'channel'=>$channel]));

        $user = $this->getUser();
        $address = false;
        $records = [];

        $money = 0;
        if($user) {
            $money = OddMoney::where('userId', $user->userId)
                ->whereHas('odd', function($q){
                    $q->where('oddBorrowStyle', 'month')->whereIn('oddBorrowPeriod', [12, 24]);
                })
                ->where('time', '>=', $this->actBegin)
                ->where('time', '<', $this->actEnd)
                ->where('type', 'invest')
                ->sum('money');

            $address = ActUserAddress::where('userId', $user->userId)->first();
            $records = ActUserPrize::where('userId', $user->userId)->with('prize')->get();
        }

        $userRecords = [];
        foreach ($records as $record) {
            $row = ['prize'=>$record->prize->prizeName, 'time'=>_date('m月d日', $record->addtime)];
            $userRecords[] = $row;
            $money -= $record->prize->prizeCash;
        }

        $exchangePrizes = ActUserPrize::with('prize', 'user')->where('status', '<>', -1)->orderBy('addtime', 'desc')->limit(20)->get();
        $exchangeList = [];
        foreach ($exchangePrizes as $exchangePrize) {
            $row = ['prize'=>$exchangePrize->prize->prizeName, 'username'=>_hide_username($exchangePrize->user->username)];
            $exchangeList[] = $row;
        }

        $prizes = ActPrize::whereRaw('1=1')->get();
        $groups = [];
        foreach ($prizes as $key =>$prize) {
            $groups[$key]['name'] = $prize->prizeName;
            $groups[$key]['id'] = $prize->id;
            $groups[$key]['abled'] = true;
            if($prize->prizeCash>$money || $prize->num==0) {
                $groups[$key]['abled']  = false;
            }
        }

        $money = strval(intval($money));
        $money = str_repeat('0', 8-strlen($money)) . $money;
        $money1 = '';
        $money2 = '';
        for ($i=0; $i < strlen($money); $i++) {
            if($i==1 || $i==4) {
                $money2 .= $money[$i] . ',';
                $money1 .= '<span class="rightSpace">'.$money[$i].'</span>';
            } else {
                $money2 .= $money[$i];
                $money1 .= '<span>'.$money[$i].'</span>';
            }
        }

        $rdata['status'] = 1;
        $rdata['info'] = '访问成功！';
        $rdata['data']['groups'] = $groups;
        $rdata['data']['money1'] = $money1;
        $rdata['data']['money2'] = $money2;
        $rdata['data']['isLogin'] = $user?true:false;
        $rdata['data']['address'] = $address;
        $rdata['data']['records'] = $userRecords;
        $rdata['data']['exchangePrizes'] = $exchangeList;
        $this->backJson($rdata);
    }

    /**
     * 兑换奖品
     * @return mixed
     */
    public function exchangeMode() {
        $this->checkTime();
        
        $user = $this->getUser();
        if(!$user) {
            $this->backJson([
                'status'=>0,
                'info'=>'请先登录！'
            ]);
        }
        $userId = $user->userId;
        $prizeId = intval($this->getPost('prizeId', 0));

        if(!ActUserAddress::isUserSet($userId)) {
            $this->backJson(['status'=>-1, 'info'=>'您还未设置收货地址，请先设置！']);
        }

        $prize = ActPrize::find($prizeId);
        if(!$prize) {
            $this->backJson(['status'=>0, 'info'=>'奖品不存在！']);
        }

        $list = ActUserPrize::with('prize')->where('userId', $user->userId)->get();

        $where = 'oddBorrowPeriod in (12, 24) and ((oddBorrowPeriod=12 and oddYearRate <= 0.18 and oddReward <= 0) or (oddBorrowPeriod=24 and oddYearRate <= 0.19 and oddReward <= 0))';

        $money = OddMoney::where('userId', $user->userId)
            ->whereHas('odd', function($q) use($where) {
                $q->where('oddBorrowStyle', 'month')->whereRaw($where);
            })
            ->where('time', '>=', $this->actBegin)
            ->where('time', '<', $this->actEnd)
            ->where('type', 'invest')
            ->sum('money');
        $money = intval($money);
        foreach ($list as $item) {
            $money -= $item->prize->prizeCash;
        }
        if($money<$prize->prizeCash) {
            $this->backJson(['status'=>0, 'info'=>'投资额度不足，兑换失败！']);
        }

        try {
            ActPrize::where('id', $prizeId)->update(['num'=>DB::raw('num-1')]);
            $userPrize = new ActUserPrize();
            $userPrize->prizeId = $prize->id;
            $userPrize->userId = $userId;
            $userPrize->status = 1;
            $userPrize->addtime = date('Y-m-d H:i:s');
            if($userPrize->save()) {
                $this->backJson(['status'=>1, 'info'=>'兑换成功！']);
            }
            $this->backJson(['status'=>1, 'info'=>'兑换成功！']);
        } catch(\Exception $e) {
            $this->backJson(['status'=>0, 'info'=>'兑换失败！']);
        }
    }

    /**
     *注册
     */
    public function registerMode() {
        $params = $this->getAllPost();
        $params['pm_key'] = 'toutiao201706';
        if(!isset($params['username']) && isset($params['phone']) ) {
            $params['username'] = $params['phone'];
        }
        $form = new RegisterForm($params);

        if($form->register()) {
            $rdata['status'] = 1;
            $rdata['msg'] = '注册成功';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = $form->posError();
            $this->backJson($rdata);
        }
    }

    public function smsMode() {
        $params = $this->getAllPost();

        $redis = RedisFactory::create();
        $redis->sAdd('rp_act_click', $params['phone']);

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

}