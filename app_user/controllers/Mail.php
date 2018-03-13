<?php
use helpers\StringHelper;
use Yaf\Registry;
use tools\Pager;
use models\Mail;
use models\User;
use models\UserMail;
use traits\PaginatorInit;

class MailController extends Controller {
	use PaginatorInit;

	public $menu = 'account';
	public $submenu = 'mail';

	public function auto() {
		$user = $this->getUser();
		Mail::receiveByUsername($user->username);
	}

	public function listAction() {
		$user = $this->getUser();
		$queries = $this->queries->defaults(['status'=>'all']);
		$status = $queries->status;
		$builder = UserMail::with('mail')->where('username', $user->username)->whereIn('status', [0, 1])->orderBy('id', 'desc');

		$statusArr = ['read'=>1, 'noread'=>0];
		if($status!='all') {
			$builder->where('status', $statusArr[$status]);
		}

		$ums = $builder->paginate();
		$ums->appends($queries->all());
		$this->display('list', ['queries'=>$queries, 'ums'=>$ums]);
	}

	public function showAction($id) {
		$user = $this->getUser();
		$um = UserMail::with('mail', 'user')->where('id', $id)->where('username', $user->username)->first();
		$this->display('show', ['um'=>$um]);
	}

	public function readAction() {
		$id = $this->getPost('id');
		$user = $this->getUser();
		$um = UserMail::with('mail', 'user')->where('id', $id)->where('username', $user->username)->first();
		$rdata = [];
		if($um) {
			if($um->status==0) {
				$um->status = 1;
				$um->save();
				$rdata['status'] = 1;
			} else {
				$rdata['status'] = 2;
			}
			$um->mail->content = htmlspecialchars_decode($um->mail->content);
			$rdata['um'] = $um;
		} else {
			$rdata['status'] = 0;
			$rdata['info'] = '获取邮件内容失败！';
		}
		$this->backJson($rdata);
	}

	public function deleteAction() {
		$id = $this->getPost('id');
		$user = $this->getUser();
		$status = UserMail::where('id', $id)->where('username', $user->username)->update(['status'=>2]);
		$rdata = [];
		if($status) {
			$rdata['status'] = 1;
			$rdata['info'] = '删除邮件成功！';
			$this->backJson($rdata);
		} else {
			$rdata['status'] = 0;
			$rdata['info'] = '删除邮件失败！';
			$this->backJson($rdata);
		}
	}
	
}