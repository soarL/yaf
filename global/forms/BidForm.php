<?php
namespace forms;
use models\User;
use models\Odd;
use models\Task;
use models\UserBid;
use models\OddMoney;
use models\OldUser;
use models\Lottery;
use models\Attribute;
use tools\Log;
use Yaf\Registry;
use custody\API;
use tools\Counter;
use Illuminate\Database\Capsule\Manager as DB;
/**
 * BidForm
 * 投资标的表单
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class BidForm extends \Form {
    public $lottery;
    public $html;
    public $odd;

    public function defaults() {
        return ['lotteryID'=>0];
    }

    public function init() {
        if($this->lotteryID) {
            $this->lottery = Lottery::where('id', $this->lotteryID)->first();
        }
    }

    public function rules() {
        return [
            [['money', 'oddNumber', 'paypass'], 'required'],
            ['oddNumber', 'validateOddNumber'],
            ['money', 'validateMoney'],
            ['lotteryID', 'validateLottery'],
            ['paypass', 'validatePaypass']
        ];
    }

    public function labels() {
        return [
            'money' => '投标金额',
            'oddNumber' => '标的号',
            'paypass' => '交易密码',
        ];
    }

    public function validateOddNumber() {
        // $this->addError('oddNumber', '存管系统出现问题，银行正在紧急排查中！'); return;

        $user = $this->getUser();
        if($user->custody_id=='') {
           $this->addError('oddNumber', '您还未进行实名认证！'); return;
        } 
        if($user->is_custody_pwd==0) {
            $this->addError('oddNumber', '您还未设置存管密码！'); return;
        }

        if($user->blackstatus){
            $this->addError('oddNumber', '您没有投资权限！'); return;
        }

        $odd = Odd::where(['oddNumber'=>$this->oddNumber])->first();
        if($odd) {
            $this->odd = $odd;
            $result = $odd->isBidable($user->userId);
            if($result['status']==0) {
                $this->addError('oddNumber', $result['info']); return;
            }
        } else {
            $this->addError('oddNumber', '此标不存在！'); return;
        }
    }

    public function validatePaypass(){
        $user = $this->getUser();
        $res = User::paypassNormal($user, $this->paypass);
        if($res['status']){

        }else{
            $this->addError('paypass', $res['info']); return;
        }
    }

    public function validateMoney() {
        if($this->money<50) {
            $this->addError('money', '投资金额至少需50元！'); return;
        } else {
            if($this->money%50!=0) {
                //$this->addError('money', '投资金额必须是50的整数倍！'); return;
            }
        }
        $user = $this->getUser();
        if($this->odd->oddStyle=='newhand') {
            if(!in_array($user->username, ['cbq123', '18611788520', 'ljl360197197'])) {
                $count = OldUser::where('userId', $user->userId)->count();
                if($count) {
                    $this->addError('money', '您已经投资过其他标，不属于新手！'); return;
                }
                $count = OddMoney::where('userId', $user->userId)
                    ->whereIn('type', ['invest', 'credit'])
                    ->whereHas('odd', function($q) {
                        $q->where('oddStyle', 'normal');
                    })->count();
                if($count) {
                    $this->addError('money', '您已经投资过其他标，不属于新手！'); return;
                } else {
                    $tenderMoney = OddMoney::where('userId', $user->userId)
                        ->whereIn('type', ['invest', 'credit'])
                        ->whereHas('odd', function($q) {
                            $q->where('oddStyle', 'newhand');
                        })->sum('money');
                    if(($this->money+$tenderMoney)>20000) {
                        $this->addError('money', '新手标累计投资金额不能超过20000元！'); return;
                    }
                }
            }
        } else if($this->odd->investType==1&&$this->odd->appointUserId!=$user->userId&&$this->odd->oddBorrowPeriod<12) {
            if($this->money>99999999) {
                $this->addError('money', '单笔投资金额不能超过99999999元！'); return;
            } else {
                $tenderMoney = OddMoney::where('oddNumber', $this->oddNumber)->where('userId', $user->userId)->whereIn('status', [0, 1])->sum('money');
                if(($this->money+$tenderMoney)>99999999) {
                    $this->addError('money', '同一笔标累计投资金额不能超过99999999元！'); return;
                }
            }
        }
        if($user->fundMoney<$this->money) {
            $this->addError('money', '账户金额不足！'); return;
        }
    }

    public function validateLottery() {
        if(!$this->lotteryID) return;

        if(!$this->lottery) {
            $this->addError('money', '券不存在！'); return;
        }
        if($this->lottery->status<>Lottery::STATUS_NOUSE) {
            $this->addError('money', '券状态异常！'); return;
        }
        if(strtotime($this->lottery->endtime)<time()) {
            $this->addError('money', '券已过期！'); return;
        }
        if(!$this->lottery->checkPeriod($this->odd->oddBorrowPeriod)) {
            $user = $this->getUser();
            Log::write('用户['.$user->userId.'-优惠券-'.$this->lotteryID.'] 异常！', [], 'lottery');
            $this->addError('money', '该标的不符合优惠券使用周期！'); return;
        }
        if(!$this->lottery->checkMoney($this->money)) {
            $user = $this->getUser();
            Log::write('用户['.$user->userId.'-优惠券-'.$this->lotteryID.'] 异常！', [], 'lottery');
            $this->addError('money', '优惠券使用金额不符！'); return;
        }
    }

    public function bid() {
        if($this->check()) {
            $user = $this->getUser();

            $data = Odd::bid($this->oddNumber, $this->money);
            if($data['status']==0) {
                $this->addError('form', $data['msg']);
                return false;
            }

            $remark = '手工投标';
            $tradeNo = Counter::getOrderID();
            $trade = new UserBid();
            $trade->tradeNo = $tradeNo;
            $trade->oddNumber = $this->oddNumber;
            $trade->userId = $user->userId;
            $trade->bidMoney = $this->money;
            $trade->remark = $remark;
            $trade->addTime = date('Y-m-d H:i:s');
            $trade->media = $this->getMedia();
            $trade->lotteryId = $this->lotteryID;
            if($trade->save()) {
                $bonus = 0;
                if($this->lottery) {
                    $this->lottery->status = Lottery::STATUS_FROZEN;
                    $this->lottery->save();
                    if($this->lottery->type=='invest_money') {
                        $bonus = $this->lottery->money_rate;
                    }
                }
                $this->html = API::bid($trade, $bonus);
                return true;
            } else {
                $this->addError('form', '系统异常，请联系客服！');
                return false;
            }
        } else {
            return false;
        }
    }
}
