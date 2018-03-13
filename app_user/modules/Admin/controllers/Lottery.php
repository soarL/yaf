<?php
use Admin as Controller;
use models\Lottery;
use models\User;
use models\OperationLog;
use traits\PaginatorInit;
use forms\admin\LotteryForm;
/**
 * LotteryController
 * 奖券管理
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class LotteryController extends Controller {
    use PaginatorInit;

    public $menu = 'lottery';

    /**
     * 奖券列表
     * @return mixed
     */
    public function listAction() {
        $this->submenu = 'list';
        $queries = $this->queries->defaults(['searchType'=>'sn', 'searchContent'=>'', 'type'=>'all', 'status'=>'all']);
        $type = $queries->type;
        $status = $queries->status;
        $searchType = $queries->searchType;
        $searchContent = $queries->searchContent;

        $builder = Lottery::with('user')->whereRaw('1=1');

        if($searchContent!='') {
            if($searchType=='sn'||$searchType=='money_rate') {
                $builder->where($searchType, $searchContent);
            } else {
                $user = User::where($searchType, $searchContent)->first();
                if($user) {
                    $builder->where('userId', $user->userId);
                }
            }
        }

        if($type!='all') {
            $builder->where('type', $type);
        }

        if($status!='all') {
            $builder->where('status', $status);
        }

        $lotteries = $builder->orderBy('created_at', 'desc')->paginate(15);
        $lotteries->appends($queries->all());

        $this->display('list', ['lotteries'=>$lotteries, 'queries'=>$queries, 'types'=>Lottery::$types]);
    }

    /**
     * 生成奖券
     * @return mixed
     */
    public function generateAction() {
        $this->submenu = 'list';
        if($this->isPost()) {
            $params = $this->getAllPost();
            $form = new LotteryForm($params);
            if($form->generate()) {
                Flash::success('生成成功！');
                $this->redirect('/admin/lottery/list');
            } else {
                Flash::error($form->posError());
                $this->redirect('/admin/lottery/generate');
            }
        } else {
            $this->display('generate', ['types'=>Lottery::$types]);    
        }
    }

    /**
     * 删除奖券
     * @return mixed
     */
    public function deleteAction() {
        $id = $this->getPost('id');
        $lottery = Lottery::find($id);
        $status = false;
        if($lottery) {
            $status = $lottery->delete();
        }
        $rdata = [];
        if($status) {

            $manager = $this->getUser();
            $content = $manager->name . '【'.$manager->username.'】将' 
                . $lottery->getTypeName() . '[' . $lottery->sn . ']删除';
            OperationLog::addOne($manager, $content);

            $rdata['status'] = 1;
            $rdata['info'] = '删除成功！';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '删除失败！';
            $this->backJson($rdata);
        }
    }

    /**
     * 使用奖券
     * @return mixed
     */
    public function usedAction() {
        $id = $this->getPost('id');
        $lottery = Lottery::find($id);
        $status = false;
        if($lottery && $lottery->status==Lottery::STATUS_NOUSE) {
            $lottery->status = Lottery::STATUS_USED;
            $lottery->used_at = date('Y-m-d H:i:s');
            $status = $lottery->save();
        }
        $rdata = [];
        if($status) {
            
            $manager = $this->getUser();
            $content = $manager->name . '【'.$manager->username.'】将' 
                . $lottery->getTypeName() . '[' . $lottery->sn . ']状态变为已使用';
            OperationLog::addOne($manager, $content);

            Flash::success('改变状态成功！');
            $rdata['status'] = 1;
            $rdata['info'] = '改变状态成功！';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '改变状态失败！';
            $this->backJson($rdata);
        }
    }

    /**
     * 分配奖券
     * @return mixed
     */
    public function assignAction() {
        $id = $this->getPost('id', 0);
        $userId = $this->getPost('userId', '');
        $remark = $this->getPost('remark', '');
        $lottery = Lottery::where('id', $id)->where('status', Lottery::STATUS_NOGET)->first();
        $rdata = [];
        if(!$lottery) {
            $rdata['status'] = 0;
            $rdata['info'] = '优惠券不存在！';
            $this->backJson($rdata);
        }
        $user = User::find($userId);
        if(!$user) {
            $rdata['status'] = 0;
            $rdata['info'] = '用户不存在！';
            $this->backJson($rdata);
        }
        if($lottery->assign($user, $remark)) {

            // 操作日志
            $manager = $this->getUser();
            $content = $manager->name . '【'.$manager->username.'】将' 
                . $lottery->getTypeName() . '[' . $lottery->sn . ']分配给用户[' 
                . $user->username . ']，备注：' . $remark;
            OperationLog::addOne($manager, $content);

            Flash::success('分配成功！');
            $rdata['status'] = 1;
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = '分配失败！';
            $this->backJson($rdata);
        }
    }
}
