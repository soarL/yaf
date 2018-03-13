<?php
namespace task\handlers;

use task\Handler;
use models\Sms;
use models\User;
use tools\Log;

/**
 * SmsHandler
 * 短信处理者
 *
 * params:
 *     content  发送内容
 *             {USERNAME}:用户名 {NAME}:姓名 {SEX}:性别 {PHONE}:手机号 {FUNDMONEY}:账户资金 {INTEGRAL}:积分 {EMAIL}:邮箱 {CARDNUM}:身份证
 *     phone    手机号，多手机号使用因为逗号隔开，如果该值未all则表示所有用户
 *     type     0:纯文本 1:含占位符
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class SmsHandler extends Handler {

    public function handle() {
        $content = isset($this->params['content'])?$this->params['content']:'';
        $phone = isset($this->params['phone'])?$this->params['phone']:'';
        $type = isset($this->params['type'])?$this->params['type']:0;

        $rdata = [];
        if($content=='') {
            $rdata['status'] = 0;
            $rdata['msg'] = '发送内容不能为空！';
            return $rdata;
        }

        if($phone=='') {
            $rdata['status'] = 0;
            $rdata['msg'] = '发送手机号不能为空！';
            return $rdata;
        }

        if($phone=='all') {
            $columns = ['userId', 'username', 'phone', 'cardnum', 'custody_id', 'email', 'fundMoney', 'sex', 'name', 'integral'];
            User::select($columns)->where('phone', '18760419185')->chunk(500, function ($users) use ($type, $content){
                $list = [];
                foreach ($users as $user) {
                    if($type==1) {
                        $content = $this->getRealContent($content, $user);
                        $result = Sms::dxOne($content, $user->phone);
                        Log::write('发送短信【'.$user->phone.'】，结果：'.$result, [], 'sms');
                        usleep(1000);
                    } else {
                        $list[] = $user->phone;
                    }
                }
                if($type==0) {
                    $result = Sms::dxOne($content, implode(',', $list));
                    Log::write('发送短信【'.$user->phone.'】，结果：'.$result, [], 'sms');
                    usleep(1000);
                }
            });
        } else {
            if($type==1) {
                $list = explode(',', $phone);
                $columns = ['userId', 'username', 'phone', 'cardnum', 'custody_id', 'email', 'fundMoney', 'sex', 'name', 'integral'];
                User::select($columns)->whereIn('phone', $list)->chunk(500, function ($users) use ($type, $content) {
                    foreach ($users as $user) {
                        $content = $this->getRealContent($content, $user);
                        $result = Sms::dxOne($content, $user->phone);
                        Log::write('发送短信【'.$user->phone.'】，结果：'.$result, [], 'sms');
                        usleep(1000);
                    }
                });
            } else {
                $result = Sms::dxOne($content, $phone);
                Log::write('发送短信【'.$phone.'】，结果：'.$result, [], 'sms');
                usleep(1000);
            }
        }

        $rdata['status'] = 1;
        $rdata['msg'] = '发送完成！';
        return $rdata;
    }

    private function getRealContent($content, $user) {
        $msg = str_replace('{USERNAME}', $user->username, $content);
        $msg = str_replace('{NAME}', $user->name, $msg);
        $sex = '先生';
        if($user->sex=='women') {
            $sex = '女士';
        }
        $msg = str_replace('{SEX}', $sex, $msg);
        $msg = str_replace('{PHONE}', $user->phone, $msg);
        $msg = str_replace('{FUNDMONEY}', $user->fundMoney, $msg);
        $msg = str_replace('{INTEGRAL}', intval($user->integral/100), $msg);
        $msg = str_replace('{EMAIL}', $user->email, $msg);
        return $msg;
    }
}