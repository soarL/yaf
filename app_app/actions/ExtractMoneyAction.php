<?php
use models\User;
use models\SpreadExtract;
use models\MoneyLog;
use custody\API;
use Illuminate\Database\Capsule\Manager as DB;
use traits\handles\ITFAuthHandle;

/**
 * ExtractMoneyAction
 * APP提取推荐奖励
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class ExtractMoneyAction extends Action {
    use ITFAuthHandle;

    public function execute() {
    	$params = $this->getAllPost();
    	$this->authenticate($params, ['userId'=>'用户ID', 'money'=>'提取金额']);
        
        $money = floatval($params['money']);

        $rdata = [];
        if($money<0.01) {
            $rdata['msg'] = '提取金额不能小于0.01元！';
            $rdata['status'] = 0;
            $this->backJson($rdata);
        }

        $money = _cut_float($money, 2);
        
        $user = $this->getUser();
        $userId = $user->userId;
        $gradeSum = $user->gradeSum;
        
        if($gradeSum<$money) {
            $rdata['msg'] = '推荐奖励余额不足！';
            $rdata['status'] = 0;
            $this->backJson($rdata);
        }

        $remark = '提取推荐奖励'.$money.'元';
        $status = API::redpack($user->userId, $money, 'rpk-spread', $remark);

        if($status) {
            $spreadExtract = new SpreadExtract();
            $spreadExtract->extract_money = $money;
            $spreadExtract->userId = $userId;
            $spreadExtract->created_at = date('Y-m-d H:i:s');
            $spreadExtract->save();

            User::where('userId', $userId)->update([
                'gradeSum'=>DB::raw('gradeSum-'.$money)
            ]);

            $rdata['msg'] = '提取推荐奖励成功！';
            $rdata['status'] = 1;
            $this->backJson($rdata);
        } else {
            $rdata['msg'] = '提取推荐奖励失败！';
            $rdata['status'] = 0;
            $this->backJson($rdata);
        }
    }
}