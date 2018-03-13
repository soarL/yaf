<?php
use Admin as Controller;
use helpers\ExcelHelper;
use models\StandMoney;
use models\UserFriend;
use Yaf\Registry;
use traits\PaginatorInit;
use models\User;
use helpers\IDHelper;
use models\Invest;
use models\Odd;
use models\Interest;
use models\OddMoney;
use models\TrafficHour;
use models\TrafficDay;
use models\TrafficWeek;
use models\TrafficMonth;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * DataController
 * 数据分析
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class DataController extends Controller {
	use PaginatorInit;

	public $menu = 'data';

	/**
     * 投资信息
     * @return  mixed
     */
	public function tendersAction() {
	
	}

    /**
     * 注册客群分析
     * @return  mixed
     */
    public function dayUsersAction() {
        $this->submenu = 'index';
        $queries = $this->queries->defaults(['day'=>'']);
        $day = $queries->day;
        /*
        select t1.dt day, regCount, rechargeCount, tenderCount from (select count(userId) regCount,DATE_FORMAT(addtime, "%Y-%m-%d") dt from system_userinfo GROUP BY DATE_FORMAT(addtime, "%Y-%m-%d")) t1 inner join 
(select count(distinct userId) rechargeCount,DATE_FORMAT(time, "%Y-%m-%d") dt from user_moneyrecharge where `status`='1' and mode='in' and source='1' GROUP BY DATE_FORMAT(time, "%Y-%m-%d")) t2 on t1.dt=t2.dt inner join 
(select count(distinct userId) tenderCount,DATE_FORMAT(time, "%Y-%m-%d") dt from work_oddmoney where (`status`=0 or `status`=1) and type='invest' GROUP BY DATE_FORMAT(time, "%Y-%m-%d")) t3 on t1.dt=t3.dt


select userId, username, phone from system_userinfo where userId in (select distinct userId from work_oddmoney where time like '2016-05%') and
userId in (select distinct userId from work_oddmoney where time like '2016-06%') and
userId in (select distinct userId from work_oddmoney where time like '2016-07%')

select * from (select t1.userId, CONCAT('@',cardnum) IDCard,sex,(2016-if(LENGTH(cardnum)=18, substring(cardnum, 7, 4), substring(cardnum, 7, 2))) age, DATE_FORMAT(addtime, "%Y-%m-%d") regTime, DATE_FORMAT(t2.time, "%Y-%m-%d") tenderTime from system_userinfo t1 left JOIN 
(select userId, time from work_oddmoney where `status`=1 and type='invest' group by userId order by time asc) t2 on t1.userId=t2.userId and cardnum is not null and cardnum<>'') t where tendertime is not null



         */

        $this->display('index', ['records'=>'', 'queries'=>$queries]);
    }

    /**
     * 地域数据（以用户身份证分析）
     * @return mixed
     */
    public function areaAction() {
        $this->submenu = 'area';
        $invests = Invest::with(['user'=>function($q) {$q->select('userId', 'cardnum');}])->where('status', 0)->groupBy('userId')->get([DB::raw('sum(zongEr) stayAll'), 'userId']);
        $list = [];
        foreach ($invests as $invest) {
            if($invest->user==null) {
                continue;
            }
            $cardnum = $invest->user->cardnum;
            $province = IDHelper::getProvince($cardnum);
            if($province==false) {
                $province = '其他';
            }
            if(isset($list[$province])) {
                $list[$province] += $invest->stayAll;
            } else {
                $list[$province] = $invest->stayAll;
            }
        }
        arsort($list);
        $this->display('area', ['list'=>$list]);
    }

    public function yjhAction() {
        $queries = $this->queries->defaults(['day'=>date('Y-m-d')]);
        $dayEnd = $queries->day . ' 23:59:59';
        $oddTable = with(new Odd())->getTable();
        $investTable = with(new Invest())->getTable();
        $repayTable = with(new Interest())->getTable();
        $omTable = with(new OddMoney())->getTable();

        $result1 = DB::select("select sum(zongEr) total from ".$repayTable." t1 left join ".$oddTable." t2 on t1.oddNumber=t2.oddNumber where t2.progress<>'fail' and t1.endtime>? and t2.oddRehearTime<?", [$dayEnd, $dayEnd]);

        $result2 = DB::select("select count(DISTINCT t2.userId) count from ".$repayTable." t1 left join ".$oddTable." t2 on t1.oddNumber=t2.oddNumber where t2.progress<>'fail' and t1.endtime>? and t2.oddRehearTime<?", [$dayEnd, $dayEnd]);

        $result3 = DB::select("select count(DISTINCT t1.userId) count from ".$investTable." t1 left join ".$oddTable." t2 on t1.oddNumber=t2.oddNumber where t2.progress<>'fail' and t1.endtime>? and t2.oddRehearTime<?", [$dayEnd, $dayEnd]);

        $result4 = DB::select("select sum(oddBorrowPeriod*30)/count(1) total from ".$oddTable." where progress<>'fail' and oddTrialTime<?", [$dayEnd]);

        $result5 = DB::select("select sum(zongEr)/COUNT(DISTINCT t2.oddNumber) total from ".$repayTable." t1 left join ".$oddTable." t2 on t1.oddNumber=t2.oddNumber where t2.progress<>'fail' and t1.endtime>? and t2.oddRehearTime<?", [$dayEnd, $dayEnd]);

        $result6 = DB::select("select sum(val1)/sum(val2) total from (select sum(benJin)*(t2.oddYearRate/360)*t2.oddBorrowPeriod*30 val1, sum(benJin) val2 from ".$repayTable." t1 left join ".$oddTable." t2 on t1.oddNumber=t2.oddNumber where t2.progress<>'fail' and t1.endtime>? and t2.oddRehearTime<? GROUP BY t2.oddNumber) t", [$dayEnd, $dayEnd]);

        $result7 = DB::select("select 365/(sum(oddBorrowPeriod*30)/count(1)) total from ".$oddTable." where progress<>'fail' and oddTrialTime<?", [$dayEnd]);
        $this->display('yjh', [
            'stay'=>$result1[0]->total, 
            'inum'=>$result2[0]->count, 
            'onum'=>$result3[0]->count, 
            'period'=>$result4[0]->total, 
            'money'=>$result5[0]->total, 
            'rate'=>($result7[0]->total*$result6[0]->total),
            'queries'=>$queries
        ]);
    }


    /**
     * 运营每日活动数据提取
     */
    public function activityDataAction(){
        $queries = $this->queries->defaults(['searchType'=>'','beginTime'=>'', 'endTime'=>'']);
        //每日注册用户投资数据
        if($queries->searchType== 'registerInvest'){
            $result = DB::table('system_userinfo as t1')
                ->leftJoin(DB::raw('(select sum(money) rechargeMoney, userId from user_moneyrecharge where mode = "in" and status = "1"  and source = "1" group by userId) as t2'), 't1.userId', '=','t2.userId' )
                ->leftJoin(DB::raw('(select sum(zongEr) stayMoney, strUserId from work_oddinterest_invest where status = "0" group by strUserId) as t3'), 't1.userId', '=', 't3.strUserId')
                ->leftJoin(DB::raw('(select sum(money) tenderMoney, userId from  work_oddmoney where type = "invest" group by userId ) as t4'), 't1.userId', '=', 't4.userId')
                 ->where('t1.channel_id', '>', 0)
                 ->where('t1.channel_id', '<', 11)
                ->where('addtime', '>=', $queries->beginTime)
                ->where('addtime', '<=', $queries->endTime)
                ->select('t1.username', 't1.phone', 't1.addtime','t1.channel_id', 't1.cardstatus', 't1.thirdAccountAuth', 't2.rechargeMoney', 't3.stayMoney')->get();
               $result = json_decode(json_encode($result), true);
                if(isset($result)){
                    $other = [
                        'title' => '每日活动注册用户',
                        'columns' => [
                            'username' => ['name'=>'用户名'],
                            'phone' => ['name'=>'手机'],
                            'addtime' => ['name'=>'注册时间'],
                            'channel_id' => ['name'=>'渠道id'],
                            'cardstatus' => ['name'=>'实名认证'],
                            'thirdAccountAuth' => ['name'=>'授权情况'],
                            'rechargeMoney' => ['name'=>'充值金额'],
                            'stayMoney' => ['name'=>'目前待收'],
                        ],
                    ];
                    $excelRecords = [];
                    foreach ($result as $row) {
                        $excelRecords[] = $row;
                    }
                    ExcelHelper::getDataExcel($excelRecords, $other);
                }
        }

        //每日活动注册量数据
        if($queries->searchType== 'registerCount'){
            $result = DB::select('SELECT  count(userId) as RegCount ,channel_id,DATE(addtime) as rtime  FROM system_userinfo
                     where channel_id > 0 and channel_id < 11 and DATE(addtime) between ? and ? GROUP BY channel_id 
                    ',[$queries->beginTime,$queries->endTime]);
            $result = json_decode(json_encode($result), true);
            if(isset($result)){
                $other = [
                    'title' => '推广页面注册量',
                    'columns' => [
                        'RegCount' => ['name'=>'注册量'],
                        'channel_id' => ['name'=>'渠道id'],
                        'rtime' => ['name'=>'注册时间'],
                    ],
                ];
                $excelRecords = [];
                foreach ($result as $row) {
                    $excelRecords[] = $row;
                }
                ExcelHelper::getDataExcel($excelRecords, $other);
            }
        }

        //大佛A级用户 注册数据 A级用户：通过pengfujun账号推荐的用户 421需求
        if ($queries->searchType == 'dafoA'){

            $builder = User::with(['oddMoney'=>function($query){
                $query->select('userId',DB::raw('sum(money) stayMoney'))->groupBy('userId');
            }])->where('tuijian','pengfujun');

            if($queries->beginTime!='') {
                $builder->where('addtime', '>=', $queries->beginTime);
            }
            if($queries->endTime!='') {
                $builder->where('addtime', '<=', $queries->endTime);
            }

            $result = $builder->get(['userId','name','phone']);

            if(isset($result)){
                $other = [
                    'title' => '大佛A级用户注册数据',
                    'columns' => [
                        'userId' => ['name'=>'用户id'],
                        'phone' => ['name'=>'手机号'],
                        'name' => ['name'=>'姓名'],
                        'money' => ['name'=>'投资金额'],
                    ],
                ];
                $excelRecords = [];
                foreach ($result as $row) {
                    $item = [];
                    $item['userId'] = $row->userId;
                    $item['phone'] = $row->phone;
                    $item['name'] = $row->name;
                    $item['money'] = $row->oddMoney[0]->stayMoney;
                    $excelRecords[] = $item;

                }
                ExcelHelper::getDataExcel($excelRecords, $other);
            }
        }

        //大佛A级用户 注册数据 A级用户：通过pengfujun账号推荐的用户 422需求 月底待收
        if ($queries->searchType == 'dafoAmonth'){
            $endTime = $queries->endTime;
            if ($queries->endTime) {
                $result = DB::select("SELECT t1.userId, phone,name,stayBenjin FROM system_userinfo t1 LEFT JOIN
                (select a.userId,sum(benJin) stayBenjin ,a.oddNumber from work_oddinterest_invest a left join work_odd b on a.oddNumber=b.oddNumber where  DATE(endtime)>='$endTime' and DATE(b.oddRehearTime)< '$endTime' group by a.userId)
                t2 ON t1.userId = t2.userId
                where tuijian = 'pengfujun' ");
            }
            if(isset($result)){
                $other = [
                    'title' => '大佛A级用户注册数据',
                    'columns' => [
                        'userId' => ['name'=>'用户id'],
                        'phone' => ['name'=>'手机号'],
                        'name' => ['name'=>'姓名'],
                        'money' => ['name'=>'月底待收'],
                    ],
                ];
                $excelRecords = [];
                foreach ($result as $row) {
                    $item = [];
                    $item['userId'] = $row->userId;
                    $item['phone'] = $row->phone;
                    $item['name'] = $row->name;
                    $item['money'] = $row->stayBenjin;
                    $excelRecords[] = $item;

                }
                ExcelHelper::getDataExcel($excelRecords, $other);
            }
        }

        //大佛B级用户 注册数据 B级用户：通过A级账号推荐的用户 421需求
        if ($queries->searchType == 'dafoB'){
            $userIds = UserFriend::where('userId',1000003000)->lists('friend');
            $friends = UserFriend::whereIn('userId',$userIds)->lists('friend');
            $builder = User::with(['oddMoney'=>function($query){
                $query->select('userId',DB::raw('sum(money) stayMoney'))->groupBy('userId');
            }])->whereIn('userId',$friends);

            if($queries->beginTime!='') {
                $builder->where('addtime', '>=', $queries->beginTime);
            }
            if($queries->endTime!='') {
                $builder->where('addtime', '<=', $queries->endTime);
            }

            $result = $builder->get(['userId','name','phone']);
            if(isset($result)){
                $other = [
                    'title' => '大佛B级用户注册数据',
                    'columns' => [
                        'userId' => ['name'=>'用户id'],
                        'phone' => ['name'=>'手机号'],
                        'name' => ['name'=>'姓名'],
                        'money' => ['name'=>'投资金额'],
                    ],
                ];
                $excelRecords = [];
                foreach ($result as $row) {
                    $item = [];
                    $item['userId'] = $row->userId;
                    $item['phone'] = $row->phone;
                    $item['name'] = $row->name;
                    $item['money'] = $row->oddMoney[0]->stayMoney;
                    $excelRecords[] = $item;
                }
                ExcelHelper::getDataExcel($excelRecords, $other);
            }
        }

        //大佛B级用户  422需求 月底待收
        if ($queries->searchType == 'dafoBmonth'){
            $endTime = $queries->endTime;
            if ($endTime){
                $userIds = UserFriend::where('userId',1000003000)->lists('friend');
                $friends = UserFriend::whereIn('userId',$userIds)->lists('friend')->toArray();
                $sqlStr = '('.rtrim(str_repeat('?, ', count($friends)), ', ').')';
                $result = DB::select("SELECT t1.userId, phone,name,stayBenjin FROM system_userinfo t1 LEFT JOIN
                (select a.userId,sum(benJin) stayBenjin ,a.oddNumber from work_oddinterest_invest a left join work_odd b on a.oddNumber=b.oddNumber and DATE(endtime)>='$endTime' and DATE(b.oddRehearTime)< '$endTime' group by a.userId)
                t2 ON t1.userId = t2.userId
                where t1.userId IN $sqlStr ",$friends);
            }
            if(isset($result)){
                $other = [
                    'title' => '大佛B级用户注册数据',
                    'columns' => [
                        'userId' => ['name'=>'用户id'],
                        'phone' => ['name'=>'手机号'],
                        'name' => ['name'=>'姓名'],
                        'money' => ['name'=>'月底待收'],
                    ],
                ];
                $excelRecords = [];
                foreach ($result as $row) {
                    $item = [];
                    $item['userId'] = $row->userId;
                    $item['phone'] = $row->phone;
                    $item['name'] = $row->name;
                    $item['money'] = $row->stayBenjin;
                    $excelRecords[] = $item;

                }
                ExcelHelper::getDataExcel($excelRecords, $other);
            }
        }
        $this->display('activity',['queries'=>$queries]);


    }

    /**
     * 站岗资金统计
     */
    public function standMoneyAction()
    {
        $this->submenu = 'standMoney';
        $excel = $this->getQuery('excel', 0);
        $queries = $this->queries->defaults(['sortBy'=>'id', 'sortType'=>'desc', 'beginTime'=>'', 'endTime'=>'']);
        $builder = StandMoney::orderBy($queries->sortBy, $queries->sortType);
        if ($queries->beginTime !='') {
            $builder->where('addtime', '>=', $queries->beginTime . ' 00:00:00');
        }
        if ($queries->endTime !='') {
            $builder->where('addtime', '<=', $queries->endTime . ' 23:59:59');
        }

        if($excel) {
            $data = $builder->get();
            $other = [
                'title' => '站岗资金统计',
                'columns' => [
                    'id' => ['name'=>'编号'],
                    'addtime' => ['name'=>'日期'],
                    'validCount' => ['name'=>'有效站岗人数'],
                    'validMoney' => ['name'=>'有效站岗资金'],
                    'invalidCount' => ['name'=>'无效站岗人数'],
                    'invalidMoney' => ['name'=>'无效站岗资金'],
                    'sumCount' => ['name'=>'站岗总人数'],
                    'sumMoney' => ['name'=>'站岗总额'],
                ],
            ];
            $excelRecords = [];
            foreach ($data as $row) {
                $excelRecords[] = $row;
            }
            ExcelHelper::getDataExcel($excelRecords, $other);
        } else {
            $field = [
                'sum(validCount) as sumValidCount',
                'sum(validMoney) as sumVaildMoney',
                'sum(invalidCount) as sumInvalidCount',
                'sum(invalidMoney) as sumInvalidMoney',
            ];
            $builderClone = clone $builder;
            $data = $builder->paginate();
            $data->appends($queries->all());
            $sum = $builderClone->select(DB::raw(implode(',',$field)))->first();
        }
        $this->display('standmoney',['data'=>$data,'queries'=>$queries,'sum'=>$sum]);
    }

    public function accessChartsAction() {
        $this->display('accessCharts');
    }

    public function chartDataAction() {
        $queries = $this->queries->defaults(['type'=>'hour', 'channel'=>'all', 'beginTime'=>'', 'endTime'=>'']);
        $rdata = [];
        if($queries->type!='hour' && ($queries->beginTime==''||$queries->endTime=='')) {
            $rdata['status'] = 1;
            $rdata['info'] = '请选择统计时间';
            $this->backJson($rdata);
        }

        $channelName = '全部访问';
        if($queries->channel=='direct') {
            $channelName = '直接访问';
        }

        $builder = null;
        $title = '';
        $col = 'hour';
        $begin = '';
        $end = '';
        if($queries->type=='hour') {
            $builder = TrafficHour::whereRaw('1=1');
            $col = 'hour';
        } else if($queries->type=='date') {
            $builder = TrafficDay::whereRaw('1=1');
            $col = 'date';
            $begin = _date('Ymd', $queries->beginTime);
            $end = _date('Ymd', $queries->endTime);
        } else if($queries->type=='week') {
            $builder = TrafficWeek::whereRaw('1=1');
            $col = 'monday';
            $beginTime = strtotime($queries->beginTime);
            $endTime = strtotime($queries->endTime);
            $begin = date('Ymd', ($beginTime-((date('w', $beginTime)==0?7:date('w', $beginTime))-1)*24*3600));
            $end = date('Ymd', ($endTime-((date('w', $endTime)==0?7:date('w', $endTime))-1)*24*3600));

        } else if($queries->type=='month') {
            $builder = TrafficMonth::whereRaw('1=1');
            $col = 'month';
            $begin = _date('Ym', $queries->beginTime);
            $end = _date('Ym', $queries->endTime);
        }

        $builder->where('pm_key', $queries->channel);

        if($queries->type=='hour') {
            if($queries->beginTime=='') {
                $yestody = time()-24*60*60;
                $title = '['.$channelName.']'.date('n月j日', $yestody);
                $builder->where('hour', '>=', date('Ymd00', $yestody))->where('hour', '<=', date('Ymd23', $yestody));
            } else {
                $title = '['.$channelName.']'._date('n月j日', $queries->beginTime);
                $builder->where('hour', '>=', _date('Ymd00', $queries->beginTime))->where('hour', '<=', _date('Ymd23', $queries->beginTime));
            }
        } else {
            $builder->where($col, '>=', $begin)->where($col, '<=', $end);
        }

        $records = $builder->orderBy($col, 'asc')->get();

        $list = [];

        if($queries->type=='hour') {
            for ($i=0; $i < 24; $i++) {
                $list[$i.'H'] = ['pv'=>0, 'uv'=>0, 'ip'=>0];
            }
        }
        foreach ($records as $record) {
            $key = '';
            if($queries->type=='hour') {
                $key = intval(substr($record->$col, -2)).'H';
            } else if($queries->type=='date' || $queries->type=='week' ) {
                $key = date('Y-m-d', strtotime($record->$col));
            } else {
                $key = date('Y-m', strtotime($record->$col));
            }
            $list[$key] = ['pv'=>$record->pv, 'uv'=>$record->uv, 'ip'=>$record->ip];
        }
        $pvList = [];
        $ipList = [];
        $uvList = [];
        foreach ($list as $row) {
            $pvList[] = $row['pv'];
            $ipList[] = $row['ip'];
            $uvList[] = $row['uv'];
        }
        
        $rdata['status'] = 1;
        $rdata['info'] = '数据获取成功';
        $rdata['data']['pvList'] = $pvList;
        $rdata['data']['uvList'] = $uvList;
        $rdata['data']['ipList'] = $ipList;
        $rdata['data']['timeList'] = array_keys($list);
        $rdata['data']['title'] = $title;
        $this->backJson($rdata);
    }
}