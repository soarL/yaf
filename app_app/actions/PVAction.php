<?php
use traits\handles\ITFAuthHandle;
use models\PV;
use tools\Log;
use Illuminate\Database\Capsule\Manager as DB;
use tools\AppPV;

/**
 * PVAction
 * 埋点
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class PVAction extends Action {
    use ITFAuthHandle;

    public function execute() {
    	$params = $this->getAllPost();
        $this->authenticate($params);

        $data = $params['data'];

        Log::write($data, [], 'pv');

        $list = explode('|', $data);

        $pvList = PV::whereRaw('1=1')->get(['name']);
        $nameList = [];
        foreach ($pvList as $pv) {
            $nameList[] = $pv->name;
        }

        $serverList = AppPV::get();
        AppPV::del();
        foreach ($serverList as $key => $num) {
            $list[] = AppPV::$list[$key].':'.$num;
        }
        $insertList = [];
        foreach ($list as $row) {
            $item = explode(':', $row);
            $num = intval($item[1]);
            $name = trim($item[0]);
            if($name) {
                if(in_array($name, $nameList)) {
                    PV::where('name', $name)->update(['num'=>DB::raw('num+'.$num)]);
                } else {
                    $insertList[] = ['name'=>$name, 'num'=>$num, 'created_at'=>date('Y-m-d H:i:s'), 'updated_at'=>date('Y-m-d H:i:s')];
                }
            }
        }
        if(count($insertList)>0) {
            DB::table(with(new PV())->getTable())->insert($insertList);
        }
        
        $rdata = [];
        $rdata['status'] = 1;
        $rdata['msg'] = '接收成功';

        $this->backJson($rdata);
    }
}