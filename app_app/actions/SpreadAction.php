<?php
use models\User;
use models\UserFriend;
use models\GradeSum;
use models\MoneyLog;
use tools\Pager;
use traits\handles\ITFAuthHandle;

/**
 * SpreadAction
 * APP获取推荐链接
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class SpreadAction extends Action {
    use ITFAuthHandle;

    public function execute() {
    	$params = $this->getAllQuery();
    	$this->authenticate($params, ['userId'=>'用户ID']);

        $user = $this->getUser();
        $userId = $user->userId;

        $page = $this->getQuery('page', 1);
        $pageSize = $this->getQuery('pageSize', 5);


        $spreadCode = $user->getSpreadCode();
        $spreadUrl = WEB_USER.'/register/'.$spreadCode;

        $friends = UserFriend::getFriendRecursive($userId);
        $count = count($friends);
        
        $pager = new Pager(['total'=>$count, 'request'=>$this->getRequest(), 'pageSize'=>$pageSize]);

        $friendList = [];
        $total = 0;
        if($count<$pager->getLimit()) {
            $total = $count;
        } else {
            $total = $pager->getOffset() + $pager->getLimit();
        }
        $from = $pager->getOffset();
        for ($i=$from; $i<$total; $i++) {
            $friend = $friends[$i];
            $money = MoneyLog::whereRaw('userId=? and type=? and mode=?', [$userId, 'spread', 'in'])->sum('mvalue');
            $friendSum = GradeSum::whereRaw('friend=? and userId=?', [$userId, $friend['friend']])->sum('money');
            $friend['username'] = User::where('userId', $friend['friend'])->value('username');
            $friend['money'] = $money+$friendSum;
            $friendList[] = $friend;
        }

        $sum = GradeSum::where('friend', $userId)->sum('money');

        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功！';
        $rdata['data']['link'] = $spreadUrl;
        $rdata['data']['records'] = $friendList;
        $rdata['data']['page'] = $page;
        $rdata['data']['count'] = $count; 
        $rdata['data']['spreadMoney'] = $sum;
        $rdata['data']['lastSpreadMoney'] = $user->gradeSum;
        $this->backJson($rdata);
    }
}