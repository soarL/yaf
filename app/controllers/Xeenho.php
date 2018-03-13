<?php

use forms\XhztRegisterForm;
use helpers\NetworkHelper;
use Illuminate\Database\Capsule\Manager as DB;
use models\Invest;
use models\Odd;
use models\OddMoney;
use models\User;
use models\Xinghuo;
use third\xeenho;

/**
 * 对接星火智投的接口
 */
class XeenhoController extends Controller
{
    private $key = 'xwsd_yhxhAjh0OPz';
    private $iv = 'xwsd_yhxhAjh0OPz';
    private $c_code = 'xwsd_xhzt';


    private function decrypt_aes($data)
    {
        return xeenho::decrypt_aes($data, $this->key, $this->iv);
    }

    
    /**
     *注册绑定查询接口
     */
    public function registerQueryAction()
    {
        $params = $this->getAllPost();
        $serial_num = $params['serial_num'];
        $t_code = $this->decrypt_aes($params['t_code']);
        $phone = $this->decrypt_aes($params['mobile']);
        $sign = xeenho::sh256($serial_num.$this->c_code.$t_code.$this->key);
        $rdata = [];
        if($sign == $params['sign']){
            $userInfo = User::where('phone',$phone)->first();
            if ($userInfo){
                switch ($userInfo['channel_id']){
                    case 0:
                        $status = '1001';
                        $errorMsg= '已注册，未绑定任何渠道（含星火智投）';
                        break;
                    case 83:
                        $status = '1002';
                        $errorMsg= '已注册， 已绑定星火智投的引流用户';
                        break;
                    default:
                        $status = '1004';
                        $errorMsg= '已注册，其他渠道用户';
                }
                if($userInfo['cardstatus'] == 'y'){
                    $isRealNameAuthentic = 'true';
                }else{
                    $isRealNameAuthentic = 'false';
                }
                $token = Xinghuo::findToken($phone);

                $rdata['result'] = $status;
                $rdata['serial_num'] = $serial_num;
                $rdata['mobile'] = $params['mobile'];
                $rdata['register_token'] = xeenho::encrypt_aes($token,$this->key,$this->iv);
                $rdata['platform_uid'] = xeenho::encrypt_aes($userInfo['userId'],$this->key,$this->iv);
                $rdata['isRealNameAuthentic'] = $isRealNameAuthentic;
                $rdata['err_msg'] = $errorMsg;
                $this->backJson($rdata);
            } else{
                $rdata['result'] = 1000;
                $rdata['serial_num'] = $serial_num;
                $rdata['mobile'] = $params['mobile'];
                $rdata['register_token'] = '';
                $rdata['platform_uid'] = '';
                $rdata['isRealNameAuthentic'] = '';
                $rdata['err_msg'] = '用户未注册';
                $this->backJson($rdata);
            }
        }else{
            $rdata['result'] = 0001;
            $rdata['serial_num'] = $serial_num;
            $rdata['mobile'] = $params['mobile'];
            $rdata['register_token'] = '';
            $rdata['platform_uid'] = '';
            $rdata['isRealNameAuthentic'] = '';
            $rdata['err_msg'] = '验证未通过';
            $this->backJson($rdata);
        }

    }

    /**
     *注册接口
     */
    public function registerAction()
    {
        $params = $this->getAllPost();
        $serial_num = $params['serial_num'];
        $t_code = $this->decrypt_aes($params['t_code']);
        $phone = $this->decrypt_aes($params['mobile']);
        $sign = xeenho::sh256($serial_num.$this->c_code.$t_code.$this->key);
        $rdata = [];
        if($sign == $params['sign']) {
            $data['user_id'] = $this->decrypt_aes($params['user_id']);
            $data['user_name'] = $this->decrypt_aes($params['user_name']);
            $data['user_identity'] = $this->decrypt_aes($params['user_identity']);
            $data['channel_id'] = 83;
            $data['username'] = $phone;
            $data['phone'] = $phone;
            $data['isCheckSms'] = 0;
            $data['smsCode'] = '123456';
            $data['password'] = '123456';
            $data['token'] = md5(strtotime(time()).$params['mobile']);
            $form = new XhztRegisterForm($data);
            $rdata = [];
            if ($form->register()) {
                $rdata['result'] = 0;
                $rdata['serial_num'] = $serial_num;
                $rdata['mobile'] = $params['mobile'];
                $rdata['user_id'] = $params['user_id'];
                $rdata['RealNameAuthenticResult'] = '1';
                $rdata['platform_uid'] = xeenho::encrypt_aes($form->user->userId,$this->key,$this->iv);
                $rdata['register_token'] = xeenho::encrypt_aes($data['token'],$this->key,$this->iv);
                $this->backJson($rdata);
            } else {
                $rdata['result'] = 2001;
                $rdata['serial_num'] = $serial_num;
                $rdata['mobile'] = $params['mobile'];
                $rdata['user_id'] = $params['user_id'];
                $rdata['RealNameAuthenticResult'] = '1';
                $rdata['err_msg'] = $form->posError();
                $this->backJson($rdata);
            }

        }else{
            $rdata['result'] = 0001;
            $rdata['serial_num'] = $serial_num;
            $rdata['mobile'] = $params['mobile'];
            $rdata['register_token'] = '';
            $rdata['platform_uid'] = '';
            $rdata['isRealNameAuthentic'] = '';
            $rdata['err_msg'] = '验证未通过';
            $this->backJson($rdata);
        }
    }

