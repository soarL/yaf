<?php
use Admin as Controller;
use tools\Pager;
use models\Recharge;
use models\User;
use Yaf\Registry;
use helpers\NetworkHelper;
use helpers\StringHelper;
use tools\MSBank;
use helpers\ExcelHelper;
use helpers\ArrayHelper;
use models\Attribute;
use traits\PaginatorInit;
use tools\API;
use tools\Log;
use custody\Handler;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * RechargeController
 * 用户充值查询等
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class RechargeController extends Controller {
    use PaginatorInit;

	public $menu = 'recharge';

    /**
     * 充值设置
     * @return  mixed
     */
    public function settingAction() {
        $this->submenu = 'setting';
        $weeks = ['1'=>'星期一', '2'=>'星期二', '3'=>'星期三', '4'=>'星期四', '5'=>'星期五', '6'=>'星期六', '0'=>'星期天'];
        $pays = ['lianlian'=>'连连支付', 'minsheng'=>'民生支付', 'baofoo'=>'宝付支付'];
        if($this->isPost()) {
            $params = $this->getAllPost();
            $data = [];
            foreach ($pays as $key => $pay) {
                if(isset($params[$key])) {
                    $data[$key]  = $params[$key];
                }
            }
            $attribute = Attribute::where('identity', 'recharge')->first();
            $status = false;
            if($attribute) {
                $attribute->value = json_encode($data);
                $status = $attribute->save();
            } else {
                $attribute = new Attribute();
                $attribute->name = '充值设置(请勿删除或修改)';
                $attribute->type = 'string';
                $attribute->identity = 'recharge';
                $attribute->value = json_encode($data);
                $status = $attribute->save();
            }
            if($status) {
                Flash::success('操作成功！');
                $this->redirect('/admin/recharge/setting');
            } else {
                Flash::error('操作失败！');
                $this->redirect('/admin/recharge/setting');
            }
        } else {
            $attribute = Attribute::where('identity', 'recharge')->first();
            $setting = $attribute['value'];
            $settingArr = [];
            foreach ($pays as $key => $pay) {
                $settingArr[$key] = [];
            }
            if($setting) {
                $settingList = json_decode($setting, true);
                foreach ($settingList as $key => $st) {
                    $settingArr[$key]  = $st;
                }
            }
            $this->display('setting', ['weeks'=>$weeks, 'pays'=>$pays, 'setting'=>$settingArr]);
        }
    }

	/**
     * 网站充值
     * @return  mixed
     */
	public function listAction() {
		$this->submenu = 'list';
        $payTypes = Recharge::$payTypes;
        $payWays = Recharge::$payWays;

        $excel = $this->getQuery('excel', 0);

        $queries = $this->queries->defaults([
            'searchType'=>'serialNumber', 
            'searchContent'=>'', 
            'status'=>'all', 
            'type'=>'all', 
            'beginTime'=>'', 
            'endTime'=>'',
            'payWay'=>'all',
            'payStatus'=>'all',
        ]);

        $builder = Recharge::getListBuilder($queries);

        $records = null;
        if($excel) {
            $records = $builder->orderBy('time', 'desc')->get();
            $other = [
                'title' => '网站充值',
                'columns' => [
                    'serialNumber' => ['name'=>'订单号', 'type'=>'string'],
                    'userId' => ['name'=>'用户ID', 'type'=>'string'],
                    'username' => ['name'=>'用户名', 'type'=>'string'],
                    'money' => ['name'=>'金额'],
                    'status' => ['name'=>'状态'],
                    'payType' => ['name'=>'类型'],
                    'payWay' => ['name'=>'方式'],
                    'time' => ['name'=>'时间'],
                ],
            ];
            $excelRecords = [];
            foreach ($records as $row) {
                $row['serialNumber']  = $row['serialNumber'];
                $row['userId'] = $row['userId'];
                $row['username'] = $row['user']['username'];
                $row['money'] = $row['money'];
                $row['status'] = ArrayHelper::getValue([1=>'成功', 0=>'未完成', -1=>'失败'], $row['status']);
                $row['payType'] = ArrayHelper::getValue($payTypes, $row['payType']);
                $row['payWay'] = ArrayHelper::getValue($payWays, $row['payWay']);
                $row['time'] = $row['time'];
                $excelRecords[] = $row;
            }
            ExcelHelper::getDataExcel($excelRecords, $other);
        } else {
            $records = $builder->orderBy('time', 'desc')->paginate(20);
            $records->appends($queries->all());
        }

		$this->display('list', ['records'=>$records, 'queries'=>$queries, 'payTypes'=>$payTypes, 'payWays'=>$payWays]);
	}

    /**
     * 充值汇总
     * @return  mixed
     */
    public function countAction() {
        $this->submenu = 'count';
        $beginTime = $this->getQuery('beginTime', '');
        $endTime = $this->getQuery('endTime', '');

        $data = [];
        $data['beginTime'] = $beginTime;
        $data['endTime'] = $endTime;

        $where = ['and', 'mode=\'in\'', 'source=\'1\'', 'operator<>\'admin\'', 'status=\'1\''];
        
        $builder = Recharge::where('status', 1);

        if($beginTime!='') {
            $builder->where('time', '>=', $beginTime . ' 00:00:00');
        }

        if($endTime!='') {
            $builder->where('time', '<=', $endTime . ' 23:59:59');
        }

        $data['total'] = $builder->sum('money');
        $data['count'] = $builder->count();

        // 连连支付
        $llBuilder = clone $builder;
        $llBuilder->where('payType', 'lianlian');
        $data['llCount'] = $llBuilder->count();
        $data['llTotal'] = $llBuilder->sum('money');

        // 连连支付-认证支付
        $llrzBuilder = clone $llBuilder;
        $llrzBuilder->where('payWay', 'D');
        $data['rzCount'] = $llrzBuilder->count();
        $data['rzTotal'] = $llrzBuilder->sum('money');

        // 连连支付-网银支付
        $llwyWhere = clone $llBuilder;
        $llwyWhere->where('payWay', 1);
        $data['wyCount'] = $llwyWhere->count();
        $data['wyTotal'] = $llwyWhere->sum('money');

        // 汇潮支付
        $hcBuilder = clone $builder;
        $hcBuilder->whereRaw('(payType=? or payType=?)', ['yemadai', '']);
        $data['hcCount'] = $hcBuilder->count();
        $data['hcTotal'] = $hcBuilder->sum('money');

        // 民生支付
        $msBuilder = clone $builder;
        $msBuilder->where('payType', 'minsheng');
        $data['msCount'] = $msBuilder->count();
        $data['msTotal'] = $msBuilder->sum('money');

        // 民生支付-借记卡
        $jjmsBuilder = clone $msBuilder;
        $jjmsBuilder->where('payWay', 1);
        $data['jjCount'] = $jjmsBuilder->count();
        $data['jjTotal'] = $jjmsBuilder->sum('money');

        // 民生支付-信用卡
        $xymsBuilder = clone $msBuilder;
        $xymsBuilder->where('payWay', 2);
        $data['xyCount'] = $xymsBuilder->count();
        $data['xyTotal'] = $xymsBuilder->sum('money');

        // 民生支付-混合通道
        $hhmsBuilder = clone $msBuilder;
        $hhmsBuilder->where('payWay', 3);
        $data['hhCount'] = $hhmsBuilder->count();
        $data['hhTotal'] = $hhmsBuilder->sum('money');

        // 宝付支付
        $bfBuilder = clone $builder;
        $bfBuilder->where('payType', 'baofoo');
        $data['bfCount'] = $bfBuilder->count();
        $data['bfTotal'] = $bfBuilder->sum('money');

        // 宝付网银支付
        $wybfBuilder = clone $bfBuilder;
        $wybfBuilder->where('payWay', 'WEB');
        $data['wybfCount'] = $wybfBuilder->count();
        $data['wybfTotal'] = $wybfBuilder->sum('money');

        // 宝付认证支付
        $rzbfBuilder = clone $bfBuilder;
        $rzbfBuilder->where('payWay', 'SWIFT');
        $data['rzbfCount'] = $rzbfBuilder->count();
        $data['rzbfTotal'] = $rzbfBuilder->sum('money');

        // 宝付代扣支付
        $rzbfBuilder = clone $bfBuilder;
        $rzbfBuilder->where('payWay', 'deduct');
        $data['debfCount'] = $rzbfBuilder->count();
        $data['debfTotal'] = $rzbfBuilder->sum('money');

        // 富友支付
        $fyBuilder = clone $builder;
        $fyBuilder->where('payType', 'fuiou');
        $data['fyCount'] = $fyBuilder->count();
        $data['fyTotal'] = $fyBuilder->sum('money');

        $this->display('count', $data);
    }

	/**
     * 民生支付充值列表
     * @return  mixed
     */
	public function minshengAction() {
		$this->submenu = 'minsheng';

        $excel = $this->getQuery('excel', 0);
        $queries = $this->queries->defaults([
            'searchType'=>'serialNumber', 
            'searchContent'=>'', 
            'status'=>'all', 
            'type'=>'all', 
            'payWay'=>'all', 
            'beginTime'=>'', 
            'endTime'=>'',
            'payStatus'=>'all',
        ]);

        $builder = Recharge::getListBuilder($queries);
        $builder->where('payType', 'minsheng');
        $records = null;       
        if($excel) {
            $records = $builder->orderBy('time', 'desc')->get();
            $other = [
                'title' => '网站充值',
                'columns' => [
                    'serialNumber' => ['name'=>'订单号', 'type'=>'string'],
                    'userId' => ['name'=>'用户ID', 'type'=>'string'],
                    'username' => ['name'=>'用户名', 'type'=>'string'],
                    'money' => ['name'=>'金额'],
                    'status' => ['name'=>'状态'],
                    'payWay' => ['name'=>'充值方式'],
                    'payStatus' => ['name'=>'是否入账'],
                    'time' => ['name'=>'时间'],
                ],
            ];
            $excelRecords = [];
            foreach ($records as $row) {
                $row['serialNumber']  = $row['serialNumber'];
                $row['userId'] = $row['userId'];
                $row['username'] = $row['username'];
                $row['status'] = ArrayHelper::getValue([1=>'成功', 0=>'未完成', -1=>'失败'], $row['status']);
                $row['payWay'] = ArrayHelper::getValue(['1'=>'借记卡', '2'=>'信用卡', '3'=>'混合通道'], $row['payWay']);
                $row['payStatus'] = ArrayHelper::getValue([1=>'转入成功', 0=>'未转入', -1=>'转入失败'], $row['payStatus']);
                $excelRecords[] = $row;
            }
            ExcelHelper::getDataExcel($excelRecords, $other);
        } else {
            $records = $builder->orderBy('time', 'desc')->paginate(20);
            $records->appends($queries->all());
        }

        $this->display('minsheng', ['records'=>$records, 'queries'=>$queries]);
	}

	/**
     * 民生支付结果查询
     * @return  mixed
     */
	public function minshengResultAction() {
		$this->submenu = 'minsheng';

		$serialNumber = $this->getQuery('num', '');
		$oidPartner = Registry::get('config')->get('minsheng')->get('name');
		$url = Registry::get('config')->get('minsheng')->get('url').'/queryServlet';

	    $tradeNo = date('YmdHis').rand(1000,9999);

    	$postData = [];
    	$postData['oid_partner'] = $oidPartner;
    	$postData['no_order'] = $tradeNo;
    	$postData['dt_order'] = date('YmdHis');
    	$postData['ori_no_order'] = $serialNumber;

		$privateKey = MSBank::getKey('private', 'xwsd');

    	$sign = StringHelper::rsaSign(StringHelper::createLinkString(StringHelper::paramsSort($postData, true)), $privateKey);

    	$postData['sign'] = $sign;

    	$result = NetworkHelper::curlRequest($url, $postData, 'post');
    	
    	$data = json_decode($result, true);

    	if($data['resp_type']=='S') {
    		$this->display('minshengResult', ['result'=>$data]);
    	} else {
    		Flash::error('对账失败！'.$data['resp_msg']);
            $this->redirect('/admin/recharge/minsheng');
    	}
	}

	/**
     * 民生支付结果查询
     * @return  mixed
     */
	public function minshengBalAction() {
		$this->submenu = 'minshengBal';
		$this->display('minshengBal');
	}

	/**
     * 民生支付结果查询
     * @return  mixed
     */
	public function doMinshengBalAction() {
		$date = $this->getPost('time', date('Y-m-d'));

		$oidPartner = Registry::get('config')->get('minsheng')->get('name');
		$url = Registry::get('config')->get('minsheng')->get('url').'/dzQueryServlet';

	    $tradeNo = date('YmdHis').rand(1000,9999);

    	$postData = [];
    	$postData['oid_partner'] = $oidPartner;
    	$postData['settle_date'] = date('Ymd', strtotime($date));
    	// $postData['settle_date'] = '20160228';

		$privateKey = MSBank::getKey('private', 'xwsd');

    	$sign = StringHelper::rsaSign(StringHelper::createLinkString(StringHelper::paramsSort($postData, true)), $privateKey);

    	$postData['sign'] = $sign;

    	$result = NetworkHelper::curlRequest($url, $postData, 'post');


    	$data = json_decode($result, true);

    	$rdata = [];
    	$exString = '@';
    	if($data['resp_type']=='S') {
    		$rdata['status'] = 1;
    		$data['content'] = str_replace("\r\n", $exString, $data['content']);
    		$dataArray = explode($exString."########", $data['content']);
    		$zhifu = $dataArray[0];
    		$zhifuResults = [];
    		if($zhifu!=''&&$zhifu!='########') {
	    		$zhifuArray = explode($exString, $zhifu);
	    		foreach ($zhifuArray as $key => $item) {
	    			$row = explode('|', $item);
	    			$zhifuResults[$key]['oid_paybill'] = $row[0];
	    			$zhifuResults[$key]['no_order'] = $row[1];
	    			$zhifuResults[$key]['money_order'] = $row[2];
	    			$zhifuResults[$key]['settle_date'] = $row[3];
	    			$zhifuResults[$key]['resp_type'] = $row[4];
	    			$zhifuResults[$key]['resp_code'] = $row[5];
	    			$zhifuResults[$key]['resp_msg'] = $row[6];
	    			$zhifuResults[$key]['transaction_type'] = $row[7];
	    			$zhifuResults[$key]['fee'] = $row[8];
	    		}
    		}

    		$tuikuan = $dataArray[1];
    		$tuikuanResults = [];
    		if($tuikuan!=''&&$tuikuan!='########') {
	    		$tuikuanArray = explode($exString, $tuikuan);
	    		foreach ($tuikuanArray as $key => $item) {
	    			$row = explode('|', $item);
	    			$tuikuanResults[$key]['oid_paybill'] = $row[0];
	    			$tuikuanResults[$key]['no_order'] = $row[1];
	    			$tuikuanResults[$key]['money_order'] = $row[2];
	    			$tuikuanResults[$key]['settle_date'] = $row[3];
	    			$tuikuanResults[$key]['resp_type'] = $row[4];
	    			$tuikuanResults[$key]['resp_code'] = $row[5];
	    			$tuikuanResults[$key]['resp_msg'] = $row[6];
	    			$tuikuanResults[$key]['transaction_type'] = $row[7];
	    			$tuikuanResults[$key]['fee'] = $row[8];
	    		}
    		}
    		
    		$rdata['zhifuResults'] = $zhifuResults;
    		$rdata['tuikuanResults']  = $tuikuanResults;
    		
    	} else {
    		$rdata['status'] = 0;
    		$rdata['info'] = $data['resp_msg'];
    	}
    	$this->backJson($rdata);
	}

    /**
     * 汇潮充值
     * @return  mixed
     */
    public function huichaoAction() {
        $this->submenu = 'huichao';
        $excel = $this->getQuery('excel', 0);
        $queries = $this->queries->defaults([
            'searchType'=>'serialNumber', 
            'searchContent'=>'', 
            'status'=>'all', 
            'type'=>'all', 
            'payWay'=>'all', 
            'beginTime'=>'', 
            'endTime'=>'',
            'payStatus'=>'all',
        ]);

        $builder = Recharge::getListBuilder($queries);
        $builder->whereRaw('(payType=? or payType=?)', ['yemadai', '']);
        $records = null;
        // excel
        if($excel) {
            $records = $builder->orderBy('time', 'desc')->get();
            $other = [
                'title' => '网站充值',
                'columns' => [
                    'serialNumber' => ['name'=>'订单号', 'type'=>'string'],
                    'userId' => ['name'=>'用户ID', 'type'=>'string'],
                    'username' => ['name'=>'用户名', 'type'=>'string'],
                    'money' => ['name'=>'金额'],
                    'status' => ['name'=>'状态'],
                    'time' => ['name'=>'时间'],
                ],
            ];
            $excelRecords = [];
            foreach ($records as $row) {
                $row['serialNumber']  = $row['serialNumber'];
                $row['userId'] = $row['userId'];
                $row['username'] = $row['username'];
                $row['status'] = ArrayHelper::getValue([1=>'成功', 0=>'未完成', -1=>'失败'], $row['status']);
                $excelRecords[] = $row;
            }
            ExcelHelper::getDataExcel($excelRecords, $other);
        } else {
            $records = $builder->orderBy('time', 'desc')->paginate(20);
            $records->appends($queries->all());
        }

        $this->display('huichao', ['records'=>$records, 'queries'=>$queries]);
    }

    /**
     * 连连充值
     * @return  mixed
     */
    public function lianlianAction() {
        $this->submenu = 'lianlian';
        $excel = $this->getQuery('excel', 0);
        $queries = $this->queries->defaults([
            'searchType'=>'serialNumber', 
            'searchContent'=>'', 
            'status'=>'all', 
            'type'=>'all', 
            'payWay'=>'all', 
            'beginTime'=>'', 
            'endTime'=>'',
            'payStatus'=>'all',
        ]);

        $builder = Recharge::getListBuilder($queries);
        $builder->where('payType', 'lianlian');
        $records = null;

        // excel
        if($excel) {
            $records = $builder->orderBy('time', 'desc')->get();
            $other = [
                'title' => '网站充值',
                'columns' => [
                    'serialNumber' => ['name'=>'订单号', 'type'=>'string'],
                    'userId' => ['name'=>'用户ID', 'type'=>'string'],
                    'username' => ['name'=>'用户名', 'type'=>'string'],
                    'money' => ['name'=>'金额'],
                    'status' => ['name'=>'状态'],
                    'payWay' => ['name'=>'充值方式'],
                    'payStatus' => ['name'=>'是否入账'],
                    'time' => ['name'=>'时间'],
                ],
            ];
            $excelRecords = [];
            foreach ($records as $row) {
                $row['serialNumber']  = $row['serialNumber'];
                $row['userId'] = $row['userId'];
                $row['username'] = $row['username'];
                $row['status'] = ArrayHelper::getValue([1=>'成功', 0=>'未完成', -1=>'失败'], $row['status']);
                $row['payWay'] = ArrayHelper::getValue(['1'=>'网银支付', 'D'=>'认证支付'], $row['payWay']);
                $row['payStatus'] = ArrayHelper::getValue([1=>'转入成功', 0=>'未转入', -1=>'转入失败'], $row['payStatus']);
                $excelRecords[] = $row;
            }
            ExcelHelper::getDataExcel($excelRecords, $other);
        } else {
            $records = $builder->orderBy('time', 'desc')->paginate(20);
            $records->appends($queries->all());
        }

        $this->display('lianlian', ['records'=>$records, 'queries'=>$queries]);
    }

    /**
     * 宝付充值
     * @return  mixed
     */
    public function baofooAction() {
        $this->submenu = 'baofoo';
        $excel = $this->getQuery('excel', 0);
        $queries = $this->queries->defaults([
            'searchType'=>'serialNumber', 
            'searchContent'=>'', 
            'status'=>'all', 
            'type'=>'all', 
            'payWay'=>'all', 
            'beginTime'=>'', 
            'endTime'=>'',
            'payStatus'=>'all',
        ]);

        $builder = Recharge::getListBuilder($queries);
        $builder->where('payType', 'baofoo');
        $records = null;

        // excel
        if($excel) {
            $records = $builder->orderBy('time', 'desc')->get();
            $other = [
                'title' => '网站充值',
                'columns' => [
                    'serialNumber' => ['name'=>'订单号', 'type'=>'string'],
                    'userId' => ['name'=>'用户ID', 'type'=>'string'],
                    'username' => ['name'=>'用户名', 'type'=>'string'],
                    'money' => ['name'=>'金额'],
                    'status' => ['name'=>'状态'],
                    'payWay' => ['name'=>'充值方式'],
                    'payStatus' => ['name'=>'是否入账'],
                    'time' => ['name'=>'时间'],
                ],
            ];
            $excelRecords = [];
            foreach ($records as $row) {
                $row['serialNumber']  = $row['serialNumber'];
                $row['userId'] = $row['userId'];
                $row['username'] = $row['username'];
                $row['status'] = ArrayHelper::getValue([1=>'成功', 0=>'未完成', -1=>'失败'], $row['status']);
                $row['payWay'] = ArrayHelper::getValue(['1'=>'网银支付', 'D'=>'认证支付'], $row['payWay']);
                $row['payStatus'] = ArrayHelper::getValue([1=>'转入成功', 0=>'未转入', -1=>'转入失败'], $row['payStatus']);
                $excelRecords[] = $row;
            }
            ExcelHelper::getDataExcel($excelRecords, $other);
        } else {
            $records = $builder->orderBy('time', 'desc')->paginate(20);
            $records->appends($queries->all());
        }

        $this->display('baofoo', ['records'=>$records, 'queries'=>$queries]);
    }

    /**
     * 补单
     * @return  mixed
     */
    public function packAction() {
        $tradeNo = $this->getPost('tradeNo', '');
        $time = date('Y-m-d H:i:s', time()-20*60);
        $recharge = Recharge::where('status', '0')
            ->where('serialNumber', $tradeNo)
            ->where('addTime', '<=', $time)
            ->where('payWay', 'T')
            ->first();
        $rdata = [];
        if(!$recharge) {
            $rdata['status'] = 0;
            $rdata['msg'] = '补单失败，订单不存在或者订单不可补单！';
            $this->backJson($rdata);
        }

        $data  = [];
        $data['accountId'] = User::getCID($recharge->userId);
        $data['orgTxDate'] = substr($tradeNo, 0, 8);
        $data['orgTxTime'] = substr($tradeNo, 8, 6);
        $data['orgSeqNo'] = substr($tradeNo, 14);
        $handler = new Handler('fundTransQuery', $data);
        $result = $handler->api();
        $status = 0;
        if($result['retCode']==Handler::SUCCESS) {
            if($result['orFlag']!=1) {
                $status = 1;
            }
        }
        
        $return = [];
        $return['tradeNo'] = $tradeNo;
        $return['result'] = $result['retCode'];
        $return['status'] = $status;
        Recharge::after($return);

        $rdata['status'] = 1;
        $rdata['msg'] = '补单完成！';
        $this->backJson($rdata);
    }
}