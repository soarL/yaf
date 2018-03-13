<?php
namespace plugins\ancun;

use Yaf\Registry;
use models\AncunData;
use tools\Log;

class Ancun {
    private static $config;
    private $url;
    private $partnerKey;
    private $secret;
    private $data;
    private $files = [];
    private $itemKey = '';
    private $flowNo = false;
    private $recordNo = false;
    private $certSec = false;
    private $flow = 0;
    private $obj;
    private $userId;
    private $tradeNo;
    private $items;
    private $path;
    private $user;

    public function __construct($obj, $type, $flow=0) {
        $this->obj = $obj;
        $this->type = $type;
        $this->flow = $flow;

        $configFile = Registry::get('config')->ancun->config;
        $this->url = Registry::get('config')->ancun->url;
        $this->path = Registry::get('config')->ancun->path;
        // $this->path = APP_PATH.'/public/protocols/';
        if(!self::$config&&$configFile) {
            self::$config = require(__DIR__.'/'.$configFile.'.php');
        }
        if(self::$config) {
            $this->partnerKey = self::$config['key'];
            $this->secret = self::$config['secret'];
            $this->items = self::$config['items'];
        }
    }

    public function send() {
        $isOpen = Registry::get('config')->ancun->open;
        if(!$isOpen) {
            return [
                'code'=> '000000',
                'msg' => '功能未开启',
                'data' => null,
                'logno' => null,
                'serversion' => null
            ];
        }

        $data = [];
        if($this->type=='tender') {
            if($this->flow==0) {
                $data = $this->tender();
            } else if($this->flow==1) {
                $data = $this->tenderPro();
            } else if($this->flow==2) {
                $data = $this->repay();
            }
        } else if($this->type=='user') {
            if($this->flow==0) {
                $data = $this->register();
            } else if($this->flow==1) {
                $data = $this->custody();
            }
        } else if($this->type=='assign') {
            if($this->flow==0) {
                $data = $this->assign();
            } else if($this->flow==1) {
                $data = $this->assignPro();
            }
        } else if($this->type=='recharge') {
            if($this->flow==0) {
                $data = $this->recharge();
            }
        } else if($this->type=='withdraw') {
            if($this->flow==0) {
                $data = $this->withdraw();
            }
        }
        return $data;
    }

    /**
     * 投标
     * @return array 结果信息
     */
    public function tender() {
        $debt = $this->obj;
        $investor = $debt->user;
        $odd = $debt->odd;
        $borrower = $odd->user;

        $this->user = $debt->user;
        $this->userId = $debt->userId;
        $this->tradeNo = $debt->tradeNo;

        $data = [];
        // 姓名
        $data['name'] = $investor->name;
        // 身份证号
        $data['idCardNo'] = $investor->cardnum;
        // 项目名称
        $data['prjName'] = $odd->oddTitle;
        // 项目编号
        $data['prjNo'] = $odd->oddNumber;
        // 年利率
        $data['loanRate'] = ($odd->oddYearRate*100).'%';
        // 项目金额
        $data['loanAmount'] = $odd->oddMoney.'元';
        // 最小认购金额
        $data['startInvAmount'] = $odd->startMoney.'元';
        // 投资期限
        $data['investPeriod'] = $odd->getPeriod();
        // 还款方式
        $data['repayType'] = $odd->getRepayType();
        // 发标时间
        $data['prjReleaseTime'] = $odd->oddTrialTime;

        // 借款用途
        $data['loanDescr'] = $odd->oddUse;
        
        // 募集截止时间
        $data['raiseCloseTime'] = date('Y-m-d H:i:s', strtotime($odd->oddTrialTime)+$odd->oddBorrowValidTime*24*60*60);
        // 募集结束时间
        $data['raiseEndTime'] = $odd->fullTime;
        // 审核通过时间
        $data['auditPassTime'] = $odd->oddRehearTime;
        // 借款人姓名
        $data['borrowerName'] = $borrower->name;
        // 借款人身份证号
        $data['borrowerIdCardNo'] = $borrower->cardnum;
        
        // 投资金额
        $data['investAmount'] = $debt->money.'元';
        // 交易流水号
        $data['investNo'] = $debt->tradeNo;
        // 购买时间
        $data['buyTime'] = $debt->time;
        // 支付成功时间
        $data['paySucTime'] = $debt->time;
        

        $this->data = $data;

        $this->itemKey = $this->items['tender']['key'];
        $this->flowNo = $this->items['tender']['flowNo'][0];

        return $this->save();
    }

