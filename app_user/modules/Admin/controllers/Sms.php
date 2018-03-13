<?php
use Admin as Controller;
use models\User;
use models\Sms;
use traits\PaginatorInit;
use helpers\ExcelHelper;
use task\Task;
/**
 * SmsController
 * 短信管理
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class SmsController extends Controller {
	use PaginatorInit;

	public $menu = 'sms';

	/**
     * 发送短信页面
     * @return mixed
     */
	public function indexAction() {
		echo "呵呵 短信暂时发不了!";
		exit;
		$this->submenu = 'sms';
		$this->display('index');
	}

	/**
     * 发送短信
     * @return mixed
     */
	public function sendAction() {
		set_time_limit(0);
		$this->submenu = 'sms';
		$phones = $this->getPost('phones', '');
		$content = $this->getPost('content', '');
		$type = $this->getPost('type', 0);
		if($phones=='') {
			$this->backJson([
				'status' => 0,
				'info' => '请输入手机号码！',
			]);
		}
		if($content=='') {
			$this->backJson([
				'status' => 0,
				'info' => '请输入发送内容！',
			]);
		}

		$status = Task::add('sms', ['content'=>$content, 'phone'=>$phones, 'type'=>$type]);

		if($status) {
			Flash::success('添加发送任务成功！');
		} else {
			Flash::error('添加发送任务失败！');
		}

		$this->backJson([
			'status' => 1,
			'info' => '处理完成',
		]);
	}
}
