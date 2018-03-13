<?php
namespace forms\admin;
use models\Lottery;
use models\User;
use models\OperationLog;

/**
 * LotteryForm|form类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class LotteryForm extends \Form {
	public $typeRow;

	public function rules() {
		return [
			[['type'], 'required'],
			['type', 'validateType']
		];
	}

	public function labels() {
		return [
        	'type' => '类型',
        	'num;' => '生成数量',
        	'money_rate' => '奖励额度',
        	'useful_day' => '有效天数',
        	'money_lower' => '最小金额',
        	'money_uper' => '最大金额',
        	'period_lower' => '最小期限',
        	'period_uper' => '最大期限',
        	'remark'=>'备注',
        	'assign_users'=>'分配用户',
        	'endtime'=>'有效时间',
        ];
	}

	public function validateType() {
		if(isset(Lottery::$types[$this->type])) {
			$this->typeRow = Lottery::$types[$this->type];
		} else {
			$this->addError('name', '奖券类型不存在！');
		}
	}

	public function generate() {
		if($this->check()) {
            $type = $this->type;
            $remark = $this->remark;
            $num = $this->num;
            $assign_users = trim($this->assign_users, '');
            $assign_users = str_replace(' ', '', $assign_users);
            $assign_users = str_replace('，', ',', $assign_users);

            $mr = $this->typeRow['mr'];
            $tk = $this->typeRow['key'];

            $money_lower = $this->money_lower?$this->money_lower:null;
            $money_uper = $this->money_uper?$this->money_uper:null;
            $period_lower = $this->period_lower?$this->period_lower:null;
            $period_uper = $this->period_uper?$this->period_uper:null;
            $endtime = $this->endtime?$this->endtime.' 23:59:59':null;
            $useful_day = $this->useful_day?$this->useful_day:Lottery::USERFUL_DAY;
            $money_rate = $this->money_rate?$this->money_rate:$mr;

            $common = [];
            $common['type'] = $type;
            $common['useful_day'] = $useful_day;
            $common['money_rate'] = $money_rate;
            $common['money_lower'] = $money_lower;
            $common['money_uper'] = $money_uper;
            $common['period_lower'] = $period_lower;
            $common['period_uper'] = $period_uper;
            $common['remark'] = $remark;
            $common['created_at'] = date('Y-m-d H:i:s');
            $common['updated_at'] = date('Y-m-d H:i:s');

            $data = [];
            $index = 0;
            if($assign_users!='') {
                $users = explode(',', $assign_users);
                if($endtime==null) {
                    $endtime = date('Y-m-d H:i:s', time()+($useful_day)*24*60*60);
                }
                foreach ($users as $userId) {
                    if($userId!='') {
                        $sn = strtoupper(substr(md5($index.microtime().$tk.rand(1000, 9999)), 8, 16));
                        $common['sn'] = $sn;
                        $common['endtime'] = $endtime;
                        $common['get_at'] = date('Y-m-d H:i:s');
                        $common['status'] = Lottery::STATUS_NOUSE;
                        $common['userId'] = $userId;
                        $data[] = $common;
                        $index++;
                    }
                }
            } else {
                for ($i=0; $i<$num; $i++) {
                    $sn = strtoupper(substr(md5($index.microtime().$tk.rand(1000, 9999)), 8, 16));
                    $common['sn'] = $sn;
                    $common['endtime'] = $endtime;
                    $data[] = $common;
                    $index++;
                }
            }
            $failCount = Lottery::batchInsert($data);
            if($failCount==0) {

            	// 操作日志
            	$manager = $this->getUser();
            	$content = $manager->name.'【'.$manager->username.'】生成'.$index.'张'.$this->typeRow['name'].'，奖券备注：'.$remark;
            	OperationLog::addOne($manager, $content);

            	return true;
            } else {
            	$this->addError('form', '生成有问题，错误数量：'.$failCount);
				return false;
            }
		} else {
			return false;
		}
	}
}