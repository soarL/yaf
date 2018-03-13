<?php
namespace traits;

use models\ActUserAddress;
use models\User;

trait ActCommon {

    public function setAddressMode() {
        $user = $this->getUser();
        $params = $this->getAllPost(true);
        if(!$user) {
            $this->backJson(['status'=>0, 'info'=>'请先登录！']);
        }
        $userId = $user->userId;
        $userAddress = null;
        if(ActUserAddress::isUserSet($userId)) {
            $userAddress = ActUserAddress::where('userId', $userId)->first();
        } else {
            $userAddress = new ActUserAddress();
        }
        $userAddress->userId = $userId;
        $userAddress->addtime = date('Y-m-d H:i:s');
        $userAddress->name = $params['name'];
        $userAddress->addressDetail = $params['address'];
        
        if(isset($params['zipcode'])) {
            $userAddress->zipcode = $params['zipcode'];
        }
        $userAddress->phone = $params['phone'];

        if($userAddress->save()) {
            $this->backJson(['status'=>1, 'info'=>'设置地址成功！']);
        } else {
            $this->backJson(['status'=>0, 'info'=>'设置地址失败！']);
        }
    }

    public function ajaxLoginMode() {
        $phone = $this->getPost('phone', '');
        $password = $this->getPost('password', '');

        $result = User::loginNormal($phone, $password, false);
        
        if($result['status']==1) {
            $rdata['status'] = 1;
            $rdata['info'] = '登录成功!';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = $result['info'];
            $this->backJson($rdata);
        }
    }
}