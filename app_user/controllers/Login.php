<?php
use Yaf\Registry;
use tools\DuoZhuan;
use forms\LoginForm;
use forms\ForgetForm;
use forms\ForgetTwoForm;
use models\User;
use helpers\NetworkHelper;
use tools\Captcha;
use factories\RedisFactory;
use forms\ForgetPaypassForm;

/**
 * LoginController
 * 处理登录、忘记密码等等
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class LoginController extends Controller {
    public $menu = 'login';

    public function indexAction() {
        $session = Registry::get('session');
        if($this->isGet()) {
            $returnUrl = $this->getQuery('ret', false);
            if($returnUrl) {
                $session->set(AccessPlugin::RETURN_URL, urldecode($returnUrl));
            }

            if(User::isLogin()) {
                if($returnUrl) {
                    $this->redirect($returnUrl);
                } else {
                    $this->redirect(WEB_ACCOUNT);
                }
            } else {
                if($this->getQuery('login',false) !== false){
                    $this->display('index',['login'=>1]);
                }
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
                $this->redirect(WEB_LOGIN);
            }
        }
    }
    
    public function submitAction() {
        $params = $this->getAllPost();
        $form = new LoginForm($params);
        $rdata = [];
        if($form->login()) {
            $rdata['status'] = 1;
            $rdata['info'] = '登录成功!';
            //Flash::success('登录成功!');
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = $form->posError();
        }
        $this->backJson($rdata);
    }

    public function ajaxLogoutAction() {
        User::logout();
        Flash::success('退出成功！');
        $this->backJsonp(['status'=>1]);
    }

    public function isLoginAction() {
        if(User::isLogin()) {
            $this->backJsonp(['status'=>1]);
        } else {
            $this->backJsonp(['status'=>0]);
        }
    }
    
    public function logoutAction() {
        User::logout();
        $this->goHome();
    }

    public function forgetAction() {
        if ($this->getRequest()->isGet()) {
            $this->display('forget', [
                'step'=>1,
            ]);
        } else {
            $form = new ForgetForm($this->getAllPost());
            if($form->send()) {
                $this->display('forget', [
                    'step'=>2, 
                    'phone'=>$form->phone,
                ]);
            } else {
                Flash::error($form->posError());
                $this->redirect('/login/forget');
            }
        }
    }

    public function forgetPaypassAction() {
        $form = new ForgetPaypassForm($this->getAllPost());
        if($form->update()) {
            $rdata['status'] = 1;
            $rdata['info'] = '操作成功!';
            Flash::success('操作成功!');
            // Flash::success('找回成功！');
            // $this->redirect('/login');
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = $form->posError();
            // Flash::error($form->posError());
            // $this->redirect('/login/forget');
        }
        $this->backJson($rdata);
    }

    public function forgetTwoAction() {
        $form = new ForgetTwoForm($this->getAllPost());
        if($form->update()) {
            $rdata['status'] = 1;
            $rdata['info'] = '操作成功!';
            Flash::success('操作成功!');
            // Flash::success('找回成功！');
            // $this->redirect('/login');
        } else {
            $rdata['status'] = 0;
            $rdata['info'] = $form->posError();
            // Flash::error($form->posError());
            // $this->redirect('/login/forget');
        }
        $this->backJson($rdata);
    }

    public function duozhuanAction() {
        if($this->isGet()) {
            $key = $this->getQuery('key', '');
            $secret = $this->getQuery('secret', '');
            $nonce = $this->getQuery('nonce', '');
            $callback = $this->getQuery('callback_uri', '');
            if(DuoZhuan::check($key, $nonce, $secret)) {
                $this->display('duozhuan', ['key'=>$key, 'secret'=>$secret, 'nonce'=>$nonce, 'callback'=>urlencode($callback)]);
            } else {
                Flash::error('校验失败！');
                $this->redirect('/login');
            }
        } else {
            $params = $this->getAllPost();
            $key = $params['key'];
            $secret = $params['secret'];
            $nonce = $params['nonce'];
            $callback = urldecode($params['callback']);
            $callback .= '&key='.$key.'&nonce='.$nonce.'&secret='.$secret;
            if(!DuoZhuan::check($key, $nonce, $secret)) {
                Flash::error('校验失败！');
                $this->redirect('/login');
            }

            $form = new LoginForm($params);
            if($form->login()) {
                $user = Registry::get('user');
                $redis = RedisFactory::create();
                $redis->sAdd('dz_users', $user->userId);
                $callback .= '&authorized=true&user='.md5($user->userId);
                $this->redirect($callback);
            } else {
                Flash::error($form->posError());
                $this->display('duozhuan', ['key'=>$key, 'secret'=>$secret, 'nonce'=>$nonce, 'callback'=>urlencode($callback)]);
            }
        }
    }
}
