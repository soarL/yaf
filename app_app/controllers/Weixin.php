<?php
use models\Sms;
use forms\app\OrderForm;
use traits\handles\ITFAuthHandle;
use Yaf\Registry;

/**
 * WeixinController
 * weixin接口
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class WeixinController extends Controller {
    use ITFAuthHandle;

    public $checkSign = false;

    public function init() {
        parent::init();
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
    }

    public $actions = [
        'index' => 'actions/IndexAction.php',
        'crtrs' => 'actions/CrtrsAction.php',
        'odds' => 'actions/OddsAction.php',
        'areas' => 'actions/AreasAction.php',
        'news' => 'actions/NewsAction.php',
        'calculate' => 'actions/CalculateAction.php',
    ];

    /**
     * 借款
     * @return mixed
     */
    public function orderAction() {
        $params = $this->getAllPost();
        $this->authenticate($params, ['smsCode'=>'手机验证码', 'phone'=>'手机号', 'name'=>'姓名']);
        
        $params['checkSms'] = 1;

        $form = new OrderForm($params);
        $rdata = [];
        if($form->save()) {
            $rdata['msg'] = '恭喜您提交成功，我们的客服尽快联系您！';
            $rdata['status'] = 1;
        } else {
            $rdata['msg'] = $form->posError();
            $rdata['status'] = 0;
        }
        $this->backJson($rdata);
    }

    /**
     * 发送短信
     * 需要参数：
     *  username
     *  password
     *  phone
     * @return  mixed
     */
    public function smsAction() {
        $params = $this->getAllPost();
        $this->authenticate($params, ['msgType'=>'用户名', 'phone'=>'手机号']);

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
            $rdata['data']['code'] = $result['code'];
            $this->backJson($rdata);
        } else {
            $rdata['status'] = 0;
            $rdata['msg'] = $result['info'];
            $this->backJson($rdata);
        }
    }
}

