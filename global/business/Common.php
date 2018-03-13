<?php
namespace business;

use models\OddLog;
use tools\Counter;
use models\OddLogErr;
use models\MoneyLog;
use Illuminate\Database\Capsule\Manager as DB;

trait Common {

    //资金日志
    protected $logData = [];

    //操作日志
    public $log = [];
    public $oddLog = [];
    
    /**
     * 添加用户金额 与日志
     * @param  [type] $money       [description]
     * @param  [type] $type        [description]
     * @param  [type] $oddNumber   [description]
     * @return [type]              [description]
     */
    public function payMoney($money,$type,$oddNumber){
        if($type != 'interestService'){
            $paymentUser = 'out';
            $paymentOffice = 'in';
            $this->user->fundMoney = $this->user->fundMoney - $money;
            $this->user->investMoney = $this->user->investMoney - $money;
            $this->account->fundMoney = $this->account->fundMoney + $money;
        }else if($type != 'lottery'){
            $paymentUser = 'in';
            $paymentOffice = 'out';
            $this->user->fundMoney = $this->user->fundMoney + $money;
            $this->user->investMoney = $this->user->investMoney + $money;
            $this->repayUser->fundMoney = $this->repayUser->fundMoney - $money;
        }else{
            $paymentUser = 'in';
            $paymentOffice = 'out';
            $this->user->fundMoney = $this->user->fundMoney + $money;
            $this->user->investMoney = $this->user->investMoney + $money;
            $this->account->fundMoney = $this->account->fundMoney - $money;
        }
        OddLog::writeLog($this->log);
        //用户资金日志
        $moneylog = [];
        $moneylog['type'] = $type;
        $moneylog['mode'] = $paymentUser;
        $moneylog['mvalue'] = $money;
        $moneylog['userId'] = $this->user->userId;
        $moneylog['remark'] = $this->log['remark'];
        $moneylog['remain'] = $this->user->fundMoney;
        $moneylog['frozen'] = $this->user->frozenMoney;
        $this->logData[] = $moneylog;
        //公司账户日志
        if($type == 'lottery' || $type == 'interestService'){
            $moneylog['mode'] = $paymentOffice;
            $moneylog['userId'] = $this->account->userId;
            $moneylog['remain'] = $this->account->fundMoney;
            $moneylog['remark'] = '用户:'.$this->user->userId.$this->log['remark'];
            $this->logData[] = $moneylog;
        }else{
            if(empty($this->logData[$type])){
                $this->logData[$type] = ['mvalue'=>'','mode'=>'','userId'=>'','remain'=>'','remark'=>''];
            }
            $this->logData[$type]['mvalue'] += $money;
            $this->logData[$type]['type'] = $type;
            $this->logData[$type]['mode'] = 'out';
            $this->logData[$type]['userId'] = $this->repayUser->userId;
            $this->logData[$type]['remain'] = $this->repayUser->fundMoney;
            $this->logData[$type]['frozen'] = $this->repayUser->frozenMoney;
            $remark = explode(',', $this->log['remark'],2);
            $this->logData[$type]['remark'] = '扣除:'.$this->logData[$type]['mvalue'].'元,'.$remark[1];
        }

    }

    /**
     * 获取流水号
     * @global type $db
     * @param type $number 流水号字段名称 20位
     * @param type $table  对应的表
     * @return string 
     */
    public function getSystemNumber($number, $table) {
        $number = DB::table($table)->orderBy($number,'desc')->where($number,'like',date("Ymd") . '%')->first([$number])->$number;
        if (!empty($number)) {
            $num = substr($number, 8) + 1;
            $num = sprintf("%06s", $num);
            $number = date("Ymd") . $num;
        } else {
            $number = date("Ymd") . "000001";
        }
        return $number;
    }

