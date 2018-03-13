<?php
namespace helpers;

/**
 * NetworkHelper
 * 网络数据传输帮助类，用于传输数据。
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class NetworkHelper {

        /**izbp1hmy7jgsk90ez97rc8z
     * 模拟提交https 数据
     * @param type $url
     * @param type $data
     * @return type
     */
        public static Function CurlPost($url, $data, $header = array('Expect:'), $cmd = '1') { // 模拟提交数据函数
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header); //设置header
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        if ($cmd) {
            curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        }
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
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

	/**
	 * http协议(post|get)传输数据
	 * @param  string $url       网络地址
	 * @param  array  $data      需要传输的数据
	 * @param  string $type 	 类型
	 * @return mixed             返回结果
	 */
	public static function curlRequest($url,$data=array(),$type='get') {
		$requestType = strtolower($type);
		if($requestType!='get'&&$requestType!='post') {
			exit(0);
		}
		$ch = curl_init();
		//设置选项，包括url
		$agent = isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']
			:'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.16 Safari/537.36';
		
		if(strpos($url, 'https')===0) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:')); //设置header
		    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
		    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
		}

		curl_setopt($ch, CURLOPT_USERAGENT, $agent);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		if($type=='post') {
			$post = '';
			while (list($k,$v) = each($data)) {
				$post .= rawurlencode($k)."=".rawurlencode($v)."&";	//转URL标准码
			}
			$post = substr( $post , 0 , -1 );
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
		} else {
			$pos = strpos($url, '?');
			$hasParams = true;
			if($pos===false) {
				$hasParams = false;
			}
			$i = 0;
			$requestUrl = $url;
			$get = '';
			while (list($k,$v) = each($data)) {
				$get .= rawurlencode($k)."=".rawurlencode($v)."&";	//转URL标准码
			}
			$get = substr( $get , 0 , -1 );
			
			if($hasParams) {
				$requestUrl .= '&' . $get;
			} else {
				$requestUrl .= '?' . $get;
			}
			
			curl_setopt($ch, CURLOPT_URL, $requestUrl);
		}


		//执行并获取html内容
		$output = curl_exec($ch);
		//释放curl句柄
		curl_close($ch);
		return $output;
	}

	/**
	 * http及https协议post传输数据
	 * @param  string $url       网络地址
	 * @param  array  $dataArray 需要post的数据
	 * @param  array  $files 	文件
	 * @return mixed             返回结果
	 */
	public static function post($url,$dataArray,$files=[]) {
		$array = explode(':', $url);
		if($array[0]=='http') {
			return self::simplePost($url,$dataArray,$files);
		} else if($array[0]=='https') {
			return self::httpsPost($url,$dataArray,$files);
		} else {
			die('暂不支持该传输协议！');
		}
	}

	/**
	 * http协议post传输数据
	 * @param  string $url       网络地址
	 * @param  array  $dataArray 需要post的数据
	 * @param  array  $files 	文件
	 * @return mixed             返回结果
	 */
	public static function simplePost($url,$dataArray,$files=[]) {
		$data = $dataArray;
		foreach ($files as $file) {
			$data[$file['postName']] = new \CurlFile($file['path'], $file['type'], $file['name']);
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$response=curl_exec($ch);
		if(curl_errno($ch)){
			echo 'error';
			print curl_error($ch);
		}
		curl_close($ch); //返回
		return $response;
	}

	/**
	 * http协议post传输数据
	 * @param  string $url       网络地址
	 * @param  array  $dataArray 需要post的数据
	 * @param  array  $files 	文件
	 * @return mixed             返回结果
	 */
	public static function jsonPost($url, $data) {
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_POST, 1);                  
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
	    curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8','Content-Length:' . strlen($data)));
		
		$result = curl_exec($curl);

		curl_close($curl);

		return $result;
	}

	/**
	 * https协议post传输数据
	 * @param  string $url       网络地址
	 * @param  array  $dataArray 需要post的数据
	 * @param  array  $files 	文件
	 * @return mixed             返回结果
	 */
	public static function httpsPost($url,$dataArray,$files=[]) {
		$agent = isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']
			:'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.16 Safari/537.36';
		$data = $dataArray;
		foreach ($files as $file) {
			$data[$file['postName']] = new \CurlFile($file['path'], $file['type'], $file['name']);
		}
		$curl = curl_init(); // 启动一个CURL会话
	    curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
	    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Expect:')); //设置header
	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
	    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
	    curl_setopt($curl, CURLOPT_USERAGENT, $agent); // 模拟用户使用的浏览器
	    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
	    curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
	    curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
	    curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
	    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
	    curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
	    $response = curl_exec($curl); // 执行操作
	    if (curl_errno($curl)) {
	    	echo 'error';
	        print curl_error($curl); //捕抓异常
	    }
	    curl_close($curl); // 关闭CURL会话
	    return $response; // 返回数据
	}

	public static function postTwo($url, $data){ 
		$postDataString = http_build_query($data);//格式化参数

		$curl = curl_init(); // 启动一个CURL会话
		curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在		
		curl_setopt($curl, CURLOPT_POST, true); // 发送一个常规的Post请求
		curl_setopt($curl, CURLOPT_POSTFIELDS, $postDataString); // Post提交的数据包
		curl_setopt($curl, CURLOPT_TIMEOUT, 60); // 设置超时限制防止死循环返回
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
			
		$info = curl_exec($curl); // 执行操作
		if (curl_errno($curl)) {
			echo 'error';
	        $info = curl_error($curl);//捕抓异常
		}
		curl_close($curl); // 关闭CURL会话
		return $info; // 返回数据
    }

	/**
	 * 通过fsock传输数据
	 * @param  string $url       网络地址
	 * @param  array  $data 	 需要post的数据
	 * @return mixed             返回结果
	 */
	public static function fsPost($url,$data=array()){
		$row = parse_url($url);
		$host = isset($row['host'])?$row['host']:'';
		$port = isset($row['port'])?$row['port']:80;
		$file = isset($row['path'])?$row['path']:'';
		$post = '';
		while (list($k,$v) = each($data)) {
			$post .= rawurlencode($k)."=".rawurlencode($v)."&";	//转URL标准码
		}
		$post = substr( $post , 0 , -1 );
		$len = strlen($post);
		$fp = @fsockopen( $host ,$port, $errno, $errstr, 10);
		if (!$fp) {
			return "$errstr ($errno)\n";
		} else {
			$receive = '';
			$out = "POST $file HTTP/1.1\r\n";
			$out .= "Host: $host\r\n";
			$out .= "Content-type: application/x-www-form-urlencoded\r\n";
			$out .= "Connection: Close\r\n";
			$out .= "Content-Length: $len\r\n\r\n";
			$out .= $post;		
			fwrite($fp, $out);
			while (!feof($fp)) {
				$receive .= fgets($fp, 128);
			}
			fclose($fp);
			$receive = explode("\r\n\r\n",$receive);
			unset($receive[0]);
			return implode("",$receive);
		}
	}

	/**
	 * 通过soap传输数据, PHP需要开启soap扩展模块
	 * @param  string $url       网络地址
	 * @param  array  $data 	 需要传输的数据
	 * @return mixed             返回结果
	 */
	public static function soapMsg($url, $data) {
		try {
            $client = new \SoapClient($url, array('encoding' => 'UTF-8'));
            $result = $client->sendBatchMessage($data);
            return $result->sendBatchMessageReturn;
        } catch (\SOAPFault $e) {
            return false;
        }
	}

	/**
	 * 通过socket传输数据
	 * @param  string  $ip       网络IP
	 * @param  string  $port     端口
	 * @param  string  $string 	 需要传输的数据
	 * @param  string  $bug 	 是否调试
	 * @return mixed             返回结果
	 */
	public static function socketPost($ip, $port, $string, $bug = '0') {
	    set_time_limit(0);
	    $msg = ""; //反馈信息
	    if (!empty($string)) {
	        $in = trim($string);
	        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	        if ($socket < 0) {
	            echo "socket_create() failed: reason: " . socket_strerror($socket) . "\n";
	        } else {
	            if ($bug)
	                echo "OK.\n";
	        }
	        if ($bug)
	            echo "试图连接 '$ip' 端口 '$port'...\n";
	        $result = socket_connect($socket, $ip, $port);
	        if ($result < 0) {
	            echo "socket_connect() failed.\nReason: ($result) " . socket_strerror($result) . "\n";
	        } else {
	            if ($bug) {
	                echo "连接OK\n";
	            }
	        }
	        if (!socket_write($socket, $in, strlen($in))) {
	            echo "socket_write() failed: reason: " . socket_strerror($socket) . "\n";
	        } else {
	            //echo $in."   ok\n";
	        }
	        $out = "";
	        while ($out = socket_read($socket, 8192)) {
	            $msg = $out;
	        }
	        if ($bug) {
	            echo "关闭SOCKET...\n";
	        }
	        socket_close($socket);
	        if ($bug) {
	            echo "关闭OK\n";
	        }
	    }
	    return $msg;
	}
}
