<?php
use Admin as Controller;
use factories\RedisFactory;
use models\Invest;
use models\Promotion;
use models\User;
use models\OddMoney;
use models\AccessLog;
use models\Recharge;
use models\Withdraw;
use forms\admin\PromotionForm;
use traits\PaginatorInit;
// use tools\Qrcode;
use Illuminate\Database\Capsule\Manager as DB;

use models\TrafficDay;
use models\TrafficMonth;
use models\TrafficHour;
use models\TrafficWeek;

use helpers\ExcelHelper;

/**
 * PromotionController
 * 渠道管理
 * @version 1.0
 */
class PromotionController extends Controller {
    use PaginatorInit;

    public $menu = 'promotion';
  
    /**
     * 渠道推广列表
     * @return mixed
     */
    public function listAction() {
        $this->submenu = 'promotion';

        $queries = $this->queries->defaults(['type'=>'hour', 'channel'=>'']);
        $type = $queries->type;
        $user = $this->getUser();
        if ($user->username == '13798562295'){
            $like = 'advertise%1';
        }elseif ($user->username == '15001361401'){
            $like = 'advertise%2';
        }elseif ($user->username == 'ledouwan'){
            $like = 'advertise%6';
        }elseif ($user->username == '13388513905'){
            $like = 'advertise%7';
        }elseif ($user->username == '15208460965'){
            $like = 'advertise%3';
        }else{
            $like = '';
        }
        if (empty($like)){
            $builder = Promotion::whereRaw('1=1');
        }else{
            $builder = Promotion::where('channelCode','like',$like);
        }


        $records = $builder->orderBy('id', 'desc')->paginate(15);
        $records->appends($queries->all());

        $list = [];
        $cList = [];
        $cInList = [];
        foreach ($records as $record) {
            $cList[] = $record->channelCode;
            $cInList[] = '\'' . $record->channelCode . '\'';
            $list[$record->channelCode] = [
                'channelName'=>$record->channelName, 
                'channelCode'=>$record->channelCode, 
//                'pvAvg'=>0,
//                'uvAvg'=>0,
//                'ipAvg'=>0,
                'channelName'=>$record->channelName, 
                'regCount'=>0,
//                'wMoney'=>0,
//                'wCount'=>0,
//                'rMoney'=>0,
//                'rCount'=>0,
                'tMoney'=>0, 
                'tCount'=>0,
            ];
        }

//        $trafficBuilder = null;
//        $column = '';
//        $timeFormat = '';
//        if($type=='week') {
//            $trafficBuilder = TrafficWeek::whereIn('pm_key', $cList);
//            $column = 'monday';
//            $timeFormat = 'Ymd';
//        } else if($type=='day') {
//            $trafficBuilder = TrafficDay::whereIn('pm_key', $cList);
//            $column = 'date';
//            $timeFormat = 'Ymd';
//        } else if($type=='month') {
//            $trafficBuilder = TrafficMonth::whereIn('pm_key', $cList);
//            $column = 'month';
//            $timeFormat = 'Ym';
//        } else {
//            $trafficBuilder = TrafficHour::whereIn('pm_key', $cList);
//            $column = 'hour';
//            $timeFormat = 'YmdH';
//        }
//
//        $beginTime = $this->_timeBeginReal($beginTime, $type);
//        $endTime = $this->_timeEndReal($endTime, $type);
//        $queries->beginTime = $beginTime;
//        $queries->endTime = $endTime;
//
//        $trafficBuilder->where($column, '>=', _date($timeFormat, $beginTime));
//        $trafficBuilder->where($column, '<=', _date($timeFormat, $endTime));
//
//        $result = $trafficBuilder->groupBy('pm_key')->get(['pm_key', DB::raw('sum(pv)/count(1) pvAvg'), DB::raw('sum(uv)/count(1) uvAvg'), DB::raw('sum(ip)/count(1) ipAvg')]);
//
//        foreach ($result as $item) {
//            $list[$item->pm_key]['pvAvg'] = $item->pvAvg;
//            $list[$item->pm_key]['uvAvg'] = $item->uvAvg;
//            $list[$item->pm_key]['ipAvg'] = $item->ipAvg;
//        }

        $userTable = with(new User())->getTable();
//        $withdrawTable = with(new Withdraw())->getTable();
//        $rechargeTable = with(new Recharge())->getTable();
        $omTable = with(new OddMoney())->getTable();

        $rcList = User::whereIn('channelCode', $cList)
            ->groupBy('channelCode')
            ->get([DB::raw('count(1) regCount'), 'channelCode']);
        foreach ($rcList as $item) {
            $list[$item->channelCode]['regCount'] = $item->regCount;
        }

//        $result1 = DB::select("select sum(outMoney) wMoney, count(distinct t1.userId) wCount, channelCode from ".$withdrawTable." t1 left join ".$userTable." t2 on t1.userId=t2.userId where t2.channelCode in (". implode(',', $cInList) .") and t1.addTime>=? and t1.addTime<=? and t1.status=1 group by t2.channelCode", [$beginTime, $endTime]);

//        $result2 = DB::select("select sum(money) rMoney, count(distinct t1.userId) rCount, channelCode from ".$rechargeTable." t1 left join ".$userTable." t2 on t1.userId=t2.userId where t2.channelCode in (". implode(',', $cInList) .") and t1.time>=? and t1.time<=? and t1.status=1 group by t2.channelCode", [$beginTime, $endTime]);

        $result3 = DB::select("select sum(money) tMoney, count(distinct t1.userId) tCount, channelCode from ".$omTable." t1 left join ".$userTable." t2 on t1.userId=t2.userId where t2.channelCode in (". implode(',', $cInList) .") and t1.type=? group by t2.channelCode", ['invest']);

//        foreach ($result1 as $item) {
//            $list[$item->channelCode]['wMoney'] = $item->wMoney;
//            $list[$item->channelCode]['wCount'] = $item->wCount;
//        }
//
//        foreach ($result2 as $item) {
//            $list[$item->channelCode]['rMoney'] = $item->rMoney;
//            $list[$item->channelCode]['rCount'] = $item->rCount;
//        }

        foreach ($result3 as $item) {
            $list[$item->channelCode]['tMoney'] = $item->tMoney;
            $list[$item->channelCode]['tCount'] = $item->tCount;
        }

        $this->display('list', ['list'=> $list, 'records'=>$records, 'queries'=>$queries]);
    }

