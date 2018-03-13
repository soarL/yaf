<?php
namespace tools;

/**
 * Calculator
 * 工具类，计算器
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Calculator {
	public static function  getResult($data) {
		/*$data = [];
		$data['account'] = 50000;
		$data['apr'] = 20;
		$data['period'] = 12;
		$data['time'] = time();
		$data['style'] = 'end';
		$data['feeRate'] = 0;
		$data['rewardRate'] = 0;
		$data['type'] = 'all';*/
    	$style =$data['style'];
    	if ($style=="month"){
    		return self::getByMonth($data);
    	}elseif ($style=="season"){
    		return self::getBySeason($data);
    	}elseif ($style=="end"){
    		return self::getByEnd($data);
    	}elseif ($style=="endmonth"){
    		return self::getByEndMonth($data);
    	}elseif ($style=="endmonths"){
    		return self::getByEndMonths($data);
    	}elseif ($style=="endday"){
    		return self::getByEndDay($data);
    	}elseif($style=='week'){
            return self::getByWeek($data);
        }

	}

	//等额本息法
    //贷款本金×月利率×（1+月利率）还款月数/[（1+月利率）还款月数-1] 
    //a*[i*(1+i)^n]/[(1+I)^n-1] 
    //（a×i－b）×（1＋i）
    public static function getByMonth($data){
    	$account = $data['account'];
    	$yearApr = $data['apr'];
    	$period = $data['period'];
        $time = $data['time'];
		$rewardRate = $data['rewardRate'];
		$feeRate = $data['feeRate'];
		
		$reward = $account*$rewardRate/100;
		$fee = $account*$feeRate/100*$period;
		$dayApr = $yearApr/(365);
		$rf = $fee+$reward;
		$monthApr = $yearApr/(12*100);
    	$totalTimes = pow((1+$monthApr),$period);
    	if ($account<0) return;
        
        if ($totalTimes>1){
    	   $repayAccount = round($account * ($monthApr * $totalTimes)/($totalTimes-1),2);//515.1
    	}else{
    	    $repayAccount = $account;
    	}
    	$result = array();
    	$capitalAll = 0;
    	$interestAll = 0;
    	$accountAll = 0.00;
    	for($i=0;$i<$period;$i++){
    	    if ($totalTimes<=1){
    	       $interest = 0;
    		}elseif ($i==0){
    			$interest = round($account*$monthApr,2);
    		}else{
    			$totalTimes = pow((1+$monthApr),$i);
    			$interest = round(($account*$monthApr - $repayAccount)*$totalTimes + $repayAccount,2);
    		}
    		
    		//防止一分钱的问题
    		if ($i==$period-1)
    		{
    			$capital = $account - $capitalAll;
    			$interest = $repayAccount-$capital;
    		}else{
    			$capital =  $repayAccount - $interest;
    		}
    		
    		//echo $capital."<br>";
    		$accountAll +=  $repayAccount;
    		$interestAll +=  $interest;
    		$capitalAll +=  $capital;
    		$result[$i]['accountAll'] =  round($repayAccount,2);
    		$result[$i]['accountInterest'] = round( $interest,2);
    		$result[$i]['accountCapital'] =  round($capital,2);
    		$result[$i]['accountOther'] =  round($repayAccount*$period-$repayAccount*($i+1),2);
    		$result[$i]['repayMonth'] =  round($repayAccount,2);
    		$result[$i]['repayTime'] = self::getTimes(array("time"=>$time,"num"=>$i+1));
    	}
    	if (isset($data["type"])&&$data["type"]=="all"){
    		$resultAll['accountTotal'] = round($accountAll,2);
    		$resultAll['interestTotal'] = round($interestAll,2);
    		$resultAll['capitalTotal'] = round($capitalAll,2);
    		$resultAll['repayMonth'] = round($repayAccount,2);
    		$resultAll['monthApr'] = round($monthApr*100,2);
			$resultAll['reward'] = round($reward,2);
			$resultAll['fee'] = round($fee,2);
			$resultAll['rf'] = round($rf,2);
			$resultAll['yearApr'] = round($yearApr,2);
			$resultAll['dayApr'] = round($dayApr,2);
    		return $resultAll;
    	}
    	return $result;
    }

    //等额本息法
    //贷款本金×周利率×（1+周利率）还款周数/[（1+周利率）还款周数-1] 
    //a*[i*(1+i)^n]/[(1+I)^n-1] 
    //（a×i－b）×（1＋i）
    public static function getByWeek($data){
        $account = $data['account'];
        $yearApr = $data['apr'];
        $period = $data['period'];
        $time = $data['time'];
        $rewardRate = $data['rewardRate'];
        $feeRate = $data['feeRate'];
        
        $reward = $account*$rewardRate/100;
        $fee = $account*$feeRate/100*$period;
        $dayApr = $yearApr/(365);
        $rf = $fee+$reward;
        $weekApr = $dayApr*7;
        $totalTimes = pow((1+$weekApr), $period);
        if ($account<0) return;
        
        if ($totalTimes>1){
            $repayAccount = round($account * ($weekApr * $totalTimes)/($totalTimes-1),2);
        }else{
            $repayAccount = $account;
        }
        $result = array();
        $capitalAll = 0;
        $interestAll = 0;
        $accountAll = 0.00;
        for($i=0;$i<$period;$i++){
            if($totalTimes<=1) {
               $interest = 0;
            }elseif ($i==0) {
                $interest = round($account*$weekApr,2);
            }else{
                $totalTimes = pow((1+$weekApr), $i);
                $interest = round(($account*$weekApr - $repayAccount)*$totalTimes + $repayAccount,2);
            }
            
            //防止一分钱的问题
            if ($i==$period-1) {
                $capital = $account - $capitalAll;
                $interest = $repayAccount - $capital;
            }else{
                $capital =  $repayAccount - $interest;
            }
            
            $accountAll +=  $repayAccount;
            $interestAll +=  $interest;
            $capitalAll +=  $capital;
            $result[$i]['accountAll'] =  round($repayAccount,2);
            $result[$i]['accountInterest'] = round( $interest,2);
            $result[$i]['accountCapital'] =  round($capital,2);
            $result[$i]['accountOther'] =  round($repayAccount*$period-$repayAccount*($i+1),2);
            $result[$i]['repayMonth'] =  round($repayAccount,2);
            $result[$i]['repayTime'] = self::getTimes(array("time"=>$time,"num"=>$i+1));
        }
        if (isset($data["type"])&&$data["type"]=="all"){
            $resultAll['accountTotal'] = round($accountAll,2);
            $resultAll['interestTotal'] = round($interestAll,2);
            $resultAll['capitalTotal'] = round($capitalAll,2);
            $resultAll['repayMonth'] = round($repayAccount,2);
            $resultAll['monthApr'] = round($weekApr*100,2);
            $resultAll['reward'] = round($reward,2);
            $resultAll['fee'] = round($fee,2);
            $resultAll['rf'] = round($rf,2);
            $resultAll['yearApr'] = round($yearApr,2);
            $resultAll['dayApr'] = round($dayApr,2);
            return $resultAll;
        }
        return $result;
    }

    //按季还款
    public static function getBySeason ($data){
    	
    	//借款的月数
    	if (isset($data['period']) && $data['period']>0){
    		$period = $data['period'];
    	}
    	
    	//按季还款必须是季的倍数
    	if ($period%3!=0){
    		return false;
    	}
     
    	//借款的总金额
    	if (isset($data['account']) && $data['account']>0){
    		$account = $data['account'];
    	}else{
    		return "";
    	}
    	
    	//借款的年利率
    	if (isset($data['apr']) && $data['apr']>0){
    		$yearApr = $data['apr'];
    	}else{
    		return "";
    	}
    	
    	//借款的时间
    	if (isset($data['time']) && $data['time']>0){
    		$time = $data['time'];
    	}else{
    		$time = time();
    	}
    	
    	//月利率
    	$monthApr = $yearApr/(12*100);
		$rewardRate = $data['rewardRate'];
		$feeRate = $data['feeRate'];
		
		$reward = $account*$rewardRate/100;
		$fee = $account*$feeRate/100*$period;
		$dayApr = $yearApr/(365);
		$rf = $fee+$reward;
		
    	//得到总季数
    	$_season = $period/3;
    	
    	//每季应还的本金
    	$seasonMoney = round($account/$_season,2);
    	
    	//$re_month = date("n",$time);
    	$yesAccount = 0 ;
    	$repayAccount = 0;//总还款额
    	$capitalAll = 0;
    	$interestAll = 0;
    	$accountAll = 0.00;
    	for($i=0;$i<$period;$i++){
    		$repay = $account - $yesAccount;//应还的金额
    		$interest = round($repay*$monthApr,2);//利息等于应还金额乘月利率
    		$repayAccount = $repayAccount+$interest;//总还款额+利息
    		$capital = 0;
    		if ($i%3==2){
    			$capital = $seasonMoney;//本金只在第三个月还，本金等于借款金额除季度
    			$yesAccount = $yesAccount+$capital;
    			$repay = $account - $yesAccount;
    			$repayAccount = $repayAccount+$capital;//总还款额+本金
    		}
    		$_repayAccount = $interest+$capital;
    		$result[$i]['accountInterest'] = round($interest,2);
    		$result[$i]['accountCapital'] = round($capital,2);
    		$result[$i]['accountAll'] =round($_repayAccount,2);
    		
    		$accountAll +=  $_repayAccount;
    		$interestAll +=  $interest;
    		$capitalAll +=  $capital;
    		
    		$result[$i]['accountOther'] = round($repay,2);
    		$result[$i]['repayMonth'] = round($repayAccount,2);
    		$result[$i]['repayTime'] = self::getTimes(array("time"=>$time,"num"=>$i+1));
    	}
    	if (isset($data['type'])&&$data['type']=='all'){
    		$resultAll['accountTotal'] =  round($accountAll,2);
    		$resultAll['interestTotal'] =  round($interestAll,2);
    		$resultAll['capitalTotal'] =  round($capitalAll,2);
    		$resultAll['repayMonth'] = "-";
    		$resultAll['repaySeason'] = $seasonMoney;
    		$resultAll['monthApr'] = round($monthApr*100,2);
			$resultAll['reward'] = round($reward,2);
			$resultAll['fee'] = round($fee,2);
			$resultAll['rf'] = round($rf,2);
			$resultAll['yearApr'] = round($yearApr,2);
			$resultAll['dayApr'] = round($dayApr,2);
    		return $resultAll;
    	}
    	return $result;
    }

    
    //到期还本还息
    public static function getByEnd($data){
    	
    	//借款的月数
    	if (isset($data['period']) && $data['period']>0){
    		$period = $data['period'];
    	}
     
    	//借款的总金额
    	if (isset($data['account']) && $data['account']>0){
    		$account = $data['account'];
    	}else{
    		return "";
    	}
    	//借款的年利率
    	if (isset($data['apr']) && $data['apr']>0){
    		$yearApr = $data['apr'];
    	}else{
    		return "";
    	}
    	//借款的时间
    	if (isset($data['time']) && $data['time']>0){
    		$time = $data['time'];
    	}else{
    		$time = time();
    	}
    	
    	//月利率
    	$monthApr = $yearApr/(12*100);
    	$rewardRate = $data['rewardRate'];
		$feeRate = $data['feeRate'];
		
		$reward = $account*$rewardRate/100;
		$fee = $account*$feeRate/100*$period;
		$dayApr = $yearApr/(365);
		$rf = $fee+$reward;
    	$interest = $monthApr*$period*$account;
    	
    	if (isset($data['type']) && $data['type']=="all"){
    		$resultAll['accountTotal'] =   round($account + $interest,2);
    		$resultAll['interestTotal'] =  round($interest,2);
    		$resultAll['capitalTotal'] =  round($account,2);
    		$resultAll['repayMonth'] =  round($account + $interest,2);
    		$resultAll['monthApr'] = round($monthApr*100,2);
			$resultAll['reward'] = round($reward,2);
			$resultAll['fee'] = round($fee,2);
			$resultAll['rf'] = round($rf,2);
			$resultAll['yearApr'] = round($yearApr,2);
			$resultAll['dayApr'] = round($dayApr,2);
    		return $resultAll;
    	}else{
    		$result[0]['accountAll'] = round($interest+$account,2);
    		$result[0]['accountInterest'] = round($interest,2);
    		$result[0]['accountCapital'] = round($account,2);
    		$result[0]['accountOther'] = 0;
    		$result[0]['repayMonth'] = round($interest+$account,2);
    		$result[0]['repayTime'] = self::getTimes(array("time"=>$time,"num"=>$period));
			$resultAll['reward'] = round($reward,2);
			$resultAll['fee'] = round($fee,2);
			$resultAll['rf'] = round($rf,2);
			$resultAll['yearApr'] = round($yearApr,2);
			$resultAll['dayApr'] = round($dayApr,2);
    		return $result;
    	}
    }
    
    
    //到期还本，按月付息
    public static function getByEndMonth ($data = array()){
    	//借款的月数
    	if (isset($data['period']) && $data['period']>0){
    		$period = $data['period'];
    	}
     
    	//借款的总金额
    	if (isset($data['account']) && $data['account']>0){
    		$account = $data['account'];
    	}else{
    		return "";
    	}
    	
    	//借款的年利率
    	if (isset($data['apr']) && $data['apr']>0){
    		$yearApr = $data['apr'];
    	}else{
    		return "";
    	}
    	
    	//借款的时间
    	if (isset($data['time']) && $data['time']>0){
    		$borrowTime = $data['time'];
    	}else{
    		$borrowTime = time();
    	}
    	
    	//月利率
    	$monthApr = $yearApr/(12*100);
    	$rewardRate = $data['rewardRate'];
		$feeRate = $data['feeRate'];
		$reward = $account*$rewardRate/100;
		$fee = $account*$feeRate/100*$period;
		$dayApr = $yearApr/(365);
		$rf = $fee+$reward;
    	$yesAccount = 0 ;
    	// $repayment_account = 0;//总还款额
    	$interestAll = 0;
    	$capitalAll = 0;
    	
    	$interest = round($account*$monthApr,2);//利息等于应还金额乘月利率
    	for($i=0;$i<$period;$i++){
    		$capital = 0;
    		if ($i+1 == $period){
    			$capital = $account;//本金只在第三个月还，本金等于借款金额除季度
    		}
    		// $accountAll +=  $_repayAccount;
    		$interestAll +=  $interest;
    		$capitalAll +=  $capital;
    		
    		$result[$i]['accountAll'] = $interest+$capital;
    		$result[$i]['accountInterest'] = $interest;
    		$result[$i]['accountCapital'] = $capital;
    		$result[$i]['accountOther'] = round($account-$capital,2);
    		$result[$i]['repayYear'] = $account;
    		$result[$i]['repayTime'] = self::getTimes(array("time"=>$borrowTime,"num"=>$i+1));
    	}
    	if (isset($data['type'])&&$data['type']=='all'){
    		$resultAll['accountTotal'] =  $account + $interest*$period;
    		$resultAll['interestTotal'] = $interestAll;
    		$resultAll['capitalTotal'] = $account;
    		$resultAll['repayMonth'] = $interest;
    		$resultAll['monthApr'] = round($monthApr*100,2);
    		$resultAll['repayTime'] = self::getTimes(array("time"=>$borrowTime,"num"=>$period));
			$resultAll['reward'] = round($reward,2);
			$resultAll['fee'] = round($fee,2);
			$resultAll['rf'] = round($rf,2);
			$resultAll['yearApr'] = round($yearApr,2);
			$resultAll['dayApr'] = round($dayApr,2);
    		return $resultAll;
    	}
    		return $result;
    }
    
    
    
    //到期还本，按天付息
    public static function getByEndDay ($data = array()){
    	if ( $data['account']=="" ) { return "";}  
    	if ( $data['period']=="" ) { return "";}  //天数
    	if ( $data['apr']=="" ) { return "";}  
    
    	//借款的时间
    	if (isset($data['time']) && $data['time']>0){
    		$borrowTime = $data['time'];
    	}else{
    		$borrowTime = time();
    	}
    	
    	//天利率
    	$dayApr = $data['apr']/365/100;
        $interestAll = round($data['account']*$data['period']*$dayApr,2);
    	$accountAll = $interestAll +$data['account'];
    	
    	if (isset($data['type'])&&$data['type']=='all'){
    		$result['accountTotal'] = $accountAll;
    		$result['interestTotal'] = $interestAll;
    		$result['capitalTotal'] = $data['account'];
    		$result['dayApr'] = round($dayApr,2);
    	}else{
            $result[0]['accountAll'] = $accountAll;
            $result[0]['accountInterest'] = $interestAll;
            $result[0]['accountCapital'] = $data['account'];
            $result[0]['repayTime'] = self::getTimes(array("time"=>$borrowTime,"num"=>$data["period"],"type"=>"day"));
    	}
    	return $result;
    }


    
    //到期还本，按月付息,且当月还息
    public static function getByEndMonths ($data = array()){
    	
    	//借款的月数
    	if (isset($data['period']) && $data['period']>0){
    		$period = $data['period'];
    	}
     
    	//借款的总金额
    	if (isset($data['account']) && $data['account']>0){
    		$account = $data['account'];
    	}else{
    		return "";
    	}
    	
    	//借款的年利率
    	if (isset($data['apr']) && $data['apr']>0){
    		$yearApr = $data['apr'];
    	}else{
    		return "";
    	}
    	
    	//借款的时间
    	if (isset($data['time']) && $data['time']>0){
    		$borrowTime = $data['time'];
    	}else{
    		$borrowTime = time();
    	}
    	
    	//月利率
    	$monthApr = $yearApr/(12*100);
    	$yesAccount = 0 ;
    	$accountAll = 0;//总还款额
    	$interestAll = 0;
    	$capitalAll = 0;
    	
    	$interest = round($account*$monthApr,2);//利息等于应还金额乘月利率
    	for($i=0;$i<$period;$i++){
    		$capital = 0;
    		
    		// $accountAll +=  $_repayAccount;
    		$interestAll +=  $interest;
    		$capitalAll +=  $capital;
    		$result[$i]['accountAll'] = $interest+$capital;
    		$result[$i]['accountInterest'] = $interest;
    		$result[$i]['accountCapital'] = $capital;
    		$result[$i]['accountOther'] = round($account-$capital,2);
    		$result[$i]['repayYear'] = $account;
    		$result[$i]['repayTime'] = self::getTimes(array("time"=>$borrowTime,"num"=>$i));
    	}
		
		$result[$period]['accountAll'] = $account;
		$result[$period]['accountInterest'] = 0;
		$result[$period]['accountCapital'] = $account;
		$result[$period]['accountOther'] = 0;
		$result[$period]['repayYear'] = $account;
		$result[$period]['repayTime'] = self::getTimes(array("time"=>$borrowTime,"num"=>$period));
		
    	if (isset($data['type'])&&$data['type']=='all'){
    		$resultAll['accountTotal'] =  $account + $interest*$period;
    		$resultAll['interestTotal'] = $interestAll;
    		$resultAll['capitalTotal'] = $account;
    		$resultAll['repayMonth'] = $interest;
    		$resultAll['monthApr'] = round($monthApr*100,2);
    		$resultAll['repayTime'] = self::getTimes(array("time"=>$borrowTime,"num"=>$period));
    		return $resultAll;
    	}
    		return $result;
    }

    public static function getTimes($data=array()){
  
		if (isset($data['time']) && $data['time']!=""){
			$time = $data['time'];//时间
		}elseif (isset($data['date']) && $data['date']!=""){
			$time = strtotime($data['date']);//日期
		}else{
			$time = time();//现在时间
		}
		if (isset($data['type']) && $data['type']!=""){ 
			$type = $data['type'];//时间转换类型，有day week month year
		}else{
			$type = "month";
		}
		if (isset($data['num']) && ($data['num']!="" || $data['num']=="0")){ 
			$num = $data['num'];
		}else{
			$num = 1;
		}
		if ($type=="month"){
			$month = date("m",$time);
			$year = date("Y",$time);
			$result = strtotime("$num month",$time);
			$_month = (int)date("m",$result);
			if ($month+$num>12){
				$_num = $month+$num-12;
				$year = $year+1;
			}else{
				$_num = $month+$num;
			}
			
			if ($_num!=$_month){
				//$result = strtotime("-1 day",strtotime("{$year}-{$_month}-01"));
			}
		}else{
			$result = strtotime("$num $type",$time);
		}
		if (isset($data['format']) && $data['format']!=""){ 
			return date($data['format'],$result);
		}else{
			return $result;
		}

	}
}