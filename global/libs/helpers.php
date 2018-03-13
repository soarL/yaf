<?php

if (! function_exists('_value')) {
    function _value($value1, $value2) {
        return $value1?$value1:$value2;
    }
}

if (! function_exists('_isset')) {
    function _isset($array, $value, $default=false) {
        return isset($array[$value])?$array[$value]:$default;
    }
}

if (! function_exists('_decode')) {
    function _decode($string) {
        return htmlspecialchars_decode($string);
    }
}

if (! function_exists('_substr')) {
	function _substr($str, $start=0, $len=0) {
        $length = strlen($str);
        $new_str = [];
        for($i=0;$i<$length;$i++) {
            $temp_str=substr($str,0,1);
            if(ord($temp_str) > 127) {
                $i++;
                if($i<$length) {
                    $new_str[]=substr($str,0,3);
                    $str=substr($str,3);
                }
            } else {
                $substr = substr($str,0,1);
                if($substr!==false) {
                    $new_str[] = $substr;
                }
                $str=substr($str,1);
            }

        }
        $string = '';
        $newLength = count($new_str);
        $subLength = $newLength;
        if($len>0) {
            $subLength = $len;
        }
        $endPos = $start+$subLength;
        if($endPos>$newLength) {
            $endPos = $newLength;
        }
        for ($i=$start; $i < $endPos; $i++) {
            $string .= $new_str[$i];
        }
        return $string;
    }
}

if (! function_exists('_strlen')) {
    function _strlen($str) {
        $length = strlen($str);
        $newLength = 0;
        for($i=0;$i<$length;$i++) {
            $temp_str=substr($str,0,1);
            if(ord($temp_str) > 127) {
                $i++;
                if($i<$length) {
                    $newLength++;
                    $str=substr($str,3);
                }
            } else {
                $substr = substr($str,0,1);
                if($substr!==false) {
                    $newLength++;
                }
                $str=substr($str,1);
            }

        }
        return $newLength;
    }
}

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

if (! function_exists('_secret')) {
    function _secret($length) {
        $prepareCode = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
             '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $secret = '';
        for ($i=0; $i < $length; $i++) { 
            $rand = rand(0, count($prepareCode)-1);
            $secret .= $prepareCode[$rand];
        }
        return $secret;
    }
}

if (! function_exists('_password')) {
    function _password($password, $secret) {
        return md5($password.$secret);
    }
}

if (! function_exists('_options')) {
    function _options($options, $selectKey=null) {
        $tagOptions = '';
        foreach ($options as $key => $value) {
            $selected = '';
            if($selectKey==$key) {
                $selected = ' selected';
            }
            $tagOptions .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>' . "\n";
        }
        echo $tagOptions;
    }
}

if (! function_exists('_package')) {
    function _package($array, $keyKey, $valueKey, $others = null) {
        $newArray = [];
        if(count($array)>0) {
            foreach ($array as $row) {
                $newArray[$row[$keyKey]] = $row[$valueKey];
            }
        }
        if($others!=null) {
            $newArray = array_merge($others, $newArray);
        }
        return $newArray;
    }
}

if (! function_exists('_key')) {
    function _key($value, $map, $default=null) {
        if(isset($map[$value])) {
            return $map[$value] . "\n";
        } else {
            if($default!=null&&isset($map[$default])) {
                return $map[$default] ."\n";
            }
        }
        return '';
    }
}

if (! function_exists('_label')) {
    function _label($value, $map) {
        if(isset($map[$value])) {
            $item = $map[$value];
            return '<span class="label label-' . $item['type'] . '">' . $item['name'] . '</span>'."\n";
        }
        return '';
    }
}

