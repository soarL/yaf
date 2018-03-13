<?php
namespace forms\admin;
use models\Promotion;
use Illuminate\Validator;

/**
 * PromotionForm|form类
 * 
 * @version 1.0
 */
class PromotionForm extends \Form {
	public $promotion = false;

	public function init() {
		if($this->id && $this->id!='') {
			$this->promotion = Promotion::find($this->id);
		} else {
			$this->promotion = false;
		}
	}

	public function rules() {
		return [
			[['channel_name', 'channel_code'], 'required'],
		];
	}

	public function labels() {
		return [
        	'channel_name' => '渠道名称',
        	'channel_code' => '渠道简称',
        ];
	}


	public function save() {
		if($this->check()) {
			$promotion = $this->promotion;
			$builder = Promotion::where('channelName', '=', $this->channel_name)
						->orWhere('channelCode', '=', $this->channel_code)->count();
						// var_dump($builder);exit;
			if(!empty($builder)){
				$this->addError('', '该渠道信息已存在');
				return false;
			}else{
				$promotion = new Promotion();
			}

			$promotion->channelCode = $this->channel_code;
			$promotion->channelName = $this->channel_name;
			if($promotion->save()) {
				return true;
			} else {
				$this->addError('', '操作失败！');
				return false;
			}
		} else {
			return false;
		}
	}
}