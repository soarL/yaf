<?php
use models\User;
use models\Dtarea;

class IndexController extends Controller {
	public function indexAction() {//默认Action
		// $data = User::get();
		// $this->backJson(['data'=>$data]);
		$this->display('index');
	}

	public function cityAction() {//默认Action
		$data = Dtarea::get();
		$backData = array();
		foreach ($data as $key => $value) {
			$data = array();
			if($value->area_parent_id === 0){
				$data['value'] = $value->id;
				$data['lavel'] = $value->area_name;
				array_push($backData,$data);
			}
			# code...
		}
		_dd($backData);
		exit;
		$this->backJson(['data'=>$data]);
	}

	public function wxconfigAction(){

		$appId = 'wx2973416012c3598e';
		$secret = '40558d04dc627152acf720be86d3af4e';
		$access_token = _get('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appId . '&secret=' . $secret) ->access_token;

		$jsapi_ticket = _get('https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='. $access_token .'&type=jsapi')->ticket;
		
		$params = [];
		$params['jsapi_ticket'] = $jsapi_ticket;
		$params['noncestr'] = 'abcdf'.rand(1000,9000);
		$params['timestamp'] = time();
		$params['url'] = "http://192.168.1.132:3000";
		$paramsSort = _paramsSort($params);
		$paramsLinkString = _createLinkString($paramsSort);
		$params['signature'] = sha1($paramsLinkString);

		$this->backJson(['params'=>$params]);

	}

}
