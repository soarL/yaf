<?php
use Admin as Controller;
use models\LookOdd;
use models\LookVote;
use models\Odd;
use traits\PaginatorInit;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * LookoddController
 * 查标管理
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class LookoddController extends Controller {
	use PaginatorInit;
	
	public $menu = 'look-odd';

	/**
     * 投票记录
     * @return  mixed
     */
	public function votesAction() {
		$this->submenu = 'votes';
		$queries = $this->queries;

		$records = LookVote::orderBy('created_at', 'desc')->paginate(15);
        $records->appends($queries->all());

		$this->display('votes', ['records'=>$records, 'queries'=>$queries]);
	}

	/**
     * 删除投票记录
     * @return  mixed
     */
	public function deleteVoteAction() {
		$this->submenu = 'votes';
		$id = $this->getPost('id', 0);
		$lookVote = LookVote::find($id);
		$rdata = [];
		$status = false;
        if($lookVote) {
            $status = $lookVote->delete();
        }
        $rdata = [];
        if($status) {
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
     * 投票排名
     * @return  mixed
     */
	public function ranksAction() {
		$this->submenu = 'ranks';
		
		$records = LookVote::groupBy('oddNumber')
			->orderBy('voteNum', 'desc')
            ->limit(20)
            ->get([DB::raw('count(userId) as voteNum'), 'oddNumber']);

		$this->display('ranks', ['records'=>$records]);
	}


	/**
     * 往期查标
     * @return  mixed
     */
	public function historyAction() {
		$this->submenu = 'history';
		$queries = $this->queries->defaults(['period'=>'', 'beginTime'=>'', 'endTime'=>'']);

		$period = $queries->period;
        $beginTime = $queries->beginTime;
        $endTime = $queries->endTime;

        $builder = LookOdd::with('odd');

        if($period!='') {
            $builder->where('period', $period);
        }

        if($beginTime!='') {
            $builder->where('created_at', '>=', $beginTime);
        }

        if($endTime!='') {
            $builder->where('created_at', '<=', $endTime);
        }

		$records = $builder->orderBy('period', 'desc')->orderBy('created_at', 'desc')->paginate(15);
        $records->appends($queries->all());

		$this->display('history', ['records'=>$records, 'queries'=>$queries]);
	}

	/**
     * 修改往期查标
     * @return  mixed
     */
	public function updateHistoryAction() {
		$this->submenu = 'history';
		$id = $this->getQuery('id', 0);

        $lookOdd = LookOdd::find($id);

		$this->display('historyForm', ['lookOdd'=>$lookOdd]);
	}

	/**
     * 保存往期查标
     * @return  mixed
     */
	public function saveHistoryAction() {
		$this->submenu = 'history';
		$id = $this->getPost('id', 0);
		$link = $this->getPost('link', '');
		$lookOdd = LookOdd::find($id);
		if($lookOdd) {
			$lookOdd->link = $link;
			if($lookOdd->save()) {
	            Flash::success('修改成功！');
	            $this->redirect('/admin/lookodd/history');
	        } else {
	            Flash::error('修改失败！');
	            $this->goBack();
	        }
		} else {
			Flash::success('记录不存在！');
	        $this->redirect('/admin/lookodd/history');
		}
	}

	/**
     * 删除历史查标
     * @return  mixed
     */
	public function deleteHistoryAction() {
		$this->submenu = 'history';
		$id = $this->getPost('id', 0);
		$lookOdd = LookOdd::find($id);
		$rdata = [];
		$status = false;
        if($lookOdd) {
            $status = $lookOdd->delete();
        }
        $rdata = [];
        if($status) {
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
     * 生成查标
     * @return  mixed
     */
	public function generateAction() {
		$week = date('w');
		if(in_array($week, [0, 1, 2, 4, 5, 6])) {
			$rdata['status'] = 0;
            $rdata['msg'] = '投票日不可生成查标！';
            $this->backJson($rdata);
		}
		$votes = LookVote::with('odd')
            ->groupBy('oddNumber')
            ->orderBy('voteNum', 'desc')
            ->limit(4)
            ->get([DB::raw('count(userId) as voteNum'), 'oddNumber']);
        $last = LookOdd::orderBy('period', 'desc')->first();
        $period = 1;
        if($last) {
        	$period = $last->period+1;
        }
        $num = 0;
        foreach ($votes as $vote) {
       		$odd = new LookOdd();
       		$odd->oddNumber = $vote->oddNumber;
       		$odd->num = $vote->voteNum;
       		$odd->period = $period;
       		$odd->save();
       		Odd::where('oddNumber', $vote->oddNumber)->update(['isUserLook'=>'y']);
       		$num ++;
        }

        LookVote::truncate();

        $rdata['status'] = 1;
        $rdata['msg'] = '生成成功！';
        $this->backJson($rdata);
	}
}