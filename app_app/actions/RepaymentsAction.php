<?php
use traits\handles\ITFAuthHandle;
use models\Invest;

/**
 * RepaymentsAction
 * APP回款日历页数据
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class RepaymentsAction extends Action {
    use ITFAuthHandle;

    public function execute() {
    	$params = $this->getAllQuery();
        $this->authenticate($params, ['userId'=>'用户ID']);

        $user = $this->getUser();
        $userId = $user->userId;

        $this->pv('ap');
         
        $year = intval($this->getQuery('year'));
        $month = intval($this->getQuery('month'));
        $isAll = $this->getQuery('isAll', 0);
        $type = $this->getQuery('type', 'old');

        if($month<10) {
            $month = '0'.$month;
        }
        $firstDay = $year . '-' . $month .'-01';
        $lastDay = date('Y-m-d',strtotime("$firstDay +1 month -1 day"));
        $firstDay .= ' 00:00:00';
        $lastDay .= ' 23:59:59';

        $repayments = Invest::getRepaymentsBuilder($userId, $firstDay, $lastDay)->get();
        $dayCount = intval(date('d', strtotime($lastDay))) - intval(date('d', strtotime($firstDay))) + 1;
        $repaymentList = [];
        $allMoney = 0;
        $benJin = 0;
        $interest = 0;
        foreach ($repayments as $repayment) {
            $key = intval(date('d', strtotime($repayment['endtime'])));
            if(isset($repaymentList[$key])) {
                $repaymentList[$key]['benJin'] += $repayment->getPrincipal();
                $repaymentList[$key]['interest'] += $repayment->getInterest();
                $repaymentList[$key]['oughtMoney'] += $repayment->getAmount();
                $repaymentList[$key]['realMoney'] += $repayment->realAmount;
                $repaymentList[$key]['realInterest'] += $repayment->realinterest;
                $repaymentList[$key]['realBenjin'] += $repayment->realAmount-$repayment->realinterest;
                $repaymentList[$key]['serviceMoney'] += $repayment->serviceMoney;

                if($type=='old') {
                    $odd = [];
                    $odd['oddNumber'] = $repayment->oddMoney->oddNumber;
                    $odd['oddTitle'] = $repayment->oddMoney->odd->oddTitle;
                    $odd['oddPeriod'] = $repayment->oddMoney->odd->getPeriod();
                    $odd['oddYearRate'] = $repayment->oddMoney->odd->oddYearRate;
                    $odd['money'] = $repayment->oddMoney->money;
                    $odd['status'] = $repayment->status;

                    $repaymentList[$key]['odds'][] = $odd;
                }

                if( ($repayment->status==Invest::STATUS_STAY&&$repaymentList[$key]['status']=='over')||
                    ($repayment->status!=Invest::STATUS_STAY&&$repaymentList[$key]['status']=='stay') ) {
                    $repaymentList[$key]['status'] = 'ing';
                }

                if($repayment->status==Invest::STATUS_REPAYING) {
                    $repaymentList[$key]['status'] = 'ing';
                } else {
                    if($repaymentList[$key]['status']=='over' && $repayment->status==Invest::STATUS_STAY) {
                        $repaymentList[$key]['status'] = 'ing';
                    } else if($repaymentList[$key]['status']=='stay' && $repayment->status!=Invest::STATUS_STAY) {
                        $repaymentList[$key]['status'] = 'ing';
                    }
                }

                $allMoney += $repayment->zongEr;
                $interest += $repayment->interest;
                $benJin += $repayment->benJin;
            } else {
                $repaymentList[$key]['benJin'] = $repayment->getPrincipal();
                $repaymentList[$key]['interest'] = $repayment->getInterest();
                $repaymentList[$key]['oughtMoney'] = $repayment->getAmount();
                $repaymentList[$key]['realMoney'] = $repayment->realAmount;
                $repaymentList[$key]['realInterest'] = $repayment->realinterest;
                $repaymentList[$key]['realBenjin'] = $repayment->realAmount-$repayment->realinterest;
                $repaymentList[$key]['serviceMoney'] = $repayment->serviceMoney;
                
                if($type=='old') {
                    $odd = [];
                    $odd['oddNumber'] = $repayment->oddMoney->oddNumber;
                    $odd['oddTitle'] = $repayment->oddMoney->odd->oddTitle;
                    $odd['oddPeriod'] = $repayment->oddMoney->odd->getPeriod();
                    $odd['oddYearRate'] = $repayment->oddMoney->odd->oddYearRate;
                    $odd['money'] = $repayment->oddMoney->money;
                    $odd['status'] = $repayment->status;

                    $repaymentList[$key]['odds'][] = $odd;
                }

                if($repayment->status==Invest::STATUS_STAY) {
                    $repaymentList[$key]['status'] = 'stay';
                } else if($repayment->status==Invest::STATUS_REPAYING) {
                    $repaymentList[$key]['status'] = 'ing';
                } else {
                    $repaymentList[$key]['status'] = 'over';
                }

                $allMoney += $repayment->zongEr;
                $interest += $repayment->interest;
                $benJin += $repayment->benJin;
            }
        }
        $monthRepayments = [];
        if($type=='old') {
            for ($i=0; $i < $dayCount; $i++) {
                if(isset($repaymentList[$i+1])) {
                    $monthRepayments[] = $repaymentList[$i+1];  
                } else {
                    $monthRepayments[] = ['benJin'=>0, 'interest'=>0, 'oughtMoney'=>0, 'realMoney'=>0, 'serviceMoney'=>0, 'status'=>'none', 'realInterest'=>0, 'realBenjin'=>0];
                }
            }
        } else {
            $monthRepayments = $repaymentList;
        }

        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功！';
        $rdata['data']['repayments'] = $monthRepayments;
        $rdata['data']['allMoney'] = $allMoney;
        $rdata['data']['interest'] = $interest;
        $rdata['data']['benJin'] = $benJin;

        if($isAll==1) {
        	$repayments = Invest::getRepaymentsBuilder($userId, '', '', 'stay')->get();
	        $stayDays = [];
	        foreach ($repayments as $repayment) {
	            $endDay = date('Ymd', strtotime($repayment['endtime']));
	            if(!in_array($endDay, $stayDays)) {
	                $stayDays[] = $endDay;
	            }
	        }
	        sort($stayDays);
	        $rdata['data']['stayDays'] = $stayDays;
        }
        
        $this->backJson($rdata);
    }
}