    public function bindLoginAction()
    {
        $params = $this->getAllPost();
        $this->display('login',['data'=>$params]);
    }
    



    /**
     *对接平台登录绑定接口
     */
    public function bindAction()
    {
        $params = $this->getAllPost();
        $username = $params['username'];
        $password = $params['password'];
        $result = User::loginNormal($username, $password, false);

        if($result['status']==1) {
            $serial_num = $params['serial_num'];
            $t_code = $this->decrypt_aes($params['t_code']);
            $phone = $this->decrypt_aes($params['mobile']);
            $user_name = $this->decrypt_aes($params['user_name']);
            $user_identity = $this->decrypt_aes($params['user_identity']);
            $source = $params['source'];
            $url = $params['bid_url'];
            $sign = xeenho::sh256($serial_num.$this->c_code.$t_code.$this->key);
            if ($sign == $params['sign']){
                $userId = User::where('username',$username)->orWhere('phone',$username)->value('userId');
                $user_id = $this->decrypt_aes($params['user_id']);
                if($userId){
                    $token = md5(time().$params['mobile']);
                    $user = Xinghuo::where('user_id',$user_id)->first();
                    if($user){
                        $rdata['status'] = 0;
                        $rdata['info'] = '该用户已经绑定过了!';
                        $this->backJson($rdata);
                    }else{
                        $resultBind = Xinghuo::bind($userId,$user_id,$token,$phone,$user_name,$user_identity,$source);
                    }
                }
                if (!empty($resultBind)){
                    $postData=[];
                    $postData['result'] = 0;
                    $postData['c_code'] = $this->c_code;
                    $postData['t_code'] = $params['t_code'];
                    $postData['serial_num'] = $params['serial_num'];
                    $postData['mobile'] = $params['mobile'];
                    $postData['user_id'] = $params['user_id'];
                    $postData['platform_uid'] = $params['platform_uid'];
                    $postData['RealNameAuthenticResult'] = 1;
                    $isInvest = OddMoney::where('userId',$userId)->where('type','invest')->where('status',1)->get();
                    if ($isInvest){
                        $postData['isInvested'] = true;
                    } else {
                        $postData['isInvested'] = false;
                    }
                    $channel_id = User::where('userId',$userId)->value('channel_id');
                    if ($channel_id == 83){
                        $postData['isXeenhoChannel'] = true;
                    } else {
                        $postData['isXeenhoChannel'] = true;
                    }
                    $token = Xinghuo::findToken($userId);
                    $postData['register_token'] = xeenho::encrypt_aes($token,$this->key,$this->iv);
                    NetworkHelper::post('http://www.xeenho.cc/openapi/user_bind/notify/',$postData);
                    $rdata['status'] = 1;
                    $rdata['url'] = $url;
                    $rdata['info'] = '绑定成功!';
                    $this->backJson($rdata);
                }else{
                    $postData=[];
                    $postData['result'] = 3001;
                    $postData['c_code'] = $this->c_code;
                    $postData['t_code'] = $params['t_code'];
                    $postData['serial_num'] = $params['serial_num'];
                    $postData['mobile'] = $params['mobile'];
                    $postData['user_id'] = $params['user_id'];
                    $postData['platform_uid'] = $params['platform_uid'];
                    $postData['RealNameAuthenticResult'] = 1;
                    $isInvest = OddMoney::where('user',$userId)->get();
                    if ($isInvest){
                        $postData['isInvested'] = true;
                    } else {
                        $postData['isInvested'] = false;
                    }
                    $channel_id = User::where('userId',$userId)->value('channel_id');
                    if ($channel_id == 83){
                        $postData['isXeenhoChannel'] = true;
                    } else {
                        $postData['isXeenhoChannel'] = true;
                    }
                    $postData['register_token'] = '';
                    $postData['err_msg'] = '绑定失败';
		            NetworkHelper::Post('http://www.xeenho.cc/openapi/user_bind/notify/',$postData);
                    $rdata['status'] = 0;
                    $rdata['info'] = '绑定失败!';
                    $this->backJson($rdata);
                }
            }else{
                $rdata['result'] = 0001;
                $rdata['serial_num'] = $serial_num;
                $rdata['mobile'] = $params['mobile'];
                $rdata['register_token'] = '';
                $rdata['platform_uid'] = '';
                $rdata['isRealNameAuthentic'] = '';
                $rdata['err_msg'] = '验证未通过';
                $this->backJson($rdata);
            }

        } else {
            $rdata['status'] = 0;
            $rdata['info'] = $result['info'];
            $this->backJson($rdata);
        }

        
    }


