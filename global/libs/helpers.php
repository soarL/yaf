<?php

if (! function_exists('_title')) {
	function _title($title, $length=18) {
        $newTitle = _substr($title, 0, $length);
        if($newTitle==$title) {
            return $title;
        } else {
            return $newTitle.'...';
        }
    }
}

if (! function_exists('_date')) {
    function _date($format, $old, $nv='无') {
        if($old=='0000-00-00 00:00:00') {
            return $nv;
        }
        return date($format, strtotime($old));
    }
}


if (! function_exists('_password')) {
    function _password($password, $secret) {
        return md5($password.$secret);
    }
}

if (! function_exists('_hide_phone')) {
    function _hide_phone($phone) {
        if(!$phone) {
            return '';
        }
        return substr($phone, 0, 3) . '****' . substr($phone, 7);
    }
}

if (! function_exists('_hide_name')) {
    function _hide_name($name) {
        if(!$name) {
            return '';
        }
        return substr($name, 0, 3) . '**';
    }
}

if (! function_exists('_hide_company')) {
    function _hide_company($name) {
        if(!$name) {
            return '';
        }
        return substr($name, 0, 6) . '**'. substr($name, -6, 6);
    }
}

if (! function_exists('_hide_cardnum')) {
    function _hide_cardnum($cardnum) {
       if(!$cardnum) {
            return '';
        }
        $hideCardnum = '';
        if(strlen($cardnum)==15) {
            $cardnumBegin = substr($cardnum, 0, 3);
            $cardnumEnd = substr($cardnum, 11);
            $hideCardnum = $cardnumBegin . '****' . $cardnumEnd;
        }
        if(strlen($cardnum)==18) {
            $cardnumBegin = substr($cardnum, 0, 8);
            $cardnumEnd = substr($cardnum, 16);
            $hideCardnum = $cardnumBegin . '****' . $cardnumEnd;
        }
        return $hideCardnum;
    }
}

if (! function_exists('_hide_email')) {
    function _hide_email($email) {
        if(!$email) {
            return '';
        }
        $es = explode('.', $email);
        $suf = end($es);
        if(strlen($email)>11) {
            return substr($email, 0, 4) . '****' . '.' . $suf;
        } else {
            return substr($email, 0, 2) . '***' . '.' . $suf;
        }
    }
}

if (! function_exists('_hide_username')) {
    function _hide_username($username) {
        if(!$username) {
            return '';
        }
        $length = _strlen($username);
        if($length<=2) {
            return '**'._substr($username, 1);
        } else if($length>2&&$length<=3) {
            return '**'._substr($username, 2);
        } else if($length>3&&$length<5) {
            return '***'._substr($username, 3);
        } else {
            return '****'._substr($username, 4);
        }
    }
}


/**
 * 打印调试
 */

if(! function_exists('_dump')) {
    function _dump($var, $echo=true, $label=null, $strict=true) {
        $label = ($label === null) ? '' : rtrim($label) . ' ';
        if (!$strict) {
            if (ini_get('html_errors')) {
                $output = print_r($var, true);
                $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
            } else {
                $output = $label . print_r($var, true);
            }
        } else {
            ob_start();
            var_dump($var);
            $output = ob_get_clean();
            if (!extension_loaded('xdebug')) {
                $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
                $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
            }
        }
        if ($echo) {
            echo($output);
            return null;
        }else
            return $output;
    }
}

if(! function_exists('_dd')) {
	function _dd() {
		array_map(function($x) { _dump($x); }, func_get_args());
		exit;
	}
}



if(! function_exists('_cn_number')) {
    function _cn_number($number) {
        if($number<10000) {
            return $number;
        } else if($number>=10000 && $number<10000*10000) {
            return round($number/10000, 2).'万';
        } else {
            return round($number/(10000*10000), 2).'亿';
        }
    }
}


if(! function_exists('_post')){
    function _post($url, $data, $header = array(), $cmd = '1'){
       $curl = curl_init(); // 启动一个CURL会话
       curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
       if (!empty($header)) {
           curl_setopt($curl, CURLOPT_HTTPHEADER, $header); //设置header
       }
       curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
       curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
       if ($cmd) {
           curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
       }
       curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
       curl_setopt($curl,CURLOPT_AUTOREFERER, 1); // 自动设置Referer
       curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
       curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
       curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
       curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
       curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
       $response = curl_exec($curl); // 执行操作
       if (curl_errno($curl)) {
           echo 'Errno' . curl_error($curl); //捕抓异常
       }
       curl_close($curl); // 关闭CURL会话
       return $response; // 返回数据
    }
}

if(! function_exists('_get')){

    function _get($url){
         //初始化
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        // 执行后不直接打印出来
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        // 跳过证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // 不从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        //执行并获取HTML文档内容
        $output = curl_exec($ch);

        //释放curl句柄
        curl_close($ch);
        
        return json_decode($output);
    }
}



if(! function_exists('_randomkeys')){
    function _randomkeys($length){
       $returnStr='';
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for($i = 0; $i < $length; $i ++) {
            $returnStr .= $pattern {mt_rand ( 0, 61 )};
        }
        return $returnStr;
    }
}

if(! function_exists('_paramsSort')){
    function _paramsSort($params,$isDelEmpty=false){
        ksort($params);
        reset($params);
        if($isDelEmpty) {
            foreach ($params as $key => $value) {
                if($value===''||$value===null) {
                    unset($params[$key]);
                }
            }
        }
        return $params;
    }
}


if(! function_exists('_createLinkString')){
    function _createLinkString($params){
        $arg  = "";
         while (list ($key, $val) = each ($params)) {
             if(is_array($val)) {
                 $val = implode(',', $val);
             }
             $arg.=$key."=".$val."&";
         }
         //去掉最后一个&字符
         $arg = substr($arg,0,count($arg)-2);
         //如果存在转义字符，那么去掉转义
         if(get_magic_quotes_gpc()){
             $arg = stripslashes($arg);
         }
         return $arg;
    }
}


 