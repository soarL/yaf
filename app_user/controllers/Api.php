<?php
use models\User;
use models\Sms;
use forms\app\RegisterForm;
use factories\RedisFactory;
use helpers\NetworkHelper;
use Yaf\Registry;
use tools\DuoZhuan;
use models\Odd;
use models\Invest;
use models\Lottery;
use tools\Captcha;
use tools\Log;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * ActivityController
 * 活动接口
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class ApiController extends Controller {

    /**
     *多赚推广
     */
    public function dzRegisterAction() {
        date_default_timezone_set('Asia/Shanghai');
        $params = $this->getAllPost();
        $params['pm_key'] = 'duozhuan201706';
        $params['isCheckSms'] = 0;
        $phone = isset($params['phone'])?$params['phone']:'';
        $params['username'] = $phone;
        $params['smsCode'] = '123456';
        $params['password'] = '123456';
        $form = new RegisterForm($params);
        if($form->register()) {
            $channel = isset($params['channel'])?$params['channel']:'';
            $result = DuoZhuan::actReg($phone, 2, $channel);
            $result = json_decode($result, true);

            $redis = RedisFactory::create();
            $redis->sAdd('dz_act_users', $phone);
            
            Sms::dxOne('如何激活红包？进入汇诚普惠官网（www.hcjrfw.com）→登录账号密码→我的账户→领取红包', $phone);

            $rdata['status'] = 1;
            $rdata['msg'] = '注册成功';
            $rdata['data']['send'] = $result;



            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = $form->posError();

            $this->backJson($rdata);
        }
    }

    public function dzSmsAction() {
        $params = $this->getAllPost();

        $rdata = [];
        $count = User::whereRaw('phone=? or username=?', [$params['phone'], $params['phone']])->count();
        if($count>0) {
            $rdata['status'] = 0;
            $rdata['msg'] = '手机号已经注册！';
            $this->backJson($rdata);
        }

        $data = [];
        $data['userId'] = '';
        $data['phone'] = $params['phone'];
        $data['msgType'] = $params['msgType'];
        $data['code'] = Sms::generateCode(Sms::CODE_LENGTH);
        $data['params'] = [$data['code'],Sms::$msg[$data['msgType']],15];
        $result = Sms::send($data);

        if($result['status']==1) {
            $rdata['status'] = 1;
            $rdata['msg'] = '发送成功';
            $rdata['data']['code'] = '';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = $result['info'];
            $this->backJson($rdata);
        }
    }

    public function dzClickAction() {
        $channel = $this->getQuery('channel', '');
        $from = $this->getQuery('from', 1);
        $phone = $this->getQuery('phone', '');

        $redis = RedisFactory::create();
        $redis->incr('dz_act_click');

        setcookie('dz_channel', $channel, time()+3600, '/', WEB_DOMAIN);

        $result = DuoZhuan::actClick($from, $channel, $phone);
        $result = json_decode($result, true);

        $rdata['status'] = 1;
        $rdata['msg'] = '访问成功！';
        $rdata['data']['send'] = $result;
        $this->backJson($rdata);
    }

    public function dzViewAction() {
        $from = $this->getQuery('from', 1);

        $siteinfo = Registry::get('siteinfo');

        $redis = RedisFactory::create();
        $redis->lpush('dz_act_view', json_encode(['time'=>date('Y-m-d H:i:s'), 'ip'=>$siteinfo['clientIp'], 'from'=>$from]));

        $rdata['status'] = 1;
        $rdata['msg'] = '访问成功！';
        $this->backJson($rdata);
    }

    /**
     *七六推广
     */
    public function qlRegisterAction() {
        $params = $this->getAllPost();
        $params['pm_key'] = 'qiliu201706';
        $params['isCheckSms'] = 0;
        $phone = isset($params['phone'])?$params['phone']:'';
        $params['username'] = $phone;
        $params['smsCode'] = '123456';
        $params['password'] = '123456';
        $form = new RegisterForm($params);
        if($form->register()) {

            $redis = RedisFactory::create();
            $redis->sAdd('ql_act_users', $phone);
            
            Sms::dxOne('如何激活红包？进入汇诚普惠官网（www.hcjrfw.com）→登录账号密码→我的账户→领取红包', $phone);

            $rdata['status'] = 1;
            $rdata['msg'] = '注册成功';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = $form->posError();
            $this->backJson($rdata);
        }
    }

    public function qlSmsAction() {
        $params = $this->getAllPost();

        $rdata = [];
        $count = User::whereRaw('phone=? or username=?', [$params['phone'], $params['phone']])->count();
        if($count>0) {
            $rdata['status'] = 0;
            $rdata['msg'] = '手机号已经注册！';
            $this->backJson($rdata);
        }

        $data = [];
        $data['userId'] = '';
        $data['phone'] = $params['phone'];
        $data['msgType'] = $params['msgType'];
        $data['code'] = Sms::generateCode(Sms::CODE_LENGTH);
        $data['params'] = [$data['code'],Sms::$msg[$data['msgType']],15];
        $result = Sms::send($data);

        if($result['status']==1) {
            $rdata['status'] = 1;
            $rdata['msg'] = '发送成功';
            $rdata['data']['code'] = '';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = $result['info'];
            $this->backJson($rdata);
        }
    }

    public function qlClickAction() {
        $from = $this->getQuery('from', 1);
        $phone = $this->getQuery('phone', '');

        $redis = RedisFactory::create();
        $redis->incr('ql_act_click');

        $rdata['status'] = 1;
        $rdata['msg'] = '访问成功！';
        $rdata['data']['send'] = $result;
        $this->backJson($rdata);
    }

    public function qlViewAction() {
        $from = $this->getQuery('from', 1);

        $siteinfo = Registry::get('siteinfo');

        $redis = RedisFactory::create();
        $redis->lpush('ql_act_view', json_encode(['time'=>date('Y-m-d H:i:s'), 'ip'=>$siteinfo['clientIp'], 'from'=>$from]));

        $rdata['status'] = 1;
        $rdata['msg'] = '访问成功！';
        $this->backJson($rdata);
    }


    /**
     * 魔方推广
     */
    public function mfRegisterAction() {
        $params = $this->getAllPost();
        $params['pm_key'] = 'mofang201706';
        $params['isCheckSms'] = 0;
        $phone = isset($params['phone'])?$params['phone']:'';
        $params['username'] = $phone;
        $params['smsCode'] = '123456';
        $params['password'] = '123456';
        $form = new RegisterForm($params);
        if($form->register()) {

            $redis = RedisFactory::create();
            $redis->sAdd('mf_act_users', $phone);

            Sms::dxOne('如何激活红包？进入汇诚普惠官网（www.hcjrfw.com）→登录账号密码→我的账户→领取红包', $phone);

            $rdata['status'] = 1;
            $rdata['msg'] = '注册成功';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = $form->posError();
            $this->backJson($rdata);
        }
    }

    public function mfSmsAction() {
        $params = $this->getAllPost();

        $rdata = [];
        $count = User::whereRaw('phone=? or username=?', [$params['phone'], $params['phone']])->count();
        if($count>0) {
            $rdata['status'] = 0;
            $rdata['msg'] = '手机号已经注册！';
            $this->backJson($rdata);
        }

        $data = [];
        $data['userId'] = '';
        $data['phone'] = $params['phone'];
        $data['msgType'] = $params['msgType'];
        $data['code'] = Sms::generateCode(Sms::CODE_LENGTH);
        $data['params'] = [$data['code'],Sms::$msg[$data['msgType']],15];
        $result = Sms::send($data);

        if($result['status']==1) {
            $rdata['status'] = 1;
            $rdata['msg'] = '发送成功';
            $rdata['data']['code'] = '';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = $result['info'];
            $this->backJson($rdata);
        }
    }

    public function mfClickAction() {
        $from = $this->getQuery('from', 1);
        $phone = $this->getQuery('phone', '');

        $redis = RedisFactory::create();
        $redis->incr('mf_act_click');

        $rdata['status'] = 1;
        $rdata['msg'] = '访问成功！';
        $rdata['data']['send'] = $result;
        $this->backJson($rdata);
    }

    public function mfViewAction() {
        $from = $this->getQuery('from', 1);

        $siteinfo = Registry::get('siteinfo');

        $redis = RedisFactory::create();
        $redis->lpush('mf_act_view', json_encode(['time'=>date('Y-m-d H:i:s'), 'ip'=>$siteinfo['clientIp'], 'from'=>$from]));

        $rdata['status'] = 1;
        $rdata['msg'] = '访问成功！';
        $this->backJson($rdata);
    }

    /**
     *多赚快速注册
     */
    public function dzFastRegisterAction()
    {
        date_default_timezone_set('Asia/Shanghai');
        $data = $this->getAllPost();
        if($data['method'] == 'phone_fast_registered'){
            $key = "DUOZHUANAPI";
            $crypt = new CookieCrypt($key);
            $phone = $crypt->decrypt($data['request']['phone']);
            $params['phone'] = $phone;
            $params['pm_key'] = 'duozhuan201707';
            $params['username'] = $phone;
            $params['isCheckSms'] = 0;
            $params['smsCode'] = '123456';
            $params['password'] = '123456';
            $form = new RegisterForm($params);
            if($form->register()) {
                Sms::dxOne('如何激活红包？进入汇诚普惠官网（www.hcjrfw.com）→登录账号密码→我的账户→领取红包', $phone);
                $rdata['status'] = 1;
                $rdata['info'] = '注册成功';
                $rdata['data'] = '';
                $this->backJson($rdata);
            } else {
                $rdata['status'] = 0;
                $rdata['info'] = $form->posError();
                $rdata['data'] = '';
                $this->backJson($rdata);
            }
        }

    }

    /**
     *头条推广
     */
    public function toutiaoViewAction() {
        $from = $this->getQuery('from', 1);

        $siteinfo = Registry::get('siteinfo');

        $redis = RedisFactory::create();
        $redis->lpush('tou_act_view', json_encode(['time'=>date('Y-m-d H:i:s'), 'ip'=>$siteinfo['clientIp'], 'from'=>$from]));

        $rdata['status'] = 1;
        $rdata['msg'] = '访问成功！';
        $rdata['data']['safeDay'] = intval((time()-strtotime('2015-01-09 00:00:00'))/(24*60*60));
        $rdata['data']['totalVolume'] = 1031210590 + intval(Odd::getTotalVolume());
        $rdata['data']['allInterest'] = 32131082 + Invest::whereIn('status', [1, 3, 4])->sum('interest');
        $this->backJson($rdata);
    }

    public function toutiaoRegisterAction() {
        $params = $this->getAllPost();
        $params['pm_key'] = 'toutiao201707';
        $params['isCheckSms'] = 0;
        $phone = isset($params['phone'])?$params['phone']:'';
        $params['username'] = $phone;
        $params['smsCode'] = '123456';
        $params['password'] = '123456';
        $form = new RegisterForm($params);
        if($form->register()) {
            Sms::dxOne('如何激活红包？进入汇诚普惠官网（www.hcjrfw.com）→登录账号密码→我的账户→领取红包', $phone);
            $rdata['status'] = 1;
            $rdata['msg'] = '注册成功';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = $form->posError();
            $this->backJson($rdata);
        }
    }

    /**
     *微信推广页面
     */
    public function weixinViewAction() {
        $from = $this->getQuery('from', 1);

        $siteinfo = Registry::get('siteinfo');

        $redis = RedisFactory::create();
        $redis->lpush('wx_act_view', json_encode(['time'=>date('Y-m-d H:i:s'), 'ip'=>$siteinfo['clientIp'], 'from'=>$from]));

        $rdata['status'] = 1;
        $rdata['msg'] = '访问成功！';
        $rdata['data']['safeDay'] = intval((time()-strtotime('2015-01-09 00:00:00'))/(24*60*60));
        $rdata['data']['totalVolume'] = 1031210590 + intval(Odd::getTotalVolume());
        $rdata['data']['allInterest'] = 32131082 + Invest::whereIn('status', [1, 3, 4])->sum('interest');
        $this->backJson($rdata);
    }

    public function weixinRegisterAction() {
        $params = $this->getAllPost();
        if(empty($params['pm_key'])){
            $params['pm_key'] = 'weixin201707';
        }
        $params['isCheckSms'] = 0;
        $phone = isset($params['phone'])?$params['phone']:'';
        $params['username'] = $phone;
        $params['smsCode'] = '123456';
        $params['password'] = '123456';
        $form = new RegisterForm($params);
        if($form->register()) {
            Sms::dxOne('如何激活红包？进入汇诚普惠官网（www.hcjrfw.com）→登录账号密码→我的账户→领取红包', $phone);
            $rdata['status'] = 1;
            $rdata['msg'] = '注册成功';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = $form->posError();
            $this->backJson($rdata);
        }
    }


    /**
     * 新手注册返380红包
     * 注册
     */
    public function newRegisterAction() {
        $params = $this->getAllPost();
        $params['pm_key'] = 'zhuce380';
        $params['isCheckSms'] = 1;
        if(Captcha::check($params['captcha'])){
            $phone = isset($params['phone'])?$params['phone']:'';
            $params['username'] = $phone;
            $form = new RegisterForm($params);
            $rdata = [];
            if($form->register()){
                $rdata['status'] = 1;
                $rdata['msg'] = '注册成功';
                $this->backJson($rdata);
            }else{
                $rdata['status'] = 0;
                $rdata['msg'] = $form->posError();
                $this->backJson($rdata);
            }
       }else{
           $rdata['status'] = 0;
           $rdata['msg'] = '验证码错误!';
           $this->backJson($rdata);
       }
    }

    /**
     * 新手注册返380红包
     * 获取验证码
     */
    public function getSmsAction() {
        $params = $this->getAllPost();
        if(Captcha::check($params['captcha'])){
            $rdata = [];
            $count = User::whereRaw('phone=? or username=?', [$params['phone'], $params['phone']])->count();
            if($count>0) {
                $rdata['status'] = 0;
                $rdata['msg'] = '手机号已经注册！';
                $this->backJson($rdata);
            }

            $data = [];
            $data['userId'] = '';
            $data['phone'] = $params['phone'];
            $data['msgType'] = 'register';
            $data['code'] = Sms::generateCode(Sms::CODE_LENGTH);
            $data['params'] = [$data['code'],Sms::$msg[$data['msgType']],15];
            $result = Sms::send($data);

            if($result['status']==1) {
                $rdata['status'] = 1;
                $rdata['msg'] = '发送成功';
                $rdata['data']['code'] = '';
                $this->backJson($rdata);
            } else {
                $rdata['status'] = 0;
                $rdata['msg'] = $result['info'];
                $this->backJson($rdata);
            }
       }else{
           $rdata['status'] = 0;
           $rdata['msg'] = '验证码错误!';
           $this->backJson($rdata);
       }
    }

    /**
     * 新手注册返380红包
     * 返回成交额
     */
    public function newRegisterViewAction() {
        $siteinfo = Registry::get('siteinfo');

        $redis = RedisFactory::create();
        $redis->lpush('380_act_view', json_encode(['time'=>date('Y-m-d H:i:s'), 'ip'=>$siteinfo['clientIp']]));

        $rdata['status'] = 1;
        $rdata['msg'] = '访问成功！';
        $rdata['data']['safeDay'] = intval((time()-strtotime('2015-01-09 00:00:00'))/(24*60*60));
        $rdata['data']['totalVolume'] = 1031210590 + intval(Odd::getTotalVolume());
        $rdata['data']['allInterest'] = 32131082 + Invest::whereIn('status', [1, 3, 4])->sum('interest');
        $this->backJson($rdata);
    }


    /**
     * 新手注册返380红包--无验证码
     * 返回成交额
     */
    public function newRegister380Action() {
        $params = $this->getAllPost();
        $params['pm_key'] = 'zhuce380-app';
        $params['isCheckSms'] = 1;
        $phone = isset($params['phone'])?$params['phone']:'';
        $params['username'] = $phone;
        $form = new RegisterForm($params);
        $rdata = [];
        if($form->register()){
            $rdata['status'] = 1;
            $rdata['msg'] = '注册成功';
            $this->backJson($rdata);
        }else{
            $rdata['status'] = 0;
            $rdata['msg'] = $form->posError();
            $this->backJson($rdata);
        }

    }

    /**
     * 新手注册返380红包--无验证码
     * 获取验证码
     */
    public function getSms380Action() {
        $params = $this->getAllPost();
        $rdata = [];
        if(!isset($params['phone'])) {
            $rdata['status'] = 0;
            $rdata['msg'] = '请输入手机号！';
            $this->backJson($rdata);
        }
        if(!preg_match('/^1\d{10}$/', $params['phone'])) {
            $rdata['status'] = 0;
            $rdata['msg'] = '手机号格式错误！';
            $this->backJson($rdata);
        }
        $redis = RedisFactory::create();
        $redis->sAdd('phone380_act_users', $params['phone']);
        $count = User::whereRaw('phone=? or username=?', [$params['phone'], $params['phone']])->count();
        if($count>0) {
            $rdata['status'] = 0;
            $rdata['msg'] = '手机号已经注册！';
            $this->backJson($rdata);
        }

        $data = [];
        $data['userId'] = '';
        $data['phone'] = $params['phone'];
        $data['msgType'] = 'register';
        $data['code'] = Sms::generateCode(Sms::CODE_LENGTH);
        $data['params'] = [$data['code'],Sms::$msg[$data['msgType']],15];
        $result = Sms::send($data);

        if($result['status']==1) {
            $rdata['status'] = 1;
            $rdata['msg'] = '发送成功';
            $rdata['data']['code'] = '';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = $result['info'];
            $this->backJson($rdata);
        }
    }

    /**
     * 新手注册返380红包——无验证码
     * 返回成交额
     */
    public function newRegisterView380Action() {
        $siteinfo = Registry::get('siteinfo');
        $redis = RedisFactory::create();
        $redis->lpush('phone380_act_view', json_encode(['time'=>date('Y-m-d H:i:s'), 'ip'=>$siteinfo['clientIp']]));
        $rdata['status'] = 1;
        $rdata['msg'] = '访问成功！';
        $rdata['data']['safeDay'] = intval((time()-strtotime('2015-01-09 00:00:00'))/(24*60*60));
        $rdata['data']['totalVolume'] = 1031210590 + intval(Odd::getTotalVolume());
        $rdata['data']['allInterest'] = 32131082 + Invest::whereIn('status', [1, 3, 4])->sum('interest');
        $this->backJson($rdata);
    }


    /**
     *罗盘贷和魔积分等的注册推广页面
     */
    public function advertiseAction() {
        $from = $this->getQuery('from', 1);
        $pm_key = $this->getQuery('pm_key');
        $siteinfo = Registry::get('siteinfo');
        if($pm_key != ''){
            $redis = RedisFactory::create();
            $redis->lpush($pm_key.'_view', json_encode(['time'=>date('Y-m-d H:i:s'), 'ip'=>$siteinfo['clientIp']]));
        }
        $rdata['status'] = 1;
        $rdata['msg'] = '访问成功！';
        $rdata['data']['safeDay'] = intval((time()-strtotime('2015-01-09 00:00:00'))/(24*60*60));
        $rdata['data']['totalVolume'] = 1031210590 + intval(Odd::getTotalVolume());
        $rdata['data']['allInterest'] = 32131082 + Invest::whereIn('status', [1, 3, 4])->sum('interest');
        $this->backJson($rdata);
    }

    /**
     *罗盘贷和魔积分等的注册推广页面
     */
    public function advertiseRegisterAction() {
        $params = $this->getAllPost();
        $redis = RedisFactory::create();
        $redis->lpush($params['pm_key'].'_register', json_encode(['time'=>date('Y-m-d H:i:s')]));
        $params['isCheckSms'] = 1;
        $phone = isset($params['phone'])?$params['phone']:'';
        $params['username'] = $phone;
        $form = new RegisterForm($params);
        if($form->register()) {
            Sms::dxOne('如何激活红包？进入汇诚普惠官网（www.hcjrfw.com）→登录账号密码→我的账户→活动优惠', $phone);
            if ($params['pm_key'] == 'duozhuanPC' || $params['pm_key'] == 'duozhuanAPP'){
                //请求多赚接口
                $config = Registry::get('config');
                $data = [];
                $ret = [];
                $data['method'] = 'dz_account_create';
                $data['partner_id'] = $config->get('duozhuan.pid');
                $data['timestamp'] = date('Y-m-d H:i:s',time());
                $data['sign'] = strtoupper(md5('method'.$data['method'].'partner_id'.$data['partner_id'].'timestamp'.$data['timestamp']));
                $ret['register_time'] = date('Y-m-d H:i:s',time());
                $ret['register_phone'] = $phone;
                $ret['register_status'] = 1;
                $data['data'] = json_encode($ret);
                $url = $config->get('duozhuan.url2').'dz_account_create';
                NetworkHelper::post($url, $data);
            }
            $rdata['status'] = 1;
            $rdata['msg'] = '注册成功';
            $this->backJson($rdata);
        } else {
            if ($params['pm_key'] == 'duozhuanPC' || $params['pm_key'] == 'duozhuanAPP') {
                //请求多赚接口
                $config = Registry::get('config');
                $data = [];
                $ret = [];
                $data['method'] = 'dz_account_create';
                $data['partner_id'] = $config->get('duozhuan.pid');
                $data['timestamp'] = date('Y-m-d H:i:s');
                $data['sign'] = strtoupper(md5('method' . $data['method'] . 'partner_id' . $data['partner_id'] . 'timestamp' . $data['timestamp']));
                $ret['register_time'] = date('Y-m-d H:i:s', time());
                $ret['register_phone'] = $phone;
                $ret['register_status'] = 0;
                $data['data'] = json_encode($ret);
                $url = $config->get('duozhuan.url2') . 'dz_account_create';
                NetworkHelper::post($url, $data);
            }
            $rdata['status'] = 0;
            $rdata['msg'] = $form->posError();
            $this->backJson($rdata);
        }
    }

    /**
     * 双节活动的注册
     */
    public function festivalRegisterAction() {
        $params = $this->getAllPost();
        $redis = RedisFactory::create();
        $redis->lpush($params['pm_key'].'_register', json_encode(['time'=>date('Y-m-d H:i:s')]));
        $params['isCheckSms'] = 1;
        $phone = isset($params['phone'])?$params['phone']:'';
        $params['username'] = $phone;
        $form = new RegisterForm($params);
        if($form->register()) {
            $rdata['status'] = 1;
            $rdata['msg'] = '注册成功';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = $form->posError();
            $this->backJson($rdata);
        }
    }

    /**
     *双节活动推广页下载点击统计
     */
    public function festivalDownAction() {
        $from = $this->getQuery('from', 1);
        $pm_key = $this->getPost('pm_key');
        $siteinfo = Registry::get('siteinfo');
        if($pm_key != ''){
            $redis = RedisFactory::create();
            $redis->lpush($pm_key.'_view_download', json_encode(['time'=>date('Y-m-d H:i:s'), 'ip'=>$siteinfo['clientIp']]));
        }
        $rdata['status'] = 1;
        $rdata['msg'] = 'https://app.hcjrfw.com/v2page/download.html';
        $this->backJson($rdata);
    }

    /**
     * 双节活动
     * 获取验证码
     */
    public function getSmsFestivalAction() {
        $params = $this->getAllPost();
        $rdata = [];
        $count = User::whereRaw('phone=? or username=?', [$params['phone'], $params['phone']])->count();
        if($count>0) {
            $rdata['status'] = 0;
            $rdata['msg'] = '手机号已经注册！';
            $this->backJson($rdata);
        }

        $data = [];
        $data['userId'] = '';
        $data['phone'] = $params['phone'];
        $data['msgType'] = 'register';
        $data['code'] = Sms::generateCode(Sms::CODE_LENGTH);
        $data['params'] = [$data['code'],Sms::$msg[$data['msgType']],15];
        $result = Sms::send($data);

        if($result['status']==1) {
            $rdata['status'] = 1;
            $rdata['msg'] = '发送成功';
            $rdata['data']['code'] = '';
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = $result['info'];
            $this->backJson($rdata);
        }
    }


}





