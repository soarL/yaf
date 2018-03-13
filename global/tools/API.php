<?php
namespace tools;
use helpers\NetworkHelper;
use helpers\FileHelper;
use Yaf\Registry;
class API {
    
    public static $msg;

    public static function getConfig($name) {
        // url | name | port | key | advice
        return Registry::get('config')->get('webapi')->get($name);
    }

    /**
     * 投标
     * @param  array   $data 数据项
     * @param  string  $type 投标类型 userSendOdd|virtualUserSendOdd|currentSendOdd
     * @return boolean       是否成功
     */
	public static function bid($data=array(), $type='userSendOdd') {
		$postData = [];
		$postData['accountNumber'] = self::getConfig('name');
        $postData['cmd'] = $type;
        $postData['oddNumber'] = $data['oddNumber'];
        $postData['userId'] = $data['userId'];
        $postData['money'] = $data['money'];
        $postData['tradeNo'] = $data['tradeNo'];
        $postData['adviceURL'] = self::getConfig('advice');
        $postData['time'] = microtime();
        return self::post($postData);
	}

    /**
     * 债权转让(新)
     * @param  array  $data 数据项
     * @return boolean       是否成功
     */
    public static function transfer($data=array()) {
        $postData = [];
        $postData['accountNumber'] = self::getConfig('name');
        $postData['cmd'] = 'publishClaimsOdd';
        $postData['userId'] = $data['userId'];
        $postData['oddmoneyId'] = $data['oddmoneyId'];
        $postData['oddNumber'] = $data['oddNumber'];
        $postData['adviceURL'] = self::getConfig('advice');
        $postData['time'] = microtime();
        return self::post($postData);
    }

    /**
     * 给用户平台账户及一麻袋账户添加金额
     * @param  array  $data 数据项
     * @return boolean       是否成功
     */
    public static function addMoney($data=array()) {
        $postData = [];
        $postData['accountNumber'] = self::getConfig('name');
        $postData['cmd'] = 'addMoneyApi';
        $postData['userId'] = $data['userId'];
        $postData['money'] = $data['money'];
        $postData['remark'] = $data['remark'];
        $postData['adviceURL'] = self::getConfig('advice');
        $postData['time'] = microtime();
        return self::post($postData);
    }