    /**
     * 渠道推广新增
     * @return mixed
     */
    public function addListAction() {
        $this->display('add');
    }


    /**
     * 修改渠道推广
     * @return mixed
     */
    public function updateAction() {

        $params = $this->getAllPost();

        $hidden_id = $params['hidden_id'];
        $channelCode = $params['channelCode'];
        $channelName = $params['channelName'];

      
        $data=[
            'channelCode'=>$channelCode,
            'channelName'=>$channelName,
        ];

        $resCate = Promotion::where("channelCode",$channelCode)->orWhere("channelName",$channelName)->find();
        if(!empty($resCate)){
            Flash::error('该渠道信息已存在');
            $this->redirect('/admin/promotion/list');
        }

        $res = Promotion::where("id",$hidden_id)->update($data);
        if(empty($res)){
            Flash::error('异常错误');
            $this->redirect('/admin/promotion/list');
        }else{
            Flash::success('修改成功');
            $this->redirect('/admin/promotion/list');
        }

    }

    /**
     * 新增推广列表
     * @return mixed
     */
    public function addAction() {
        $params = $this->getAllPost();
        if(empty($params['channel_code']) || empty($params['channel_name'])){
                Flash::error('渠道名称或者渠道代号不能为空');
             $this->redirect('/admin/promotion/list');
        }

        $params['channel_code'] = strtoupper($params['channel_code']);
        $promotion = new PromotionForm($params);
        if($promotion->save()) {
            Flash::success('操作成功！');
            $this->redirect('/admin/promotion/list');
        } else {
            Flash::error('渠道名称或者渠道代号不能重复');
            $this->redirect('/admin/promotion/list');
        }
    }

    private function _timeFormat($col, $format) {
        return DB::raw("FROM_UNIXTIME(UNIX_TIMESTAMP(".$col."), '".$format."') AS fmt_time");
    }

    private function _timeBeginReal($time, $type) {
        $list = [];
        $begin = '';
        if($type=='day') {
            $begin = date('Y-m-d 00:00:00', strtotime($time));
        } else if($type=='hour') {
            $begin = date('Y-m-d 00:00:00', strtotime($time));
        } else if($type=='month') {
            $begin = date('Y-m-01 00:00:00', strtotime($time));
        } else if($type=='week') {
            $t = strtotime($time);
            $begin = date('Y-m-d 00:00:00', ($t-((date('w', $t)==0?7:date('w', $t))-1)*24*3600));
        }
        return $begin;
    }

    private function _timeEndReal($time, $type) {
        $list = [];
        $end = '';
        if($type=='day') {
            $end = date('Y-m-d 23:59:59', strtotime($time));
        } else if($type=='hour') {
            $end = date('Y-m-d 23:59:59', strtotime($time));
        } else if($type=='month') {
            $t = strtotime(date('Y-m-01 00:00:00', strtotime($time)) . ' +1 month') - 1;
            $end = date('Y-m-H H:i:s', $t);
        } else if($type=='week') {
            $t = strtotime($time);
            $monday = date('Y-m-d 00:00:00', ($t-((date('w', $t)==0?7:date('w', $t))-1)*24*3600));
            $end = date('Y-m-d H:i:s', strtotime($monday) + 7*24*3600 -1);
        }
        return $end;
    }