    /**
     * 投资合同
     */
    public function tenderPro() {
        $debt = $this->obj;
        $odd = $debt->odd;

        if($odd->oddType=='danbao') {
            $this->certSec = true;
        }

        $fileName = $debt->generateProtocol(false);
        $file = $this->path.$fileName;

        $this->user = $debt->user;
        $this->userId = $debt->userId;
        $this->tradeNo = $debt->tradeNo;

        $fileKey = str_replace('.pdf', '', $fileName);
        $this->files[$fileKey] = $file;

        $this->itemKey = $this->items['tender']['key'];
        $this->flowNo = $this->items['tender']['flowNo'][1];

        $this->data = ['append-data'=>'noUse'];

        return $this->save();
    }

    /**
     * 回款
     */
    public function repay() {
        $invest = $this->obj;
        $debt = $invest->oddMoney;
        $data = [];
        
        $this->user = $debt->user;
        $this->userId = $debt->userId;
        $this->tradeNo = $debt->tradeNo;

        // 实际回款日期
        $data['actRepayTime'] = $invest->operatetime;
        // 实际回款金额
        $data['actRepayAmount'] = $invest->realAmount;

        $this->data = $data;

        $this->itemKey = $this->items['tender']['key'];
        $this->flowNo = $this->items['tender']['flowNo'][2];

        return $this->save();
    }

    public function assign() {
        $debt = $this->obj;
        $odd = $debt->odd;
        $parent = $debt->parent;
        $purchaser = $debt->user;
        $seller = $parent->user;
        $borrower = $odd->user;
        $pcrtr = $debt->pcrtr;

        $this->user = $debt->user;
        $this->userId = $debt->userId;
        $this->tradeNo = $debt->tradeNo;

        $data = [];
        // 转让人姓名
        $data['transferName'] = $seller->name;
        // 转让人用户名
        $data['transferUserName'] = $seller->username;
        // 转让人身份证号
        $data['transferIdCardNo'] = $seller->cardnum;
        // 受让人姓名
        $data['transfereeName'] = $purchaser->name;
        // 受让人用户名
        $data['transfereeUserName'] = $purchaser->username;
        // 受让人身份证号
        $data['transfereeIdCardNo'] = $purchaser->cardnum;

        // 原债权项目名称
        $data['PrjName'] = $odd->oddTitle;
        // 原债权项目编号
        $data['PrjNo'] = $odd->oddNumber;
        // 债权本金
        $data['debtCapital'] = $odd->oddMoney.'元';
        // 债权期限
        $data['debtPeriod'] = $odd->getPeriod();
        // 剩余期限
        $data['remainPeriod'] = $pcrtr->getRemainDay(true).'天';
        // 债务人姓名
        $data['debtor'] = $borrower->name;
        // 债务人身份证号
        $data['debtorIdCardNo'] = $borrower->cardnum;

        // 项目名称
        $data['transferPrjName'] = '债权转让' . $pcrtr->getSN() . '号';
        // 项目编号
        $data['transferPrjNo'] = $pcrtr->getSN();
        // 债权收益率
        $data['loanRate'] = ($odd->oddYearRate*100).'%';
        // 转让金额
        $data['transferAmount'] = $debt->money.'元';
        // 转让时间
        $data['transferTime'] = $pcrtr->addtime;
        // 受让价款
        $data['transfereePrice'] = $debt->money.'元';

        // 转让费
        $data['transferPoundage'] = $pcrtr->serviceMoney.'元';
        // 受让时间
        $data['transfereeTime'] = $debt->time;
        // 受让债权期限
        $data['transfereeDebtPeriod'] = $data['remainPeriod'];
        // 收益支付方式
        $data['incomePaymentWay'] = $odd->getRepayType();

        $this->data = $data;

        $this->itemKey = $this->items['assign']['key'];
        $this->flowNo = $this->items['assign']['flowNo'][0];

        return $this->save();
    }