    /**
     *获取login_token
     */
    public function fetchTokenAction()
    {
        $params = $this->getAllPost();
        $serial_num = $params['serial_num'];
        $t_code = $this->decrypt_aes($params['t_code']);
        $phone = $this->decrypt_aes($params['mobile']);
        $sign = xeenho::sh256($serial_num.$this->c_code.$t_code.$this->key);
        $rdata = [];
        if ($sign == $params['sign']) {
            $rdata['result'] = 0;
            $rdata['serial_num'] = $serial_num;
            $rdata['login_token'] = xeenho::encrypt_aes(md5($phone).time(),$this->key,$this->iv);
            $this->backJson($rdata);
        } else {
            $rdata['result'] = 0001;
            $rdata['serial_num'] = $serial_num;
            $rdata['err_msg'] = '验证未通过';
            $this->backJson($rdata);
        }
    }

    /**
     *登录接口
     */
    public function loginAction()
    {
        $params = $this->getAllPost();
        $serial_num = $params['serial_num'];
        $t_code = $this->decrypt_aes($params['t_code']);
        $phone = $this->decrypt_aes($params['mobile']);
        $sign = xeenho::sh256($serial_num.$this->c_code.$t_code.$this->key);
        $rdata = [];
        if ($sign == $params['sign']) {
            $loginToken = $this->decrypt_aes($params['login_token']);
            $time = str_replace(md5($phone),'',$loginToken);
            if ((time()-$time) <= 300 ) {
                $xinghuo_userId = $this->decrypt_aes($params['platform_uid']);
                $user_id = $this->decrypt_aes($params['user_id']);
                $source = $params['source'];
                $bid_url = $params['bid_url'];
                $user = Xinghuo::findOne($user_id,$xinghuo_userId);
                if ($user){
                    $userOne = User::where('userId',$xinghuo_userId)->first();
                    User::doLogin($userOne);
                    $this->redirect($bid_url ? $bid_url : WEB_MAIN);
                }

            }else{
                $rdata['result'] = 0001;
                $rdata['serial_num'] = $serial_num;
                $rdata['err_msg'] = 'token已失效！';
                $this->backJson($rdata);
            }

        } else {
            $rdata['result'] = 0001;
            $rdata['serial_num'] = $serial_num;
            $rdata['err_msg'] = '验证未通过';
            $this->backJson($rdata);
        }
        
    }