    private function _getTimeList($beginTime, $endTime, $type) {
        $list = [];
        if($type=='day') {
            $begin = strtotime(date('Y-m-d 00:00:00', strtotime($beginTime)));
            $end = strtotime(date('Y-m-d 23:59:59', strtotime($endTime)));
            while ($begin < $end) {
                $list[] = date('Ymd', $begin);
                $begin += 24*3600;
            }
        } else if($type=='hour') {
            $begin = strtotime(date('Y-m-d 00:00:00', strtotime($beginTime)));
            $end = strtotime(date('Y-m-d 23:59:59', strtotime($endTime)));
            while ($begin < $end) {
                $list[] = date('YmdH', $begin);
                $begin += 3600;
            }
        } else if($type=='month') {
            $begin = strtotime(date('Y-m-01 00:00:00', strtotime($beginTime)));
            $end = strtotime(date('Y-m-01 00:00:00', strtotime($endTime)) . ' +1 month');
            while ($begin < $end) {
                $list[] = date('Ym', $begin);
                $begin = strtotime(date('Y-m-01 00:00:00', $begin) . ' +1 month');
            }
        } else if($type=='week') {
            $a = strtotime($beginTime);
            $b = strtotime($endTime);
            $aMonday = date('Y-m-d 00:00:00', ($a-((date('w', $a)==0?7:date('w', $a))-1)*24*3600));
            $bMonday = date('Y-m-d 00:00:00', ($b-((date('w', $b)==0?7:date('w', $b))-1)*24*3600));
            $begin = strtotime($aMonday);
            $end = strtotime($bMonday);
            while ($begin <= $end) {
                $list[] = date('Ymd', $begin);
                $begin += 7*24*3600;
            }
        }
        return $list;
    }

    /**
     * 用户投资日志列表
     * @return mixed
     */
    public function detailAction() {
        $this->submenu = 'promotion';
        $queries = $this->queries->defaults(['beginTime'=>'', 'endTime'=>'']);

        $beginTime = $queries->beginTime;
        $endTime = $queries->endTime;
        $channel = $queries->channel;
        $excel = $this->getQuery('excel', 0);

        $builder = OddMoney::with(['user'=>function($query) {
            $query->select('userId','addtime','phone');
        }, 'odd'=>function($query){
            $query->select('oddNumber','oddBorrowPeriod');
        }])->whereHas('user', function($query) use($channel,$beginTime,$endTime) {
            if($beginTime != '' && $endTime!= ''){
                $query->where('channelCode',$channel)->where('addtime', '>=', $beginTime.' 00:00:00')->where('addtime', '<=', $endTime.' 23:59:59');
            }else{
                $query->where('channelCode',$channel);
            }
            })->select('oddNumber','userId','money','time','id');

        $firstTimeId = OddMoney::whereHas('user',function($query) use($channel){
            $query->where('channelCode',$channel);
        })->where('type','invest')->groupBy('userId')->lists('id')->toArray();

        if($excel) {
            $records = $builder->get();
            foreach ($records as $key =>$record){
                if (in_array($record->id,$firstTimeId)){
                    $records[$key]['isFirstInvest'] ='是';
                }else{
                    $records[$key]['isFirstInvest'] ='否';
                }
                $record['user']['phone'] = _hide_phone($record['user']['phone']);
            }
            $other = [
                'title' => '用户投资日志',
                'columns' => [
                    'addtime' => ['name'=>'注册时间'],
                    'phone' => ['name'=>'手机号'],
                    'oddNumber' => ['name'=>'项目编号'],
                    'time' => ['name'=>'投资时间'],
                    'money' => ['name'=>'投资金额'],
                    'oddBorrowPeriod' => ['name'=>'投资期限'],
                    'isFirstInvest' => ['name'=>'是否首投'],
                ],
            ];
            $excelRecords = [];
            foreach ($records as $row) {
                $item = [];
                $item['addtime'] = $row->user->addtime;
                $item['phone'] = $row->user->phone;
                $item['oddNumber'] = $row->oddNumber;
                $item['time'] = $row->time;
                $item['money'] = $row->money;
                $item['oddBorrowPeriod'] = $row->odd->oddBorrowPeriod.'个月';
                $item['isFirstInvest'] = $row->isFirstInvest;
                $excelRecords[] = $item;
            }
            ExcelHelper::getDataExcel($excelRecords, $other);
        }

        $records = $builder->orderBy('time', 'desc')->paginate(15);


        $records->appends($queries->all());

        foreach ($records as $key =>$record){
            if (in_array($record->id,$firstTimeId)){
                $records[$key]['isFirstInvest'] ='是';
            }else{
                $records[$key]['isFirstInvest'] ='否';
            }
            $record['user']['phone'] = _hide_phone($record['user']['phone']);
        }
        $this->display('detail',['queries'=>$queries,'records'=>$records]);

    }

