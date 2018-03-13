<?php
use Admin as Controller;

use helpers\StringHelper;
use traits\PaginatorInit;
use custody\Handler;
use custody\Type;
use custody\API;
use models\User;
use models\Odd;
use models\CustodyBatch;
use models\CustodyLog;
use models\CustodyFullLog;
use models\OddMoney;
use models\BailRepay;
use business\BailHandler;
use helpers\ExcelHelper;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * CustodyController
 * 银行存管查询
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class CustodyController extends Controller {
    use PaginatorInit;

    public $menu = 'custody';

    /**
     * 交易明细查询
     */
    public function accountDetailsAction() {
        $this->submenu = 'account-details';
        $queries = $this->queries->defaults(['searchType'=>'username', 'searchContent'=>'', 'type'=>0, 'beginTime'=>date('Y-m-d'), 'endTime'=>date('Y-m-d'), 'tranType'=>'', 'page'=>1]);
        
        $excel = $this->getQuery('excel', 0);

        $pageSize = 20;
        if($excel) {
            $pageSize = 99;
        }
        $page = $queries->page;

        $user = User::where($queries->searchType, $queries->searchContent)->first();
        if(!$user) {
            $this->display('accountDetails', ['status'=>0, 'msg'=>'请输入用户相关信息进行查询！', 'list'=>[], 'queries'=>$queries, 'tranTypes'=>Type::$tranTypes]);
        }
        $data = [];
        $data['accountId'] = $user->custody_id;
        $data['startDate'] = _date('Ymd', $queries->beginTime);
        $data['endDate'] = _date('Ymd', $queries->endTime);
        $data['type'] = $queries->type;
        $data['tranType'] = $queries->tranType;
        $data['pageNum'] = $page;
        $data['pageSize'] = $pageSize;

        $handler = new Handler('accountDetailsQuery', $data);
        $result = $handler->api();

        if($result['retCode']!=Handler::SUCCESS) {
            $this->display('accountDetails', ['status'=>0, 'msg'=>'查询失败，原因：'.$result['retMsg'], 'list'=>[], 'queries'=>$queries, 'tranTypes'=>Type::$tranTypes]);
        }

        $list = json_decode($result['subPacks'], true);
        $total = $result['totalItems'];

        $tranTypes = Type::$tranTypes;

        if($excel) {
            $other = [
                'title' => '资金流水',
                'columns' => [
                    'time' => ['name'=>'时间'],
                    'money' => ['name'=>'金额'],
                    'type' => ['name'=>'类型'],
                    'description' => ['name'=>'描述'],
                    'remain' => ['name'=>'余额'],
                ],
            ];
            $excelRecords = [];
            foreach ($list as $row) {
                $item = [];
                $item['time'] = _date('Y-m-d H:i:s', substr($row['inpDate'].$row['inpTime'], 0, 14));
                $item['money'] = $row['txFlag'].$row['txAmount'];
                $item['type'] = $tranTypes[$row['tranType']];
                $item['description'] = $row['describe'];
                $item['remain'] = $row['currBal'];
                $excelRecords[] = $item;
            }
            ExcelHelper::getDataExcel($excelRecords, $other);
        }
        
        $paginator = $this->paginate($list, $queries, 15, 3000);

        $this->display('accountDetails', ['status'=>1, 'msg'=>'查询成功！', 'list'=>$paginator, 'queries'=>$queries, 'tranTypes'=>$tranTypes]);
    }

    /**
     * 债权明细查询
     */
    public function creditDetailsAction() {
        $this->submenu = 'credit-details';
        $queries = $this->queries->defaults(['searchType'=>'username', 'searchContent'=>'', 'state'=>0, 'beginTime'=>'2016-09-06', 'endTime'=>date('Y-m-d'), 'oddNumber'=>'', 'page'=>1]);
        
        $pageSize = 20;
        $page = $queries->page;

        $user = null;
        if($queries->oddNumber!='') {
            $odd = Odd::with(['user'=>function($q) {$q->select('userId', 'custody_id');}])
                ->where('oddNumber', $queries->oddNumber)->first(['oddNumber', 'userId']);
            if(!$odd) {
                $this->display('creditDetails', ['status'=>0, 'msg'=>'标的不存在！', 'list'=>[], 'queries'=>$queries]);
            }
            $user = $odd->user;
        }

        if($queries->searchContent!='') {
            $user = User::where($queries->searchType, $queries->searchContent)->first();
        }
        if(!$user) {
            $this->display('creditDetails', ['status'=>0, 'msg'=>'请输入用户相关信息进行查询！', 'list'=>[], 'queries'=>$queries]);
        }

        $data = [];
        $data['accountId'] = $user->custody_id;
        $data['startDate'] = _date('Ymd', $queries->beginTime);
        $data['endDate'] = _date('Ymd', $queries->endTime);
        $data['state'] = $queries->state;
        $data['pageNum'] = $page;
        $data['pageSize'] = $pageSize;
        $data['productId'] = $odd->getPID();

        $handler = new Handler('creditDetailsQuery', $data);
        $result = $handler->api();

        if($result['retCode']!=Handler::SUCCESS) {
            $this->display('creditDetails', ['status'=>0, 'msg'=>'查询失败，原因：'.$result['retMsg'], 'list'=>[], 'queries'=>$queries]);
        }

        $list = json_decode($result['subPacks'], true);
        $total = $result['totalItems'];

        $paginator =new LengthAwarePaginator($list, $total, $pageSize, $page);
        $paginator->appends($queries->all());

        $this->display('creditDetails', ['status'=>1, 'msg'=>'查询成功！', 'list'=>$paginator, 'queries'=>$queries]);
    }


    /**
     * 借款人标的信息查询
     */
    public function loanDetailsAction() {
        $this->submenu = 'loan-details';
        $queries = $this->queries->defaults(['searchType'=>'username', 'searchContent'=>'', 'state'=>0, 'beginTime'=>'2016-09-06', 'endTime'=>date('Y-m-d'), 'oddNumber'=>'', 'page'=>1]);
        
        $pageSize = 20;
        $page = $queries->page;

        $user = null;
        if($queries->oddNumber!='') {
            $odd = Odd::with(['user'=>function($q) {$q->select('userId', 'custody_id');}])
                ->where('oddNumber', $queries->oddNumber)->first(['oddNumber', 'userId']);
            if(!$odd) {
                $this->display('loanDetails', ['status'=>0, 'msg'=>'标的不存在！', 'list'=>[], 'queries'=>$queries]);
            }
            $user = $odd->user;
        } else {
            $user = User::where($queries->searchType, $queries->searchContent)->first();
        }
        if(!$user) {
            $this->display('loanDetails', ['status'=>0, 'msg'=>'请输入用户相关信息进行查询！', 'list'=>[], 'queries'=>$queries]);
        }

        $data = [];
        $data['accountId'] = $user->custody_id;
        $data['startDate'] = _date('Ymd', $queries->beginTime);
        $data['endDate'] = _date('Ymd', $queries->endTime);
        $data['pageNum'] = $page;
        $data['pageSize'] = $pageSize;

        if($queries->oddNumber!='') {
            $data['productId'] = _ntop($queries->oddNumber);
        }

        $handler = new Handler('debtDetailsQuery', $data);
        $result = $handler->api();

        if($result['retCode']!=Handler::SUCCESS) {
            $this->display('loanDetails', ['status'=>0, 'msg'=>'查询失败，原因：'.$result['retMsg'], 'list'=>[], 'queries'=>$queries]);
        }

        $list = json_decode($result['subPacks'], true);
        $total = $result['totalItems'];

        $paginator =new LengthAwarePaginator($list, $total, $pageSize, $page);
        $paginator->appends($queries->all());

        $this->display('loanDetails', ['status'=>1, 'msg'=>'查询成功！', 'list'=>$paginator, 'queries'=>$queries]);
    }

    /**
     * 查询交易状态
     */
    public function transactionDetailsAction() {
        $this->submenu = 'transaction-details';
        $queries = $this->queries->defaults(['searchType'=>'username', 'searchContent'=>'', 'reqType'=>'', 'reqData'=>'', 'reqTxCode'=>'', 'page'=>1]);
        
        $pageSize = 20;
        $page = $queries->page;

        if(!$queries->reqData) {
            $this->display('transactionDetails', ['status'=>0, 'msg'=>'请输入相关信息进行查询！', 'list'=>[], 'queries'=>$queries]);
        }

        $data = [];
        $data['reqType'] = $queries->reqType;
        $data['reqTxCode'] = $queries->reqTxCode;
        
        $reqData = trim($queries->reqData);
        if($data['reqType'] == '1'){
            $data['reqTxDate'] = substr($reqData, 0,8);
            $data['reqTxTime'] = substr($reqData, 8,6);
            $data['reqSeqNo'] = substr($reqData, 14,6);
        }else{
            $data['reqOrderId'] = $reqData;
        }

        if($queries->searchContent != ''){
            $user = User::where($queries->searchType, $queries->searchContent)->first();
            $data['accountId'] = $user->custody_id;
        }

        $handler = new Handler('transactionStatusQuery', $data);
        $result = $handler->api();

        if($result['retCode']!=Handler::SUCCESS) {
            $this->display('transactionDetails', ['status'=>0, 'msg'=>'查询失败，原因：'.$result['retMsg'], 'list'=>[], 'queries'=>$queries]);
        }

        $list = json_decode($result['subPacks'], true);
        $total = $result['totalItems'];

        $paginator =new LengthAwarePaginator($list, $total, $pageSize, $page);
        $paginator->appends($queries->all());

        $this->display('transactionDetails', ['status'=>1, 'msg'=>'查询成功！', 'list'=>$paginator, 'queries'=>$queries]);
    }

    /**
     * 查询批次交易明细状态
     */
    public function batchDetailsAction() {
        $this->submenu = 'batch-details';
        $queries = $this->queries->defaults(['batchTxDate'=>'20160906', 'batchNo'=>'', 'type'=>0, 'page'=>1]);
        
        $pageSize = 20;
        $page = $queries->page;

        if($queries->batchNo == '') {
            $this->display('batchDetails', ['status'=>0, 'msg'=>'请输入相关信息进行查询！', 'list'=>[], 'queries'=>$queries]);
        }

        $data = [];
        $data['batchTxDate'] = $queries->batchTxDate;
        $data['batchNo'] = $queries->batchNo;
        $data['type'] = $queries->type;
        $data['pageNum'] = $page;
        $data['pageSize'] = $pageSize;

        $handler = new Handler('batchDetailsQuery', $data);
        $result = $handler->api();

        if($result['retCode']!=Handler::SUCCESS) {
            $this->display('batchDetails', ['status'=>0, 'msg'=>'查询失败，原因：'.$result['retMsg'], 'list'=>[], 'queries'=>$queries]);
        }

        $list = json_decode($result['subPacks'], true);
        $total = $result['totalItems'];
        $paginator =new LengthAwarePaginator($list, $total, $pageSize, $page);
        $paginator->appends($queries->all());

        $this->display('batchDetails', ['status'=>1, 'msg'=>'查询成功！', 'list'=>$paginator, 'queries'=>$queries]);
    }

    /**
     * 查询批次发红包交易明细
     */
    public function batchVouchersAction() {
        $this->submenu = 'batch-details';
        $queries = $this->queries->defaults(['batchTxDate'=>'20160906', 'batchNo'=>'', 'type'=>0, 'page'=>1]);
        
        $pageSize = 20;
        $page = $queries->page;

        if($queries->batchNo == '') {
            $this->display('batchVouchers', ['status'=>0, 'msg'=>'请输入相关信息进行查询！', 'list'=>[], 'queries'=>$queries]);
        }

        $data = [];
        $data['batchTxDate'] = $queries->batchTxDate;
        $data['batchNo'] = $queries->batchNo;
        $data['type'] = $queries->type;
        $data['pageNum'] = $page;
        $data['pageSize'] = $pageSize;

        $handler = new Handler('batchVoucherDetailsQuery', $data);
        $result = $handler->api();

        if($result['retCode']!=Handler::SUCCESS) {
            $this->display('batchVouchers', ['status'=>0, 'msg'=>'查询失败，原因：'.$result['retMsg'], 'list'=>[], 'queries'=>$queries]);
        }

        $list = json_decode($result['subPacks'], true);
        $total = $result['totalItems'];
        $paginator =new LengthAwarePaginator($list, $total, $pageSize, $page);
        $paginator->appends($queries->all());

        $this->display('batchVouchers', ['status'=>1, 'msg'=>'查询成功！', 'list'=>$paginator, 'queries'=>$queries]);
    }

    /**
     * 批次列表
     */
    public function batchListAction() {
        $this->submenu = 'batch-list';
        $queries = $this->queries->defaults(['beginTime'=>'', 'endTime'=>'', 'type'=>'all', 'searchContent'=>'', 'searchType'=>'']);
        $beginTime = $queries->beginTime;
        $endTime = $queries->endTime;
        $type = $queries->type;

        $builder = CustodyBatch::whereRaw('1=1');
        if($queries->searchContent!='') {
            $builder->where($queries->searchType, $queries->searchContent);
        }

        if($type!='all') {
            $builder->where('type', $type);
        }

        if($beginTime!='') {
            $builder->where('sendTime', '>=', $beginTime);
        }

        if($endTime!='') {
            $builder->where('sendTime', '<=', $endTime);
        }

        $records = $builder->orderBy('sendTime', 'desc')->paginate(15);
        $records->appends($queries->all());

        $this->display('batchList', ['records'=>$records, 'queries'=>$queries, 'types'=>CustodyBatch::$types]);
    }

    /**
     * 代偿列表
     */
    public function bailListAction() {
        $this->submenu = 'bail-list';
        $queries = $this->queries->defaults(['beginTime'=>'', 'endTime'=>'', 'searchContent'=>'', 'searchType'=>'']);
        $beginTime = $queries->beginTime;
        $endTime = $queries->endTime;

        $builder = BailRepay::whereRaw('1=1');
        if($queries->searchContent!='') {
            $builder->where($queries->searchType, $queries->searchContent);
        }

        if($beginTime!='') {
            $builder->where('sendTime', '>=', $beginTime);
        }

        if($endTime!='') {
            $builder->where('sendTime', '<=', $endTime);
        }

        $records = $builder->orderBy('sendTime', 'desc')->paginate(15);
        $records->appends($queries->all());

        $this->display('bailList', ['records'=>$records, 'queries'=>$queries]);
    }

    /**
     * 投标申请查询
     */
    public function bidQueryAction() {
        $this->submenu = 'bid-query';
        $id = $this->getQuery('id', 0);

        $oddMoney = OddMoney::where('id', $id)->first();

        $rdata = [];
        if(!$oddMoney) {
            $rdata['status'] = 0;
            $rdata['info'] = '投资记录不存在！';
            $this->backJson($rdata);
        }

        $data = [];
        $data['accountId'] = User::getCID($oddMoney->userId);
        $data['orgOrderId'] = $oddMoney->tradeNo;
        $handler = new Handler('bidApplyQuery', $data);
        $result = $handler->api();

        if($result['retCode']==Handler::SUCCESS) {
            $rdata['status'] = 1;
            $rdata['info'] = '查询成功！';
            $rdata['data']['accountId'] = $result['accountId'];
            $rdata['data']['name'] = $result['name'];
            $rdata['data']['productId'] = $result['productId'].'['._pton($result['productId']).']';
            $rdata['data']['txAmount'] = $result['txAmount'];
            $rdata['data']['forIncome'] = $result['forIncome'];
            $rdata['data']['buyDate'] = _date('Y-m-d', $result['buyDate']);
            $rdata['data']['state'] = Type::getName('loanState', $result['state']);
            $rdata['data']['authCode'] = $result['authCode'];
            $rdata['data']['bonusAmount'] = $result['bonusAmount'];
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '查询错误！';
            $rdata['data']['retCode'] = $result['retCode'];
            $rdata['data']['retMsg'] = $result['retMsg'];
            $this->backJson($rdata);
        }
    }

    /**
     * 查询用户资金冻结明细
     */
    public function freezeDetailsAction() {
        $this->submenu = 'freeze-details';
        $queries = $this->queries->defaults(['searchType'=>'username', 'searchContent'=>'', 'state'=>0, 'beginTime'=>'2016-09-06', 'endTime'=>date('Y-m-d'), 'page'=>1]);
        
        $pageSize = 20;
        $page = $queries->page;

        $user = User::where($queries->searchType, $queries->searchContent)->first();
        if(!$user) {
            $this->display('freezeDetails', ['status'=>0, 'msg'=>'请输入用户相关信息进行查询！', 'list'=>[], 'queries'=>$queries]);
        }
        $data = [];
        $data['accountId'] = $user->custody_id;
        $data['startDate'] = _date('Ymd', $queries->beginTime);
        $data['endDate'] = _date('Ymd', $queries->endTime);
        $data['state'] = $queries->state;
        $data['pageNum'] = $page;
        $data['pageSize'] = $pageSize;

        $handler = new Handler('freezeDetailsQuery', $data);
        $result = $handler->api();

        if($result['retCode']!=Handler::SUCCESS) {
            $this->display('freezeDetails', ['status'=>0, 'msg'=>'查询失败，原因：'.$result['retMsg'], 'list'=>[], 'queries'=>$queries]);
        }

        $list = json_decode($result['subPacks'], true);
        $total = $result['totalItems'];

        $paginator =new LengthAwarePaginator($list, $total, $pageSize, $page);
        $paginator->appends($queries->all());
        
        $this->display('freezeDetails', ['status'=>1, 'msg'=>'查询成功！', 'list'=>$paginator, 'queries'=>$queries]);
    }

    /**
     * 单笔还款申请冻结查询
     */
    public function freezeQueryAction() {
        $this->submenu = 'freeze-query';
        $queries = $this->queries->defaults(['searchType'=>'username', 'searchContent'=>'', 'orderId'=>'']);
        
        $pageSize = 20;
        $page = $queries->page;

        $user = User::where($queries->searchType, $queries->searchContent)->first();
        if(!$user) {
            $this->display('freezeQuery', ['status'=>0, 'msg'=>'请输入用户相关信息进行查询！', 'list'=>[], 'queries'=>$queries]);
        }
        $data = [];
        $data['accountId'] = $user->custody_id;
        $data['orgOrderId'] = $queries->orderId;

        $handler = new Handler('balanceFreezeQuery', $data);
        $result = $handler->api();

        if($result['retCode']!=Handler::SUCCESS) {
            $this->display('freezeQuery', ['status'=>0, 'msg'=>'查询失败，原因：'.$result['retMsg'], 'list'=>[], 'queries'=>$queries]);
        }

        $this->display('freezeQuery', ['status'=>1, 'msg'=>'查询成功！', 'item'=>$result, 'queries'=>$queries]);
    }

    /**
     * 债权购买查询
     */
    public function creditQueryAction() {
        $this->submenu = 'credit-query';
        $id = $this->getQuery('id', 0);

        $oddMoney = OddMoney::where('id', $id)->first();

        $rdata = [];
        if(!$oddMoney) {
            $rdata['status'] = 0;
            $rdata['info'] = '投资记录不存在！';
            $this->backJson($rdata);
        }

        $data = [];
        $data['accountId'] = User::getCID($oddMoney->userId);
        $data['orgOrderId'] = $oddMoney->tradeNo;
        $handler = new Handler('creditInvestQuery', $data);
        $result = $handler->api();

        if($result['retCode']==Handler::SUCCESS) {
            $rdata['status'] = 1;
            $rdata['info'] = '查询成功！';
            $rdata['data']['accountId'] = $result['accountId'];
            $rdata['data']['name'] = $result['name'];
            $rdata['data']['forAccountId'] = $result['forAccountId'];
            $rdata['data']['forName'] = $result['forName'];
            $rdata['data']['tsfAmount'] = $result['tsfAmount'];
            $rdata['data']['txAmount'] = $result['txAmount'];
            $rdata['data']['availAmount'] = $result['availAmount'];
            $rdata['data']['txFee'] = $result['txFee'];
            $rdata['data']['txIncome'] = $result['txIncome'];
            $rdata['data']['authCode'] = $result['authCode'];
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '查询错误！';
            $rdata['data']['retCode'] = $result['retCode'];
            $rdata['data']['retMsg'] = $result['retMsg'];
            $this->backJson($rdata);
        }
    }

    /**
     * 偿还垫付
     */
    public function repayBailAction() {
        $this->submenu = 'bail-repay';
        $orgBatchNo = $this->getQuery('orgBatchNo', '');
        if($orgBatchNo=='') {
            $rdata['status'] = 0;
            $rdata['info'] = '请输入原批次号';
            $this->backJson($rdata);
        }
        $handler = new BailHandler(['orgBatchNo'=>$orgBatchNo]);
        $result = $handler->handle();

        if($result['status']) {
            $rdata['status'] = 1;
            $rdata['info'] = $result['msg'];
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = $result['msg'];
            $this->backJson($rdata);
        }
    }

    /**
     * 受托支付申请查询
     */
    public function trustPayAction() {
        $this->submenu = 'trust-pay';
        $queries = $this->queries->defaults(['oddNumber'=>'']);

        $oddNumber = $queries->oddNumber;
        if($oddNumber=='') {
            $this->display('trustPay', ['status'=>0, 'msg'=>'请输入标的号进行查询！', 'row'=>false, 'queries'=>$queries]);
        }
        $odd = Odd::where('oddNumber', $oddNumber)->first(['oddNumber', 'userId']);
        if(!$odd) {
            $this->display('trustPay', ['status'=>0, 'msg'=>'标的不存在！', 'row'=>false, 'queries'=>$queries]);
        }

        $data = [];
        $data['accountId'] = User::getCID($odd->userId);
        $data['productId'] = _ntop($oddNumber);
        $handler = new Handler('trusteePayQuery', $data);
        $result = $handler->api();

        if($result['retCode']==Handler::SUCCESS) {
            $row = [];
            $row['accountId'] = $result['accountId'];
            $row['name'] = $result['name'];
            $row['productId'] = $result['productId'];
            $row['receiptAccountId'] = $result['receiptAccountId'];
            $row['state'] = $result['state'];
            $row['affirmDate'] = $result['affirmDate'];
            $row['affirmTime'] = $result['affirmTime'];
            $row['receiptAccountId'] = $result['receiptAccountId'];

            if($result['state']==1) {
                Odd::where('oddNumber', _pton($result['productId']))->update(['receiptStatus'=>1]);
            }
            
            $this->display('trustPay', ['status'=>1, 'msg'=>'查询成功！', 'row'=>$row, 'queries'=>$queries]);
        } else {
            $this->display('trustPay', ['status'=>0, 'msg'=>'查询失败！'.$result['retMsg'], 'row'=>false, 'queries'=>$queries]);
        }
    }

    /**
     * 用户资金日志
     */
    public function syncLogAction() {
        $params = $this->getAllPost();
        $rdata = [];
        $result = API::syncLog($params);
        if($result['status']) {
            $rdata['status'] = 1;
            $rdata['info'] = $result['msg'];
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = $result['msg'];
            $this->backJson($rdata);
        }
    }

    public function packBatchAction() {
        $batchNo = $this->getPost('batchNo', '');
        if($batchNo=='') {
            $rdata['status'] = 1;
            $rdata['info'] = '无批次号';
            $this->backJson($rdata);
        }

        $batch = CustodyBatch::where('batchNo', $batchNo)->where('status', 0)->first();
        if(!$batch) {
            $rdata['status'] = 1;
            $rdata['info'] = '批次不存在或已处理！';
            $this->backJson($rdata);
        }

        $data['batchTxDate'] = substr($batchNo, 0, 8);
        $data['batchNo'] = substr($batchNo, 8);
        $handler = new Handler('batchQuery', $data);
        $result = $handler->api();
        if($result['retCode']==Handler::SUCCESS) {
            if($result['batchState']=='S') {
                $data = [];
                $data['retCode'] = Handler::SUCCESS;
                $data['sucCounts'] = $result['sucCounts'];
                $data['sucAmount'] = $result['sucAmount'];
                $data['failCounts'] = $result['failCounts'];
                $data['failAmount'] = $result['failAmount'];
                $batch->handle($data);

                Flash::success('批次补单成功，批次处理成功！');

                $rdata['status'] = 1;
                $rdata['info'] = '批次补单成功，批次处理成功！';
                $this->backJson($rdata);

            } else if($result['batchState']=='F') {
                $data = [];
                $data['retCode'] = 'XW000004';
                $data['sucCounts'] = isset($result['sucCounts'])?$result['sucCounts']:0;
                $data['sucAmount'] = isset($result['sucAmount'])?$result['sucAmount']:0;
                $data['failCounts'] = isset($result['failCounts'])?$result['failCounts']:0;
                $data['failAmount'] = isset($result['failAmount'])?$result['failAmount']:0;
                $batch->handle($data);

                Flash::success('批次补单成功，批次处理失败，失败原因：'.$result['failMsg']);

                $rdata['status'] = 1;
                $rdata['info'] = '批次补单成功，批次处理失败，失败原因：'.$result['failMsg'];
                $this->backJson($rdata);

            } else if($result['batchState']=='C') {
                $rdata['status'] = 0;
                $rdata['info'] = '批次补单失败，批次已撤销';
                $this->backJson($rdata);
            } else if($result['batchState']=='A') {
                $rdata['status'] = 0;
                $rdata['info'] = '批次补单失败，批次待处理';
                $this->backJson($rdata);
            } else if($result['batchState']=='D') {
                $rdata['status'] = 0;
                $rdata['info'] = '批次补单失败，批次正在处理';
                $this->backJson($rdata);
            }

        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '批次补单失败，失败原因：'.$result['retMsg'].'['.$result['retCode'].']';
            $this->backJson($rdata);
        }
    }

    public function accountFullLogsAction() {
        $this->submenu = 'account-full-logs';
        $excel = $this->getQuery('excel', 0);
        $queries = $this->queries->defaults(['beginTime'=>'', 'endTime'=>'', 'searchType'=>'username', 'searchContent'=>'', 'type'=>0, 'tranType'=>'']);

        $builder = CustodyFullLog::with(['user'=>function($q){ $q->select('userId', 'username', 'custody_id');}])->whereRaw('1=1');
        $user = null;
        if($queries->searchContent!='') {
            $searchContent = trim($queries->searchContent);
            $user = User::where($queries->searchType, $queries->searchContent)->first();
        }
        if($user) {
            $builder->where('cardnbr', $user->custody_id);
        }
        if($queries->beginTime!=''){
            $builder->where('inpdate', '>=', _date('Ymd', $queries->beginTime));
        }
        if($queries->endTime!=''){
            $builder->where('inpdate', '<=', _date('Ymd', $queries->endTime));
        }
        if($queries->type!=0){
            if($queries->type==1) {
                $builder->where('crflag', 'D');
            } else if($queries->type==2) {
                $builder->where('crflag', 'C');
            } else if($queries->type==9) {
                $builder->where('transtype', $queries->tranType);
            }
        }

        $tranTypes = Type::$tranTypes;

        if($excel) {
            $records = $builder->get();
            $other = [
                'title' => '银行全流水',
                'columns' => [
                    'userId' => ['name'=>'用户ID', 'type'=>'string'],
                    'username' => ['name'=>'用户名', 'type'=>'string'],
                    'money' => ['name'=>'金额'],
                    'mode' => ['name'=>'收入/支出'],
                    'type' => ['name'=>'类型'],
                    'description' => ['name'=>' 描述'],
                    'remain' => ['name'=>'余额'],
                    'time' => ['name'=>'操作时间'],
                ],
            ];
            $excelRecords = [];
            foreach ($records as $record) {
                $item = [];
                $item['userId'] = $record->user->userId;
                $item['username'] = $record->user->username;
                $item['type'] = $tranTypes[$record->transtype];
                $item['money'] = $record->amount;
                $item['mode'] = $record->crflag=='C'?'支出':'收入';
                $item['time'] = _date('Y-m-d H:i:s', substr($record->inpdate.$record->inptime, 0, 14));
                $item['remain'] = $record->curr_bal;
                $item['description'] = $record->desline;
                $excelRecords[] = $item;
            }
            ExcelHelper::getDataExcel($excelRecords, $other);
        }

        $records = $builder->orderBy('inpdate', 'desc')->orderBy('inptime', 'desc')->paginate();
        $records->appends($queries->all());
        $this->display('accountFullLogs', ['records'=>$records, 'queries'=>$queries, 'tranTypes'=>$tranTypes]);
    }

    public function accountLogsAction() {
        $this->submenu = 'account-logs';
        $excel = $this->getQuery('excel', 0);
        $queries = $this->queries->defaults(['beginTime'=>'', 'endTime'=>'', 'searchType'=>'username', 'searchContent'=>'', 'type'=>0, 'tranType'=>'']);

        $builder = CustodyLog::with(['user'=>function($q){ $q->select('userId', 'username', 'custody_id');}])->whereRaw('1=1');
        $user = null;
        if($queries->searchContent!='') {
            $searchContent = trim($queries->searchContent);
            $user = User::where($queries->searchType, $queries->searchContent)->first();
        }
        if($user) {
            $builder->where('cardnbr', $user->custody_id);
        }
        if($queries->beginTime!=''){
            $builder->where('transdate', '>=', _date('Ymd', $queries->beginTime));
        }
        if($queries->endTime!=''){
            $builder->where('transdate', '<=', _date('Ymd', $queries->endTime));
        }
        if($queries->type!=0){
            if($queries->type==1) {
                $builder->where('crflag', 'D');
            } else if($queries->type==2) {
                $builder->where('crflag', 'C');
            } else if($queries->type==9) {
                $builder->where('transtype', $queries->tranType);
            }
        }

        $tranTypes = Type::$tranTypes;
        if($excel) {
            $records = $builder->get();
            $other = [
                'title' => '银行对账流水',
                'columns' => [
                    'userId' => ['name'=>'用户ID', 'type'=>'string'],
                    'username' => ['name'=>'用户名', 'type'=>'string'],
                    'money' => ['name'=>'金额'],
                    'mode' => ['name'=>'收入/支出'],
                    'type' => ['name'=>'类型'],
                    'proccode' => ['name'=>' 交易类型码'],
                    'retseqno' => ['name'=>'检索参考号'],
                    'ervind' => ['name'=>'交易状态'],
                    'time' => ['name'=>'交易日期'],
                ],
            ];
            $excelRecords = [];
            foreach ($records as $record) {
                $item = [];
                $item['userId'] = $record->user->userId;
                $item['username'] = $record->user->username;
                $item['type'] = $tranTypes[$record->transtype];
                $item['money'] = $record->amount;
                $item['mode'] = $record->crflag=='C'?'支出':'收入';
                $item['time'] = $record->transdate;
                $item['retseqno'] = $record->retseqno;
                $item['proccode'] = $record->proccode;
                $item['ervind'] = $record->ervind==1?'已撤销/冲正':'正常交易';
                $excelRecords[] = $item;
            }
            ExcelHelper::getDataExcel($excelRecords, $other);
        }

        $records = $builder->orderBy('transdate', 'desc')->paginate();
        $records->appends($queries->all());
        $this->display('accountLogs', ['records'=>$records, 'queries'=>$queries, 'tranTypes'=>$tranTypes]);
    }

    public function accountQueryAction() {
        $this->submenu = 'account-query';
        $searchType = $this->getQuery('type', '');
        $searchContent = $this->getQuery('content', '');
        $info = [];
        $info['searchType'] = $searchType;
        $info['searchContent'] = $searchContent;
        if($searchType=='') {
            $this->display('accountQuery', ['status'=>$records, 'msg'=>'请选择查询类型！', 'info'=>$info]);
        }
        if($searchContent=='') {
            $this->display('accountQuery', ['status'=>$records, 'msg'=>'请输入查询内容！', 'info'=>$info]);
        }

        if($searchType=='id') {
            $data = [];
            $data['idType'] = '01';
            $data['idNo'] = $searchContent;
            $handler = new Handler('accountIdQuery', $data);
            $result = $handler->api();
            if($result['retCode']==Handler::SUCCESS) {
                $info['idNo'] = $result['idNo'];
                $info['accountId'] = $result['accountId'];
                $info['openDate'] = $result['openDate'];
                $info['acctState'] = $result['acctState'];
                $info['frzState'] = $result['frzState'];
                $info['pinLosCd'] = $result['pinLosCd'];
                $user = User::where('custody_id', $result['accountId'])->first(['userId', 'username']);
                $info['user'] = $user;
                $this->display('accountQuery', ['status'=>1, 'msg'=>'查询类型不存在！', 'info'=>$info]);
            } else {
                $this->display('accountQuery', [
                    'status'=>0, 
                    'msg'=>'查询失败，原因：['.$result['retCode'].']'.$result['retMsg'], 
                    'info'=>$info
                ]);
            }
        } else if($searchType=='mobile') {
            $data = [];
            $data['mobile'] = $searchContent;
            $handler = new Handler('accountQueryByMobile', $data);
            $result = $handler->api();
            if($result['retCode']==Handler::SUCCESS) {
                $info['idNo'] = $result['idNo'];
                $info['accountId'] = $result['accountId'];
                $info['name'] = $result['name'];
                $info['mobile'] = $result['mobile'];
                $info['acctState'] = $result['acctState'];
                $user = User::where('custody_id', $result['accountId'])->first(['userId', 'username']);
                $info['user'] = $user;
                $this->display('accountQuery', ['status'=>1, 'msg'=>'查询类型不存在！', 'info'=>$info]);
            } else {
                $this->display('accountQuery', [
                    'status'=>0, 
                    'msg'=>'查询失败，原因：['.$result['retCode'].']'.$result['retMsg'], 
                    'info'=>$info
                ]);
            }
        } else {
            $this->display('accountQuery', ['status'=>0, 'msg'=>'查询类型不存在！', 'info'=>$info]);
        }
    }

    public function debtCancelAction() {
        $id = $this->getQuery('id', 0);
        $oddMoney = OddMoney::where('id', $id)->where('type', 'invest')->first();
        $rdata = [];
        if(!$oddMoney) {
            $rdata['status'] = 0;
            $rdata['info'] = '债权不存在!';
            $this->backJson($rdata);
        }
        $result = API::debtCancel($oddMoney);
        if($result['status']) {
            Flash::success('撤销成功!');
            $rdata['status'] = 1;
            $rdata['info'] = '撤销成功!';
            $this->backJson($rdata);   
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = $result['msg'];
            $this->backJson($rdata);
        }
    }
}
