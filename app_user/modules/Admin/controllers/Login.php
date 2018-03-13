<?php
use Yaf\Registry;
use forms\LoginForm;
use models\User;
use helpers\NetworkHelper;
use tools\Captcha;

/**
 * LoginController
 * 处理登录、忘记密码等等
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class LoginController extends Controller {
    public $menu = 'index';

    public function indexAction() {
        $session = Registry::get('session');
        if($this->isGet()) {

            if(User::isLogin()) {
                $this->redirect(WEB_USER.'/admin');
            } else {
                $this->display('index');
            }
        } else {
            $params = $this->getAllPost();
            $form = new LoginForm($params);
            if($form->login()) {
                Flash::success('登录成功！');
                $this->goBack();
            } else {
                Flash::error($form->posError());
                $this->redirect(WEB_USER.'/login');
            }
        }
    }
    
    public function logoutAction() {
        User::logout();
        $this->goHome();
    }
}
