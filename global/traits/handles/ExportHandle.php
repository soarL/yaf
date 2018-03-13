<?php
namespace traits\handles;

use Yaf\Registry;
use \AccessPlugin;
/**
 * ExportHandle
 * 输出处理-控制器方法分离
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
trait ExportHandle {
    public function export($msg, $mode=self::CONSOLE_FULL_SUF, $isExit=false) {
        $config = Registry::get('config');
        if($config->console&&$config->console->charset!=null&&$config->console->charset!='utf8') {
            $msg = iconv('UTF-8',$config->console->charset.'//IGNORE', $msg);
        } 
        if($mode==self::CONSOLE_FULL_SUF) {
            $msg .= '-----'.date('Y-m-d H:i:s')."\n";
        } else if($mode==self::CONSOLE_LINE_SUF) {
            $msg .= "\n";
        } else if($mode==self::CONSOLE_NONE_SUF) {
            
        }
        echo $msg;
        if($isExit) {
            exit(0);
        }
    }

    public function backJson($array) {
        if(empty($array['data']['version'])){
            $array['data']['version'] = '0.1.0';
            $array['data']['time'] = time();
        }
        echo json_encode($array);exit(0);
    }

    public function backJsonp($array) {
        $callback = $this->getRequest()->getQuery('callback');
        echo $callback.'('.json_encode($array).')';exit(0);
    }

    public function goBack() {
        $session = Registry::get('session');
        $returnUrl = $session->get(AccessPlugin::RETURN_URL);
        $session->del(AccessPlugin::RETURN_URL);
        if($returnUrl) {
            $urlInfo = parse_url($returnUrl);
            $userUrlInfo = parse_url(WEB_USER);
            $mainUrlInfo = parse_url(WEB_MAIN);
            if($urlInfo['host']!=$userUrlInfo['host']&&$urlInfo['host']!=$mainUrlInfo['host']) {
                $this->display('redirect', ['returnUrl'=>$returnUrl]);
            } else {
                $this->redirect($returnUrl);
            }
        } else {
            $this->redirect('/');
        }
    }

    public function goHome() {
        $this->redirect('/');
    }

    /**
     * 页面显示
     * @param  string $template 模版名称
     * @param  array $data 数据
     */
    public function displayBasic($template, array $data = null) {
        parent::display($template, $data);
        exit(0);
    }
}