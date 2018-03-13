<?php
namespace tools;
use helpers\NetworkHelper;
use helpers\FileHelper;
use Yaf\Registry;
class Yemadai {
    public static $apiQueues = [
        'bid' => ['mode', 'order_no', 'total_amount', 'total_size', 'status'],
        'crtr' => ['mode', 'order_no', 'total_amount', 'total_size', 'status'],
    ];

    public static function check($params, $type) {
        if(isset(self::$apiQueues[$type])) {
            $signLinkString = '';
            $merchantKey = Registry::get('config')->get('third')->get('key');
            $numberID = Registry::get('config')->get('third')->get('number_id');
            $sign = $params['sign_info'];
            foreach (self::$apiQueues[$type] as $name) {
                $signLinkString .= $name . '=' . $params[$name] . '&';
            }
            $signLinkString = 'number_id='. $numberID . '&' . $signLinkString . 'merchantKey=' . $merchantKey;
            $computeSign = strtolower(md5($signLinkString));
            if($sign==$computeSign) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }
}