class CookieCrypt
{
    var $key;
    function CookieCrypt($key)
    {
        $this->key = $key;
    }
    function encrypt($input)
    {
        $size = mcrypt_get_block_size('des','ecb');
        $input = $this->pkcs5_pad($input, $size);
        $key = $this->key;
        $td = mcrypt_module_open('des', '', 'ecb', '');
        $iv = @mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        @mcrypt_generic_init($td, $key, $iv);
        $data = mcrypt_generic($td, $input);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $data = base64_encode($data);
        return $data;
    }
    function decrypt($encrypted)
    {
        $encrypted = base64_decode($encrypted);
        $key =$this->key;
        $td = mcrypt_module_open('des','','ecb','');
        //使用MCRYPT_DES算法,cbc模式
        $iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        $ks = mcrypt_enc_get_key_size($td);
        @mcrypt_generic_init($td, $key, $iv);
        //初始处理
        $decrypted = @mdecrypt_generic($td, $encrypted);
        //解密
        mcrypt_generic_deinit($td);
        //结束
        mcrypt_module_close($td);
        $y=$this->pkcs5_unpad($decrypted);
        return $y;
    }
    function pkcs5_pad ($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }
    function pkcs5_unpad($text)
    {
        $pad = ord($text{strlen($text)-1});
        if ($pad > strlen($text))
        {
            return false;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad)
        {
            return false;
        }
        return substr($text, 0, -1 * $pad);
    }
}

