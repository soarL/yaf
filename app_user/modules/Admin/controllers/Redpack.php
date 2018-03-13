<?php
use Admin as Controller;
use models\Redpack;
use models\OperationLog;
use models\User;
use custody\API;
use Illuminate\Database\Capsule\Manager as DB;
use traits\PaginatorInit;

/**
 * RedpackController
 * 红包管理
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class RedpackController extends Controller {
    use PaginatorInit;

    public $menu = 'user';

    /**
     * 红包列表
     */
    public function listAction() {
        $this->submenu = 'repack-list';
        $excel = $this->getQuery('excel', 0);
        $queries = $this->queries->defaults(['beginTime'=>'', 'endTime'=>'', 'searchType'=>'username', 'searchContent'=>'', 'status'=>'all', 'type'=>'all']);

        $builder = Redpack::with(['user'=>function($q){ $q->select('userId', 'username');}])->whereRaw('1=1');
        $user = null;
        if($queries->searchContent!='') {
            $searchContent = trim($queries->searchContent);
            $user = User::where($queries->searchType, $queries->searchContent)->first();
        }
        if($user) {
            $builder->where('userId', $user->userId);
        }
        if($queries->beginTime!=''){
            $builder->where('addtime', '>=', $queries->beginTime.' 00:00:00');
        }
        if($queries->endTime!=''){
            $builder->where('addtime', '<=', $queries->endTime.' 23:59:59');
        }
        if($queries->status!='all'){
            $builder->where('status', $queries->status);
        }
        if($queries->type!='all'){
            $builder->where('type', $queries->type);
        }

        $sumBuilder = clone $builder;
        $countBuilder = clone $builder;
        $totalCount = $countBuilder->count();
        $totalMoney = $sumBuilder->sum('money');

        $redpacks = $builder->orderBy('addtime', 'desc')->orderBy('id', 'desc')->paginate(15);
        $redpacks->appends($queries->all());
        $this->display('list', ['redpacks' => $redpacks, 'queries'=>$queries, 'totalCount'=>$totalCount, 'totalMoney'=>$totalMoney, 'types'=>Redpack::$types]);
    }

    /**
     * 发放红包
     */
    public function sendAction() {
        $money = $this->getPost('money', 0);
        $remark = $this->getPost('remark', '');
        $userId = $this->getPost('userId', '');
        $user = User::where('userId', $userId)->first();
        $rdata = [];
        if(!$user) {
            $rdata['status'] = 0;
            $rdata['info'] = '用户不存在！';
            $this->backJson($rdata);
        }
        if($user->custody_id=='') {
            $rdata['status'] = 0;
            $rdata['info'] = '用户未开通存管！';
            $this->backJson($rdata);
        }
        if($money<=0) {
            $rdata['status'] = 0;
            $rdata['info'] = '红包金额需大于0！';
            $this->backJson($rdata);
        }
        $result = API::redpack($userId, $money, 'rpk-normal', $remark);
        if($result['status']) {

            $manager = $this->getUser();
            $content = $manager->name . '【'.$manager->username.'】发送'.$money.'元红包[' . $result['orderId'] . ']给用户[' 
                . $user->username . ']';
            OperationLog::addOne($manager, $content);

            Flash::success('红包发放成功！');
            $rdata['status'] = 1;
            $rdata['info'] = '红包发放成功！';
            $this->backJson($rdata);   
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = $result['msg'];
            $this->backJson($rdata);   
        }
    }

    /**
     * 撤销红包
     */
    public function cancelAction() {
        $id = $this->getPost('id', 0);
        $remark = $this->getPost('remark', '');
        $redpack = Redpack::where('id', $id)->first();
        $rdata = [];
        if(!$redpack) {
            $rdata['status'] = 0;
            $rdata['info'] = '红包不存在！';
            $this->backJson($rdata);
        }
        if($redpack->status<>1) {
            $rdata['status'] = 0;
            $rdata['info'] = '红包状态异常，不能撤销！';
            $this->backJson($rdata);   
        }
        $result = API::cancelRedpack($redpack, $remark);
        if($result['status']) {

            $manager = $this->getUser();
            $content = $manager->name . '【'.$manager->username.'】将红包[' . $redpack->orderId . ']撤回';
            OperationLog::addOne($manager, $content);

            Flash::success('红包撤销成功！');
            $rdata['status'] = 1;
            $rdata['info'] = '红包撤销成功！';
            $this->backJson($rdata);   
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = $result['msg'];
            $this->backJson($rdata);   
        }
    }
}