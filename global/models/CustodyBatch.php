<?php
namespace models;

use task\Task;
use custody\Code;
use custody\Handler;
use Illuminate\Database\Eloquent\Model;

/**
 * CustodyBatch|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class CustodyBatch extends Model {

    protected $table = 'work_custody_batch';

    public $timestamps = false;

    public static $types = [
        'batchLendPay' => '批次放款',
        'batchRepay' => '批次还款',
        'batchBailRepay' => '批次代偿',
        'batchCreditEnd' => '批次结束债权',
        'batchRepayBail' => '批次还代偿',
        'batchVoucherPay' => '批次发红包',
    ];

    public function getTypeName() {
        return isset(self::$types[$this->type])?self::$types[$this->type]:'无该类型';
    }

    public function getBNQ() {
        return substr($this->batchNo, 8);
    }

    public function getBNDate() {
        return substr($this->batchNo, 0, 8);
    }

    public function getResult($type, $col='') {
        $item = [];
        if($type=='check') {
            $item = json_decode($this->checkResult, true);
        } else if($type=='return') {
            $item = json_decode($this->returnResult, true);
        } else if($type=='send') {
            $item = json_decode($this->sendData, true);
        }
        if(isset($item['retCode'])) {
            $item['retMsg'] = Code::getMsg($item['retCode']);
        }
        if($col!='') {
            return isset($item[$col])?$item[$col]:'';
        } else {
            return $item;
        }
    }

    public function getWord($type='check') {
        $item = [];
        if($type=='check') {
            $item = json_decode($this->checkResult, true);
        } else if($type=='return') {
            $item = json_decode($this->returnResult, true);
        } else if($type=='send') {
            $item = json_decode($this->sendData, true);
        }
        $words = [];
        if(isset($item['count'])) {
            $words[] = $item['count'] . '笔';
        }
        if(isset($item['amount'])) {
            $words[] = $item['amount'] . '元';
        }
        if(isset($item['txCounts'])) {
            $words[] = $item['txCounts'] . '笔';
        }
        if(isset($item['txAmount'])) {
            $words[] = $item['txAmount'] . '元';
        }
        if(isset($item['sucCounts'])) {
            $words[] = '成功'.$item['sucCounts'] . '笔';
        }
        if(isset($item['sucAmount'])) {
            $words[] = '成功'.$item['sucAmount'] . '元';
        }
        if(isset($item['failCounts'])) {
            $words[] = '失败'.$item['failCounts'] . '笔';
        }
        if(isset($item['failAmount'])) {
            $words[] = '失败'.$item['failAmount'] . '元';
        }
        if(isset($item['retCode'])) {
            $words[] = '代码'.$item['retCode'];
            $words[] = Code::getMsg($item['retCode']);
        }
        return count($words)?implode(' / ', $words):'无数据';
    }

    public function handle($data) {
        if($this->type=='batchLendPay') {
            $this->handleRehear($data);
        } else if($this->type=='batchVoucherPay') {
            $this->handleVoucherPay($data);
        } else if($this->type=='batchRepay') {
            $this->handleRepay($data);
        } else if($this->type=='batchBailRepay') {
            $this->handleBailRepay($data);
        }
    }

    /**
     * 批次放款后处理
     */
    public function handleRehear($data) {
        $status = 0;
        if($data['retCode']==Handler::SUCCESS && $data['failAmount']==0 && $data['failCounts']==0) {
            $status = 1;
        } else {
            $status = -1;
        }
        $result = [
            'retCode' => $data['retCode'],
            'sucAmount' => $data['sucAmount'],
            'sucCounts' => $data['sucCounts'],
            'failAmount' => $data['failAmount'],
            'failCounts' => $data['failCounts'],
        ];
        
        $count = self::where('batchNo', $this->batchNo)->where('status', 0)->update([
            'returnTime'=>date('Y-m-d H:i:s'), 
            'returnResult'=>json_encode($result), 
            'status' => $status
        ]);

        if($status==1) {
            Task::add('rehear', ['oddNumber'=>$this->refNum, 'step'=>2]);
        }
    }

    /**
     * 批次发红包后处理
     */
    public function handleVoucherPay($data) {
        $status = 0;
        if($data['retCode']==Handler::SUCCESS && $data['failAmount']==0 && $data['failCounts']==0) {
            $status = 1;
        } else {
            $status = -1;
        }
        $result = [
            'retCode' => $data['retCode'],
            'sucAmount' => $data['sucAmount'],
            'sucCounts' => $data['sucCounts'],
            'failAmount' => $data['failAmount'],
            'failCounts' => $data['failCounts'],
        ];

        $count = self::where('batchNo', $this->batchNo)->where('status', 0)->update([
            'returnTime'=>date('Y-m-d H:i:s'), 
            'returnResult'=>json_encode($result), 
            'status'=>$status,
        ]);

        if($status==1) {
            $redpackBatch = RedpackBatch::where('batchNo', $this->batchNo)->where('status', 0)->first();
            if($redpackBatch) {
                $redpackStatus = $redpackBatch->handle();
            }
        }
    }

    /**
     * 批次还款后处理
     */
    public function handleRepay($data) {
        $status = 0;
        if($data['retCode']==Handler::SUCCESS && $data['failAmount']==0 && $data['failCounts']==0) {
            $status = 1;
        } else {
            $status = -1;
        }
        $result = [
            'retCode' => $data['retCode'],
            'sucAmount' => $data['sucAmount'],
            'sucCounts' => $data['sucCounts'],
            'failAmount' => $data['failAmount'],
            'failCounts' => $data['failCounts'],
        ];

        $count = self::where('batchNo', $this->batchNo)->where('status', 0)->update([
            'returnTime'=>date('Y-m-d H:i:s'), 
            'returnResult'=>json_encode($result), 
            'status'=>$status,
        ]);

        if($status==1) {
            Task::add('repay', [
                'oddNumber'=>$this->refNum, 
                'period'=>$this->getResult('send', 'period'), 
                'type'=>$this->getResult('send', 'type'), 
                'step'=>2
            ]);
        }
    }

    /**
     * 批次代偿后处理
     */
    public function handleBailRepay($data) {
        $status = 0;
        if($data['retCode']==Handler::SUCCESS && $data['failAmount']==0 && $data['failCounts']==0) {
            $status = 1;
        } else {
            $status = -1;
        }
        $result = [
            'retCode' => $data['retCode'],
            'sucAmount' => $data['sucAmount'],
            'sucCounts' => $data['sucCounts'],
            'failAmount' => $data['failAmount'],
            'failCounts' => $data['failCounts'],
        ];

        $count = self::where('batchNo', $this->batchNo)->where('status', 0)->update([
            'returnTime'=>date('Y-m-d H:i:s'), 
            'returnResult'=>json_encode($result), 
            'status'=>$status,
        ]);

        if($status==1) {
            $list = json_decode($data['subPacks'], true);
            $rows = [];
            $period = 0;
            foreach ($list as $item) {
                $row = [];
                $row['txAmount'] = $item['txCapAmout'];
                $row['intAmount'] = $item['txIntAmount'];
                $row['authCode'] = $item['authCode'];
                $row['creditId'] = intval(substr($item['orderId'], 15, 10));
                $period = intval(substr($item['orderId'], 25));
                $row['period'] = $period;
                $rows = $row;
            }

            BailRepay::insert([
                'oddNumber' => _pton($data['productId']),
                'period' => $period,
                'items' => json_encode($rows),
                'orgBatchNo' => $data['acqRes'],
                'addTime' => date('Y-m-d H:i:s'),
            ]);

            Task::add('repay', [
                'oddNumber'=>$this->refNum, 
                'period'=>$this->getResult('send', 'period'), 
                'type'=>$this->getResult('send', 'type'), 
                'step'=>2
            ]);
        }
    }
}
