<?php
use Yaf\Registry;
use helpers\StringHelper;
use models\User;
use models\Sms;
use forms\RegisterForm;
use forms\LoginForm;
use exceptions\HttpException;
use factories\RedisFactory;
use helpers\NetworkHelper;

/**
 * RegisterController
 * 注册控制器
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class RegisterController extends Controller {
    public $menu = 'register';

    /**
     * 注册
     * @param  string $spread 推广码
     * @return mixed
     */
    public function indexAction() {
        $params = $this->getAllPost();
        $form = new RegisterForm($params);
        $rdata = [];
        if($form->register()) {
            $data['phone'] = $params['phone'];
            $data['password'] = $params['password'];
            $form = new LoginForm($data);
            $form->login();

            $rdata['status']=1;
            $rdata['info']='注册成功！';
        } else {
            $rdata['status']=0;
            $rdata['info']=$form->posError();
        }
        $this->backJson($rdata);
    }

    public function guideAction() {
        $this->display('guide');
    }
}
