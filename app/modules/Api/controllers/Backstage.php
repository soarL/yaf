<?php
use tools\Pager;
use models\Odd;
use models\User;
use models\OddMoney;
use models\Crtr;
use models\AutoInvest;
use models\Invest;
use forms\AutoInvestForm;
use helpers\NetworkHelper;
use helpers\StringHelper;
use Yaf\Registry;
use plugins\ancun\ACTool;
use tools\WebSign;
use tools\Log;

/**
 * BackstageController
 * 后台接口
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class BackstageController extends Controller {

	public function authenticate($params, $expects=array()) {
		if(!WebSign::check($params, $expects)) {
	        $rdata['status'] = 0;
	        $rdata['msg'] = WebSign::getMsg();
	        $this->backJson($rdata);
	    }

	    $rdata['status'] = 1;
        $rdata['msg'] = '接口暂停访问';
        $this->backJson($rdata);
	}

	public function acPaymentAction() {
		set_time_limit(0);
		$params = $this->getAllPost();
		$this->authenticate($params, ['oddNumber'=>'标号', 'qishu'=>'期数']);
		$oddNumber = $params['oddNumber'];
		$qishu = $params['qishu'];
		$msg = '回款安存['.$oddMoneyId.']：';
		$invests = Invest::where('oddNumber', $oddNumber)->where('qishu', $qishu)->where('status', '<>', 2)->get();
		foreach ($invests as $key => $invest) {
			$acTool = new ACTool($invest, 'tender', 2);
			$result = $acTool->send();
			if($result['code']==100000) {
				$msg .= $invest->id . '成功,';
			} else {
				$msg .= $invest->id . '失败【'.$result['msg'].'】,';
			}
		}
		Log::write($msg, [], 'ancun-api', 'INFO');

		$rdata = [];
		$rdata['status'] = 1;
		$rdata['msg'] = $msg;
		$this->backJson($rdata);
	}

	public function acReviewAction() {
		set_time_limit(0);
		$params = $this->getAllPost();
		$this->authenticate($params, ['oddNumber'=>'标号']);
		$step = $params['step'];
		$oddNumber = $params['oddNumber'];
		// $oddNumber = '20171204000007';
		// $step = 1;

		$oddMoneys = OddMoney::with('protocol', 'trade', 'user', 'odd.user')->where('oddNumber', $oddNumber)->where('type', 'invest')->get();
		$loan = OddMoney::with('protocol', 'trade', 'user', 'odd.user')->where('oddNumber', $oddNumber)->where('type', 'loan')->first();
		
		if($step==1) {
			$msg = '复审安存ONE['.$oddNumber.']：';
			foreach ($oddMoneys as $oddMoney) {
				$acTool = new ACTool($oddMoney, 'tender', 0);
				$result = $acTool->send();
				if($result['code']==100000) {
					$msg .= $oddMoney->id . '成功,';
				} else {
					$msg .= $oddMoney->id . '失败【'.$result['msg'].'】,';
				}
			}
			$acTool = new ACTool($loan, 'loan', 0);
			$result = $acTool->send();
			if($result['code']==100000) {
				$msg .= $loan->id . '成功,';
			} else {
				$msg .= $loan->id . '失败【'.$result['msg'].'】,';
			}

			Log::write($msg, [], 'ancun-api', 'INFO');
		}

		if($step==2) {
			$msg = '复审安存TWO['.$oddNumber.']：';
			foreach ($oddMoneys as $oddMoney) {
				$acTool = new ACTool($oddMoney, 'tender', 1);
				$result = $acTool->send();
				if($result['code']==100000) {
					$msg .= $oddMoney->id . '成功,';
				} else {
					$msg .= $oddMoney->id . '失败【'.$result['msg'].'】,';
				}
			}
			Log::write($msg, [], 'ancun-api', 'INFO');
		}

		$rdata = [];
		$rdata['status'] = 1;
		$rdata['msg'] = $msg;
		$this->backJson($rdata);
	}

	public function acCrtrReviewAction() {
		set_time_limit(0);
		$params = $this->getAllPost();
		$this->authenticate($params, ['oddMoneyId'=>'债权号']);
		$oddMoneyId = $params['oddMoneyId'];
		$step = $params['step'];
		// $oddMoneyId = 84;
		// $step = 1;
		
		$oddMoneys = OddMoney::with('protocol', 'crtrTrade.crtr', 'parent.user', 'odd.user')->where('bid', $oddMoneyId)->where('type', 'credit')->get();

		if($step==1) {
			$msg = '债转复审安存ONE['.$oddMoneyId.']：';
			foreach ($oddMoneys as $oddMoney) {
				$acTool = new ACTool($oddMoney, 'assign', 0);
				$result = $acTool->send();
				if($result['code']==100000) {
					$msg .= $oddMoney->id . '成功,';
				} else {
					$msg .= $oddMoney->id . '失败【'.$result['msg'].'】,';
				}
			}
			Log::write($msg, [], 'ancun-api', 'INFO');
		}

		if($step==2) {
			$msg = '债转复审安存TWO['.$oddMoneyId.']：';
			foreach ($oddMoneys as $oddMoney) {
				$acTool = new ACTool($oddMoney, 'assign', 1);
				$result = $acTool->send();
				if($result['code']==100000) {
					$msg .= $oddMoney->id . '成功,';
				} else {
					$msg .= $oddMoney->id . '失败【'.$result['msg'].'】,';
				}
			}
			Log::write($msg, [], 'ancun-api', 'INFO');
		}

		$rdata = [];
		$rdata['status'] = 1;
		$rdata['msg'] = $msg;
		$this->backJson($rdata);
	}

	public function generateProtocolsAction() {
		set_time_limit(0);
		$params = $this->getAllPost();
		$this->authenticate($params, ['id'=>'标识符', 'type'=>'类型']);
		$id = $params['id'];
		$type = $params['type'];

		//$id="20171204000007";
		// $id="84";
		// $type='credit';

		if($type=='invest') {
			$oddMoneys = OddMoney::with('protocol')->where('oddNumber', $id)->where('type', 'invest')->get();
			foreach ($oddMoneys as $oddMoney) {
				$result = $oddMoney->generateProtocol(false);
			}
		} else if($type=='credit') {
			$oddMoneys = OddMoney::with('protocol')->where('bid', $id)->where('type', 'credit')->get();
			foreach ($oddMoneys as $oddMoney) {
				$result = $oddMoney->generateProtocol(false);
			}
		}

		$rdata = [];
		$rdata['status'] = 1;
		$rdata['msg'] = '生成成功！';
		$this->backJson($rdata);
	}
}

