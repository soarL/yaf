<?php
use tools\WebSign;
use models\Banner;
class TestAction extends Action {
	public function test() {
		echo 'nihao';
		$this->backJson(['a'=>1, 'b'=>2]);
	}

    public function execute() {

    	$params = $this->getAllPost();
    	
        $rdata = [];

    	if(!WebSign::check($params)) {
            $rdata['resultCode'] = '99';
            $rdata['resultMsg'] = AppChecker::getMsg();
            $this->backJson($rdata);
        }

    	Banner::where('type_id', 5)->where('status', '1')->orderBy('banner_order', 'desc');
    }
}