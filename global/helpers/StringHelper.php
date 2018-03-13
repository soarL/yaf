<?php
namespace helpers;

/**
 * StringHelper
 * 字符串帮助类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class StringHelper {
	const ENCRYPT_KEY = 'xiaovshidai';
	public static function modelToTable($modelName) {
		return strtolower(preg_replace('/((?<=[a-z])(?=[A-Z]))/', '_', $modelName));
	}

	 /**
     * 生成随机字符串，用作where参数的临时储存
     * @param  string $prefix 前缀
     * @return string         生成的key
     */
    public static function generateRandomString($length) {
        $prepareStrArr = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','0','1','2','3','4','5','6','7','8','9'];
        $count = count($prepareStrArr);
        $randomStr = '';
        for ($i=0; $i < $length; $i++) { 
            $position = rand(0,$count-1);
            $randomStr .= $prepareStrArr[$position];
        }
        return $randomStr;
    }

    /**
     * 加密
     * @param  string $string    要加密的字符串
     * @param  string $operation D|E, 解密D或者加密E
     * @param  string $key       加密密钥
     * @return string            加密后的字符串
     */
    public static function encrypt($string,$operation,$key=self::ENCRYPT_KEY) {
        $key=md5($key);
        $key_length=strlen($key);
        $string=$operation=='D'?base64_decode($string):substr(md5($string.$key),0,8).$string;
        $string_length=strlen($string);
        $rndkey=$box=array();
        $result='';
        for($i=0;$i<=255;$i++) {
            $rndkey[$i]=ord($key[$i%$key_length]);
            $box[$i]=$i;
        }
        for($j=$i=0;$i<256;$i++) {
            $j=($j+$box[$i]+$rndkey[$i])%256;
            $tmp=$box[$i];
            $box[$i]=$box[$j];
            $box[$j]=$tmp;
        }
        for($a=$j=$i=0;$i<$string_length;$i++) {
            $a=($a+1)%256;
            $j=($j+$box[$a])%256;
            $tmp=$box[$a];
            $box[$a]=$box[$j];
            $box[$j]=$tmp;
            $result.=chr(ord($string[$i])^($box[($box[$a]+$box[$j])%256]));
        }
        if($operation=='D') {
            if(substr($result,0,8)==substr(md5(substr($result,8).$key),0,8))
            {
                return substr($result,8);
            }
            else
            {
                return'';
            }
        } else {
            return str_replace('=','',base64_encode($result));
        }
    }

    public static function utfSubstr($str, $start=0, $len=0) {
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

    public static function getUtfStrLength($str) {
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

    public static function newsTitle($title, $length=18) {
        $newTitle = self::utfSubstr($title, 0, $length);
        if($newTitle==$title) {
            return $title;
        } else {
            return $newTitle.'...';
        }
    }

    public static function getHideName($name) {
        if(!$name) {
            return '';
        }
        return substr($name, 0, 3) . '**';
    }

    public static function getHideUsername($username) {
        if(!$username) {
            return '';
        }
        $length = self::getUtfStrLength($username);
        if($length<=2) {
            return '**'.self::utfSubstr($username, 1);
        } else if($length>2&&$length<=3) {
            return '**'.self::utfSubstr($username, 2);
        } else if($length>3&&$length<5) {
            return '***'.self::utfSubstr($username, 3);
        } else {
            return '****'.self::utfSubstr($username, 4);
        }
    }

    public static function getHideCardnum($cardnum) {
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
            $cardnumBegin = substr($cardnum, 0, 3);
            $cardnumEnd = substr($cardnum, 14);
            $hideCardnum = $cardnumBegin . '****' . $cardnumEnd;
        }
        return $hideCardnum;
    }

    public static function getHidePhone($phone) {
        if(!$phone) {
            return '';
        }
        return substr($phone, 0, 3) . '****' . substr($phone, 7);
    }

    public static function getHideEmail($email) {
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

    public static function filterCensorWord($inputWord) {
        $censorWords = \Data::getFileContent('CensorWords.txt');
        $censorWordList = explode("\n", $censorWords);
        $censorWordList1 = [];
        foreach ($censorWordList as $word) {
            $wordArr = explode('=', $word);
            if(count($wordArr)>1) {
                $censorWordList1[$wordArr[0]] = $wordArr[1];
            } else {
                $censorWordList1[$wordArr[0]] = '***';
            }
        }
        return strtr($inputWord, $censorWordList1);
    }

    /**
     * 除去数组中的空值和签名参数
     * @param   array   $params   签名参数组
     * @return  array             去掉空值与签名参数后的新签名参数组
     */
    public static function paramsFilter($params, $signKey='sign') {
        $params_filter = array();
        while (list ($key, $val) = each ($params)) {
            if($key == $signKey || $val == '') {
                continue;
            } else {
                $params_filter[$key] = $params[$key];
            }
        }
        return $params_filter;
    }

    /**
     * 对数组排序
     * @param  array   $params  排序前的数组
     * @return  array            排序后的数组
     */
    public static function paramsSort($params, $isDelEmpty=false) {
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

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * @param   array   $params     需要拼接的数组
     * @return  string              拼接完成以后的字符串
     */
    public static function createLinkString($params) {
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

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
     * @param   array  $params  需要拼接的数组
     * @return  string          拼接完成以后的字符串
     */
    public static function createLinkStringUrlencode($params) {
        $arg  = "";
        while (list ($key, $val) = each ($params)) {
            if(is_array($val)) {
                $val = implode(',', $val);
            }
            $arg.=$key."=".urlencode($val)."&";
        }
        //去掉最后一个&字符
        $arg = substr($arg,0,count($arg)-2);
        
        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
        
        return $arg;
    }

    /**RSA签名
     * $data签名数据(需要先排序，然后拼接)
     * 签名用商户私钥，必须是没有经过pkcs8转换的私钥
     * 最后的签名，需要用base64编码
     * return Sign签名
     */
    public static function rsaSign($data, $priKey, $type=OPENSSL_ALGO_SHA1) {
        //转换为openssl密钥，必须是没有经过pkcs8转换的私钥
        $res = openssl_get_privatekey($priKey);
        //调用openssl内置签名方法，生成签名$sign
        openssl_sign($data, $sign, $res, $type);

        //释放资源
        openssl_free_key($res);
        
        //base64编码
        $sign = base64_encode($sign);
        //file_put_contents("log.txt","签名原串:".$data."\n", FILE_APPEND);
        return $sign;
    }

    /**RSA验签
     * $data待签名数据(需要先排序，然后拼接)
     * $sign需要验签的签名,需要base64_decode解码
     * 验签用连连支付公钥
     * return 验签是否通过 bool值
     */
    public static function rsaVerify($data, $sign, $pubKey, $type=OPENSSL_ALGO_SHA1)  {
        //转换为openssl格式密钥
        $res = openssl_get_publickey($pubKey);

        //调用openssl内置方法验签，返回bool值
        $result = (bool)openssl_verify($data, base64_decode($sign), $res, $type);
        
        //释放资源
        openssl_free_key($res);

        //返回资源是否成功
        return $result;
    }
    
    /**
     * cer, pfx签名
     * @param  string $data     需要加密的数据
     * @param  string $pkcs12   私钥
     * @param  string $password 私钥密码
     * @return string           签名数据
     */
    public static function pkcs12Sign($data, $pkcs12, $password) {
        $privateKey = '';
        $signed = '';

        if(openssl_pkcs12_read($pkcs12, $certs, $password)) {
            $privateKey = $certs['pkey'];
        }
        return self::rsaSign($data, $privateKey, OPENSSL_ALGO_SHA1);
    }

    /**
     * 宝付支付加密
     * @param  string $data     需要加密的数据
     * @param  string $pkcs12   私钥
     * @param  string $password 私钥密码
     * @return string           签名数据
     */
    public static function bfSign($data, $pkcs12, $password) {
        $content = base64_encode($data);
        $privateKey = '';
        if(openssl_pkcs12_read($pkcs12, $certs, $password)) {
            $privateKey = $certs['pkey'];
        }
        $length = 32;
        $encrypted = '';
        $totalLen = strlen($content);
        $encryptPos = 0;
        while ($encryptPos < $totalLen){
            openssl_private_encrypt(substr($content, $encryptPos, $length), $encryptData, $privateKey);
            $encrypted .= bin2hex($encryptData);
            $encryptPos += $length;
        }
        return $encrypted;
    }

    /**
     * 宝付错误信息转码
     * @param  [type]
     * @return [type]
     */
    public static function bfReMsg($msg){
        $msg = str_replace('报文交易要素格式错误:', '', $msg);
        return $msg;
    }

    /**
     * 宝付支付解密
     * @param  string $data     需要解密的数据
     * @return string           解密数据
     */
    public static function bfVerify($encrypted, $publicKey){
        // $decrypt = "";
        // $length = 32;
        // $totalLen = strlen($encrypted);
        // $decryptPos = 0;
        // $pubKey = openssl_get_publickey($publicKey);
        // while ($decryptPos < $totalLen) {
        //     openssl_public_decrypt(hex2bin(substr($encrypted, $decryptPos, $length * 8)), $decryptData, $pubKey);
        //     $decrypt .= $decryptData;
        //     $decryptPos += $length * 8;
        // }
        // //openssl_public_decrypt($encrypted, $decryptData, $this->public_key);
        // $decrypt = base64_decode($decrypt);
        // return $decrypt;

        $decrypt = "";
        $totalLen = strlen($encrypted);
        $pubKey = openssl_get_publickey($publicKey);
        $decryptPos = 0;
        while ($decryptPos < $totalLen) {
            openssl_public_decrypt(hex2bin(substr($encrypted, $decryptPos, 256)), $decryptData, $pubKey);
            $decrypt .= $decryptData;
            $decryptPos += 256;
        }
        $decrypt=base64_decode($decrypt);
        return $decrypt;
    }

    /**
     * 上传图片处理
     * @param  string $images 图片字符串
     * @param  string $type   返回类型 all|min|max|normal
     * @return array          图片数组
     */
    public static function decodeImages($images, $type='all') {
        if(!$images||$images=='') {
            return [];
        }
        $imageList = explode('|', trim($images, '|'));
        if($type=='max'||$type=='normal') {
            return $imageList;
        }
        $thumbnailList = [];
        foreach ($imageList as $key => $image) {
            $list = explode('/', $image);
            $name = end($list);
            $thumbnail = str_replace($name, 'thumbnail/' . $name, $image);

            $row['max'] = $image;
            $row['min'] = $thumbnail;
            $row['normal'] = $image;
            $imageList[$key] = $row;
            $thumbnailList[] = $row['min'];
        }
        if($type=='min') {
            return $thumbnailList;
        } else {
            return $imageList;
        }
    }

    /**
     * 删除图片
     * @param  string $image 图片名称
     * @return mixed
     */
    public static function deleteImage($image) {
        $imageFolder = 'uploads/images/';
        $thumbnailFolder = 'thumbnail';
        $imageRow = explode('/', $image);
        $thumbnail = $imageRow[0] . '/' . $thumbnailFolder . '/' . $imageRow[1];
        
        $imageB = $imageFolder.$image;
        if(file_exists($imageB)) {
            unlink($imageB);
        }
        $imageS = $imageFolder.$thumbnail;
        if(file_exists($imageS)) {
            unlink($imageS);
        }
    }

    /**
     * 通过身份证号获取生日
     * @param  string $cardnum  身份证号
     * @return string           生日
     */
    public static function getBirthdayByCardnum($cardnum) {
        $length = strlen($cardnum);
        $birthday = '';
        if ($length == 18) {
            $birthday = substr($cardnum, 6, 4) . '-' . substr($cardnum, 10, 2) . '-' . substr($cardnum, 12, 2);
        } else {
            $birthday = '19' . substr($cardnum, 6, 2) . '-' . substr($cardnum, 8, 2) . '-' . substr($cardnum, 10, 2);
        }
        return $birthday;
    }

    /**
     * 通过身份证号获取性别
     * @param  string $cardnum  身份证号
     * @return string           性别
     */
    public static function getSexByCardnum($cardnum) {
        $length = strlen($cardnum);
        $sexNumber = '';
        if ($length == 18) {
            $sexNumber = substr($cardnum, 16, 1);
        } else {
            $sexNumber = substr($cardnum, 14, 1);
        }
        if($sexNumber%2==0) {
            return 'women';
        } else {
            return 'man';
        }
    }

    /**
     * 通过生日获取年龄
     * @param  string $cardnum  生日
     * @return int              年龄
     */
    public static function getAgeByBirthday($birthday) {
        $timestamp = strtotime($birthday);
        $year = intval(date('Y', $timestamp));
        $month = intval(date('m', $timestamp));
        $day = intval(date('d', $timestamp));
        $nowYear = intval(date('Y'));
        $nowMonth = intval(date('m'));
        $nowDay = intval(date('d'));
        $age = $nowYear - $year;
        if($month>$nowMonth) {
            $age = $age - 1;
        } else {
            if($month==$nowMonth) {
                if($day>$nowDay) {
                    $age = $age - 1;
                }
            }
        }
        return $age;
    }

    //将IP转换为数字
    public static function ipton($ip) {
        $ipArr = explode('.',$ip);//分隔ip段
        $ipstr = '';
        foreach ($ipArr as $value) {
            $iphex = dechex($value); //将每段ip转换成16进制
            //255的16进制表示是ff，所以每段ip的16进制长度不会超过2
            if(strlen($iphex)<2) {
                $iphex = '0' . $iphex;//如果转换后的16进制数长度小于2，在其前面加一个0
                //没有长度为2，且第一位是0的16进制表示，这是为了在将数字转换成ip时，好处理
            }
            $ipstr .= $iphex;//将四段IP的16进制数连接起来，得到一个16进制字符串，长度为8
        }
        return hexdec($ipstr);//将16进制字符串转换成10进制，得到ip的数字表示
    }
     
     
    //将数字转换为IP，进行上面函数的逆向过程
    public static function ntoip($n) {
        $iphex = dechex($n);//将10进制数字转换成16进制
        $len = strlen($iphex);//得到16进制字符串的长度
        if(strlen($iphex) < 8) {
            $iphex = '0'.$iphex;//如果长度小于8，在最前面加0
            $len = strlen($iphex); //重新得到16进制字符串的长度
        }
        //这是因为ipton函数得到的16进制字符串，如果第一位为0，在转换成数字后，是不会显示的
        //所以，如果长度小于8，肯定要把第一位的0加上去
        //为什么一定是第一位的0呢，因为在ipton函数中，后面各段加的'0'都在中间，转换成数字后，不会消失
        for($i=0,$j=0; $j<$len; $i=$i+1,$j=$j+2) {
            //循环截取16进制字符串，每次截取2个长度
            $ippart = substr($iphex, $j, 2);//得到每段IP所对应的16进制数
            $fipart = substr($ippart, 0, 1);//截取16进制数的第一位
            //如果第一位为0，说明原数只有1位
            if($fipart == '0') {
                $ippart = substr($ippart, 1, 1);//将0截取掉
            }
            $ip[] = hexdec($ippart);//将每段16进制数转换成对应的10进制数，即IP各段的值
        }
        $ip = array_reverse($ip);
        return implode('.', $ip);//连接各段，返回原IP值
    }

    public static function getUrlParam($url, $name) {
        $params = self::getUrlParams($url);
        return isset($params[$name])?$params[$name]:null;
    }

    public static function getUrlParams($url) {
        $info = parse_url($url);
        $list = explode('&', $url);
        $params = [];
        foreach ($list as $item) {
            $row = explode('=', $item);
            $params[$row[0]] = isset($row[1])?$row[1]:'';
        }
        return $params;
    }

    /**
     * 根据银行卡号获取所属银行
     * @param  string $card 银行卡号
     * @return mixed        返回所属银行，未识别返回false
     */
    public static function getBankName($card) {
        $bankList = \Data::get('bankICList');

        $icCode = substr($card, 0, 8);
        if (isset($bankList[$icCode])) {
            return $bankList[$icCode];
        }
        $icCode = substr($card, 0, 6);
        if (isset($bankList[$icCode])) {
            return $bankList[$icCode];
        }
        $icCode = substr($card, 0, 5);
        if (isset($bankList[$icCode])) {
            return $bankList[$icCode];
        }
        $icCode = substr($card, 0, 4);
        if (isset($bankList[$icCode])) {
            return $bankList[$icCode];
        }
        return false;
    }

    public static function encodeQueryString($params, $ue=true) {
        $list = [];
        foreach ($params as $key => $value) {
            $list[] = $key . '=' .$value;
        }
        if($ue) {
            return urlencode(implode('&', $list));
        } else {
            return implode('&', $list);  
        }
    }

    public static function decodeQueryString($string) {
        $str = urldecode($string);
        $list = explode('&', $str);
        $params = [];
        foreach ($list as $item) {
            $row = explode('=', $item);
            if(isset($row[0])) {
                $params[$row[0]] = isset($row[1])?$row[1]:'';
            }
        }
        return $params;
    }

    public static function l2uNum($num){
        $num = $num - '2000000000';
        $da_num=array('零','一','二','三','四','五','六','七','八','九');
        $ren='签';  
        $len_num = strlen($num);  
        for($i=0;$i<$len_num;$i++){  
            //substr() 函数返回字符串的一部分。  
            $item = substr($num,$i,1);
            $ren.= $da_num[$item];  
        }
        return $ren.'章';
    }

    public static function msubstr($fStr, $fStart, $fLen, $fCode = 'utf-8') {
        $fCode = strtolower($fCode);
        switch ($fCode) {
            case 'utf8':
            case 'utf-8':
                preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $fStr, $ar);
                $i = 0;
                $aC = count($ar[0]);
                foreach ($ar[0] as $value) {
                    $i++;
                    if (ord($value) > 127) {
                        if (1 == strlen($fStr)) {
                            $ar[1][] = $fStr . " ";
                            $result = "";
                        }
                        $ar[1][] = $value;
                    } else {
                        $result .= htmlspecialchars($value);
                        if (strlen($result) > 1) {
                            $ar[1][] = $result;
                            $result = "";
                        } elseif ($i == $aC) {
                            $ar[1][] = $result;
                        }
                    }
                }
                if (func_num_args() >= 3) {
                    if (count($ar[1]) > $fLen) {
                        return @join("", array_slice($ar[1], $fStart, $fLen)) . "..";
                    }
                    return @join("", array_slice($ar[1], $fStart, $fLen));
                } else {
                    return @join("", array_slice($ar[1], $fStart));
                }
                break;
            default:
                $fStart = $fStart * 2;
                $fLen = $fLen * 2;
                $strlen = strlen($fStr);
                $tmpstr = '';
                for ($i = 0; $i < $strlen; $i++) {
                    if ($i >= $fStart && $i < ( $fStart + $fLen )) {
                        if (ord(substr($fStr, $i, 1)) > 129)
                            $tmpstr .= substr($fStr, $i, 2);
                        else
                            $tmpstr .= substr($fStr, $i, 1);
                    }
                    if (ord(substr($fStr, $i, 1)) > 129)
                        $i++;
                }
                if (strlen($tmpstr) < $strlen)
                    $tmpstr .= "...";
                Return $tmpstr;
        }
    }
}