<?php
use models\User;
use models\CustomService;
use models\UserVip;
use Illuminate\Database\Capsule\Manager as DB;

use traits\handles\ITFAuthHandle;

/**
 * UserVipAction
 * APP VIP类型
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class UserVipAction extends Action {
    use ITFAuthHandle;
    
    public function execute() {
    	$params = $this->getAllQuery();
        $this->authenticate($params, ['userId'=>'用户ID']);
        
        $user = $this->getUser();
        $userId = $user->userId;

        $this->pv('aq');
        
        $rdata = [];

        $customServices = CustomService::where('dept_id', 11)->orderBy(DB::raw('rand()'))->get(['uid', 'user_name']);
        $userVip = UserVip::getVipByUser($user->userId);
        $customService = null;

        $list = [];
        foreach ($customServices as $cs) {
            $list[] = ['id'=>$cs->uid, 'name'=>$cs->user_name];
        }

        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功！';
        $rdata['data']['times'] = [['id'=>5, 'name'=>'1年(365天)']];
        $rdata['data']['customServices'] = $list;
        $rdata['data']['isVip'] = 0;

        if($userVip) {
            $customService = CustomService::where('uid', $userVip->customService)->where('dept_id', 11)->first();
            $rdata['data']['isVip'] = 1;
            $rdata['data']['customService'] = $customService->user_name;
            $rdata['data']['endTime'] = $userVip->endTime;
        }

        $this->backJson($rdata);
    }
}