    /**
     *用户统计数据
     */
    public function statisticsAction()
    {
        $this->submenu = 'promotion';
        $queries = $this->queries->defaults(['beginTime'=>date('Y-m-d'), 'endTime'=>date('Y-m-d')]);
        $excel = $this->getQuery('excel', 0);
        $channel = $queries->channel;

        $builder = User::with(['invests'=>function($query){
            $query->groupBy('userId')->select('userId', DB::raw('sum(zongEr) as stayMoney'));
        }])->where('channelCode',$channel)->select('phone','addtime','userId');

        if($excel) {
            $records = $builder->get();
            foreach ($records as $key =>$record){
                $records[$key]['phone'] = _hide_phone($records[$key]['phone']);
            }
            $other = [
                'title' => '用户统计数据',
                'columns' => [
                    'addtime' => ['name'=>'注册时间'],
                    'phone' => ['name'=>'手机号'],
                    'stayMoney' => ['name'=>'待收总额'],
                ],
            ];
            $excelRecords = [];
            foreach ($records as $row) {
                $item = [];
                $item['addtime'] = $row->addtime;
                $item['phone'] = $row->phone;
                $item['stayMoney'] = isset($row['invests'][0]['stayMoney']) ? $row['invests'][0]['stayMoney'] : 0;
                $excelRecords[] = $item;
            }
            ExcelHelper::getDataExcel($excelRecords, $other);
        }

        if($queries->startTime!=''){
            $builder->where('addtime', '>=', $queries->startTime.' 00:00:00');
        }
        if($queries->endTime!=''){
            $builder->where('addtime', '<=', $queries->endTime.' 23:59:59');
        }

        $records = $builder->orderBy('addtime', 'desc')->paginate(15);
        foreach ($records as $key =>$record){
            $records[$key]['phone'] = _hide_phone($records[$key]['phone']);
        }
        $records->appends($queries->all());
        $this->display('statistics',['queries'=>$queries,'records'=>$records]);

    }

    /**
     *页面统计
     */
    public function pageInfoAction()
    {
        $this->submenu = 'promotion';
        $queries = $this->queries;
        $channel = $queries->channel;
        $redis = RedisFactory::create();
        $results = $redis->lRange($channel.'_view',0,-1);
        $resultClick = $redis->lRange($channel.'_register',0,-1);
        $downClick = $redis->lRange($channel.'_view_download',0,-1);
        foreach ($results as &$result) {
            $result = json_decode($result, true);
        }
        $registerCount = User::where('channelCode',$channel)->count();

        $pv = sizeof($results);
        $uv = sizeof(array_unique(array_column($results, 'ip')));
        $clicks = count($resultClick);
        $downClicks = count($downClick);
        $this->display('pageInfo',['queries'=>$queries,'pv'=>$pv,'uv'=>$uv,'count'=>$registerCount,'clicks'=>$clicks,'downClicks'=>$downClicks]);
        
    }