    public function assignPro() {
        $debt = $this->obj;

        $this->user = $debt->user;
        $this->userId = $debt->userId;
        $this->tradeNo = $debt->tradeNo;

        $fileName = $debt->generateProtocol(false);
        
        $file = $this->path.$fileName;

        $fileKey = str_replace('.pdf', '', $fileName);
        $this->files[$fileKey] = $file;
        
        $this->itemKey = $this->items['assign']['key'];
        $this->flowNo = $this->items['assign']['flowNo'][1];

        $this->data = ['append-data'=>'noUse'];

        return $this->save();
    }

    public function recharge() {
        $trade = $this->obj;
        $user = $trade->user;

        $this->user = $trade->user;
        $this->userId = $trade->userId;
        $this->tradeNo = $trade->serialNumber;

        $data = [];
        // 用户名
        $data['rPfAccount'] = $user->username;
        // 姓名
        $data['rUserName'] = $user->name;
        // 身份证号
        $data['rIdCardNo'] = $user->cardnum;
        // 充值金额
        $data['rechargeAmount'] = $trade->money.'元';
        // 充值渠道
        $data['rechargeChannel'] = $trade->getPayTypeName();
        // 充值方式
        $data['rechargeType'] = $trade->getPayWayName();
        
        // 操作时间
        $data['rechargeOperateTime'] = $trade->time;
        // 充值成功时间
        $data['rechargeSucTime'] = $trade->validTime;
        // 支付流水号
        $data['rPaySerialNo'] = $trade->serialNumber;

        $this->data = $data;

        $this->itemKey = $this->items['recharge']['key'];
        $this->flowNo = $this->items['recharge']['flowNo'][0];

        return $this->save();
    }

    public function withdraw() {
        $trade = $this->obj;
        $user = $trade->user;

        $this->user = $trade->user;
        $this->userId = $trade->userId;
        $this->tradeNo = $trade->tradeNo;

        $data = [];
        // 用户名
        $data['wPfAccount'] = $user->username;
        // 姓名
        $data['wUserName'] = $user->name;
        // 身份证号
        $data['wIdCardNo'] = $user->cardnum;
        // 银行名称
        $data['wBankName'] = $trade->bankNum;
        // 提现金额
        $data['withdrawAmount'] = $trade->outMoney.'元';
        // 操作时间
        $data['withdrawOperateTime'] = $trade->addTime;
        // 提现成功时间
        $data['withdrawSucTime'] = $trade->validTime;

        $this->data = $data;

        $this->itemKey = $this->items['withdraw']['key'];
        $this->flowNo = $this->items['withdraw']['flowNo'][0];

        return $this->save();
    }

    public function register() {
        $user = $this->obj;

        $this->user = $user;
        $this->userId = $user->userId;
        $this->tradeNo = substr(md5($this->userId), 8, 16);

        $data = [];
        //用户名
        $data['pfUserName'] = $user->username;
        //手机号
        // $data['mobile'] = $user->phone;
        // 注册时间
        $data['regTime'] = $user->addtime;
        // 用户姓名
        $data['name'] = $user->name;
        // 身份证号
        $data['idCardNo'] = $user->cardnum;
        // 实名认证时间 流程改变
        $data['identityCertSucTime'] = $user->certificationTime==null?$user->addtime:$user->certificationTime;

        $this->data = $data;

        $this->itemKey = $this->items['user']['key'];
        $this->flowNo = $this->items['user']['flowNo'][0];

        // 附件 《小微时贷用户协议》
        $fileKey = 'user_protocol';
        $this->files[$fileKey] = $this->path.'user_protocol.pdf';

        return $this->save();
    }

