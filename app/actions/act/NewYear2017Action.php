<?php
use models\OddMoney;
use models\Invest;
use models\User;
use models\ActUserPrize;
use models\ActPrize;
use models\ActUserAddress;
use models\ActUserPacket;
use traits\handles\ModeHandle;
use traits\handles\DisplayHandle;
use factories\RedisFactory;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * NewYear2017Action
 * 2017年元旦活动
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class NewYear2017Action extends Action {
    use ModeHandle, DisplayHandle;

    public $actBegin = '2017-01-01 00:00:00';
    public $actEnd = '2017-02-06 00:00:00';
    public $packetes = [
        1=>['id'=>1, 'name'=>'5元现金红包', 'money'=>5], 
        2=>['id'=>2, 'name'=>'10元现金红包', 'money'=>10], 
        3=>['id'=>3, 'name'=>'20元现金红包', 'money'=>20]
    ];

    private function checkTime() {
        $beginTime = strtotime($this->actBegin);
        $endTime = strtotime($this->actEnd);
        if($beginTime>time()) {
            $this->backJson(['status'=>0, 'info'=>date('Y年m月d日', $beginTime).'零点开放！']);
        }
        if($endTime<time()) {
            $this->backJson(['status'=>0, 'info'=>'兑换已经结束！']);
        }
    }

    public function indexMode() {
        $user = $this->getUser();
        $address = false;
        $records = false;
        $packetMoney = 0;
        $num = 0;
        if($user) {
            $address = ActUserAddress::where('userId', $user->userId)->first();
            $records = ActUserPrize::where('userId', $user->userId)->with('prize')->get();
            $packetMoney = ActUserPacket::where('userId', $user->userId)->where('status', '<>', -1)->sum('money');
            $redis = RedisFactory::create();
            $userCardNum = $redis->get('user_card_num');
            if($userCardNum) {
                $userCardNum = unserialize($userCardNum);
            } else {
                $userCardNum = [];
            }
            if(isset($userCardNum[$user->userId])) {
                $num = $userCardNum[$user->userId];
            }
        }

        $prizes = ActPrize::whereRaw('1=1')->get()->toArray();
        foreach ($prizes as $key => $prize) {
            $prize['level'] = $this->getLevel($prize['prizeCash']);
            $prizes[$key] = $prize;
        }
        $redis = RedisFactory::create();
        $levels = [];
        for($i=0; $i<6; $i++) {
            $result = $redis->get('exnum_level'.($i+1));
            $levels[$i+1] = $result?$result:0;
        }

        $exchangePrizes = ActUserPrize::with('prize', 'user')->where('status', '<>', -1)->orderBy('addtime', 'desc')->limit(20)->get();

        $this->display('newyear2017', [
            'user'=>$user, 
            'address'=>$address, 
            'prizes'=>$prizes, 
            'records'=>$records, 
            'exchangePrizes'=>$exchangePrizes,
            'packetMoney'=>$packetMoney==null?0:$packetMoney,
            'num'=>$num,
            'levels'=>$levels
        ]);
    }

    /**
     * 抽奖
     * @return mixed
     */
    public function lotteryDrawMode() {
        $this->checkTime();
        $user = $this->getUser();
        if(!$user) {
            $this->backJson([
                'status'=>0,
                'info'=>'请先登录！'
            ]);
        }
        $redis = RedisFactory::create();
        $userCardNum = $redis->get('user_card_num');
        if($userCardNum) {
            $userCardNum = unserialize($userCardNum);
        } else {
            $userCardNum = [];
        }
        if(isset($userCardNum[$user->userId])&&$userCardNum[$user->userId]>0) {
            $userCardNum[$user->userId] --;
            $redis->set('user_card_num', serialize($userCardNum));
        } else {
            $this->backJson([
                'status'=>0,
                'info'=>'您没有抽奖机会！'
            ]);
        }
        if($this->isWin()) {
            $prizeID = $this->getPrize();
            if($prizeID) {

                $packet = new ActUserPacket();
                $packet->userId = $user->userId;
                $packet->money = $this->packetes[$prizeID]['money'];
                $packet->save();
                $this->backJson([
                    'status'=>1,
                    'info'=>'恭喜您获得'.$packet->money.'元现金红包！',
                    'packet'=>$this->packetes[$prizeID]
                ]);
            } else {
                $this->backJson([
                    'status'=>1,
                    'info'=>'感谢参与！',
                    'packet'=>['name'=>'感谢参与！', 'id'=>0, 'money'=>0]
                ]);
            }
        } else {
            $this->backJson([
                'status'=>1,
                'info'=>'感谢参与！',
                'packet'=>['name'=>'感谢参与！', 'id'=>0, 'money'=>0]
            ]);
        }
    }

    /**
     * 是否中奖
     * @return boolean
     */
    public function isWin() {
        $num = rand(1, 101);
        if($num%3==0) {
            return true;
        } 
        return false;
    }

    /**
     * 获取的奖品ID
     * @return mixed 奖品ID，无奖品返回false
     */
    public function getPrize() {
        $packetes = $this->packetes;
        $id = rand(1, 3);
        $prizeId = false;
        while (!$prizeId = $this->hasPrize($id)) {
            unset($packetes[$id]);
            $keys = array_keys($packetes);
            if(count($keys)>0) {
                $id = $keys[0];
            } else {
                break;
            }
        }
        return $prizeId;
    }

    /**
     * 是否还有奖品
     * @param  integer  $id  奖品ID
     * @return boolean
     */
    public function hasPrize($id) {
        $redis = RedisFactory::create();
        if($redis->lPop('prize'.$id)) {
            return $id;
        } else {
            return false;
        }
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

        if($user->imiMoney<$prize->prizeCash) {
            $this->backJson(['status'=>0, 'info'=>'小微币不足，兑换失败！']);
        }

        $list = ActUserPrize::with('prize')->where('userId', $user->userId)->where('status', '<>', '-1')->get();
        if(count($list)>=3) {
            $this->backJson(['status'=>0, 'info'=>'每个帐户仅限兑换3次，兑换失败！']);
        }
        $isExchange = false;
        $isSameType = false;
        foreach ($list as $userPrize) {
            if($userPrize->prizeId==$prizeId) {
                $isExchange = true;
                break;
            }
            if($prize->prizeCash==$userPrize->prize->prizeCash) {
                $isSameType = true;
                break;
            }
        }
        if($isExchange) {
            $this->backJson(['status'=>0, 'info'=>'您已经兑换过该礼品，兑换失败！']);
        }
        if($isSameType) {
            $this->backJson(['status'=>0, 'info'=>'您已经兑换过同档次的礼品，兑换失败！']);
        }

        $level = $this->getLevel($prize->prizeCash);
        $redis = RedisFactory::create();
        $num = 0;
        if($level) {
            $num = $redis->get('exnum_level'.$level);
        }

        if($num==0) {
            $this->backJson(['status'=>0, 'info'=>'奖品已无剩余！']);
        }

        $userPrize = new ActUserPrize();
        $userPrize->prizeId = $prize->id;
        $userPrize->userId = $userId;
        $userPrize->status = 1;
        $userPrize->addtime = date('Y-m-d H:i:s');

        if($userPrize->save()) {
            $redis->decr('exnum_level'.$level);

            User::where('userId', $userId)->update([
                'imiFreezeMoney'=>DB::raw('imiFreezeMoney+'.$prize->prizeCash),
                'imiMoney'=>DB::raw('imiMoney-'.$prize->prizeCash)
            ]);

            Flash::success('申请成功！');
            $this->backJson(['status'=>1, 'info'=>'申请成功！']);
        }
    }

    /**
     * 获取兑换档次
     * @return integer 档次KEY
     */
    public function getLevel($money) {
        if($money==500000) {
            return 1;
        }
        if($money==200000) {
            return 2;
        }
        if($money==100000) {
            return 3;
        }
        if($money==50000) {
            return 4;
        }
        if($money==10000) {
            return 5;
        }
        if($money==5000) {
            return 6;
        }
        return 0;
    }

    /**
     * 取消兑换奖品
     * @return mixed
     */
    public function deleteExchangeMode() {
        $this->checkTime();
        $user = $this->getUser();
        if(!$user) {
            $this->backJson([
                'status'=>0,
                'info'=>'请先登录！'
            ]);
        }
        $userId = $user->userId;
        $recordId = intval($this->getPost('recordId', 0));
        $record = ActUserPrize::find($recordId);
        if(!$record) {
            $this->backJson(['status'=>0, 'info'=>'兑换记录不存在！']);
        }
        /*if ($record['status']==1) {
            $this->backJson(['status'=>0, 'info'=>'兑换已审核，请联系客服修改！']);
        }*/
        if ($record->status==2) {
            $this->backJson(['status'=>0, 'info'=>'兑换已发货，请联系客服！']);
        }
        if ($record->status==-1) {
            $this->backJson(['status'=>0, 'info'=>'兑换审核失败，请联系客服！']);
        }

        $prize = $record->prize;

        $status = $record->delete();
        if($status) {
            User::where('userId', $userId)->update([
                'imiFreezeMoney'=>DB::raw('imiFreezeMoney-'.$prize->prizeCash),
                'imiMoney'=>DB::raw('imiMoney+'.$prize->prizeCash)
            ]);
            
            $level = $this->getLevel($prize->prizeCash);
            $redis = RedisFactory::create();
            $redis->incr('exnum_level'.$level);

            Flash::success('取消成功！');
            $this->backJson(['status'=>1, 'info'=>'取消成功！']);
        }
    }

    /**
     * 获取奖品信息ajax
     * @return mixed
     */
    public function getActPrizeMode() {
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
        $rdata = [];
        if($prize) {
            $rdata['cash'] = $user->imiMoney;
            $rdata['prize'] = $prize;
            $rdata['status'] = 1;
            $this->backJson($rdata);
        } else {
            $rdata['info'] = '奖品不存在！';
            $rdata['status'] = 0;
            $this->backJson($rdata);
        }
    }
}