    public function excelDetailAction()
    {
        $id = $this->getQuery("id");
        $arr = Promotion::where("id",$id)->first()->toArray();
        $arr_res = "";
        $arr_user = null;

        if(!empty($arr)){
            $data_user=[
                'status'=>1,
                'channel_id'=>$arr['id'],
            ];

             $arr_all= User::where($data_user)
                       ->select("username","addtime","userId")
                       ->get()
                       ->toArray();
             $res_all = $this->commonDetail($arr_all,1);
             $other = [
              'title' => '推广详情',
              'columns' => [
                  'id' => ['name'=>'编号ID', 'type'=>'string'],
                  'res_time' => ['name'=>'注册日期', 'type'=>'string'],
                  'user_name' => ['name'=>'用户名', 'type'=>'string'],
                  'charge_num' => ['name'=>'充值笔数', 'type'=>'string'],
                  'charge_money' => ['name'=>'充值金额', 'type'=>'string'],
                  'invest_num' => ['name'=>'投资笔数', 'type'=>'string'],
                  'invest_money' => ['name'=>'投资金额', 'type'=>'string'],

                  'invest_num_1' => ['name'=>'1月投资金额', 'type'=>'string'],
                  'invest_num_2' => ['name'=>'2月投资金额', 'type'=>'string'],
                  'invest_num_3' => ['name'=>'3月投资金额', 'type'=>'string'],
                  'invest_num_6' => ['name'=>'6月投资金额', 'type'=>'string'],
                  'invest_num_12' => ['name'=>'12月投资金额', 'type'=>'string'],
                  'invest_num_24' => ['name'=>'24月投资金额', 'type'=>'string'],

                  'draw_num' => ['name'=>'提现笔数', 'type'=>'string'],
                  'draw_money' => ['name'=>'提现金额', 'type'=>'string'],
              ],
            ];

              $excelRecords = [];
              foreach ($res_all as $row) {
                  $row['id']  = $row['userId'];
                  $row['res_time']  = $row['addtime'];
                  $row['user_name']  = $row['username'];

                  $row['charge_num']  = $row['charge_num'];
                  $row['charge_money']  = $row['charge_money'];
                  $row['invest_num']  = $row['invest_num'];
                  $row['invest_money']  = $row['invest_money'];

                  $row['invest_num_1']  = $row['arr_month_1'];
                  $row['invest_num_2']  = $row['arr_month_2'];
                  $row['invest_num_3']  = $row['arr_month_3'];
                  $row['invest_num_6']  = $row['arr_month_6'];
                  $row['invest_num_12']  = $row['arr_month_12'];
                  $row['invest_num_24']  = $row['arr_month_24'];

                  $row['draw_num']  = $row['draw_num'];
                  $row['draw_money']  = $row['draw_money'];

                  $excelRecords[] = $row;
              }

              ExcelHelper::getDataExcel($excelRecords, $other);
        }else{
            Flash::error("没有数据无法导excel");
        }


        
    }

    
    /**
     * 公共详情
     * @return [type] [description]
     */
    public function commonDetail($arr_user="",$status="")
    {
        $charge_num = 0; //充值人数
        $invest_num = 0; //投资人数
        $draw_num = 0; //提现人数

        $charge_money = 0;
        $invest_money = 0;
        $draw_money = 0;

        $arr_res ="";
        

        $arr_month_1 = 0;
        $arr_month_2 = 0;
        $arr_month_3 = 0;
        $arr_month_6 = 0;
        $arr_month_12 = 0;
        $arr_month_24 = 0;

        if(!empty($arr_user)){
            foreach ($arr_user as $value) {
                
                // 充值
                $userid = $value['userId'];

                $data_charge = [
                    "userid"=>$userid,
                    "status"=>"1",
                ];

                $arrUserCharge = Recharge::select("money","userId")->where($data_charge)->get()->toArray();
                
                if(!empty($arrUserCharge)){
                    $charge_num = count($arrUserCharge);
                    foreach ($arrUserCharge as $value2) {
                        $charge_money = $value2['money'] + $charge_money;
                    }
                }

                // 投资
                $data = [
                    "userid"=>$userid,
                    "type"=>"invest",
                ];
                $arrMoney = OddMoney::select("money","userId")->where($data)->get()->toArray();
                if(!empty($arrMoney)){
                    $invest_num = count($arrMoney);
                    foreach ($arrMoney as $value2) {
                        $invest_money = $value2['money'] + $invest_money;
                    }
                }

                // 提现
                $data_draw_money = [
                    "userid"=>$userid,
                    "status"=>"1",
                ];

                $arrDraw = Withdraw::select("outMoney","userId")->where($data_draw_money)->get()->toArray();

                if(!empty($arrDraw)){
                     $draw_num = count($arrDraw);
                    foreach ($arrDraw as $value2) {
                        $draw_money = $value2['outMoney'] + $draw_money;
                    }
                }

                // $res_sum_price = DB::select("select sum(t1.money) as sum_price, t2.oddBorrowPeriod from work_oddmoney t1 LEFT JOIN work_odd t2 on t1.oddNumber=t2.oddNumber where progress<>'fail' and oddBorrowStyle='month' and t1.type='invest' group by oddBorrowPeriod");


                // select sum(t1.money), t2.oddBorrowPeriod from work_oddmoney t1 LEFT JOIN work_odd t2 on t1.oddNumber=t2.oddNumber where progress<>'fail' and oddBorrowStyle='month' and t1.type='invest' group by oddBorrowPeriod

                  $res_sum_price = DB::select("select sum(t1.money) as sum_price, t2.oddBorrowPeriod from work_oddmoney t1 LEFT JOIN work_odd t2 on t1.oddNumber=t2.oddNumber where progress<>'fail' and oddBorrowStyle='month' and t1.type='invest' and t2.userId='".$userid."' group by oddBorrowPeriod");
                 if(!empty($res_sum_price)){
                    // _dd($userid,1);
                    $a = json_encode($res_sum_price);
                    $res_sum_price = json_decode($a,true);
                    // _dd($res_sum_price,1);
                    foreach ($res_sum_price as  $value2) {
                        switch ($value2['oddBorrowPeriod']) {
                            case '1':
                                $arr_month_1 = isset($value2['sum_price']) ? $value2['sum_price'] : 0;
                                break;
                            case '2':
                                $arr_month_2 = isset($value2['sum_price']) ? $value2['sum_price'] : 0;
                                break;
                            case '3':
                                $arr_month_3 = isset($value2['sum_price']) ? $value2['sum_price'] : 0;
                                break;
                            case '6':
                                $arr_month_6 = isset($value2['sum_price']) ? $value2['sum_price'] : 0;
                                break;
                            case '12':
                                $arr_month_12 = isset($value2['sum_price']) ? $value2['sum_price'] : 0;
                                break;
                            case '24':
                                $arr_month_24 = isset($value2['sum_price']) ? $value2['sum_price'] : 0;
                                break;
                        }
                        
                    }
                  
                   // _dd($arr_month_1);
                   // _dd($arr_month_2);
                   // _dd($arr_month_3);
                   // _dd($arr_month_6);
                   // _dd($arr_month_12);
                   // _dd($arr_month_24);
                   // 
                    // $arr_month_1 = isset($res_sum_price[0]['sum_price']);
                    // $arr_month_2 = $res_sum_price[1]['sum_price'];
                    // $arr_month_3 = $res_sum_price[2]['sum_price'];
                    // $arr_month_6 = $res_sum_price[3]['sum_price'];
                    // $arr_month_12 = $res_sum_price[4]['sum_price'];
                    // $arr_month_24 = $res_sum_price[5]['sum_price'];
                 }


                $arr_res[] = [
                    'charge_num' => $charge_num, 
                    'invest_num' => $invest_num, 
                    'draw_num' => $draw_num, 

                    'charge_money' => $charge_money,
                    'invest_money' => $invest_money,
                    'draw_money' => $draw_money,

                    'userId' => $value['userId'],
                    'username' => $value['username'],
                    'addtime' => $value['addtime'],

                    'arr_month_1' => $arr_month_1,
                    'arr_month_2' => $arr_month_2,
                    'arr_month_3' => $arr_month_3,
                    'arr_month_6' => $arr_month_6,
                    'arr_month_12' => $arr_month_12,
                    'arr_month_24' => $arr_month_24,
                ];
                // _dd($userid);
                // 
                $arr_month_1 = 0;
                $arr_month_2 = 0;
                $arr_month_3 = 0;
                $arr_month_6 = 0;
                $arr_month_12 = 0;
                $arr_month_24 = 0;

            }
           
        }
        // exit;
        return $arr_res;
    }


