<?php
namespace plugins\ancun;

use Yaf\Registry;
use models\AncunData;
use tools\Log;
use helpers\StringHelper;

class ACTool {
	private static $config;
	private $url;
	private $partnerKey;
	private $secret;
	private $data;
	private $files = [];
	private $itemKey = '';
	private $flowNo = false;
	private $recordNo = false;
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
		if($this->type=='user') {
			$this->user = $this->obj;
		} else {
			$this->user = $this->obj->user;
		}

		$data = [];
		if($this->type=='tender') {
		    if($this->flow==0) { // oddMoney
				$this->userId = $this->obj->userId;
		    	$this->tradeNo = $this->obj->tradeNo;
                $data = $this->tenderOne();
		    } else if($this->flow==1) { // oddMoney
		    	$this->userId = $this->obj->userId;
		    	$this->tradeNo = $this->obj->tradeNo;
		    	//$data = $this->tenderTwo();
		    	$data = '';
		    } else if($this->flow==2) { // invest
		    	$this->userId = $this->obj->oddMoney->userId;
		    	$this->tradeNo = $this->obj->oddMoney->tradeNo;
		    	$data = $this->tenderThree();
		    }
		} else if($this->type=='loan') {
			$this->userId = $this->obj->userId;
			$this->tradeNo = $this->obj->tradeNo;
		    if($this->flow==0) {
		    	$data = $this->loanOne();
		    } else if($this->flow==1) {
		    	$data = $this->loanTwo();
		    }
		} else if($this->type=='user') {
			$this->userId = $this->obj->userId;
			$this->tradeNo = substr(md5($this->userId), 8, 16);
		    if($this->flow==0) {
                $data = $this->userOne();
		    } else if($this->flow==1) {
		    	$data = $this->userTwo();
		    }
		} else if($this->type=='assign') { // oddMoney
			$this->userId = $this->obj->userId;
			$this->tradeNo = $this->obj->tradeNo;
		    if($this->flow==0) {
                $data = $this->assignOne();
		    } else if($this->flow==1) {
		    	//$data = $this->assignTwo();
		    }
		} else if($this->type=='recharge') {
			$this->userId = $this->obj->userId;
			$this->tradeNo = $this->obj->serialNumber;
		    if($this->flow==0) {
                $data = $this->recharge();
		    }
		} else if($this->type=='withdraw') {
			$this->userId = $this->obj->userId;
			$this->tradeNo = $this->obj->tradeNo;
		    if($this->flow==0) {
                $data = $this->withdraw();
		    }
		}
		return $data;
	}

	public function loanOne() {
		$oddMoney = $this->obj;

		$data = [];
		// 姓名
		$data['borrowerName'] = $oddMoney->user->name;
		// 身份证号
		$data['borrowerCardnum'] = $oddMoney->user->cardnum;
		// 项目名称
		$data['oddTitle'] = $oddMoney->odd->oddTitle;
		$data['borrowerEmail'] = '';
		// 项目编号
		$data['oddNumber'] = $oddMoney->odd->oddNumber;
		// 年利率
		$data['borrowYearRate'] = ($oddMoney->odd->oddYearRate*100).'%';
		// 项目金额
		$data['borrowMoney'] = $oddMoney->odd->oddMoney.'元';
		// 最小认购金额
		//$data['startInvAmount'] = $oddMoney->odd->startMoney.'元';
		// 投资期限
		$data['borrowTime'] = $oddMoney->odd->getPeriod();
		// 气息时间
		$data['borrowAccessTime'] = $oddMoney->odd->oddRehearTime;
		$data['borrowApplyTime'] = $oddMoney->odd->openTime;
		// 还款方式
		$data['repayType'] = $oddMoney->odd->getRepayTypeName();
		//收款方账户
		$data['recBank'] = $oddMoney->odd->user->userbank->bankCName;
		$data['recBankAccount'] = $oddMoney->odd->user->userbank->bankNum;
		

		$data['tradeNo'] = $oddMoney->tradeNo;

		$this->data['preserveData'] = json_encode($data);

		$this->itemKey = $this->items['loan']['key'];
		$this->flowNo = $this->items['loan']['flowNo'][0];

		$fileName = $this->obj->generateProtocol(false);
		// $file = $this->path.'H'.$fileName;
		// $afile = $this->path.'H'.'A'.$fileName;
		// $this->files = [$file];
		// if(file_exists($afile)){
		// 	$this->files = [$file,$afile];
		// }
		// $this->save('hide');
		// $this->recordNo = false;
		$signClients = array();
		$item = basename($fileName,'.pdf');

		$signClients[] = new OpsSignClient(strval($oddMoney->oddinfo->needcardnum),'电子签章投资章专用','A'.$item);
		$this->data["needInvestor"] = "true";
		$this->data['needName'] = $oddMoney->oddinfo->needname;
		$this->data["investorIdcard"] = strval($oddMoney->oddinfo->needcardnum);

		// 参数1表示证件号;参数2指定签章关键字
		foreach ($oddMoney->odd->invest as $key => $value) {
			if($value->user->userType == '3'){
				$this->awardCaForCompany($value->user->name,$value->user->userbank->USCI);
				$signClients[] = new OpsSignClient($value->user->userbank->USCI,StringHelper::l2uNum($value->user->userId),$item);
				//$signClients[] = new OpsSignClient($value->user->userbank->USCI,StringHelper::l2uNum($value->user->userId),'A'.$item);
			}else{
				$this->awardCaForPersonal($value->user->name,$value->user->cardnum);
				$signClients[] = new OpsSignClient($value->user->cardnum,StringHelper::l2uNum($value->user->userId),$item);
				if($value->type != 'loan'){
					$signClients[] = new OpsSignClient($value->user->cardnum,StringHelper::l2uNum($value->user->userId),'A'.$item);
				}
			}
		}
		$this->data["signClients"] = json_encode($signClients);
		
		$file = $this->path.$fileName;
		$afile = $this->path.'A'.$fileName;
		$this->files = [$file];
		if(file_exists($afile)){
			$this->files = [$file,$afile];
		}
		
		return $this->save();
	}

	public function loanTwo() {
		$interest = $this->obj;
		$data = [];
		
		// 实际回款日期
		$data['repayTime'] = $interest->operatetime;
		// 实际回款金额
		$data['repayAmount'] = $interest->realAmount;

		$this->data['preserveData'] = json_encode($data);

		$this->itemKey = $this->items['loan']['key'];
		$this->flowNo = $this->items['loan']['flowNo'][1];

		return $this->save();
	}

	public function tenderOne() {
		$oddMoney = $this->obj;

		$data = [];
		// 姓名
		$data['name'] = $oddMoney->user->name;
		// 身份证号
		$data['cardnum'] = $oddMoney->user->cardnum;
		// 项目名称
		$data['oddTitle'] = $oddMoney->odd->oddTitle;
		// 项目编号
		$data['oddNumber'] = $oddMoney->odd->oddNumber;

		$this->data["needInvestor"] = "true";
		$this->data['needName'] = $oddMoney->oddinfo->needname;

		$this->data["investorIdcard"] = strval($oddMoney->oddinfo->needcardnum);
		// 年利率
		$data['oddYearRate'] = ($oddMoney->odd->oddYearRate*100).'%';
		// 项目金额
		$data['oddMoney'] = $oddMoney->odd->oddMoney.'元';
		// 最小认购金额
		//$data['startInvAmount'] = $oddMoney->odd->startMoney.'元';
		// 投资期限
		$data['oddBorrowPeriod'] = $oddMoney->odd->getPeriod();
		// 气息时间
		$data['oddRehearTime'] = $oddMoney->odd->oddRehearTime;
		// 还款方式
		$data['oddRepaymentStyle'] = $oddMoney->odd->getRepayTypeName();
		//付款方账户
		$data['payAccount'] = $oddMoney->user->username;
		//收款方账户
		$data['recAccount'] = _hide_phone($oddMoney->odd->user->username);

		// 发标时间
		//$data['prjReleaseTime'] = $oddMoney->odd->oddTrialTime;
		// 借款用途
		//$data['loanDescr'] = $oddMoney->odd->oddUse;
		// 募集截止时间
		//$data['raiseCloseTime'] = date('Y-m-d H:i:s', strtotime($oddMoney->odd->oddTrialTime)+$oddMoney->odd->oddBorrowValidTime*24*60*60);
		// 审核通过时间
		//$data['auditPassTime'] = $oddMoney->odd->oddRehearTime;
		// 借款人姓名
		//$data['borrowerName'] = $oddMoney->odd->user->name;
		// 借款人身份证号
		//$data['borrowerIdCardNo'] = $oddMoney->odd->user->cardnum;
		
		// 投资金额
		$data['money'] = $oddMoney->money.'元';
		// 交易流水号
		$data['tradeNo'] = $oddMoney->tradeNo;
		if($oddMoney->trade) {
			// 购买时间
			$data['buyTime'] = $oddMoney->trade->addTime;
			// 支付成功时间
			$data['sucTime'] = $oddMoney->trade->validTime;
		} else {
			// 购买时间
			$data['buyTime'] = $oddMoney->time;
			// 支付成功时间
			$data['sucTime'] = $oddMoney->time;
		}

		$this->data['preserveData'] = json_encode($data);

		$this->itemKey = $this->items['tender']['key'];
		$this->flowNo = $this->items['tender']['flowNo'][0];

		$fileName = $this->obj->generateProtocol(false);
		$file = $this->path.'H'.$fileName;
		$afile = $this->path.'H'.'A'.$fileName;
		$this->files = [$file];
		if(file_exists($afile)){
			$this->files = [$file,$afile];
		}
		$this->save('hide');
		$this->recordNo = false;

		$file = $this->path.$fileName;
		$afile = $this->path.'A'.$fileName;
		$this->files = [$file];
		if(file_exists($afile)){
			$this->files = [$file,$afile];
		}
		return $this->save();
	}

	// public function tenderTwo() {
	// 	$fileName = $this->obj->generateProtocol(false);
	// 	$file = $this->path.$fileName;

	// 	$this->files = [$file];

	// 	$this->itemKey = $this->items['tender']['key'];
	// 	$this->flowNo = $this->items['tender']['flowNo'][1];

	// 	$this->data = ['append-data'=>'noUse'];

	// 	return $this->save();
	// }

	public function tenderThree() {
		$invest = $this->obj;
		$data = [];
		
		// 实际回款日期
		$data['recTime'] = $invest->operatetime;
		// 实际回款金额
		$data['recAmount'] = $invest->realAmount;

		$this->data['preserveData'] = json_encode($data);

		$this->itemKey = $this->items['tender']['key'];
		$this->flowNo = $this->items['tender']['flowNo'][1];

		return $this->save();
	}

	public function assignOne() {
		$oddMoney = $this->obj;

		$data = [];
		// 转让人姓名
		$data['transUser'] = $oddMoney->parent->user->name;
		// 转让人用户名
		$data['transMobile'] = $oddMoney->parent->user->phone;
		// 转让人身份证号
		$data['transCardnum'] = $oddMoney->parent->user->cardnum;
		// 受让人姓名
		$data['buyUser'] = $oddMoney->user->name;
		// 受让人用户名
		$data['buyMobile'] = $oddMoney->user->phone;
		// 受让人身份证号
		$data['buyCardnum'] = $oddMoney->user->cardnum;

		// 原债权项目名称
		$data['oddTitle'] = $oddMoney->odd->oddTitle;
		// 原债权项目编号
		$data['oddNumber'] = $oddMoney->odd->oddNumber;
		// 债权本金
		$data['debtCapital'] = $oddMoney->odd->oddMoney.'元';
		// 年利率
		$data['yearRate'] = ($oddMoney->odd->oddYearRate*100).'%';
		// 债权期限
		$data['debtPeriod'] = $oddMoney->odd->getPeriod();
		// 剩余期限
		$data['remainPeriod'] = $oddMoney->crtrTrade->crtr->getRemainDay().'天';
		// 债务人姓名
		$data['borrower'] = $oddMoney->odd->user->name;
		// 债务人身份证号
		$data['borrowerCardnum'] = $oddMoney->odd->user->cardnum;

		// 项目名称
		$data['crtrTitle'] = '债权转让' . $oddMoney->crtrTrade->crtr->getSN() . '号';
		// 项目编号
		$data['crtrNumber'] = $oddMoney->crtrTrade->crtr->getSN();
		// 债权收益率
		//$data['loanRate'] = ($oddMoney->odd->oddYearRate*100).'%';
		// 转让金额
		$data['crtrMoney'] = $oddMoney->crtrTrade->money.'元';
		// 转让时间
		//$data['transferTime'] = $oddMoney->crtrTrade->crtr->addtime;
		// 受让价款
		$data['buyMoney'] = $oddMoney->crtrTrade->money.'元';

		// 转让费
		$data['serviceFee'] = $oddMoney->crtrTrade->crtr->serviceMoney.'元';
		// 受让时间
		$data['crtrTime'] = $oddMoney->crtrTrade->addTime;
		// 受让债权期限
		$data['crtrPeriod'] = $oddMoney->crtrTrade->crtr->getRemainDay().'天';
		// 收益支付方式
		$data['repayWay'] = $oddMoney->odd->getRepayTypeName();

		$this->data['preserveData'] = json_encode($data);

		$this->itemKey = $this->items['assign']['key'];
		$this->flowNo = $this->items['assign']['flowNo'][0];

		$fileName = $this->obj->generateProtocol(false);

		$file = $this->path.$fileName;
		$this->files = [$file];

		//$this->save();

		// $this->data['userName'] = $oddMoney->parent->user->name;
		// $this->data['userCode'] = $oddMoney->parent->user->cardnum;

		return $this->save();
	}

	// public function assignTwo() {
	// 	$fileName = $this->obj->generateProtocol(false);
		
	// 	$file = $this->path.$fileName;

	// 	$this->files = [$file];
		
	// 	$this->itemKey = $this->items['assign']['key'];
	// 	$this->flowNo = $this->items['assign']['flowNo'][1];

	// 	$this->data['preserveData'] = json_encode($data);

	// 	return $this->save();
	// }

	public function recharge() {
		$trade = $this->obj;

		$data = [];
		// 用户名
		$data['rPfAccount'] = $trade->user->username;
		// 姓名
		$data['rUserName'] = $trade->user->name;
		// 身份证号
		$data['rIdCardNo'] = $trade->user->cardnum;
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

		$this->data['preserveData'] = json_encode($data);

		$this->itemKey = $this->items['recharge']['key'];
		$this->flowNo = $this->items['recharge']['flowNo'][0];

		return $this->save();
	}

	public function withdraw() {
		$trade = $this->obj;

		$data = [];
		// 用户名
		$data['wPfAccount'] = $trade->user->username;
		// 姓名
		$data['wUserName'] = $trade->user->name;
		// 身份证号
		$data['wIdCardNo'] = $trade->user->cardnum;
		// 银行名称
		$data['wBankName'] = '银行存管';
		// 银行账号
		$data['withdrawBankcard'] = $trade->bankNum;
		// 提现金额
		$data['withdrawAmount'] = $trade->outMoney.'元';
		// 操作时间
		$data['withdrawOperateTime'] = $trade->addTime;
		// 提现成功时间
		$data['withdrawSucTime'] = $trade->validTime;
		// 支付流水号
		$data['withdrawSerialNo'] = $trade->tradeNo;

		$this->data['preserveData'] = json_encode($data);

		$this->itemKey = $this->items['withdraw']['key'];
		$this->flowNo = $this->items['withdraw']['flowNo'][0];

		return $this->save();
	}

	public function userOne() {
		$user = $this->obj;

		$data = [];
		//用户名
		$data['username'] = _hide_phone($user->username);
		//手机号
		$data['phone'] = _hide_phone($user->phone);
		// 注册时间
		$data['addtime'] = $user->addtime;
		// 用户姓名
		$data['name'] = $user->name;
		// 身份证号
		$data['cardnum'] = $user->cardnum;
		// 实名认证时间
		$data['certificationTime'] = $user->certificationTime;

		//$data['trustOrg'] = '';

		$this->data['preserveData'] = json_encode($data);

		$this->itemKey = $this->items['user']['key'];
		$this->flowNo = $this->items['user']['flowNo'][0];

		// 附件 《用户协议》
		$this->files = [$this->path.'user_protocol.pdf'];
		return $this->save();
	}

	public function userTwo() {
		$user = $this->obj;

		$data = [];
		// 托管机构
		$data['trustOrg'] = '上海银行';
		// 资金托管账户
		$data['trustOrgAccount'] = $user->userId;

		$this->data['preserveData'] = json_encode($data);

		$this->itemKey = $this->items['user']['key'];
		$this->flowNo = $this->items['user']['flowNo'][1];

		return $this->save();
	}



	public function awardCaForPersonal($name,$cardnum,$type = 7) {
		$AncunOpsRequest = new AncunOpsRequest();
		$map = array();
		$map["isCaInterface"] = "true";// CA制章接口
		$map["userName"] = $name;// 姓名
		$map["identNo"] = $cardnum;// 身份证号
		$map["certType"] = $type;// 证书类型：1个人普通证书,7场景证书
		$map["userType"] = "1";// 用户类型：1个人
		$map["preserveData"] = json_encode($map);
		$AncunOpsRequest->setData($map);
		$AncunOpsClient = new AncunOpsClient($this->url, $this->partnerKey, $this->secret);
		$ancunOpsResponse = $AncunOpsClient->awardCaForPersonal($AncunOpsRequest);
		echo '制章'.$name.','.$ancunOpsResponse->Code.','.$ancunOpsResponse->Msg;
	}

	public function awardCaForCompany($name,$cardnum) {
		$AncunOpsRequest = new AncunOpsRequest();
		$map = array();
		$map["isCaInterface"] = "true";// CA制章接口
		$map["userName"] = $name;// 企业名称
		$map["identNo"] = $cardnum;// 组织机构代码
		$map["certType"] = "7";// 证书类型：7场景证书
		$map["userType"] = "2";// 用户类型：2企业
		$map["preserveData"] = json_encode($map);

		// *
		  //        * 不传图片默认生成的签章尺寸为 150X150,签章内容为 单位全称
		  //        * 上传图片要求: 尺寸: 200X200   格式: png,背景透明
		  //        * 
		// $AncunOpsRequest->addFile("D:/印章图片.png", "seal");

		$AncunOpsRequest->setData($map);
		$AncunOpsClient = new AncunOpsClient($this->url, $this->partnerKey, $this->secret);
		$ancunOpsResponse = $AncunOpsClient->awardCaForCompany($AncunOpsRequest);
		echo '制章'.$name.','.$ancunOpsResponse->Code;
	}

	private function save($type='') {
		$aospRequest = new AncunOpsRequest();
		//$aospRequest->setItemKey($this->itemKey);//为本次保全的事项Key 自定义
		$this->data['itemKey'] = $this->itemKey;

		if($this->flowNo) {
			//$aospRequest->setFlowNo($this->flowNo);//环节号 自定义
			$this->data['flowNo'] = $this->flowNo;
		}

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
		if($this->recordNo) {
            $this->data['recordNo'] = $this->recordNo;
		}


		foreach ($this->files as $key => $file) {
			$aospRequest->addFile($file, $key);
		}
		// if($this->type=='tender'||$this->type=='assign') {
  //           // 加盖合作方章
  //           $collaboratorIdentNos = [];
  //           array_push($collaboratorIdentNos, '91350104MA347QHDXN');
  //           // ...可添加多个合作方
  //           $this->data["collaboratorIdentNos"] = json_encode($collaboratorIdentNos);// 合作方组织机构代码
		// }

		//下面两行代码只在有PDF附件并且需要电子签章的情况下使用
        // $aospRequest->setNeedInvestor(true);
		// $aospRequest->setNeedLender(true);
		
		// 添加用户信息
		if(!isset($this->data['userName'])){
			$this->data['userName'] = $this->user->name;
			$this->data['userCode'] = $this->user->cardnum;
		}
		if($type == 'hide'){
			unset($this->data['userName']);
			unset($this->data['userCode']);
		}
		// $this->data['userMobile'] = $this->user->phone;
		$this->data['userType'] = 1;

		if(isset($this->data['needInvestor'])){
			$this->awardCaForPersonal($this->data['needName'],$this->data['investorIdcard']);
			//unset($this->data['needName']);
		}
		
		Log::write('data:', $this->data, 'ancun', 'INFO');
		$aospRequest->setData($this->data);
		$aospClient = new AncunOpsClient($this->url, $this->partnerKey, $this->secret);
		$aospResponse = $aospClient->save($aospRequest);
		$rdata = [];
		$rdata['data'] = json_decode($aospResponse->getData(),true);
		$rdata['code'] = $aospResponse->getCode();
		$rdata['msg'] = $aospResponse->getMsg();
		$rdata['logno'] = $aospResponse->getLogno();
		$rdata['serversion'] = $aospResponse->getServersion();
		if($rdata['code']==100000) {
			$this->recordNo = $rdata['data']['recordNo'];
			$this->saveData($type);
		} else {
			Log::write('ac-error', $rdata, 'ancun-error', 'ERROR');
		}
		return $rdata;
	}

	private function saveData($type = '') {
		if($this->recordNo) {
			$ancun = new AncunData();
			$ancun->userId = $this->userId;
			$ancun->tradeNo = $this->tradeNo;
			$ancun->type = $this->type;
			$ancun->flow = $this->flow;
			$ancun->recordNo = $this->recordNo;
			$ancun->display = $type;
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