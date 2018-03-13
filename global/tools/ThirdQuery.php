<?php
namespace tools;
use helpers\NetworkHelper;
use helpers\FileHelper;
use helpers\StringHelper;
use Yaf\Registry;
class ThirdQuery {
    
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
	public static function baofooRecharge($tradeNo) {
        $terminal_id = Registry::get('config')->get('baofoo')->get('wid');
        $member_id = Registry::get('config')->get('baofoo')->get('member_id');
        $baseUrl = Registry::get('config')->get('baofoo')->get('url');
        $password = Registry::get('config')->get('baofoo')->get('xwsd_key_pw');
        $bfKey = Registry::get('config')->get('baofoo')->get('key');
        /*$url = $baseUrl .'/apipay/queryQuickOrder';
        $serialNo = date('Ymd').substr(md5(microtime().rand(100, 999)), 8, 16);
		$contents = [
            'orig_trans_id' => $tradeNo,
            'trans_serial_no' => $serialNo,
            'terminal_id' => $terminal_id,
            'member_id' => $member_id
        ];
        $privateKey = BFBank::getKey('private', 'xwsd');
        $content = StringHelper::bfSign(json_encode($contents), $privateKey, $password);
        $params = [
            'version' => '4.0.0.0',
            'input_charset' => 1,
            'language' => 1,
            'terminal_id' => $terminal_id,
            'member_id' => $member_id,
            'data_type' => 'json',
            'data_content' => $content
        ];*/
        $url = $baseUrl .'/order/query';
        $params = [];
        $params['MemberID'] = $member_id;
        $params['TerminalID'] = $terminal_id;
        $params['TransID'] = $tradeNo;
        $params['MD5Sign'] = md5($member_id.'|'.$terminal_id.'|'.$tradeNo.'|'.$bfKey);
        
        var_dump($params);
        $result = NetworkHelper::post($url, $params);
        var_dump($result);
        /*$params = $this->getAllPost(true);
        $content = $params['data_content'];
        $publicKey = BFBank::getKey('public', 'bf');
        $dataStr = StringHelper::bfVerify($content, $publicKey);

        Log::write('宝付:'.$dataStr, 'recharge');
        $results = json_decode($dataStr, true);*/
	}
}