if (! function_exists('_txtp')) {
    function _txtp($text) {
        $list = explode("\n", $text);
        $html = '';
        foreach ($list as $string) {
            $html .= '<p>' . $string . '</p>';
        }
        return $html;
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

if (! function_exists('_hide_USCI')) {
    function _hide_USCI($name) {
        if(!$name) {
            return '';
        }
        return substr($name, 0, 6) . '**';
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

if (! function_exists('_hide_banknum')) {
    function _hide_num($string) {
        return preg_replace('/\d/', '*', $string);
    }
}

if (! function_exists('_hide_banknum')) {
    function _hide_banknum($bankNum) {
        $length = strlen($bankNum);
        $hideBankNum = '';
        $bankNumBegin = substr($bankNum, 0, 3);
        $bankNumEnd = substr($bankNum, $length-4);
        $hideBankNum = $bankNumBegin . ' **** **** ' . $bankNumEnd;
        return $hideBankNum;
    }
}

if (! function_exists('_merge')) {
    function _merge($array1, $array2) {
        $array = [];
        if(is_array($array1)) {
            foreach ($array1 as $value) {
                $array[] = $value;
            }    
        }
        if(is_array($array2)) {
            foreach ($array2 as $value) {
                $array[] = $value;
            }    
        }
        return $array;
    }
}

if(! function_exists('_yuan2fen')) {
    function _yuan2fen($money) {
        return intval(($money + 0.001) * 100);
    }
}

if(! function_exists('_binhas')) {
    function _binhas($need, $all) {
        if(($need&$all)==$need) {
            return true;
        }
        return false;
    }
}

if(! function_exists('_is_float_eq')) {
    function _is_float_eq($num1, $num2, $delta=0.00001) {
        if(abs($num1 - $num2) < $delta) {
            return true;
        } else {
            return false;
        }
    }
}

if(! function_exists('_thumbnail')) {
    function _thumbnail($img) {
        $list = explode('/', $img);
        $name = end($list);
        return str_replace($name, 'thumbnail/' . $name, $img);
    }
}

// 非四舍五入截取小数
if(! function_exists('_cut_float')) {
    function _cut_float($num, $hold=2) {
        $pos = strpos($num, '.');
        if($pos===false) {
            return $num;
        }
        $len = $pos + 1 + $hold;
        $num = substr($num, 0, $len);
        return $num;
    }
}

if(! function_exists('_cut_float')) {
	function _cut_float($num, $hold=2) {
		$pos = strpos($num, '.');
		if($pos===false) {
			return $num;
		}
		$len = $pos + 1 + $hold;
		$num = substr($num, 0, $len);
		return $num;
	}
}

/**
 * 浏览器友好的变量输出
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

/**
 * 打印调试
 */
if(! function_exists('_dd')) {
	function _dd() {
		array_map(function($x) { _dump($x); }, func_get_args());
		exit;
	}
}

if(! function_exists('_css_style')) {
    function _css_style($attrs) {
        $string = 'style="';
        foreach ($attrs as $key => $value) {
            $string .= $key . ':' . $value . ';';
        }
        return $string . '"';
    }
}

if (! function_exists('_format_price')) {
    function _format_price($value) {
        return number_format($value,2);
    }
}

if (! function_exists('_array_unique_fb')) {
    function _array_unique_fb($value) {
        foreach ($value as $v){
           $v=join(',',$v);
           $temp[]=$v;
       }
        $temp=array_unique($temp);
        foreach ($temp as $k => $v){
           $temp[$k]=explode(',',$v);
        }
        return $temp;
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

if(! function_exists('_ntop')) {
    function _ntop($num) {
        $begin = '2015-01-09';
        $date = substr($num, 0, 8);
        $count = intval(substr($num, 8));
        $day = (strtotime($date)-strtotime($begin))/(24*3600);
        $vd = base_convert($day, 10, 32);
        $vc = base_convert($count, 10, 32);
        return strtoupper('A' . str_repeat('0', 3-strlen($vd)) . $vd . str_repeat('0', 2-strlen($vc)) . $vc);
    }
}

if(! function_exists('_pton')) {
    function _pton($pid)  {
        $begin = '2015-01-09';
        $vd = substr($pid, 1, 3);
        $vc = substr($pid, 4);
        $count = base_convert($vc, 32, 10);
        $day = base_convert($vd, 32, 10);
        return date('Ymd', strtotime($begin)+$day*24*3600) . str_repeat('0', 6-strlen($count)) . $count;
    }
}

if(! function_exists('_col')) {
    function _col($table, $col)  {
        return $table . '.' . $col;
    }
}

if(! function_exists('_order_id')) {
    function _order_id($type, $oid, $period=0) {
        $time = date('YmdHis');
        $orderID = str_repeat('0', 10-strlen($oid)).$oid;
        $orderID .= str_repeat('0', 3-strlen($period)).$period;
        if($type=='pay') {
            $orderID = 'P'.$time.$orderID;
        } else if($type=='bail') {
            $orderID = 'B'.$time.$orderID;
        } else if($type=='end') {
            $orderID = str_repeat('0', 10-strlen($oid)).$oid;
            $orderID = 'E'.$time.$orderID;
        }
        return $orderID;
    }
}
 
