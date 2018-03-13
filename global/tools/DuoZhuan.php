<?php
namespace tools;
use Yaf\Registry;
use models\User;
use models\OddMoney;
use helpers\NetworkHelper;
use tools\Log;

class DuoZhuan {
    const RETURN_URL = '__returnUrl__';
    const KEY = 'xdqbt4B7gmTAPO7vHEOJPjxhf4zqOnSL';
    const DATA_COOKIE = 'dz_data';

    public static function before($request) {
        $session = Registry::get('session');
        $callback = $request->getQuery('callback_uri', false);
        if($callback) {
            $key = $request->getQuery('key', '');
            $secret = $request->getQuery('secret', '');
            $nonce = $request->getQuery('nonce', '');
            if(self::check($key, $nonce, $secret)) {
                if(User::isLogin()) {
                    $user = Registry::get('user');
                    $callback = $callback . '&key='.$key.'&nonce='.$nonce.'&secret='.$authSecret.'&authorized=true&user='.md5($user->userId);
                    $session->set(self::RETURN_URL, $callback);
                } else {
                    self::store($key, $nonce, $secret, $callback);
                }
            }
        }
    }

    public static function login($user) {
        $session = Registry::get('session');
        $data = isset($_COOKIE[self::DATA_COOKIE])?$_COOKIE[self::DATA_COOKIE]:false;
        if($data) {
            $data = json_decode($data, true);
            $key = isset($data['key'])?$data['key']:'';
            $nonce = isset($data['nonce'])?$data['nonce']:'';
            $secret = isset($data['secret'])?$data['secret']:'';
            $callback = isset($data['callback'])?$data['callback']:'';

            if(self::check($key, $nonce, $secret)) {
                $callback = $callback . '&key='.$key.'&nonce='.$nonce.'&secret='.$secret.'&authorized=true&user='.md5($user->userId);
                $session->set(self::RETURN_URL, $callback);
            }
            setcookie(self::DATA_COOKIE, null, time()-1,  '/', WEB_DOMAIN);
        }
    }

    public static function check($key, $nonce, $secret) {
        $date = date('YMDH');
        $authSecret = md5(self::KEY . $date . $nonce);
        if($key!=self::KEY) {
            return false;
        }
        if(!$nonce) {
            return false;
        }
        if($secret!=$authSecret) {
            return false;
        }
        return true;
    }

    public static function store($key, $nonce, $secret, $callback) {
        $date = date('YMDH');
        $dzData = [];
        $dzData['key'] = $key;
        $dzData['secret'] = $secret;
        $dzData['nonce'] = $nonce;
        $dzData['date'] = $date;
        $dzData['callback'] = $callback;
        $dzString = json_encode($dzData);
        setcookie(self::DATA_COOKIE, $dzString, time()+3600, '/', WEB_DOMAIN);
    }

    public static function actReg($phone, $from, $channel='') {
        $config = Registry::get('config');
        $siteinfo = Registry::get('siteinfo');
        $data = [];
        $data['method'] = 'post_registered_success';
        $data['partner_id'] = $config->get('duozhuan.pid');
        $data['request_time'] = date('Y-m-d H:i:s');
        $data['sign'] = md5($data['request_time'].$data['partner_id'].$data['method'].'duozhuan_api');
        $data['request']['user_name'] = '';
        $data['request']['user_phone'] = $phone;
        $data['request']['user_ip'] = $siteinfo['clientIp'];
        $data['request']['user_time'] = date('Y-m-d H:i:s');
        $data['request']['channel'] = $channel;
        $data['request']['from'] = $from;
        $url = $config->get('duozhuan.url').'post_registered_success';
        Log::write('reg:'.http_build_query($data), $data, 'duozhuan_send');
        return NetworkHelper::post($url, http_build_query($data));
    }

