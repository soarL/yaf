<?php
namespace models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

class RedpackBatch extends Model {

    protected $table = 'user_redpack_batch';

    public $timestamps = false;

    public function batch() {
        return $this->belongsTo('models\CustodyBatch', 'batchNo');
    }

    public function handle() {
        if($this->status!==0) {
            return false;
        }
        $records = json_decode($this->items, true);
        $redpacks = [];
        $logs = [];
        DB::beginTransaction();
        foreach ($records as $record) {
            // var_dump($record);
            $time = date('Y-m-d H:i:s');
            $user = User::where('userId', $record['userId'])->first(['userId', 'frozenMoney', 'fundMoney']);
            if(!$user) {
                continue;
            }
            $count = User::where('userId', $user->userId)->update(['fundMoney'=>DB::raw('fundMoney+'.$record['money'])]);
            if($count) {
                $log = [];
                $log['userId'] = $record['userId'];
                $log['type'] = $record['type'];
                $log['mode'] = 'in';
                $log['mvalue'] = $record['money'];
                $log['remark'] = $record['remark'];
                $log['remain'] = $user->fundMoney + $record['money'];
                $log['frozen'] = $user->frozenMoney;
                $log['time'] = $time;
                $logs[] = $log;

                $redpack = [];
                $redpack['money'] = $record['money'];
                $redpack['userId'] = $record['userId'];
                $redpack['orderId'] = $record['orderId'];
                $redpack['remark'] = $record['remark'];
                $redpack['type'] = $record['type'];
                $redpack['status'] = 1;
                $redpack['addtime'] = $time;
                $redpacks[] = $redpack;
            }
        }
        $this->status = 1;
        $status1 = MoneyLog::insert($logs);
        $status2 = Redpack::insert($redpacks);
        
        if($status1 && $status2 && $this->save()) {
            DB::commit();
            return true;
        } else {
            DB::rollBack();
            return false;
        }
    }
}