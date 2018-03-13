<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * Queue|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Queue extends Model {
	
	protected $table = 'user_queue';

	public $timestamps = false;

	public $queuesInfo = false;

	public function autoInvest() {
		return $this->belongsTo('models\AutoInvest', 'userId', 'userId');
	}

	public function user() {
		return $this->belongsTo('models\User', 'userId');
	}

    public static function out($userId) {
        return self::where('userId', $userId)->delete();
    }

    public static function in($userId) {
        $queueLast = self::orderBy('location', 'desc')->first();
        $location = 0;
        if($queueLast) {
            $location = $queueLast->location + 1;
        } else {
            $location = 1;
        }
        $queue = new self();
        $queue->userId = $userId;
        $queue->location = $location;
        return $queue->save();
    }

	public function getQueuesInfo($type='pre') {
		if(isset($this->queuesInfo[$type])) {
			return $this->queuesInfo[$type];
		}
		$queues = [];
        $cols = ['investEgisMoney', 'investMoneyLower', 'investMoneyUper', 'staystatus', 'autostatus', 'userId', 'types'];
		if($type=='all') {
			$queues = Queue::with([
                'autoInvest'=>function($q) use($cols) {$q->select($cols);}, 
                'user'=>function($q) { $q->select('userId','fundMoney');}
            ])->get();
		} else {
			$t = '<';
			if($type=='aft') {
				$t = '>';
			}
			$queues = Queue::with([
                'autoInvest'=>function($q) use($cols) {$q->select($cols);}, 
                'user'=>function($q) { $q->select('userId','fundMoney');}
            ])->where('location', $t, $this->location)->get();	
		}
		
        $allMoney = 0;
        $validMoney = 0;
        $invalidMoney = 0;
        $allNum = 0;
        $validNum = 0;
        $invalidNum = 0;
        
        /*$monthNum = ['1'=>0, '2'=>0, '3'=>0, '6'=>0, '12'=>0, '24'=>0, '30'=>0, '35'=>0, '40'=>0, '45'=>0, '50'=>0];
        $monthMoney = ['1'=>0, '2'=>0, '3'=>0, '6'=>0, '12'=>0, '24'=>0, '30'=>0, '35'=>0, '40'=>0, '45'=>0, '50'=>0];*/

        $monthNum = ['1'=>0, '2'=>0, '3'=>0, '6'=>0, '12'=>0, '24'=>0];
        $monthMoney = ['1'=>0, '2'=>0, '3'=>0, '6'=>0, '12'=>0, '24'=>0];

        foreach ($queues as $queue) {
            $autoInvest = $queue->autoInvest;
            $user = $queue->user;

            if(!$user || !$autoInvest) {
            	continue;
            }

            $allMoney += $user->fundMoney;
            $allNum += 1;

            if($autoInvest->investable($user)) {
                $validMoney += $user->fundMoney;
                $validNum += 1;

                $types = $autoInvest->getTypes();
                $autoPeriodSet = [];
                foreach ($types as $t) {
                    if($t['periodType']!='month') {
                        continue;
                    }
                    if(!in_array($t['period'], $autoPeriodSet)) {
                        $autoPeriodSet[] = $t['period'];
                        if(isset($monthNum[$t['period']]) && isset($monthMoney[$t['period']])) {
                            $monthNum[$t['period']]++;
                            $monthMoney[$t['period']] += $user->fundMoney;
                        }
                    }
                }
            } else {
                $invalidMoney += $user->fundMoney;
                $invalidNum += 1;
            }
        }

        $info = [];
        $info['allMoney']  = $allMoney;
        $info['validMoney'] = $validMoney;
        $info['invalidMoney'] = $invalidMoney;
        $info['allNum'] = $allNum;
        $info['validNum'] = $validNum;
        $info['invalidNum'] = $invalidNum;
        $info['monthNum'] = $monthNum;
        $info['monthMoney'] = $monthMoney;

        $this->queuesInfo[$type] = $info;

        return $info;
	}
}