    /**
     *确认还款
     */
    protected function oddPayConfirm($oddNumber,$qishu,$workId){
        $db = loadDB($this->key);
        $sql_str = "SELECT id,tradeNo,xml,status from work_task where tradeNo LIKE '%". $oddNumber .'_'.$qishu.'_'."%' and type = 'repayment' order by id asc";
        $result = $db->getRow($sql_str);
        if($result['status'] == 0){
            $id = $result['id'];
            $rootTradeNo = $result['tradeNo'];
            $tradeArray = $result['xml'];
            require_once(dirname(__FILE__) . "/function/hui.class.php");
            $result = huiCao::autoPaymentNew($rootTradeNo, $tradeArray, $type = '1');
            if($result['status'] == 0000){
                $type = '1';
            }else{
                $type = '-1';
            }
            $sql_str = "UPDATE work_task set status = '".$type."' , result = '".$result['status']. "' where id = '{$id}'";
            $db->execute($sql_str);
            $this->fileWrite(dirname(__FILE__)."/function/log/repayment.txt", $sql_str);
            $msg['status'] = $result['status'];
            $msg['msg'] = '{SYSTEM}:{ODD}' . $oddNumber . '{HUANKUAN}'.$qishu;
            $msg['data'] = $workId;
            $this->writeLog($oddNumber, $msg['msg'], $sql_str, true, 'rehearConfirm');
            return $msg;
        }else{
            $msg['status'] = false;
            $msg['msg'] = '{SYSTEM}:{ODD}' . $oddNumber . '{HUANKUAN}'.$qishu.'重复提交';
            $msg['data'] = $workId;
            $this->writeLog($oddNumber, $msg['msg'], '', false, 'rehearConfirm');
            return $msg;
        }
    }

    /**
     * 保存用户和商家资金与日志变动
     * @return [type] [description]
     */
    protected function save(){
        if($this->user->save() && $this->account->save()){
            if($this->log['time'] == ''){
                $this->log['time'] = date("Y-m-d H:i:s");
            }
            MoneyLog::addAll($this->logData,$this->log['time']);
            return TRUE;
        }else{
            return FALSE;
        }
    }

