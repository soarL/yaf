<?php
namespace tools;
use \Flash;
use \Tag;

/**
 * Toastr
 * 工具类，通知、需要配合js插件toastr
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Toastr {
	private $title;
	private $message;
	private $type;

	/** 可设置选项:
		closeButton 是否显示关闭按钮
		debug 是否调试
		progressBar 是否显示进度条
		positionClass 显示位置
		onclick 点击是触发的事件
		showDuration 显示持续时间
		hideDuration 隐藏持续时间
		timeOut 超时
		extendedTimeOut 延长时间
		showEasing 显示动画
		hideEasing 隐藏动画
		showMethod 显示方法
		hideMethod 隐藏方法
	**/
	private $options = [
		'closeButton'=> true,
		'debug' => false,
		'progressBar' => true,
		'positionClass' => 'toast-top-center',
		'onclick' => null,
		'showDuration' => 400,
		'hideDuration' => 1000,
		'timeOut' => 7000,
		'extendedTimeOut' => 1000,
		'showEasing' => 'swing',
		'hideEasing' => 'linear',
		'showMethod' => 'fadeIn',
		'hideMethod' => 'fadeOut',
	];
	

	function __construct() {
	}

	public function html() {
		$div = new Tag('div');
		$div->addClass('toastr-data')->setAttribute('style', 'display:none;');
		$div->setAttribute('data-title', $this->title);
		$div->setAttribute('data-type', $this->type);
		foreach ($this->options as $option => $value) {
			$div->setAttribute('data-'.$option, $value);
		}
		$div->setContent($this->message);
		return $div;
	}

	public function flash() {
		if(Flash::has()) {
			$flashData= Flash::get();
			$this->message = $flashData['info'];
			$this->type = $flashData['type'];
			echo $this->html();
		}
	}

	public function setType($type) {
		$this->type = $type;
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	public function setMessage($message) {
		$this->message = $message;
	}

	public function setOption($option, $value) {
		$this->options[$option] = $value;
	}

	public function setOptions($options=array()) {
		foreach ($options as $option => $value) {
			$this->setOption($option, $value);
		}
	}
}