    /**
     *用户投资记录查询接口
     */
    public function queryUserInfoAction()
    {
        set_time_limit(0);
        $params = $this->getAllPost();
        $serial_num = $params['serial_num'];
        $t_code = $this->decrypt_aes($params['t_code']);
        $sign = xeenho::sh256($serial_num.$this->c_code.$t_code.$this->key);
        $rdata = [];
        if ($sign == $params['sign']) {
            $params['platform_uid'] = explode(',',$params['platform_uid']);
            $start_time = $params['start_time'];
            $end_time = $params['end_time'];
            if(count($params['platform_uid']) == 1 && !empty($params['platform_uid'][0])){
                $userId = $this->decrypt_aes($params['platform_uid'][0]);
                $user = Xinghuo::where('userId',$userId)->get();
                if($user){
                    $result = OddMoney::with(['odd'=>function($q){ $q->select('oddNumber', 'oddReward', 'oddYearRate','oddRehearTime','oddBorrowPeriod', 'progress');}])
                        ->where('userId', $userId)->where('status', 1)->where('type', 'invest')->where('time', '<', $end_time)->where('time', '>', $start_time)->get();
                }
                $userId =array($userId);
            }elseif (count($params['platform_uid']) > 1){
                foreach ($params['platform_uid'] as $i => $list){
                    $userId[$i] = $this->decrypt_aes($list);
                }
                $userCount = Xinghuo::whereIn('userId',$userId)->get()->count();
                if($userCount == count($userId)) {
                    $result = OddMoney::with(['odd'=>function($q){ $q->select('oddNumber', 'oddReward', 'oddYearRate','oddRehearTime','oddBorrowPeriod', 'progress');}])
                        ->whereIn('userId', $userId)->where('status', 1)->where('type', 'invest')->where('time', '<', $end_time)->where('time', '>', $start_time)->get();
                }
            }else{
                $userId = Xinghuo::getUserId();
                $result = OddMoney::with(['odd'=>function($q){ $q->select('oddNumber', 'oddReward', 'oddYearRate','oddRehearTime','oddBorrowPeriod', 'progress');}])
                    ->whereIn('userId', $userId)->where('status', 1)->where('type', 'invest')->where('time', '<', $end_time)->where('time', '>', $start_time)->get();
            }
            $users = User::whereIn('userId', $userId)->get(['userId', 'phone']);

            $invests = Invest::where('status',1)->whereHas('oddMoney', function($q) use ($end_time, $start_time){
                $q->where('status', 1)->where('type', 'invest')->where('time', '<', $end_time)->where('time', '>', $start_time);
            })->whereIn('userId', $userId)->groupBy('oddMoneyId')->get(['oddMoneyId', DB::raw('sum(interest) as hasInterest')]);

            //提前还款
            $statusLists = Invest::where('status','3')->whereHas('oddMoney', function($q) use ($end_time, $start_time){
                $q->where('status', 1)->where('type', 'invest')->where('time', '<', $end_time)->where('time', '>', $start_time);
            })->whereIn('userId', $userId)->get(['oddMoneyId', 'status']);

            //操作时间
            $operatetimes = Invest::where('status','<>','0')->whereHas('oddMoney', function($q) use ($end_time, $start_time){
                $q->where('status', 1)->where('type', 'invest')->where('time', '<', $end_time)->where('time', '>', $start_time);
            })->whereIn('userId', $userId)->get(['oddMoneyId', 'operatetime']);

            $investList = [];
            foreach ($invests as $invest) {
                $investList[$invest->oddMoneyId] = $invest->hasInterest;
            }
            $statusList = [];
            foreach ($statusLists as $status) {
                $statusList[$status->oddMoneyId] = $status->status;

            }
            $operatetime = [];
            foreach ($operatetimes as $operate) {
                $operatetime[$operate->oddMoneyId] = $operate->operatetime;
            }

            $firstTimeId = OddMoney::whereIn('userId',$userId)->where('type','invest')->groupBy('userId')->lists('id')->toArray();

            if(isset($result)){
                $rdata['result'] =0;
                $rdata['serial_num'] = $serial_num;
                $rdata['totalCount'] = count($result);
                foreach($users as $k =>$row){
                    $rdata['records'][$k]['mobile'] = $row['phone'];
                    $rdata['records'][$k]['platform_uid'] = $row['userId'];
                    foreach($result as $key => $value){
                        if($value['userId'] == $row['userId']){
                            $rdata['records'][$k]['userbidrecords'][$key]['bid_id'] = $value['oddNumber'];
                            $rdata['records'][$k]['userbidrecords'][$key]['rate'] = $value->odd ? number_format($value->odd->oddYearRate*100,2) :'';
                            $rdata['records'][$k]['userbidrecords'][$key]['raiseRate'] = $value->odd ? $value->odd->oddReward : '';
                            $progress = $value->odd ? $value->odd->progress : '';
                            if($progress == 'prep'){
                                $rdata['records'][$k]['userbidrecords'][$key]['bidResult'] = '预发布';
                            }elseif($progress == 'published'){
                                $rdata['records'][$k]['userbidrecords'][$key]['bidResult'] = '已发布';
                            }elseif($progress == 'start'){
                                $rdata['records'][$k]['userbidrecords'][$key]['bidResult'] = '0';
                            }elseif($progress == 'review'){
                                $rdata['records'][$k]['userbidrecords'][$key]['bidResult'] = '1';
                            }elseif($progress == 'run'){
                                $rdata['records'][$k]['userbidrecords'][$key]['bidResult'] = '2';
                            }elseif($progress == 'end'){
                                $rdata['records'][$k]['userbidrecords'][$key]['bidResult'] = '3';
                            }elseif($progress == 'fail'){
                                $rdata['records'][$k]['userbidrecords'][$key]['bidResult'] = '11';
                            }else{
                                $rdata['records'][$k]['userbidrecords'][$key]['bidResult'] = '';
                            }

                            if (isset($statusList[$value->id]) && $statusList[$value->id] == 3){
                                $rdata['records'][$k]['userbidrecords'][$key]['bidResult'] = '4';
                            }
                            if ($value['remain'] == 0 && $value['ckclaims'] == 1){
                                $rdata['records'][$k]['userbidrecords'][$key]['bidResult'] = '8';
                            }

                            $rdata['records'][$k]['userbidrecords'][$key]['productBidId'] = $value['id'];
                            if($value->odd){
                                $expireDate = strtotime($value->odd->oddRehearTime) + $value->odd->oddBorrowPeriod*2592000;
                            }else{
                                $expireDate = 0;
                            }
                            $rdata['records'][$k]['userbidrecords'][$key]['expireDate'] = date('Y-m-d',$expireDate);
                            if (isset($statusList[$value->id]) && $statusList[$value->id] == 3){
                                $rdata['records'][$k]['userbidrecords'][$key]['expireDate'] = $operatetime[$value->id];
                            }
                            if ($rdata['records'][$k]['userbidrecords'][$key]['bidResult'] == 8){
                                $rdata['records'][$k]['userbidrecords'][$key]['expireDate'] = isset($operatetime[$value->id])? $operatetime[$value->id]:'';
                            }

                            $interestDate = $value->odd ? $value->odd->oddRehearTime : 0000;
                            $rdata['records'][$k]['userbidrecords'][$key]['interestDate'] = date('Y-m-d',strtotime($interestDate));
                            $rdata['records'][$k]['userbidrecords'][$key]['investAmount'] = $value['money'];
                            $rdata['records'][$k]['userbidrecords'][$key]['investTime'] = $value['time'];
                            $rdata['records'][$k]['userbidrecords'][$key]['canAssign'] = true;
                            $rdata['records'][$k]['userbidrecords'][$key]['profitAmount'] = isset($investList[$value->id])?$investList[$value->id]:0;
                            if(in_array($value['id'],$firstTimeId)){
                                $rdata['records'][$k]['userbidrecords'][$key]['isFirstInvest'] = true;
                            }else{
                                $rdata['records'][$k]['userbidrecords'][$key]['isFirstInvest'] = false;
                            }
                        }
                    }
                    if(isset($rdata['records'][$k]['userbidrecords'])){
                        $rdata['records'][$k]['bidtotalCount'] = count($rdata['records'][$k]['userbidrecords']);
                        $rdata['records'][$k]['userbidrecords'] = array_values($rdata['records'][$k]['userbidrecords']);
                    }else{
                        unset($rdata['records'][$k]);
                    }

                }
                if ($rdata['records']){
                    $rdata['records'] = array_values($rdata['records']);
                }else{
                    $rdata['records'] = [];
                }

                $this->backJson($rdata);
            }

        } else {
            $rdata['result'] = 0001;
            $rdata['serial_num'] = $serial_num;
            $rdata['err_msg'] = '验证未通过';
            $this->backJson($rdata);
        }
        
    }


