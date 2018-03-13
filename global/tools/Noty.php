<?php
namespace tools;

use \Flash;
use \Tag;

/**
 * Noty 配合提前端的通知插件[detached]
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Noty {
	private $theme;
	private $message;
	private $layout;
	private $type;
	private $animation;

	function __construct($data=[]) {
		$this->message = isset($data['message'])?$data['message']:'';
		$this->type = isset($data['type'])?$data['type']:'success';
		$this->layout = isset($data['layout'])?$data['layout']:'center';
		$this->theme = isset($data['theme'])?$data['theme']:'defaultTheme';
		$this->animation = isset($data['animation'])?$data['animation']:null;
	}

	public static function html($data=[]) {
		$message = isset($data['message'])?$data['message']:'';
		$type = isset($data['type'])?$data['type']:'success';
		$layout = isset($data['layout'])?$data['layout']:'center';
		$theme = isset($data['theme'])?$data['theme']:'defaultTheme';
		$animation = '';
		if(isset($data['animation'])&&is_array($data['animation'])) {
			$animation = json_encode($data['animation']);
		}

		$notyDiv = new Tag('div');
		$notyDiv->addClass('noty-data')->setAttribute('style', 'display:none;');
		$textDiv = new Tag('div');
		$textDiv->addClass('noty-data-message')->setContent($message);
		$typeDiv = new Tag('div');
		$typeDiv->addClass('noty-data-type')->setContent($type);
		$layoutDiv = new Tag('div');
		$layoutDiv->addClass('noty-data-layout')->setContent($layout);
		$themeDiv = new Tag('div');
		$themeDiv->addClass('noty-data-theme')->setContent($theme);
		$animationDiv = new Tag('div');
		$animationDiv->addClass('noty-data-animation')->setContent($animation);
		$notyDiv->setContent($textDiv.$typeDiv.$layoutDiv.$themeDiv.$animationDiv);
		return $notyDiv;
	}

	public static function flash() {
		if(Flash::has()) {
			$flashData= Flash::get();
			$data = [];
			$data['message'] = $flashData['info'];
			$data['type'] = $flashData['type'];
			$data['layout'] = 'topCenter';
			$data['theme'] = 'relax';
			$data['animation'] = [
				'open' => 'animated bounceInDown',
				'close' => 'animated bounceOutUp',
				'easing' => 'swing',
				'speed' => 500
			];
			echo self::html($data);
		}
	}

	public function show() {
		$data = [];
		$data['message'] = $this->message;
		$data['type'] = $this->type;
		$data['layout'] = $this->layout;
		$data['theme'] = $this->theme;
		$data['animation'] = $this->animation;
		echo self::html($data);
	}

	public function setMessage($message) {
		$this->message = $message;
	}

	public function setLayout($layout) {
		$this->layout = $layout;
	}

	public function setType($type) {
		$this->type = $type;
	}

	public function setTheme($theme) {
		$this->theme = $theme;
	}

	public function setAnimation($animation) {
		$this->animation = $animation;
	}
}