    public static function actClick($from, $channel='', $phone='') {
        $config = Registry::get('config');
        $data = [];
        $data['method'] = 'post_registered_click';
        $data['partner_id'] = $config->get('duozhuan.pid');
        $data['request_time'] = date('Y-m-d H:i:s');
        $data['sign'] = md5($data['request_time'].$data['partner_id'].$data['method'].'duozhuan_api');
        $data['request']['click'] = 1;
        $data['request']['channel'] = $channel;
        $data['request']['from'] = $from;
        $data['request']['phone'] = $phone;
        $url = $config->get('duozhuan.url').'post_registered_click';
        Log::write('click:'.http_build_query($data), $data, 'duozhuan_send');
        return NetworkHelper::post($url, http_build_query($data));
    }

    public static function actTenders($begin, $end) {
        $list = OddMoney::with(['user'=>function($q) {
                $q->select('userId', 'username', 'phone', 'channel_id');
            }])->whereHas('user', function($q) {
                $q->where('channel_id', 2);
            })->where('time', '>=', $begin)
            ->where('time', '<=', $end)
            ->where('type', 'invest')
            ->get();

        $rows = [];
        foreach ($list as $item) {
            $row = [];
            $row['user_name'] = $item->user->username;
            $row['user_phone'] = $item->user->phone;
            $row['investment_money'] = $item->money;
            $row['investment_time'] = $item->time;
            $rows[] = $row;
        }

        if(count($rows)==0) {
            return json_encode(['info'=>'无数据', 'status'=>0]);
        }

        $config = Registry::get('config');
        $data = [];
        $data['method'] = 'post_investment_details';
        $data['partner_id'] = $config->get('duozhuan.pid');
        $data['request_time'] = date('Y-m-d H:i:s');
        $data['sign'] = md5($data['request_time'].$data['partner_id'].$data['method'].'duozhuan_api');
        $data['request'] = $rows;
        $url = $config->get('duozhuan.url').'post_investment_details';
        return NetworkHelper::post($url, http_build_query($data));
    }


    public static function userTenderInfo($begin, $end)
    {
        date_default_timezone_set('Asia/Shanghai');
        $list = OddMoney::with(['user'=>function($q) {
            $q->select('userId', 'phone', 'channelCode');
        }],['odd'=>function($q){
            $q->select('oddNumber','oddBorrowPeriod');
        }])->whereHas('user', function($q) {
            $q->where('channelCode', 'duozhuanPC')->orWhere('channelCode','duozhuanAPP');
        })->where('time', '>=', $begin)
            ->where('time', '<', $end)
            ->where('type', 'invest')
            ->orderBy('time','desc')
            ->get();
        $channelCodePC = 'duozhuanPC';
        $channelCodeAPP = 'duozhuanAPP';
        $firstTimeId = OddMoney::whereHas('user',function($query) use($channelCodePC,$channelCodeAPP){
            $query->where('channelCode',$channelCodePC)->orWhere('channelCode',$channelCodeAPP);
        })->where('type','invest')->groupBy('userId')->lists('id')->toArray();

        $rows = [];
        foreach ($list as $item) {
            $row = [];
            $row['invest_phone'] = $item->user->phone;
            $row['invest_money'] = $item->money;
            $row['invest_time'] = $item->time;
            $row['invest_deadline'] = $item->odd->oddBorrowPeriod.'月标';
            if (in_array($item->id,$firstTimeId)){
                $row['is_first'] = 1;
            }else{
                $row['is_first'] = 0;
            }
            $row['invest_id'] = $item->id;
            $rows[] = $row;
        }

        if(count($rows)==0) {
            return json_encode(['info'=>'无数据', 'status'=>0]);
        }

        $config = Registry::get('config');
        $data = [];
        $data['method'] = 'dz_invest_list';
        $data['partner_id'] = $config->get('duozhuan.pid');
        $data['timestamp'] = date('Y-m-d H:i:s',time());
        $data['sign'] = strtoupper(md5('method'.$data['method'].'partner_id'.$data['partner_id'].'timestamp'.$data['timestamp']));
        $data['data'] = json_encode($rows);
        $url = $config->get('duozhuan.url2').'dz_invest_list';
        return NetworkHelper::post($url, $data);

    }

}