    /**
     * 对接平台标的列表查询接口
     */
    public function queryBindInfoAction()
    {
        $params = $this->getAllPost();
        $serial_num = $params['serial_num'];
        $t_code = $this->decrypt_aes($params['t_code']);
        $sign = xeenho::sh256($serial_num.$this->c_code.$t_code.$this->key);
        $rdata = [];
        if ($sign == $params['sign']) {
            $params['bid_id'] = explode(',',$params['bid_id']);
            if(count($params['bid_id']) == 1 && !empty($params['bid_id'][0])){
                $bid = $this->decrypt_aes($params['bid_id'][0]);
                $result = Odd::with(['user'=>function($q) {$q->select('userId','city','adder');}])->where('oddNumber',$bid)->get();
            }else if (count($params['bid_id']) > 1){
                foreach ($params['bid_id'] as $i => $list){
                    $bid[$i] = $this->decrypt_aes($list);
                }
                $result = Odd::with(['user'=>function($q) {$q->select('userId','city','adder');}])->whereIn('oddNumber',$bid)->get();
            }else {
                $result = Odd::with(['user'=>function($q) {$q->select('userId','city','adder');}])->where('progress','run')->get();
            }

            if($result){
                $rdata['result'] =0;
                $rdata['serial_num'] = $serial_num;
                $rdata['totalCount'] = count($result);
                foreach($result as $key => $value){
                    $rdata['records'][$key]['bid_id'] = $value['oddNumber'];
                    $rdata['records'][$key]['bid_name'] = $value['oddTitle'];
                    if($value['oddType'] == 'diya'){
                        $rdata['records'][$key]['bid_type'] = '抵押标';
                    }else if($value['oddType'] == 'xingyong'){
                        $rdata['records'][$key]['bid_type'] = '信用标';
                    }else if($value['oddType'] == 'danbao'){
                        $rdata['records'][$key]['bid_type'] = '担保';
                    }else if($value['oddType'] == 'newhand'){
                        $rdata['records'][$key]['bid_type'] = '新手标';
                    }
                    $rdata['records'][$key]['guarantee_type'] = '';//待处理
                    $rdata['records'][$key]['borrow_amount'] = $value['oddMoney'];
                    $rdata['records'][$key]['left_amount'] = ($value['oddMoney'] - $value['successMoney']);
                    $rdata['records'][$key]['borrower_area'] = $value->user->city? $value->user->city : '';
                    $rdata['records'][$key]['borrower_address'] = $value->user->adder ? $value->user->adder : '';
                    $rdata['records'][$key]['bid_rate'] = number_format($value['oddYearRate']*100,2);
                    $rdata['records'][$key]['raise_rate'] = $value['oddReward'];
                    $rdata['records'][$key]['interest_date'] = $value['oddRehearTime'];
                    if($value['oddRepaymentStyle'] == 'monthpay'){
                        $rdata['records'][$key]['repay_type'] = '按月付息';
                    }else{
                        $rdata['records'][$key]['repay_type'] = '等额本息';
                    }
                    $rdata['records'][$key]['repay_count'] = $value['oddBorrowPeriod'];
                    if($value['progress'] == 'prep'){
                        $rdata['records'][$key]['bid_status'] = '预发布';
                    }elseif($value['progress'] == 'published'){
                        $rdata['records'][$key]['bid_status'] = '已发布';
                    }elseif($value['progress'] == 'start'){
                        $rdata['records'][$key]['bid_status'] = '0';
                    }elseif($value['progress'] == 'review'){
                        $rdata['records'][$key]['bid_status'] = '1';
                    }elseif($value['progress'] == 'run'){
                        $rdata['records'][$key]['bid_status'] = '2';
                    }elseif($value['progress'] == 'end'){
                        $rdata['records'][$key]['bid_status'] = '3';
                    }elseif($value['progress'] == 'fail'){
                        $rdata['records'][$key]['bid_status'] = '11';
                    }

                    $rdata['records'][$key]['bond_code'] = $value['oddNumber'];
                    $rdata['records'][$key]['bid_url'] = WEB_MAIN.'/odd/'.$value['oddNumber'];
                    $rdata['records'][$key]['wap_bid_url'] = WEB_MAIN.'/odd/'.$value['oddNumber'];
                    $rdata['records'][$key]['isPromotion'] = false;
                    $rdata['records'][$key]['isRecommend'] = false;
                    if($value['oddStyle'] == 'newhand'){
                        $rdata['records'][$key]['isNovice'] = true;
                    }else{
                        $rdata['records'][$key]['isNovice'] = false;
                    }
                    $rdata['records'][$key]['isExclusive'] = $value['appointUserId'];
                    $rdata['records'][$key]['isAssignment'] = false;
                    $rdata['records'][$key]['canAssign'] = true;
                    $rdata['records'][$key]['bid_progress_percent'] = number_format(($value['successMoney']) /$value['oddMoney']*100,2) ;
                    //$rdata['records'][$key]['introduction'] = $value['oddUse'];
                    $rdata['records'][$key]['duration_months'] = $value['oddBorrowPeriod'];
                    $rdata['records'][$key]['duration_days'] = ($value['oddBorrowPeriod'] * 30);
                    if($value['oddBorrowStyle'] == 'month'){
                        $rdata['records'][$key]['isDurationMonths'] = true;
                    }else{
                        $rdata['records'][$key]['isDurationMonths'] = false;
                    }
                }
                $this->backJson($rdata);
            }

        } else {
            $rdata['result'] = 0001;
            $rdata['serial_num'] = $serial_num;
            $rdata['err_msg'] = '验证未通过';
            $this->backJson($rdata);
        }
        
    }