    /**
     * 详情推广列表
     * @return mixed
     */
    public function extensionListAction() {
        $this->submenu = 'extension';
        $res = Promotion::all();
        $this->display('extensionList',['all'=>$res]);

    }

    /**
     * app推广列表
     * @return mixed
     */
    public function appListAction() {
        $this->submenu = 'extensionapp';
        $this->display('appList');
    }

    /**
     * 导出excel
     * @return [type] [description]
     */
    
    public function downExcelAction(){
        $excel = $this->common(1,"","","excel");
         $other = [
             'title' => '渠道数据',
             'columns' => [
                 'id' => ['name'=>'编号', 'type'=>'string'],
                 'channel_name' => ['name'=>'渠道名称', 'type'=>'string'],
                 'channel_code' => ['name'=>'渠道代号', 'type'=>'string'],
                 'pv_num' => ['name'=>'PV人数', 'type'=>'string'],
                 'uv_num' => ['name'=>'UV人数', 'type'=>'string'],
                 'reg_sum' => ['name'=>'注册人数', 'type'=>'string'],
                 'invest_money' => ['name'=>'充值金额', 'type'=>'string'],
                 'invest_num' => ['name'=>'充值人数', 'type'=>'string'],
                 'charge_money' => ['name'=>'投资金额', 'type'=>'string'],
                 'charge_num' => ['name'=>'投资人数', 'type'=>'string'],
                 'draw_money' => ['name'=>'提现金额', 'type'=>'string'],
                 'draw_num' => ['name'=>'提现人数', 'type'=>'string'],
             ],
         ];

         $excelRecords = [];
         foreach ($excel as $row) {
             $row['id']  = $row['id'];
             $row['channel_name']  = $row['channelName'];
             $row['channel_code']  = $row['channelCode'];
             $row['pv_num']  = $row['pv_num'];
             $row['uv_nnm']  = $row['uv_num'];
             $row['reg_sum']  = $row['reg_sum'];
             $row['invest_money']  = $row['invest_money'];
             $row['invest_num']  = $row['invest_num'];
             $row['charge_money']  = $row['charge_money'];
             $row['charge_num']  = $row['charge_num'];
             $row['draw_money']  = $row['draw_money'];
             $row['draw_num']  = $row['draw_num'];
             $excelRecords[] = $row;
         }

         ExcelHelper::getDataExcel($excelRecords, $other);
    }


    /**
     * 条件搜索搜索
     */
    public function  searchAction(){
        $params = $this->getAllPost();
        $type = $params['type'];
        switch ($type) {
            // case 'user': //查询用户
            //     $this->display('search', ['channelList'=>$qd,'status'=>""]);
            //     break;        
            
            case 'qd': //查询渠道
                $name = trim($params['qd_text'],"");
                $qd = $this->channelSearch($name);
                $this->display('search', ['channelList'=>$qd,'status'=>""]);
                break;

            case 'datetime': //查询时间

                // $this->redirect('/admin/promotion/dateList');
                break;
            default:
                break;
        }
    }



    /**
     * 查询时间
     * @return [type] [description]
     */
    public function dateListAction()
    {
        $fir_time = $_REQUEST['beginTime'];
        $sec_time = $_REQUEST['endTime'];

        if(empty($fir_time) || empty($sec_time)){
            Flash::error('查询时间不能为空');
            $this->redirect('/admin/promotion/list');
        }
        $time = ['fir' => $fir_time,'sec' => $sec_time];  

        $this->submenu = 'promotion';
        $arr = [];

        $objUser = "";

        $sum = Promotion::all()->count();

        $id = $this->getQuery("id") ? $this->getQuery("id") : 0;
        $page_length = $this->getQuery("num") ? $this->getQuery("num") : 15;
        
        if($id!=0){
            $id=$id-1;
        }

        $res_id = ceil($sum / $page_length);

        $page_res = $page_length * $id;
        $arr_page = [
            'sum' =>$sum,
            'p' => $id,
            'res_id'=>$res_id
        ];

        // 获取所有
        $arr = $this->common("",$page_res,$page_length,"","",$time);

        $arr2 = $this->common(1);

        $sum_invest_money = 0;
        $sum_invest_num = 0;

        $sum_charge_money = 0;
        $sum_charge_num = 0;

        $sum_draw_money = 0;
        $sum_draw_num = 0;

        $sum_pv_num = 0;
        $sum_uv_num = 0;

        $sum_reg_num = 0;
        
        foreach ($arr2 as  $value) {
            $sum_invest_money = $value['invest_money'] + $sum_invest_money;
            $sum_invest_num = $value['invest_num'] + $sum_invest_num;

            $sum_charge_money = $value['charge_money'] + $sum_charge_money;
            $sum_charge_num = $value['charge_num'] + $sum_charge_num;

            $sum_draw_money = $value['draw_money'] + $sum_draw_money;
            $sum_draw_num = $value['draw_num'] + $sum_draw_num;

            $sum_pv_num = $value['pv_num'] + $sum_pv_num;
            $sum_uv_num = $value['uv_num'] + $sum_uv_num;
            $sum_reg_num = $value['reg_sum'] + $sum_reg_num;
        }

     
        $this->display('dateList',['channelList'=> $arr,'arr_page'=>$arr_page,'num'=>$page_length,'date_res'=>$time]);
    }

