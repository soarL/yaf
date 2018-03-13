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