    /**
     *
     * 用户所持标的回款信息查询接口
     */
    public function QueryUserBidRepayAction()
    {
        set_time_limit(0);
        $params = $this->getAllPost();
        $serial_num = $params['serial_num'];
        $t_code = $this->decrypt_aes($params['t_code']);
        $sign = xeenho::sh256($serial_num.$this->c_code.$t_code.$this->key);
        $rdata = [];
        if ($sign == $params['sign']) {
            $userId = explode(',',$params['platform_uid']);
            if(count($userId) == 1 && !empty($userId[0])){
                $userId = $this->decrypt_aes($userId[0]);
                $user = Xinghuo::where('userId',$userId)->get();
                if($user){
                    $result = OddMoney::with(['odd'=>function($q){ $q->select('oddNumber', 'oddBorrowPeriod');}])
                        ->where('userId', $userId)->where('status', 1)->where('type', 'invest')->get();
                }
                $userId = array($userId);
            }else if (count($params['platform_uid']) > 1){
                foreach ($params['platform_uid'] as $i => $list){
                    $userId[$i] = $this->decrypt_aes($list);
                }
                $userCount = Xinghuo::whereIn('userId',$userId)->get()->count();
                if($userCount == count($userId)) {
                    $result = OddMoney::with(['odd'=>function($q){ $q->select('oddNumber', 'oddBorrowPeriod');}])
                        ->whereIn('userId', $userId)->where('status', 1)->where('type', 'invest')->get();
                }
            }else{
                $userId = Xinghuo::getUserId();
                $result = OddMoney::with(['odd'=>function($q){ $q->select('oddNumber', 'oddBorrowPeriod');}])
                    ->whereIn('userId', $userId)->where('status', 1)->where('type', 'invest')->get();
            }

            $leftRepayInvest = Invest::where('status',0)->whereHas('oddMoney', function($q) {
                $q->where('status', 1)->where('type', 'invest');
            })->whereIn('userId', $userId)->groupBy('oddMoneyId')->get(['oddMoneyId', DB::raw('sum(benjin) as benjin'),DB::raw('sum(interest) as interest')]);

            foreach ($leftRepayInvest as $leftRepay){
                $leftRepayCapital[$leftRepay->oddMoneyId] = $leftRepay->benJin;
                $leftRepayInterest[$leftRepay->oddMoneyId] = $leftRepay->interest;
            }
            $accruedRepayInvest = Invest::where('status',1)->whereHas('oddMoney', function($q) {
                $q->where('status', 1)->where('type', 'invest');
            })->whereIn('userId', $userId)->groupBy('oddMoneyId')->get(['oddMoneyId', DB::raw('sum(benjin) as benjin,sum(interest) as interest')]);

            foreach ($accruedRepayInvest as $accruedRepay){
                $accruedRepayCapital[$accruedRepay->oddMoneyId] = $accruedRepay->benJin;
                $accruedRepayInterest[$accruedRepay->oddMoneyId] = $accruedRepay->interest;
            }
            $invests = Invest::where('status',1)->whereHas('oddMoney',function($q){
                $q->where('status',1)->where('type','invest');
            })->whereIn('userId',$userId)->get();

            if(!empty($result)){
                $rdata['result'] =0;
                $rdata['serial_num'] = $serial_num;
                $rdata['totalCount'] = count($result);
                foreach($userId as $k => $row){
                    $rdata['records'][$k]['mobile'] = User::where('userId',$row)->value('phone') ? User::where('userId',$row)->value('phone') :13801234567;
                    $rdata['records'][$k]['platform_uid'] = $row;

                    foreach($result as $key => $value){
                        if($value['userId'] == $row){
                            $rdata['records'][$k]['bidRecords'][$key]['bid_id'] = $value['oddNumber'];
                            $rdata['records'][$k]['bidRecords'][$key]['productBidId'] = $value['id'];
                            foreach ($invests as $j => $item){
                                if ($item->oddMoneyId == $value->id){
                                    $rdata['records'][$k]['bidRecords'][$key]['bidRepayRecords'][$j]['repayPeriods'] = $value->odd->oddBorrowPeriod;
                                    $rdata['records'][$k]['bidRecords'][$key]['bidRepayRecords'][$j]['currentRepayPeriod'] = $item['qishu'];
                                    $rdata['records'][$k]['bidRecords'][$key]['bidRepayRecords'][$j]['repayDate'] = date('Y-m-d', strtotime($item['endtime']));
                                    $rdata['records'][$k]['bidRecords'][$key]['bidRepayRecords'][$j]['actualRepayTime'] = $item['operatetime'] ? date('Y-m-d', strtotime($item['operatetime'])) : '';
                                    $rdata['records'][$k]['bidRecords'][$key]['bidRepayRecords'][$j]['currentRepayCapital'] = $item['benJin'];
                                    $rdata['records'][$k]['bidRecords'][$key]['bidRepayRecords'][$j]['currentRepayInterest'] = $item['interest'];
                                    $rdata['records'][$k]['bidRecords'][$key]['bidRepayRecords'][$j]['leftRepayCapital'] = isset($leftRepayCapital[$value->id]) ? $leftRepayCapital[$value->id] : 0;
                                    $rdata['records'][$k]['bidRecords'][$key]['bidRepayRecords'][$j]['leftRepayInterest'] = isset($leftRepayInterest[$value->id]) ? $leftRepayInterest[$value->id] : 0;
                                    $rdata['records'][$k]['bidRecords'][$key]['bidRepayRecords'][$j]['accruedRepayCapital'] = isset($accruedRepayCapital[$value->id]) ? $accruedRepayCapital[$value->id] : 0;
                                    $rdata['records'][$k]['bidRecords'][$key]['bidRepayRecords'][$j]['accruedRepayInterest'] = isset($accruedRepayInterest[$value->id]) ? $accruedRepayInterest[$value->id] : 0;
                                    $rdata['records'][$k]['bidRecords'][$key]['bidRepayRecords'][$j]['repayResult'] = $item['status'];
                                    if ($item['operatetime'] < $item['endtime']) {
                                        $rdata['records'][$k]['bidRecords'][$key]['bidRepayRecords'][$j]['repayType'] = '2';
                                    } else {
                                        $rdata['records'][$k]['bidRecords'][$key]['bidRepayRecords'][$j]['repayType'] = '1';
                                    }
                                }
                            }
                            if(isset($rdata['records'][$k]['bidRecords'][$key]['bidRepayRecords'])){
                                $rdata['records'][$k]['bidRecords'][$key]['bidRepayCount'] =count($rdata['records'][$k]['bidRecords'][$key]['bidRepayRecords']);
                            }


                            if(isset($rdata['records'][$k]['bidRecords'][$key]['bidRepayRecords'])){
                                $rdata['records'][$k]['bidRecords'][$key]['bidRepayRecords'] = array_values($rdata['records'][$k]['bidRecords'][$key]['bidRepayRecords']);
                                $rdata['records'][$k]['bidRecords'] = array_values($rdata['records'][$k]['bidRecords']);
                            }else{
                                $rdata['records'][$k]['bidRecords'][$key]['bidRepayRecords'] = [];
                            }
                        }
                    }
                    if(isset($rdata['records'][$k]['bidRecords'])){
                        $rdata['records'][$k]['bidCount'] = count($rdata['records'][$k]['bidRecords']);
                        $rdata['records'][$k]['bidRecords'] = array_values($rdata['records'][$k]['bidRecords']);
                    }else{
                        unset($rdata['records'][$k]);
                    }

                }
                if ($rdata['records']){
                    $rdata['records'] = array_values($rdata['records']);
                }else{
                    $rdata['records'] = [];
                }

                $this->backJson($rdata);
            }
        } else {
            $rdata['result'] = 0001;
            $rdata['serial_num'] = $serial_num;
            $rdata['err_msg'] = '验证未通过';
            $this->backJson($rdata);
        }
    }

    

}
