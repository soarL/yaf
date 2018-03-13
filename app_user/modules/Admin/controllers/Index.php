<?php
use Admin as Controller;
use models\User;
use models\Odd;
use models\Interest;
use models\Invest;
use models\OddMoney;
use models\SysConfig;
use tools\Calculator;
use forms\admin\UserForm;
use Yaf\Registry;

/**
 * IndexController
 * 后台首页
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class IndexController extends Controller {
	public $menu = 'index';

	/**
     * 后台首页
     * @return  mixed
     */
	public function indexAction() {
		$this->submenu = 'index';
		$this->display('index');
	}

	/**
     * 网站数据
     * @return  mixed
     */
	public function dataAction() {
		$this->submenu = 'data';
		$this->display('data');
	}

	/**
     * 当前用户信息
     * @return mixed
     */
	public function profileAction() {
		$user = $this->getUser();
		$this->display('profile', ['user'=>$user]);
	}

	/**
     * 保存用户
     * @return mixed
     */
	public function saveProfileAction() {
		$params = $this->getAllPost();
		$params['scene'] = 'profile';
		$form = new UserForm($params);
		if($form->update()) {
			Flash::success('操作成功！');
			$this->redirect('/admin/user/list');
		} else {
			Flash::error($form->posError());
			$this->goBack();
		}
	}

	/**
     * 工具助手
     * @return  mixed
     */
	public function helpersAction() {
		$this->submenu = 'helpers';
		$repayTypes = ['monthpay'=>'先息后本', 'matchpay'=>'等额本息'];

		$this->display('helpers', ['repayTypes'=>$repayTypes]);
	}

	/**
	 * 改变标的还款方式
	 * @return mixed
	 */
	public function changeRepayTypeAction() {
		set_time_limit(0);
		$oddNumber = $this->getPost('oddNumber', false);
		$repayType = $this->getPost('repayType', false);
		$repayTypes = ['monthpay'=>'endmonth', 'matchpay'=>'month'];
		$rdata = [];
		if(!$oddNumber) {
			$rdata['status'] = 0;
			$rdata['info'] = '缺少标的号！';
			$this->backJson($rdata);
		}
		if(!$repayType) {
			$rdata['status'] = 0;
			$rdata['info'] = '缺少还款方式！';
			$this->backJson($rdata);
		}
		if(!isset($repayTypes[$repayType])) {
			$rdata['status'] = 0;
			$rdata['info'] = '还款方式不存在！';
			$this->backJson($rdata);
		}
		$odd = Odd::find($oddNumber);
		if(!$odd) {
			$rdata['status'] = 0;
			$rdata['info'] = '标的不存在！';
			$this->backJson($rdata);
		}
		if($odd->oddRepaymentStyle==$repayType) {
			$rdata['status'] = 0;
			$rdata['info'] = '已经是该还款方式，无需修改！';
			$this->backJson($rdata);	
		}
		if($odd->progress=='start') {
			$odd->oddRepaymentStyle = $repayType;
			if($odd->save()) {
				$rdata['status'] = 1;
				$rdata['info'] = '修改成功！';
				$this->backJson($rdata);
			} else {
				$rdata['status'] = 0;
				$rdata['info'] = '程序异常，修改失败！';
				$this->backJson($rdata);
			}
		} else if($odd->progress=='run') {
			$interests = $odd->interests;
			$isRepay = false;
			foreach ($interests as $interest) {
				if(in_array($interest->status, Interest::$finished)) {
					$isRepay = true;
					break;
				}
			}
			if($isRepay) {
				$rdata['status'] = 0;
				$rdata['info'] = '标的已经有还款，不可修改！';
				$this->backJson($rdata);
			}

			$odd->oddRepaymentStyle = $repayType;
			$odd->save();

			$data = [];
			$data['account'] = $odd->oddMoney;
			$data['apr'] = ($odd->oddYearRate+$odd->oddReward)*100;
			$data['period'] = $odd->oddBorrowPeriod;
			$data['style'] = $repayTypes[$repayType];
			$data['time'] = strtotime($odd->oddRehearTime);
			$data['feeRate'] = 0;
			$data['rewardRate'] = 0;
			$result = Calculator::getResult($data);
			foreach ($interests as $interest) {
				$item = $result[$interest->qishu-1];
				$interest->benJin = $item['accountCapital'];
				$interest->interest = $item['accountInterest'];
				$interest->zongEr = $item['accountAll'];
				$interest->yuEr = $item['accountOther'];
				$interest->save();
			}
			$oddMoneyList = OddMoney::where('oddNumber', $oddNumber)->whereIn('type', ['invest', 'credit'])->get();
			foreach ($oddMoneyList as $oddMoney) {
				$invests = $oddMoney->invests;
				$data = [];
				$data['account'] = $oddMoney->money;
				$data['apr'] = ($odd->oddYearRate+$odd->oddReward)*100;
				$data['period'] = $odd->oddBorrowPeriod;
				$data['style'] = $repayTypes[$repayType];
				$data['time'] = strtotime($odd->oddRehearTime);
				$data['feeRate'] = 0;
				$data['rewardRate'] = 0;
				$result = Calculator::getResult($data);
				foreach ($invests as $invest) {
					$item = $result[$invest->qishu-1];
					$invest->benJin = $item['accountCapital'];
					$invest->interest = $item['accountInterest'];
					$invest->zongEr = $item['accountAll'];
					$invest->yuEr = $item['accountOther'];
					$invest->save();
				}
			}
			$rdata['status'] = 1;
			$rdata['info'] = '还款方式修改，数据已经重新生成！';
			$this->backJson($rdata);
		} else {
			$rdata['status'] = 0;
			$rdata['info'] = '标的已经还款或有其他情况，不可修改！';
			$this->backJson($rdata);
		}
	}

	/**
	 * 获取标的信息
	 * @return mixed
	 */
	public function getOddInfoAction() {
		$oddNumber = $this->getPost('oddNumber', false);
		$rdata = [];
		if(!$oddNumber) {
			$rdata['status'] = 0;
			$rdata['info'] = '缺少标的号！';
			$this->backJson($rdata);
		}
		$odd = Odd::where('oddNumber', $oddNumber)->first([
			'oddNumber', 
			'oddTitle', 
			'oddYearRate', 
			'oddReward', 
			'OddMoney', 
			'oddBorrowPeriod', 
			'oddRepaymentStyle',
			'oddType',
			'oddStyle',
			'oddBorrowStyle',
		]);
		if($odd) {
			$rdata['status'] = 1;
			$rdata['info'] = '获取成功';
			$rdata['odd'] = $odd;
			$this->backJson($rdata);
		} else {
			$rdata['status'] = 0;
			$rdata['info'] = '标的不存在！';
			$this->backJson($rdata);
		}
	}

	public function systemConfigAction() {
		$configs = SysConfig::get();
		$this->display('systemConfig', ['configs'=>$configs]);
	}
}