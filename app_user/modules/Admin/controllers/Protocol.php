<?php
use Admin as Controller;
use models\OddMoney;
use models\Odd;
use models\Crtr;
use models\AncunData;
use models\Invest;
use models\Interest;
use plugins\ancun\ACTool;
use tools\Log;
use Illuminate\Database\Capsule\Manager as DB;

class ProtocolController extends Controller{

    public $menu = 'protocol';

    /**
     * 生成合同
     * @return mixed
     */
    public function generateProtocolsAction() {
        set_time_limit(0);
        $params = $this->getAllPost();
        $id = $this->getPost('id', 0);
        $type = $this->getPost('type', 'invest');
        if($type=='invest') {
            $oddMoneys = OddMoney::with('protocol')->where('oddNumber', $id)->where('type', 'invest')->get();
            foreach ($oddMoneys as $oddMoney) {
                $result = $oddMoney->generateProtocol(false, true);
            }
            Odd::where('oddNumber', $id)->update(['cerStatus'=>DB::raw('cerStatus|'.Odd::CER_STA_PR)]);
        } else if($type=='credit') {
            $oddMoneys = OddMoney::with('protocol')->where('bid', $id)->where('type', 'credit')->get();
            foreach ($oddMoneys as $oddMoney) {
                $result = $oddMoney->generateProtocol(false, true);
            }
            Crtr::where('oddmoneyId', $id)->where('progress', '<>', 'fail')->update(['cerStatus'=>DB::raw('cerStatus|'.Crtr::CER_STA_PR)]);
        }

        $rdata = [];
        $rdata['status'] = 1;
        $rdata['info'] = '生成成功！';
        $this->backJson($rdata);
    }

    /**
     * 发送安存-回款
     * @return mixed
     */
    public function acPaymentAction() {
        set_time_limit(0);
        $params = $this->getAllPost();
        $oddNumber = $params['oddNumber'];
        $qishu = $params['qishu'];
        $msg = '回款安存['.$oddMoneyId.']：';
        $invests = Invest::where('oddNumber', $oddNumber)->where('qishu', $qishu)->where('status', '<>', 2)->get();
        $interest = Interest::where('oddNumber', $oddNumber)->where('qishu', $qishu)->first();
        foreach ($invests as $key => $invest) {
            $acTool = new ACTool($invest, 'tender', 2);
            $result = $acTool->send();
            if($result['code']==100000) {
                $msg .= $invest->id . '成功,';
            } else {
                $msg .= $invest->id . '失败【'.$result['msg'].'】,';
            }
        }

        $acTool = new ACTool($interest, 'loan', 1);
        $result = $acTool->send();
        if($result['code']==100000) {
            $msg .= $interest->id . '成功,';
        } else {
            $msg .= $interest->id . '失败【'.$result['msg'].'】,';
        }

        Log::write($msg, [], 'ancun-api', 'INFO');

        $rdata = [];
        $rdata['status'] = 1;
        $rdata['info'] = $msg;
        $this->backJson($rdata);
    }

    /**
     * 发送安存-复审
     * @return mixed
     */
    public function acReviewAction() {
        set_time_limit(0);
        $oddNumber = $this->getPost('oddNumber', '');
        $odd = Odd::where('oddNumber', $oddNumber)->where('progress', 'run')->first(['cerStatus', 'oddNumber']);
        if(!$odd) {
            $rdata = [];
            $rdata['status'] = 0;
            $rdata['info'] = '标的不存在！';
            $this->backJson($rdata);
        }
        $oddMoneys = OddMoney::with('protocol', 'trade', 'user', 'odd.user')->where('oddNumber', $oddNumber)->where('type', 'invest')->get();
        
        AncunData::whereHas('oddMoney', function($q) use($oddNumber) {
            $q->where('oddNumber', $oddNumber)->where('type', 'invest');
        })->delete();

        $msg1 = '复审安存ONE['.$oddNumber.']：';
        foreach ($oddMoneys as $oddMoney) {
            $acTool = new ACTool($oddMoney, 'tender', 0);
            $result = $acTool->send();
            $code = $result['code'];
            if($code==100000) {
                $msg1 .= $oddMoney->id . '成功,';
            } else {
                $msg1 .= $oddMoney->id . '失败【'.$result['msg'].'】,';
            }
        }
        Log::write($msg1, [], 'ancun-api', 'INFO');
        

        sleep(3);

        $msg2 = '复审安存TWO['.$oddNumber.']：';
        foreach ($oddMoneys as $oddMoney) {
            $acTool = new ACTool($oddMoney, 'tender', 1);
            $result = $acTool->send();
            $code = $result['code'];
            if($code==100000) {
                $msg2 .= $oddMoney->id . '成功,';
            } else {
                $msg2 .= $oddMoney->id . '失败【'.$result['msg'].'】,';
            }
        }
        Log::write($msg2, [], 'ancun-api', 'INFO');

        Odd::where('oddNumber', $oddNumber)->update(['cerStatus'=>DB::raw('cerStatus|'.Odd::CER_STA_AC)]);

        $rdata = [];
        $rdata['status'] = 1;
        $rdata['info'] = $msg1 . '<br/>' . $msg2;
        $this->backJson($rdata);
    }

    /**
     * 发送安存-债权转让复审
     * @return mixed
     */
    public function acCrtrReviewAction() {
        set_time_limit(0);
        $params = $this->getAllPost();
        if(!isset($params['oddMoneyId'])){
            $params = $this->getAllQuery();
        }
        $oddMoneyId = $params['oddMoneyId'];
        $oddMoneys = OddMoney::with('protocol', 'crtrTrade.crtr', 'parent.user', 'odd.user')->where('bid', $oddMoneyId)->where('type', 'credit')->get();

/*        AncunData::whereHas('oddMoney', function($q) use($oddMoneyId) {
            $q->where('bid', $oddMoneyId)->where('type', 'credit');
        })->delete();
*/
        $msg1 = '债转复审安存ONE['.$oddMoneyId.']：';
        foreach ($oddMoneys as $oddMoney) {
            $acTool = new ACTool($oddMoney, 'assign', 0);
            $result = $acTool->send();
            if($result['code']==100000) {
                $msg1 .= $oddMoney->id . '成功,';
            } else {
                $msg1 .= $oddMoney->id . '失败【'.$result['msg'].'】,';
            }
        }
        Log::write($msg1, [], 'ancun-api', 'INFO');
        
        sleep(3);

        $msg2 = '债转复审安存TWO['.$oddMoneyId.']：';
        foreach ($oddMoneys as $oddMoney) {
            $acTool = new ACTool($oddMoney, 'assign', 1);
            $result = $acTool->send();
            if($result['code']==100000) {
                $msg2 .= $oddMoney->id . '成功,';
            } else {
                $msg2 .= $oddMoney->id . '失败【'.$result['msg'].'】,';
            }
        }
        Log::write($msg2, [], 'ancun-api', 'INFO');
        Crtr::where('oddmoneyId', $oddMoneyId)->where('progress', '<>', 'fail')->update(['cerStatus'=>DB::raw('cerStatus|'.Crtr::CER_STA_AC)]);

        $rdata = [];
        $rdata['status'] = 1;
        $rdata['info'] = $msg1 . '<br/>' . $msg2;
        $this->backJson($rdata);
    }
    
}
