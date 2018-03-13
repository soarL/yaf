<?php
use models\User;
use models\Odd;
use models\OddMoney;
use models\Protocol;
use models\Recharge;
use models\Withdraw;
use plugins\ancun\ACTool;
use tools\Redis;
use tools\Log;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * CertController
 * 安存任务
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class CertController extends Controller {

    public function autoProtocolAction() {
        $time = date('Y-m-d H:i:s', time()-10*60*99999999);
        $odds = Odd::with(['debts'=>function($q) {
                $q->select(['id', 'type', 'oddNumber']);
            }])
            ->where('progress', 'run')
            ->where('oddRehearTime', '>', $time)
            ->whereRaw('cerStatus&'.Odd::CER_STA_PR.'!='.Odd::CER_STA_PR)
            ->get();

        $key = Redis::getKey('protocolQueue');
        $nums = [];
        foreach ($odds as $odd) {
            $list = [];
            //多分合同
            // foreach ($odd->debts as $debt) {
            //     $list[] = json_encode(['id'=>$debt->id, 'type'=>$debt->type]);
            // }
            // 单份合同
            $list[] = json_encode(['id'=>$odd->oddNumber, 'type'=>'loan']);

            $params = [$key];
            $params = array_merge($params, $list);
            call_user_func_array(array('tools\Redis', 'lpush'), $params);
            $count = count($list);
            $nums[] = $odd->oddNumber;
            $this->export('成功添加标的['.$odd->oddNumber.']'.$count.'个生成合同任务，稍后将会进行生成！');
        }
        if(count($nums)>0) {
            $count = Odd::whereIn('oddNumber', $nums)->update(['cerStatus'=>DB::raw('cerStatus|'.Odd::CER_STA_PR)]);
            $this->export('成功添加'.$count.'个标的的生成合同任务，稍后将会进行生成！');
        }
    }

    public function autoAncunAction() {
        $beginTime = date('Y-m-d H:i:s', time()-10*60);
        $endtime = date('Y-m-d H:i:s', time()-20*60*99999999);
        $odds = Odd::with(['debts'=>function($q) {
                $q->select(['id', 'type', 'oddNumber']);
            }])
            ->where('progress', 'run')
            ->where('oddRehearTime', '<', $beginTime)
            ->where('oddRehearTime', '>', $endtime)
            ->whereRaw('cerStatus&'.Odd::CER_STA_PR.'='.Odd::CER_STA_PR)
            ->whereRaw('cerStatus&'.Odd::CER_STA_AC.'!='.Odd::CER_STA_AC)
            ->get();

        $key = Redis::getKey('ancunQueue');
        $nums = [];
        foreach ($odds as $odd) {
            $list = [];
            //多分合同
            // foreach ($odd->debts as $debt) {
            //     $type = ($debt->type=='invest')?'tender':($debt->type=='loan'?'loan':'assign');
            //     $list[] = json_encode(['key'=>$debt->id, 'type'=>$type, 'flow'=>0]);
            // }
            // 单份合同
            $list[] = json_encode(['key'=>$odd->oddNumber, 'type'=>'loan', 'flow'=>0]);

            $params = [$key];
            $params = array_merge($params, $list);
            call_user_func_array(array('tools\Redis', 'lpush'), $params);
            $count = count($list);
            $nums[] = $odd->oddNumber;
            $this->export('成功添加标的['.$odd->oddNumber.']'.$count.'个发送安存任务，稍后将会进行发送！');
        }
        if(count($nums)>0) {
            $count = Odd::whereIn('oddNumber', $nums)->update(['cerStatus'=>DB::raw('cerStatus|'.Odd::CER_STA_AC)]);
            $this->export('成功添加'.$count.'个标的的发送安存任务，稍后将会进行发送！');
        }
    }

    /**
     * 生成合同
     */
    public function protocolAction() {
        $key = Redis::getKey('protocolQueue');
        $creditIds = [];
        $tenderIds = [];
        $count = 0;
        $tenderCount = 0;
        $creditCount = 0;
        while ($value = Redis::rPop($key)) {
            $row = json_decode($value, true);
            if($row['type']=='invest') {
                $tenderCount++;
                $tenderIds[] = $row['id'];
            } else if($row['type']=='credit') {
                $creditCount++;
                $creditIds[] = $row['id'];
            } else if($row['type']=='loan') {
                $tenderCount++;
                $tenderIds[] = $row['id'];
            }
            $count++;
            if($count>=50) {
               break; 
            }
        }

        $protocols = [];
        
        if($tenderCount>0) {
            //多分合同
            // $records = OddMoney::with('odd.user', 'user')->whereIn('id', $tenderIds)->get();
            //单份合同
            $records = OddMoney::with('odd.user', 'user')->whereIn('oddNumber', $tenderIds)->where('type','loan')->get();
            foreach ($records as $debt) {
                $fileName = $debt->generateProtocol(false);
                $protocol = [];
                $protocol['userId'] = $debt->userId;
                $protocol['oddMoneyId'] = $debt->id;
                $protocol['created_at'] = date('Y-m-d H:i:s');
                $protocol['type'] = $debt->type;
                $protocol['protocolName'] = $fileName;
                $protocols[] = $protocol;
            }
            $this->export('成功生成'.$tenderCount.'个借款合同！');
        }

        if($creditCount>0) {
            $records = OddMoney::with('odd.user', 'user', 'parent.user')->whereIn('id', $creditIds)->get();
            foreach ($records as $debt) {
                $fileName = $debt->generateProtocol(false);
                $protocol = [];
                $protocol['userId'] = $debt->userId;
                $protocol['oddMoneyId'] = $debt->id;
                $protocol['created_at'] = date('Y-m-d H:i:s');
                $protocol['type'] = $debt->type;
                $protocol['protocolName'] = $fileName;
                $protocols[] = $protocol;
            }
            $this->export('成功生成'.$creditCount.'个债权转让合同！');
        }
    }

    public function ancunAction() {
        $key = Redis::getKey('ancunQueue');
        while ($value = Redis::rPop($key)) {
            // item: type flow key
            $item = json_decode($value, true);
            
            $type = $item['type'];
            $object = null;
            if($type=='tender') {
                $object = OddMoney::where('id', $item['key'])->first();
            } else if($type=='assign') {
                $object = OddMoney::where('id', $item['key'])->first();
            } else if($type=='loan') {
                $object = OddMoney::where('oddNumber', $item['key'])->where('type','loan')->first();
            } else if($type=='recharge') {
                $object = Recharge::where('serialNumber', $item['key'])->first();
            } else if($type=='withdraw') {
                $object = Withdraw::where('tradeNo', $item['key'])->first();
            } else if($type=='user') {
                $object = User::where('userId', $item['key'])->first();
            }
            $ancun = new ACTool($object, $item['type'], $item['flow']);
            $result = $ancun->send();
            $msg = '';
            if($result['code']==100000) {
                $msg .= $item['type'] . '-' . $item['key'] . '-' . $item['flow'] . '成功,';
            } else {
                $msg .= $item['type'] . '-' . $item['key'] . '-' . $item['flow'] . '失败【'.$result['msg'].'】,';
            }
            $this->export($msg);
        }
    }
}