    public function custody() {
        $user = $this->obj;

        $this->user = $user;
        $this->userId = $user->userId;
        $this->tradeNo = substr(md5($this->userId), 8, 16);

        $data = [];
        // 托管机构
        $data['trustOrg'] = '江西银行';
        // 资金托管账户
        $data['trustOrgAccount'] = $user->custody_id;
        $this->data = $data;

        $this->itemKey = $this->items['user']['key'];
        $this->flowNo = $this->items['user']['flowNo'][1];

        return $this->save();
    }

    private function save() {
        $request = new AncunOpsRequest();
        /*$request->setItemkey($this->itemKey);//为本次保全的事项Key 自定义

        if($this->flowNo) {
            $request->setFlowNo($this->flowNo);//环节号 自定义
        }*/

        $this->recordNo = $this->getRecordNo();
        if($this->flow>0&&!$this->recordNo) {
            return [
                'code'=> '000000',
                'msg' => '未找到上一步',
                'data' => null,
                'logno' => null,
                'serversion' => null
            ];
        }

        foreach ($this->files as $key => $file) {
            $request->addFile($file, $key);
        }

        //下面两行代码只在有PDF附件并且需要电子签章的情况下使用
        // $request->setNeedInvestor(true);
        // $request->setNeedLender(true);
        
        // 添加用户信息
        $this->data['userName'] = $this->user->name;
        $this->data['userCode'] = $this->user->cardnum;
        // $this->data['userMobile'] = $this->user->phone;
        $this->data['userType'] = 1;
        
        $map = [];
        $map['itemKey'] = $this->itemKey; // 业务编号
        $map['flowNo'] = $this->flowNo; // 流程编号
        // 以下是业务数据的用户信息
        $map['userType'] = 1;
        $map['userName'] = $this->user->name;
        $map['userCode'] = $this->user->cardnum;
        $map['userMobile'] = $this->user->phone;
        $map['preserveData'] = json_encode($this->data);

        if($this->recordNo) {
            $map['recordNo'] = $this->recordNo;
        }
        if($this->certSec) {
            // 加盖合作方章
            $collaboratorIdentNos = [];
            array_push($collaboratorIdentNos, '91310000329581787H');
            // ...可添加多个合作方
            $map["collaboratorIdentNos"] = json_encode($collaboratorIdentNos);// 合作方组织机构代码
        }

        Log::write('data:', $map, 'ancun', 'INFO');
        $request->setData($map);
        $client = new AncunOpsClient($this->url, $this->partnerKey, $this->secret);
        $response = $client->save($request);

        $rdata = [];
        $rdata['data'] = $response->getData();
        $rdata['code'] = $response->getCode();
        $rdata['msg'] = $response->getMsg();
        $rdata['logno'] = $response->getLogno();
        $rdata['serversion'] = $response->getServersion();

        if($rdata['code']==100000) {
            $return = json_decode($rdata['data'], true);
            $this->recordNo = $return['recordNo'];
            $this->saveData();
        } else {
            Log::write('ac-error', $rdata, 'ancun-error', 'ERROR');
        }
        return $rdata;
    }

    private function saveData() {
        if($this->recordNo) {
            $ancun = new AncunData();
            $ancun->userId = $this->userId;
            $ancun->tradeNo = $this->tradeNo;
            $ancun->type = $this->type;
            $ancun->flow = $this->flow;
            $ancun->recordNo = $this->recordNo;
            $ancun->sendTime = date('Y-m-d H:i:s');
            return $ancun->save();
        } else {
            return false;
        }
        
    }

    private function getRecordNo() {
        if($this->recordNo) {
            return $this->recordNo;
        } else {
            if($this->flow==0) {
                return false;
            }
            $acData = AncunData::where('tradeNo', $this->tradeNo)
                ->where('userId', $this->userId)
                ->where('type', $this->type)
                ->where('flow', $this->flow-1)
                ->orderBy('id', 'desc')
                ->first();
            if($acData) {
                return $acData->recordNo;
            } else {
                return false;
            }
        }
    }
}