    /**
     * 实名认证检查
     * @param  array  $data 数据项
     * @return boolean       是否成功
     */
    public static function identify($data=array()) {
        $postData = [];
        $postData['accountNumber'] = self::getConfig('name');
        $postData['cmd'] = 'identify';
        $postData['userId'] = 'admin';
        $postData['identify'] = $data['name'].','.$data['cardnum'];
        $postData['adviceURL'] = self::getConfig('advice');
        $postData['time'] = microtime();
        
        $status = self::post($postData);

        if($status) {
            $msg = json_decode(self::$msg, true);
            if($msg['status']==0&&$msg['result']['verifystatus']==0) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 开通第三方帐号送50元红包
     * @param  array  $data 数据项
     * @return boolean       是否成功
     */
    public static function redPack($data=array()) {
        $postData = [];
        $postData['accountNumber'] = self::getConfig('name');
        $postData['cmd'] = 'redPack';
        $postData['userId'] = $data['userId'];
        $postData['time'] = microtime();
        return self::post($postData);
    }

    /**
     * 使用加息券
     * @param  array  $data 数据项
     * @return boolean       是否成功
     */
    public static function lottery($data=array()) {
        $postData = [];
        $postData['accountNumber'] = self::getConfig('name');
        $postData['cmd'] = 'interestLottery';
        $postData['lotteryId'] = $data['lotteryId'];
        $postData['oddMoneyId'] = $data['oddMoneyId'];
        $postData['userId'] = 'admin';
        $postData['time'] = microtime();
        return self::post($postData);
    }

    /**
     * 还款
     * @param  array  $data 数据
     * @param  string $type 类型
     */
    public static function repayment($data=array(), $type='regular') {
        $postData = [];
        $postData['accountNumber'] = self::getConfig('name');
        $postData['cmd'] =  $type;
        $postData['oddNumber'] = $data['oddNumber'];
        $postData['type'] = $data['type'];
        $postData['qishu'] = $data['qishu'];
        $postData['zongqishu'] = $data['zongqishu'];
        $postData['benjin'] = $data['benjin'];
        $postData['lixi'] = $data['lixi'];
        $postData['userId'] = 'admin';
        $postData['time'] = microtime();
        return self::post($postData);
    }

    /**
     * 添加提前还款
     * @param  array  $data 数据
     */
    public static function addAdvance($oddNumber) {
        $postData = [];
        $postData['cmd'] = 'addfrontal';
        $postData['oddNumber'] = $oddNumber;
        $postData['userId'] = 'admin';
        $postData['time'] = microtime();
        return self::post($postData);
    }

    /**
     * 添加逾期还款
     * @param  array  $data 数据
     */
    public static function addDelay($oddNumber) {
        $postData = [];
        $postData['cmd'] = 'adddelay';
        $postData['oddNumber'] = $oddNumber;
        $postData['userId'] = 'admin';
        $postData['time'] = microtime();
        return self::post($postData);
    }

    /**
     * 提前还款
     */
    public static function advancePay($data){
        $postData['cmd'] = 'frontal';
        $postData['accountNumber'] = self::getConfig('name');
        $postData['oddNumber'] = $data['oddNumber'];
        $postData['qishu'] = $data['qishu'];
        $postData['zongqishu'] = $data['zongqishu'];
        $postData['benjin'] = '1';
        $postData['lixi'] = '1';
        $postData['type'] = 'month';
        $postData['userId'] = 'admin';
        $postData['time'] = microtime();
        return self::post($postData);
    }
    /**
     * 启动初审队列
     * @param  array  $data 数据
     */
    public static function runtrial() {
        $postData = [];
        $postData['accountNumber'] = self::getConfig('name');
        $postData['cmd'] =  'runOddTrial';
        $postData['type'] = 'manual';
        $postData['userId'] = 'admin';
        $postData['oddNumber'] = date('Y-m-d H:i:s');
        $postData['time'] = microtime();
        return self::post($postData);
    }

    /**
     * 增减用户金额
     * @param  array  $data 数据
     */
    public static function increase($data=array()) {
        $postData = [];
        $postData['accountNumber'] = self::getConfig('name');
        $postData['cmd'] =  $data['cmd'];
        $postData['id'] = $data['id'];
        $postData['type'] = $data['type'];
        $postData['userId'] = $data['userId'];
        $postData['time'] = microtime();
        return self::post($postData);
    }

    /**
     * 初审
     * @param  array  $data 数据
     */
    public static function trial($data=array()) {
        $postData = [];
        $postData['accountNumber'] = self::getConfig('name');
        $postData['cmd'] = 'aloneOddTrial';
        $postData['oddNumber'] = $data['oddNumber'];
        $postData['status'] = $data['status'];
        $postData['type'] = 'manual';
        $postData['userId'] = 'admin';
        $postData['time'] = microtime();
        return self::post($postData);
    }

    /**
     * 邮件
     * @param  array  $data 数据
     */
    public static function sendMail($data=array()) {
        $postData = [];
        $postData['accountNumber'] = self::getConfig('name');
        $postData['cmd'] = 'sendMail';
        $postData['email'] = $data['email'];
        $postData['html'] = $data['html'];
        $postData['title'] = $data['title'];
        $postData['userId'] = 'admin';
        $postData['time'] = microtime();
        return self::post($postData);
    }

    /**
     * 复审
     * @param  array  $data 数据
     * @param  string $type 类型
     */
    public static function rehear($data=array(), $type='runOddRehear') {
        $postData = [];
        $postData['cmd'] = $type;
        $postData['accountNumber'] = self::getConfig('name');
        $postData['oddNumber'] = $data['oddNumber'];
        $postData['oddLoanServiceFees'] = $data['loanServiceFees'];   //借款服务费
        $postData['status'] = $data['status'];
        $postData['userId'] = 'admin';
        $postData['time'] = microtime();
        return self::post($postData);
    }

    /**
     * 自动投标
     * @param  array  $data 数据
     * @param  string $type 类型
     */
    public static function autoLoan($oddNumber='') {
        $postData = [];
        $postData['cmd'] = 'autoLoan';
        $postData['accountNumber'] = self::getConfig('name');
        $postData['oddNumber'] = $oddNumber;
        $postData['userId'] = 'admin';
        $postData['time'] = microtime();
        return self::post($postData);
    }

    /**
     * 获取用户投资旧数据(对接旧系统)
     * @param  string  $userId 数据项
     * @return boolean|array       数据
     */
    public static function tenderOldData($userId) {
        $postData = [];
        $key = 'xwsdxwsdolddata880066';
        $type = 'tenderData';
        $postData['sign'] = md5($key.$userId.$type);
        $postData['type'] = $type;
        $postData['userId'] = $userId;
        
        $result = NetworkHelper::post('https://www.hcjrfw.com/xwsd_bash/olddata/index.php',$postData);
        $result = json_decode($result,true);
        if($result['status']==1) {
            return $result['data'];
        } else {
            return false;
        }
    }

    /**
     * 获取待收旧数据(对接旧系统)
     * @return boolean|array       数据
     */
    public static function stayMoneyOld() {
        $postData = [];
        $key = 'xwsdxwsdolddata880066';
        $type = 'stayMoney';
        $userId = '';
        $postData['sign'] = md5($key.$userId.$type);
        $postData['type'] = $type;
        $postData['userId'] = $userId;
        $result = NetworkHelper::post('https://www.hcjrfw.com/xwsd_bash/olddata/index.php',$postData);
        $result = json_decode($result,true);
        if($result['status']==1) {
            return $result['data'];
        } else {
            return false;
        }
    }

    /**
     * 获取第三方数据(对接第三方平台)
     * @param  string $tradeNo      订单号
     * @param  string $type         数据类型
     * @return boolean|array       数据
     */
    public static function queryTrade($tradeNo, $type) {
        $url = Registry::get('config')->get('third')->get('base_url');
        $merchantKey = Registry::get('config')->get('third')->get('key');
        $numberId = Registry::get('config')->get('third')->get('number_id');
        $mode = $type;
        $beginTime = '';
        $endTime = '';
        $page = '';
        $url = $url.'/hostingTradeQuery';
        $signStr = 'number_id='.$numberId.'&mode='.$mode.'&out_trade_no='.$tradeNo.'&merchantKey='.$merchantKey;
        $sign = strtolower(md5($signStr));
        $data = [];
        $data['number_id'] = $numberId;
        $data['mode'] = $mode;
        $data['nick_name'] = '';
        $data['out_trade_no'] = $tradeNo;
        $data['trade_no'] = '';
        $data['begin_time'] = '';
        $data['end_time'] = '';
        $data['page_index'] = '';
        $data['sign_info'] = $sign;
        
        $result = NetworkHelper::post($url, $data);
        $xml = new \SimpleXMLElement($result);

        if(!$xml->resultCode=='00') {
            return false;
        }
        
        $trade = $xml->tradeList->tradeInfo;
        if($trade=='') {
            return false;
        }
        $rdata = [];
        foreach ($trade->children() as $item) {
            $rdata[$item->getName()] = ''.$item;
        }
        return $rdata;
    }

    /**
     * 获取第三方数据(对接第三方平台)
     * @param  string $batchNo      批次号
     * @param  string $type         数据类型
     * @return boolean|array        数据
     */
    public static function queryBatch($batchNo, $type) {
        $url = Registry::get('config')->get('third')->get('base_url');
        $merchantKey = Registry::get('config')->get('third')->get('key');
        $numberId = Registry::get('config')->get('third')->get('number_id');
        $mode = $type;
        $beginTime = '';
        $endTime = '';
        $page = '';
        $url = $url.'/hostingTradeQuery';
        $signStr = 'number_id='.$numberId.'&mode='.$mode.'&trade_no='.$batchNo.'&merchantKey='.$merchantKey;
        $sign = strtolower(md5($signStr));
        $data = [];
        $data['number_id'] = $numberId;
        $data['mode'] = $mode;
        $data['nick_name'] = '';
        $data['out_trade_no'] = '';
        $data['trade_no'] = $batchNo;
        $data['begin_time'] = '';
        $data['end_time'] = '';
        $data['page_index'] = '';
        $data['sign_info'] = $sign;
        
        $result = NetworkHelper::post($url, $data);
        $xml = new \SimpleXMLElement($result);
        
        if(!$xml->resultCode=='00') {
            return false;
        }
        
        $trade = $xml->tradeList->tradeInfo;
        if($trade=='') {
            return false;
        }
        $rdata = [];
        $bid = [];
        foreach ($trade as $item) {
            foreach ($item as $v) {
                $bid[$v->getName()] = ''.$v;
            }
            $rdata[] = $bid;
        }
        return $rdata;
    }

    /**
     * 获取用户账户余额(对接第三方平台)
     * @param  string $userId       用户ID
     * @return boolean|array        数据
     */
    public static function queryUserMoney($userId) {
        $url = Registry::get('config')->get('third')->get('base_url');
        $merchantKey = Registry::get('config')->get('third')->get('key');
        $numberId = Registry::get('config')->get('third')->get('number_id');
        $url = $url.'/hostingCheckBalance';
        $signStr = 'number_id='.$numberId.'&nick_name='.$userId.'&merchantKey='.$merchantKey;
        $sign = strtolower(md5($signStr));
        $data = [];
        $data['number_id'] = $numberId;
        $data['nick_name'] = $userId;
        $data['sign_info'] = $sign;
        
        $result = NetworkHelper::post($url, $data);
        $xml = new \SimpleXMLElement($result);
        if(!$xml->status=='00') {
            return false;
        }
        return ['balance'=>floatval($xml->balance), 'freeze'=>floatval($xml->noAvailableBalance)];
    }

    /**
     * 一麻袋冻结退回
     * @param  array  $data      数据
     * @return string            返回码
     */
    public static function frozenBack($data) {
        $url = Registry::get('config')->get('third')->get('base_url') . '/hostingTrade';
        $merchantKey = Registry::get('config')->get('third')->get('key');
        $numberId = Registry::get('config')->get('third')->get('number_id');
        
        $adviceURL = $data['adviceURL'];
        $batchNo = $data['batchNo'];
        $tradeNo = $data['tradeNo'];
        $outName = $data['outName'];
        $inName = $data['inName'];
        $money = $data['money'];
        $remark = $data['remark'];


        $secureCode = strtolower(md5($numberId.$batchNo.$tradeNo.$outName.$inName.$money.$remark.$merchantKey));

        $transData  =  "";
        $transData .= '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $transData .= '<yimadai>';
        $transData .=   '<accountNumber>'.$numberId.'</accountNumber>';//商户数字id
        $transData .=   '<tradeNo>'.$batchNo.'</tradeNo>';
        $transData .=   '<tradeType>T003</tradeType>';
        $transData .=   '<adviceURL>'.$adviceURL.'</adviceURL>';
        $transData .=   '<tranlist>';
        $transData .=       '<tran>';
        $transData .=           '<outTradeNo>'.$tradeNo.'</outTradeNo>';
        $transData .=           '<outName>'.$outName.'</outName>';
        $transData .=           '<inName>'.$inName.'</inName>';
        $transData .=           '<amount>'.$money.'</amount>';
        $transData .=           '<remark>'.$remark.'</remark>';
        $transData .=           '<secureCode>'.$secureCode.'</secureCode>';
        $transData .=       '</tran>';
        $transData .=   '</tranlist>';
        $transData .= '</yimadai>';
        
        $transData = base64_encode($transData);

        return NetworkHelper::post($url, ['transData'=>$transData]);
    }

    /**
     * 一麻袋冻结解除
     * @param  array  $data      数据
     * @return string            返回码
     */
    public static function frozenRelieve($data) {
        $url = Registry::get('config')->get('third')->get('base_url') . '/hostingTrade';
        $merchantKey = Registry::get('config')->get('third')->get('key');
        $numberId = Registry::get('config')->get('third')->get('number_id');
        
        $adviceURL = $data['adviceURL'];
        $batchNo = $data['batchNo'];
        $tradeNo = $data['tradeNo'];
        $outName = $data['outName'];
        $inName = $data['inName'];
        $money = $data['money'];
        $remark = $data['remark'];

        $secureCode = strtolower(md5($numberId.$batchNo.$tradeNo.$outName.$inName.$money.$remark.$merchantKey));

        $transData  =  "";
        $transData .= '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $transData .= '<yimadai>';
        $transData .=   '<accountNumber>'.$numberId.'</accountNumber>';//商户数字id
        $transData .=   '<tradeNo>'.$batchNo.'</tradeNo>';
        $transData .=   '<tradeType>T004</tradeType>';
        $transData .=   '<adviceURL>'.$adviceURL.'</adviceURL>';
        $transData .=   '<tranlist>';
        $transData .=       '<tran>';
        $transData .=           '<outTradeNo>'.$tradeNo.'</outTradeNo>';
        $transData .=           '<outName>'.$outName.'</outName>';
        $transData .=           '<inName>'.$inName.'</inName>';
        $transData .=           '<amount>'.$money.'</amount>';
        $transData .=           '<remark>'.$remark.'</remark>';
        $transData .=           '<secureCode>'.$secureCode.'</secureCode>';
        $transData .=       '</tran>';
        $transData .=   '</tranlist>';
        $transData .= '</yimadai>';
        
        $transData = base64_encode($transData);
        
        return NetworkHelper::post($url, ['transData'=>$transData]);
    }

    public static function post($data) {
        $postData = $data;
        $str = '';
        foreach ($postData as $key => $value) {
            if ('html' != $key)
            $str .= $value;
        }
        $token = self::getConfig('key');
        $postData['secureCode'] = strtolower(md5($str . $token));
        $xml = FileHelper::arrayToXml($postData);
        
        Log::write($xml, [], $postData['cmd']);
        $result = NetworkHelper::post(self::getConfig('url'), ['xml'=>$xml]);
        Log::write($result, [], $postData['cmd']);

        $result = json_decode($result, true);
        self::$msg = isset($result['msg'])?$result['msg']:'接口异常！';
        if($result['status']=='success') {
            return true;
        } else {
            Log::write('访问接口['.$postData['cmd'].']错误：'.self::$msg, [], 'error');
            return false;
        }
    }
}
