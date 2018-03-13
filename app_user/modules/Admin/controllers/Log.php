<?php
use Admin as Controller;
use Illuminate\Database\Capsule\Manager as DB;
use traits\PaginatorInit;
use models\User;
use models\MoneyLog;
use helpers\ExcelHelper;

class LogController extends Controller{
    use PaginatorInit;

    /**
     * 资金日志
     */
    public function moneyAction() {
        $this->menu = 'user';
        $this->submenu = 'moneylog';
        $excel = $this->getQuery('excel', 0);
        $queries = $this->queries->defaults(['startTime'=>'', 'endTime'=>'', 'searchType'=>'username', 'searchContent'=>'', 'type'=>'all', 'mode'=>'all']);
        $builder = MoneyLog::with(['user'=>function($q){ $q->select('userId', 'username');}])->whereRaw('1=1');
        $user = null;
        if($queries->searchContent!='') {
            $searchContent = trim($queries->searchContent);
            $user = User::where($queries->searchType, $queries->searchContent)->first();
        }
        if($user) {
            $builder->where('userId', $user->userId);
        }
        if($queries->startTime!=''){
            $builder->where('time', '>=', $queries->startTime.' 00:00:00');
        }
        if($queries->endTime!=''){
            $builder->where('time', '<=', $queries->endTime.' 23:59:59');
        }
        if($queries->mode!='all'){
            $builder->where('mode', $queries->type);
        }
        if($queries->type!='all'){
            $builder->where('type', $queries->type);
        }
        if($excel) {

            $records = $builder->get();
            $other = [
                'title' => '资金日志',
                'columns' => [
                    'username' => ['name'=>'用户名', 'type'=>'string'],
                    'type' => ['name'=>'类型'],
                    'mvalue' => ['name'=>'金额'],
                    'mode' => ['name'=>'资金流向'],
                    'remark' => ['name'=>'备注'],
                    'time' => ['name'=>'操作时间'],
                    'remain' => ['name'=>'账户余额'],
                    'frozen' => ['name'=>'冻结余额'],
                ],
            ];
            $excelRecords = [];
            foreach ($records as $row) {
                $item = [];
                $item['userId'] = $row->userId;
                $item['username'] = $row->user->username;
                $item['type'] = $row->getTypeName();
                $item['mvalue'] = $row->mvalue;
                $item['mode'] = $row->getModeName();
                $item['remark'] = $row->remark;
                $item['time'] = $row->time;
                $item['remain'] = $row->remain;
                $item['frozen'] = $row->frozen;
                $excelRecords[] = $item;
            }
            ExcelHelper::getDataExcel($excelRecords, $other);
        }
        $logs = $builder->orderBy('time', 'desc')->orderBy('id', 'desc')->paginate(15);
        $logs->appends($queries->all());
        $this->display('money', ['logs' => $logs, 'queries'=>$queries, 'types'=>MoneyLog::$types]);
    }


    public function feeAction() {
        $this->menu = 'user';
        $this->submenu = 'fee';
        $excel = $this->getQuery('excel', 0);
        $queries = $this->queries->defaults(['startTime'=>'', 'endTime'=>'']);
        $builder = MoneyLog::whereRaw('type like \'fee-%\'');
        if($queries->startTime!=''){
            $builder->where('time', '>=', $queries->startTime.' 00:00:00');
        }
        if($queries->endTime!=''){
            $builder->where('time', '<=', $queries->endTime.' 23:59:59');
        }

        if($excel) {

            $records = $builder->get();
            $other = [
                'title' => '手续费日志',
                'columns' => [
                    'username' => ['name'=>'用户名', 'type'=>'string'],
                    'type' => ['name'=>'类型'],
                    'mvalue' => ['name'=>'金额'],
                    'remark' => ['name'=>'备注'],
                    'time' => ['name'=>'操作时间'],
                ],
            ];
            $excelRecords = [];
            foreach ($records as $row) {
                $item = [];
                $item['username'] = $row->user->username;
                $item['type'] = $row->getTypeName();
                $item['mvalue'] = $row->mvalue;
                $item['remark'] = $row->remark;
                $item['time'] = $row->time;
                $excelRecords[] = $item;
            }
            ExcelHelper::getDataExcel($excelRecords, $other);
        }

        $logs = $builder->orderBy('time', 'desc')->orderBy('id', 'desc')->paginate(15);
        $logs->appends($queries->all());
        $this->display('fee', ['logs' => $logs, 'queries'=>$queries]);
    }

    public function redpackAction() {
        $this->menu = 'user';
        $this->submenu = 'redpack';
        $excel = $this->getQuery('excel', 0);
        $queries = $this->queries->defaults(['startTime'=>'', 'endTime'=>'']);
        $builder = MoneyLog::whereRaw('type like \'rpk-%\'');
        if($queries->startTime!=''){
            $builder->where('time', '>=', $queries->startTime.' 00:00:00');
        }
        if($queries->endTime!=''){
            $builder->where('time', '<=', $queries->endTime.' 23:59:59');
        }

        if($excel) {

            $records = $builder->get();
            $other = [
                'title' => '红包日志',
                'columns' => [
                    'username' => ['name'=>'用户名', 'type'=>'string'],
                    'type' => ['name'=>'类型'],
                    'mvalue' => ['name'=>'金额'],
                    'remark' => ['name'=>'备注'],
                    'time' => ['name'=>'操作时间'],
                ],
            ];
            $excelRecords = [];
            foreach ($records as $row) {
                $item = [];
                $item['username'] = $row->user->username;
                $item['type'] = $row->getTypeName();
                $item['mvalue'] = $row->mvalue;
                $item['remark'] = $row->remark;
                $item['time'] = $row->time;
                $excelRecords[] = $item;
            }
            ExcelHelper::getDataExcel($excelRecords, $other);
        }

        $logs = $builder->orderBy('time', 'desc')->orderBy('id', 'desc')->paginate(15);
        $logs->appends($queries->all());
        $this->display('redpack', ['logs' => $logs, 'queries'=>$queries]);
    }
}
