<?php
use models\Order;
use forms\OrderForm;
use tools\Areas;
class OrderController extends Controller {
	public $menu = 'order';

	public function indexAction() {
		$this->display('index');
	}

	public function saveAction() {
		$params = $this->getRequest()->getPost();
		$form = new OrderForm($params);
		if($form->check()) {
			$order = new Order();
			$order->name = $form->name;
			$order->phone = $form->phone;
			$order->address = $form->address;
			$order->need_money = $form->need_money;
			$order->acreage = $form->acreage;
			$order->year_rate = $form->year_rate;
			$order->house_type = $form->house_type;
			$order->add_time = date('Y-m-d H:i:s');
			if($order->save()) {
				Flash::success('恭喜您提交成功，我们的客服尽快联系您！');
			} else {
				Flash::error('很抱歉，提交失败！您可直接拨打网站下方的客服热线联系我们！');
			}
			$this->redirect('/order');
		} else {
			Flash::error($form->posError());
			$this->redirect('/order');
		}
	}
}