    /**
     * 渠道搜索
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function channelSearch($value='')
    {   
        $charge_num = 0; //充值人数
        $invest_num = 0; //投资人数
        $draw_num = 0; //提现人数

        $charge_money = 0;
        $invest_money = 0;
        $draw_money = 0;

        $draw_money = 0;

        $pv_num = 0;
        $uv_num = 0;


        $data=[
            'enStatus'=>1,
            'channelName'=>$value,
        ];

        $res = Promotion::where($data)->first();
        if(empty($res)){
             Flash::error('渠道信息不存在');
             $this->redirect('/admin/promotion/list');
        }

        $res = $res->toArray();

        $objUser = User::where("system_userinfo.channel_id","=",$res['id'])
                    ->select("userid")
                    ->get()->toArray(); 

        // 多数组
        foreach ($objUser as $value) {
            $accessLogs = AccessLogs::where("pm_id",$value['userid'])->get()->toArray();
            foreach ($accessLogs as $value2) {
               $arr_tmp[] = [
                'ip'=>$value2['ip'],
                'pm_id'=>$value2['pm_id'],
               ];
            }

            $pv_num = count($accessLogs);
            $uv_num_tmp = _array_unique_fb($arr_tmp);
            $uv_num = count($uv_num_tmp);
        }
        

        $reg_sum = count($objUser) ? count($objUser) : 0;

        if(!empty($objUser)){
            foreach ($objUser as $value2) {
                // 投资
                $userid = $value2['userid'];
                $data = [
                    "userid"=>$userid,
                    "type"=>"invest",
                ];
                $arrMoney = OddMoney::select("money","userId")->where($data)->get()->toArray();
                if(!empty($arrMoney)){
                    $invest_num++;
                    foreach ($arrMoney as $value2) {
                        $invest_money = $value2['money'] + $invest_money;
                    }
                }

                // 充值
                $data_charge = [
                    "userid"=>$userid,
                    "status"=>"1",
                ];
                $arrUserCharge = Recharge::select("money","userId")->where($data_charge)->get()->toArray();

                if(!empty($arrUserCharge)){
                    $charge_num++;
                    foreach ($arrUserCharge as $value2) {
                        $charge_money = $value2['money'] + $charge_money;
                    }
                }

                // 提现
                $data_draw_money = [
                    "userid"=>$userid,
                    "status"=>"1",
                ];

                $arrDraw = Withdraw::select("outMoney","userId")->where($data_draw_money)->get()->toArray();

                if(!empty($arrDraw)){
                    $draw_num++;
                    foreach ($arrDraw as $value2) {
                        $draw_money = $value2['outMoney'] + $draw_money;
                    }
                }
            }
        }

        $arr[] = [
            'id'=>$res['id'],
            'channelName'=>$res['channelName'],
            'channelCode'=>$res['channelCode'],
            'reg_sum'=>$reg_sum,

            'invest_money'=>$invest_money, //投资金额
            'invest_num'=>$invest_num, //投资人数

            'charge_money'=>$charge_money, //充值金额
            'charge_num'=>$charge_num, //充值人数
          
            'draw_money'=>$draw_money, //提现金额
            'draw_num'=>$draw_num, //提现人数

            'pv_num'=>$pv_num, //pv访问量
            'uv_num'=>$uv_num, //uv访问量
            'reg_sum'=>$reg_sum, //注册人数
        ];
        return $arr;
    }

    /**
     * 生成二维码
     * @return [type] [description]
     */
    public function appQrcodeAction() {

           $value = 'http://www.cnblogs.com/txw1958/'; //二维码内容   
           $errorCorrectionLevel = 'L';//容错级别   
           $matrixPointSize = 6;//生成图片大小   
           //生成二维码图片   
           QRcode::png($value, 'qrcode.png', $errorCorrectionLevel, $matrixPointSize, 2);   
           // $logo = 'logo.png';//准备好的logo图片   
           // $QR = 'qrcode.png';//已经生成的原始二维码图   
           $logo = 'https://ss2.baidu.com/6ONYsjip0QIZ8tyhnq/it/u=3238213565,2096713070&fm=58';//准备好的logo图片   
           $QR = 'https://ss1.baidu.com/6ONXsjip0QIZ8tyhnq/it/u=4069290322,3412673657&fm=58';//已经生成的原始二维码图   
        
           if ($logo !== FALSE) {   
               $QR = imagecreatefromstring(file_get_contents($QR));   
               $logo = imagecreatefromstring(file_get_contents($logo));   
               // _dd($logo,1);
               $QR_width = imagesx($QR);//二维码图片宽度   
               $QR_height = imagesy($QR);//二维码图片高度   
               $logo_width = imagesx($logo);//logo图片宽度   
               $logo_height = imagesy($logo);//logo图片高度   
               $logo_qr_width = $QR_width / 5;   
               $scale = $logo_width/$logo_qr_width;   
               $logo_qr_height = $logo_height/$scale;   
               $from_width = ($QR_width - $logo_qr_width) / 2;   
               //重新组合图片并调整大小   
               imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,   
               $logo_qr_height, $logo_width, $logo_height);   
           }   
           //输出图片   
           imagepng($QR, '\helloweixin.png');   
           echo '<img src="helloweixin.png">';
    }

    /**
     * [commonUser description]
     * @param  [type] $arr [description]
     * @return [type]      [description]
     */
    public function commonUser($arr,$url="")
    {
        $charge_num = 0; //充值人数
        $invest_num = 0; //投资人数
        $draw_num = 0; //提现人数

        $charge_money = 0;
        $invest_money = 0;
        $draw_money = 0;

        // $url = $_SERVER['REQUEST_URI'];
        // _dd($url,1);
        $arr_user= User::where($arr)->first();
        if(empty($arr_user)){
            Flash::error('该用户信息不存在');
            $this->redirect($url);
        }
        
        $arr_user = $arr_user->toArray();
        if(!empty($arr_user)){
            // foreach ($arr_user as $value) {
                $arr_month_1 = 0;
                $arr_month_2 = 0;
                $arr_month_3 = 0;
                $arr_month_6 = 0;
                $arr_month_12 = 0;
                $arr_month_24 = 0;
                

                // 充值
                $userid = $arr_user['userId'];
              
                $data_charge = [
                    "userid"=>$userid,
                    "status"=>"1",
                ];

                $arrUserCharge = Recharge::select("money","userId")->where($data_charge)->get()->toArray();
                
                if(!empty($arrUserCharge)){
                    $charge_num = count($arrUserCharge);
                    foreach ($arrUserCharge as $value2) {
                        $charge_money = $value2['money'] + $charge_money;
                    }
                }

                // 投资
                $userid = $arr_user['userId'];

                $data = [
                    "userid"=>$userid,
                    "type"=>"invest",
                ];
                $arrMoney = OddMoney::select("money","userId")->where($data)->get()->toArray();
                if(!empty($arrMoney)){
                    $invest_num = count($arrMoney);
                    foreach ($arrMoney as $value2) {
                        $invest_money = $value2['money'] + $invest_money;
                    }
                }

                // 提现
                $data_draw_money = [
                    "userid"=>$userid,
                    "status"=>"1",
                ];

                $arrDraw = Withdraw::select("outMoney","userId")->where($data_draw_money)->get()->toArray();

                if(!empty($arrDraw)){
                     $draw_num = count($arrDraw);
                    foreach ($arrDraw as $value2) {
                        $draw_money = $value2['outMoney'] + $draw_money;
                    }
                }
                  $res_sum_price = DB::select("select sum(t1.money) as sum_price, t2.oddBorrowPeriod from work_oddmoney t1 LEFT JOIN work_odd t2 on t1.oddNumber=t2.oddNumber where progress<>'fail' and oddBorrowStyle='month' and t1.type='invest' and t2.userId='".$userid."' group by oddBorrowPeriod");


                 if(!empty($res_sum_price)){
                    $a = json_encode($res_sum_price);
                    $res_sum_price = json_decode($a,true);
                    $arr_month_1 = $res_sum_price[0]['sum_price'];
                    $arr_month_2 = $res_sum_price[1]['sum_price'];
                    $arr_month_3 = $res_sum_price[2]['sum_price'];
                    $arr_month_6 = $res_sum_price[3]['sum_price'];
                    $arr_month_12 = $res_sum_price[4]['sum_price'];
                    $arr_month_24 = $res_sum_price[5]['sum_price'];
                 }


                $arr_res[] = [
                    'charge_num' => $charge_num, 
                    'invest_num' => $invest_num, 
                    'draw_num' => $draw_num, 

                    'charge_money' => _format_price($charge_money),
                    'invest_money' => _format_price($invest_money),
                    'draw_money' => _format_price($draw_money),

                    'userId' => $arr_user['userId'],
                    'username' => $arr_user['username'],
                    'addtime' => $arr_user['addtime'],

                    'arr_month_1' => _format_price($arr_month_1),
                    'arr_month_2' => _format_price($arr_month_2),
                    'arr_month_3' => _format_price($arr_month_3),
                    'arr_month_6' => _format_price($arr_month_6),
                    'arr_month_12' => _format_price($arr_month_12),
                    'arr_month_24' => _format_price($arr_month_24),
                ];
            //}
        }
        return $arr_res;
    }


    /**
     * 查询用户
     * @return [type] [description]
     */
    public function userSearchAction()
    {
         $this->submenu = 'promotion';
        $params = $this->getAllPost();

        $name = trim($params['name']);
     
        $arr_user = null;
        $data_user=[
            'status'=>1,
            'username'=>$name,
        ];

        $res = $this->commonUser($data_user,$params['res_url']);

        $arr = $res;
        $this->display('userSearch',['arr'=>$arr]);      
    }

}