    /**
     * 还款计算公式  payInterest('50000', '0.1', '3', '1');
     * @param type $money  借款总金额
     * @param type $yearInterest  年化率
     * @param type $month  借月份数
     * @param type $payType 标类型 month , day , sec
     * @param type $type  1：按月付息，到期还本  2：一次性还款  3：等额本息
     * @return type array
     */
    public function payInterest($money, $yearInterest, $month, $payType, $type = '', $oddType = '') {
        $array = array();
        $time = date("Y-m-d H:i:s");
        $money = floatval($money);
        $yearInterest = floatval($yearInterest);
        $interset = round((($money * $yearInterest) / 12), 2);
        if ('month' == $payType or 'week' == $payType) {
            $num = $month;
        } else {
            $num = 1;
        }
        if ($type == 'monthpay') {
            for ($i = 1; $i <= $num; $i++) {
                switch ($payType) {
                    case "month":
                        $etime = self::timeF($time);
                        $oddlixi = $interset;
                        break;
                    case "day":
                        $etime = self::timeD($time, $month);
                        $oddlixi = round(($interset / 30) * $month, 2);
                        break;
                    case "sec":
                        $etime = self::timeS($time);
                        $oddlixi = round(($interset / 30), 2);
                        break;
                }
                $array['notes'][$i]['lixi'] = $oddlixi;
                if ($i != $num) {
                    $array['notes'][$i]['month'] = $i;
                    $array['notes'][$i]['benjin'] = '0';
                    $array['notes'][$i]['zonger'] = $oddlixi;
                    $array['notes'][$i]['yuer'] = $money;
                    $array['notes'][$i]['stime'] = $time;
                    $array['notes'][$i]['etime'] = $etime;
                    $time = $etime;
                } else {
                    $array['notes'][$i]['month'] = $i;
                    $array['notes'][$i]['benjin'] = $money;
                    $array['notes'][$i]['zonger'] = ($money + $oddlixi);
                    $array['notes'][$i]['yuer'] = '0';
                    $array['notes'][$i]['stime'] = $time;
                    $array['notes'][$i]['etime'] = $etime;
                }
                $yingli = 3 * $interset;
            }
            $array['yingli'] = $yingli;
        }else if ($type == '2') {
            switch ($payType) {
                case "month":
                    $etime = self::timeF($time);
                    $oddlixi = $interset;
                    break;
                case "day":
                    $etime = self::timeD($time, $month);
                    $oddlixi = round(($interset / 30) * $month, 2);
                    break;
                case "sec":
                    $etime = self::timeS($time);
                    $oddlixi = round(($interset / 30), 2);
                    break;
            }
            $lixi = round(($oddlixi * $num), 2);
            $array['notes']['0']['lixi'] = $lixi;
            $array['notes']['0']['month'] = '1';
            $array['notes']['0']['benjin'] = $money;
            $array['notes']['0']['zonger'] = ($money + $lixi);
            $array['notes']['0']['yuer'] = '0';
            $array['notes']['0']['stime'] = $time;
            $array['notes']['0']['etime'] = $etime;
            $array['yingli'] = $lixi;
        }else if ($type == 'matchpay') {
            $zonger = monthHuan($money, $yearInterest, $num ,$type);
            $k = 0;
            foreach ($zonger as $key => $value) {
                if($oddType == 'xiaojin'){
                    $etime = self::timeSeven($time);
                }else{
                    $etime = self::timeF($time);
                }
                $array['notes'][$k]['lixi'] = $value['accountInterest'];
                $array['notes'][$k]['benjin'] = $value['accountCapital'];
                $array['notes'][$k]['zonger'] = $value['accountAll'];
                $array['notes'][$k]['yuer'] = $value['accountOther'];
                $array['notes'][$k]['month'] = $value['month'];
                $array['notes'][$k]['stime'] = $time;
                $array['notes'][$k]['etime'] = $etime;
                $time = $etime;
                $k++;
            }
        }else if ($type == '4') {
            $benjin = round(($money / $month), 2);
            $oLixi = round(($benjin * $yearInterest / 12), 2);
            $sLixi = round(($money * $yearInterest / 12), 2); //第一期3利息
            $n = 0;
            switch ($payType) {
                case "month":
                    $num = $num;
                    break;
                case "day":
                    $num = 1;
                    break;
                case "sec":
                    $num = 1;
                    break;
            }
            for ($m = 1; $m <= $num; $m++) {
                $etime = self::timeNexeMonth($time);
                if (1 == $m) {
                    $oddlixi = $sLixi;
                    $yuer = $money - $benjin;
                } else {
                    $oddlixi = $sLixi - $n * $oLixi;
                    $yuer = $money - $m * $benjin;
                    if($yuer < 0 ){
                        $yuer = 0;
                    }
                }
                $array['notes'][$n]['month'] = $m;
                $array['notes'][$n]['lixi'] = $oddlixi;
                $array['notes'][$n]['benjin'] = $benjin;
                $array['notes'][$n]['zonger'] = round(($benjin + $oddlixi), 2);
                $array['notes'][$n]['yuer'] = $yuer;
                $array['notes'][$n]['stime'] = $time;
                $array['notes'][$n]['etime'] = $etime;
                $time = $etime;
                $n++;
            }
            $array['yingli'] = $oLixi * $num;
        }
        return $array;
    }

    public static function getBatchNo(){
        $batchno = Counter::next('batchNo','d');
        return str_repeat('0', 6-strlen($batchno)).$batchno;
    }

    /**
     * 获取下一个月的时间
     */
    public static function timeF($time) {
        //return date('Y-m-d H:i:s',strtotime('+1 month',strtotime($time)));
        return date('Y-m-d H:i:s', strtotime('+30 day', strtotime($time)));
    }

    /**
     * 获取七天后的时间
     */
    public static function timeSeven($time) {
        //return date('Y-m-d H:i:s',strtotime('+1 month',strtotime($time)));
        return date('Y-m-d H:i:s', strtotime('+7 day', strtotime($time)));
    }

    /**
     * 获取过天数后时间
     * @param type $time
     * @param type $day
     */
    public static function timeD($time, $day) {
        $time = strtotime($time);
        $time = $time + ($day * 24 * 60 * 60);
        $time = date("Y-m-d H:i:s", $time);
        return $time;
    }

    /**
     * 获取秒标结束后的时间
     * @param string $time
     * @return string
     */
    public static function timeS($time) {
        $time = substr($time, 0, 10) . " 23:59:59";
        return $time;
    }
}
