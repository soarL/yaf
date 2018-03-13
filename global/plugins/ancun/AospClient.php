<?php
namespace plugins\ancun;

class AospClient {

	private $apiAddress;
	private $partnerKey;
	private $secret;

	function __construct($apiAddress, $partnerKey, $secret) {
		$this->apiAddress = $apiAddress;
		$this->partnerKey = $partnerKey;
		$this->secret = $secret;
	}
	
	/**
	 * 数据保全
	 */
	function save($aospRequest) {
		$apiHost = "/save";
		$aospResponse = $this->createHttpPostAll($this->apiAddress, $apiHost, $this->partnerKey, $this->secret, $aospRequest);
		return $aospResponse;
	}

	function send($url, $param, $method = 'POST') {
		$apiAddress = $this->apiAddress;
		$partnerKey = $this->partnerKey;
		$aospResponse = new AospResponse();
		$ContentType = "application/x-www-form-urlencoded";
		$ch = curl_init();
		$date = date('Ymdhis', time());

		$common = array (
			'action' => $url,
			'reqtime' => $date
		);
		$request = array (
			'common' => $common,
			'content' => array (
				$param
			)
		);
		$arrbody = array (
			'request' => $request
		);
		$body = json_encode($arrbody);
		$reqlength = strlen($body);
		$header = array (
			"Content-Type:$ContentType",
			"reqtime:$date",
			"reqlength:$reqlength",
			"partnerKey:$partnerKey",
			"sdkversion:php_1.0.0"
		);
		$postParams = http_build_query($param);
		//请求不同
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		if ($method == "POST") {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postParams);
		} else
			if ($method == "GET") {
				$url += "?" + $postParams;
			}
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($ch);
		$curl_errno = curl_errno($ch);
		$curl_error = curl_error($ch);
		curl_close($ch);
		if ($curl_errno > 0) {
			$aospResponse->setMsg("系统维护中");
			$aospResponse->setCode(-1);
		} else {
			$data_content = json_decode($data, true);
			$aospResponse->setMsg($data_content['msg']);
			$aospResponse->setCode($data_content['code']);
			$aospResponse->setData($data_content['data']);
		}
		return $aospResponse;
	}
	function createHttpPostAll($apiAddress, $apiHost, $partnerKey, $secret, $aospRequest) {

		$aospResponse = new AospResponse();
		$data = "";
		$length=0;
		$boundary = substr(md5(time()), 8, 16);
		$bussinsesData = $aospRequest->getData();
		$reqdata = json_encode($bussinsesData);
		$md5 = md5($reqdata);
		$aospRequest->setMd5($md5);
		$content_array = (array) $aospRequest;
		$contents = json_encode($content_array);
		$aospFiles = $aospRequest->getList();
		if ($aospFiles == null) {
			$data .= "\r\n{$contents}\r\n";
			$ContentType = "application/json;charset=utf-8";
		} else {
			$data .= "--{$boundary}\r\n";
			$data .= "Content-Disposition: form-data; name=\"content\"\r\n";
			$data .= "\r\n{$contents}\r\n";
			$data .= "--{$boundary}\r\n";
			$ContentType = "multipart/form-data; charset=utf-8;boundary=" . $boundary;
			foreach ($aospFiles as $aospFile) {

				if ($aospFile->getFile() != null) {
					$file = $aospFile->getFile();

				} else {
					if ($aospFile->getFileFullPath() != null) {
						$file = $aospFile->getFileFullPath();
					}
				}
				$filename = basename($file);
				if (file_exists(iconv('UTF-8', 'gbk', $file))) {
					$length = filesize($file);
					$suffix = substr(strrchr($file, '.'), 1); //文件类型  
					$fileNameFAosp = urlencode($aospFile->getFileName() . "." . $suffix);
					if ($aospFile->getEncryptionAlgorithm() != null) {
						$fileNameFAosp = $fileNameFAosp . "_" . "encryptionAlgorithm" . $aospFile->getEncryptionAlgorithm();
					}
					$filestring = @ file_get_contents(iconv('UTF-8', 'gbk', $file));
					//$fileMd5=md5($filestring);
					$data .= "--{$boundary}\r\n";
					$data .= "Content-Disposition: form-data; name=\"$fileNameFAosp\"; filename=\"$filename\"\r\n";
					$data .= "Content-Type: $suffix\r\n";
					$data .= "\r\n$filestring\r\n";
				} else {
					$suffix = substr(strrchr($file, '.'), 1); //文件类型 
					// $filename = $aospFile->getFileName();
					$handle = fopen($file, "rb");
					// $length = filesize($file);
					$filestring = stream_get_contents($handle);
					$length = strlen($filestring);
					$data .= "--{$boundary}\r\n";
					$data .= "Content-Disposition: form-data; name=\"$filename\"; filename=\"$filename\"\r\n";
					$data .= "Content-Type: $suffix\r\n";
					$data .= "\r\n$filestring\r\n";
					fclose($handle);
					if ($length<0) {
						$aospResponse->setMsg("保全附件" . $filename . "不存在,请选择正确的附件");
						$aospResponse->setCode(110065);
						return $aospResponse;
					}
				}
			}
			$data .= "\r\n--{$boundary}--\r\n";
		}
		$url = $apiAddress . $apiHost;
		$ch = curl_init();
		$date = date('Ymdhis', time());
		$header = array (
			"Content-Type:$ContentType",
			"reqtime:$date",
			"reqlength:$length",
			"partnerKey:$partnerKey",
			"sdkversion:php_1.0.0"
		);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //设置头信息的地方  
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_NOSIGNAL, 1); //注意，毫秒超时一定要设置这个 
		curl_setopt($ch, CURLOPT_TIMEOUT_MS, 60000); //超时毫秒，cURL7.16.2中被加入。从PHP5.2.3起可使用 
		$data = curl_exec($ch);
		$curl_errno = curl_errno($ch);
		$curl_error = curl_error($ch);
		curl_close($ch);
		if ($curl_errno > 0) {
			$aospResponse->setMsg("系统维护中");
			$aospResponse->setCode(100010);
		} else {
			$data_content = json_decode($data, true);
			$aospResponse->setMsg($data_content['msg']);
			$aospResponse->setCode($data_content['code']);
			$rdata = isset($data_content['data'])?$data_content['data']:[];
			$aospResponse->setData($rdata);
		}
		return $aospResponse;
	}

	/**
	 * 制作个人用户的印章
	 */
	function awardCaForPersonal($aospRequest) {
		$apiHost = "/awardCaForPersonal";
		$aospResponse = $this->createAwardCaForPersonal($this->apiAddress, $apiHost, $this->partnerKey, $this->secret, $aospRequest);
		return $aospResponse;
	}

	function createAwardCaForPersonal($apiAddress, $apiHost, $partnerKey, $secret, $aospRequest) {
		$aospResponse = new AospResponse();
		$data = "";
		$length=0;
		$boundary = substr(md5(time()), 8, 16);
		$bussinsesData = $aospRequest->getData();
		$reqdata = json_encode($bussinsesData);
		$data .= "\r\n{$reqdata}\r\n";
		$ContentType = "application/json;charset=utf-8";

		$url = $apiAddress . $apiHost;
		$ch = curl_init();
		$date = date('Ymdhis', time());
		$header = array (
			"Content-Type:$ContentType",
			"reqtime:$date",
			"reqlength:$length",
			"partnerKey:$partnerKey",
			"sdkversion:php_1.1.3"
		);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //设置头信息的地方  
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_NOSIGNAL, 1); //注意，毫秒超时一定要设置这个 
		curl_setopt($ch, CURLOPT_TIMEOUT_MS, 60000); //超时毫秒，cURL7.16.2中被加入。从PHP5.2.3起可使用 
		$data = curl_exec($ch);
		$curl_errno = curl_errno($ch);
		$curl_error = curl_error($ch);
		curl_close($ch);
		if ($curl_errno > 0) {
			$aospResponse->setMsg("系统维护中");
			$aospResponse->setCode(100010);
		} else {
			$data_content = json_decode($data, true);
			$aospResponse->setMsg($data_content['msg']);
			$aospResponse->setCode($data_content['code']);
			$aospResponse->setData($data_content['data']);
		}
		return $aospResponse;
	}

	/**
	 * 获取印章
	 */
	function obtainPersonSeal($aospRequest) {
		$apiHost = "/obtainPersonSeal";
		$aospResponse = $this->obtainSealForPersonal($this->apiAddress, $apiHost, $this->partnerKey, $this->secret, $aospRequest);
		return $aospResponse;
	}
	function obtainSealForPersonal($apiAddress, $apiHost, $partnerKey, $secret, $aospRequest) {

		$aospResponse = new AospResponse();
		$data = "";
		//$boundary = substr(md5(time()),8,16);
		$length=0;
		$bussinsesData = $aospRequest->getData();
		$reqdata = json_encode($bussinsesData);
		$data .= "\r\n{$reqdata}\r\n";
		$ContentType = "application/json;charset=utf-8";
		$url = $apiAddress . $apiHost;
		$ch = curl_init();
		$date = date('Ymdhis', time());
		$header = array (
			"Content-Type:$ContentType",
			"reqtime:$date",
			"reqlength:$length",
			"partnerKey:$partnerKey",
			"sdkversion:php_1.1.3"
		);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //设置头信息的地方  
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_NOSIGNAL, 1); //注意，毫秒超时一定要设置这个 
		curl_setopt($ch, CURLOPT_TIMEOUT_MS, 60000); //超时毫秒，cURL7.16.2中被加入。从PHP5.2.3起可使用 
		$data = curl_exec($ch);
		$curl_errno = curl_errno($ch);
		$curl_error = curl_error($ch);
		curl_close($ch);
		if ($curl_errno > 0) {
			$aospResponse->setMsg("系统维护中");
			$aospResponse->setCode(100010);
		} else {
			$data_content = json_decode($data, true);
			$aospResponse->setMsg($data_content['msg']);
			$aospResponse->setCode($data_content['code']);
			$aospResponse->setData($data_content['data']);
		}
		return $aospResponse;
	}

	/**
	 * 制作企业用户的印章
	 */
	function awardCaForCompany($aospRequest) {
		$apiHost = "/awardCaForCompany";
		$aospResponse = $this->awardCaCompanySeal($this->apiAddress, $apiHost, $this->partnerKey, $this->secret, $aospRequest);
		return $aospResponse;
	}

	function awardCaCompanySeal($apiAddress, $apiHost, $partnerKey, $secret, $aospRequest) {

		$aospResponse = new AospResponse();
		$data = "";
		$length=0;
		$boundary = substr(md5(time()), 8, 16);
		$bussinsesData = $aospRequest->getData();
		$reqdata = json_encode($bussinsesData);
		$md5 = md5($reqdata);
		$aospRequest->setMd5($md5);
		$content_array = (array) $aospRequest;
		$contents = json_encode($content_array);
		$aospFiles = $aospRequest->getList();
		if ($aospFiles == null) {
			$aospResponse->setMsg("印模不能为空");
			return $aospResponse;
		} else {
			$data .= "--{$boundary}\r\n";
			$data .= "Content-Disposition: form-data; name=\"content\"\r\n";
			$data .= "\r\n{$contents}\r\n";
			$data .= "--{$boundary}\r\n";
			$ContentType = "multipart/form-data; charset=utf-8;boundary=" . $boundary;
			if ($aospFiles . count() > 1) {
				$aospResponse->setMsg("印模只能为一个");
				return $aospResponse;
			}
			$aospFile = current($aospFiles);
			if ($aospFile->getFile() != null) {
				$file = $aospFile->getFile();

			} else {
				if ($aospFile->getFileFullPath() != null) {
					$file = $aospFile->getFileFullPath();
				}
			}
			$filename = basename($file);
			if (file_exists(iconv('UTF-8', 'gbk', $file))) {
				$length = filesize($file);
				$suffix = substr(strrchr($file, '.'), 1); //文件类型  
				$fileNameFAosp = urlencode($aospFile->getFileName() . "." . $suffix);
				if ($aospFile->getEncryptionAlgorithm() != null) {
					$fileNameFAosp = $fileNameFAosp . "_" . "encryptionAlgorithm" . $aospFile->getEncryptionAlgorithm();
				}
				$filestring = @ file_get_contents(iconv('UTF-8', 'gbk', $file));
				//$fileMd5=md5($filestring);
				$data .= "--{$boundary}\r\n";
				$data .= "Content-Disposition: form-data; name=\"$fileNameFAosp\"; filename=\"$filename\"\r\n";
				$data .= "Content-Type: $suffix\r\n";
				$data .= "\r\n$filestring\r\n";
			} else {
				$filename = $aospFile->getFileName();
				$handle = fopen($file, "rb");
				$contents = "";
				$length = filesize($file);
				$filestring = stream_get_contents($handle);
				$data .= "--{$boundary}\r\n";
				$data .= "Content-Disposition: form-data; name=\"$filename\"; filename=\"$filename\"\r\n";
				$data .= "Content-Type: $suffix\r\n";
				$data .= "\r\n$filestring\r\n";
				fclose($handle);
				if ($contents = "") {
					$aospResponse->setMsg("保全附件" . $filename . "不存在,请选择正确的附件");
					$aospResponse->setCode(110065);
					return $aospResponse;
				}
			}
			$data .= "\r\n--{$boundary}--\r\n";
		}
		$url = $apiAddress . $apiHost;
		$ch = curl_init();
		$date = date('Ymdhis', time());
		$header = array (
			"Content-Type:$ContentType",
			"reqtime:$date",
			"reqlength:$length",
			"partnerKey:$partnerKey",
			"sdkversion:php_1.0.0"
		);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //设置头信息的地方  
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_NOSIGNAL, 1); //注意，毫秒超时一定要设置这个 
		curl_setopt($ch, CURLOPT_TIMEOUT_MS, 60000); //超时毫秒，cURL7.16.2中被加入。从PHP5.2.3起可使用 
		$data = curl_exec($ch);
		$curl_errno = curl_errno($ch);
		$curl_error = curl_error($ch);
		curl_close($ch);
		if ($curl_errno > 0) {
			$aospResponse->setMsg("系统维护中");
			$aospResponse->setCode(100010);
		} else {
			$data_content = json_decode($data, true);
			$aospResponse->setMsg($data_content['msg']);
			$aospResponse->setCode($data_content['code']);
			$aospResponse->setData($data_content['data']);
		}
		return $aospResponse;
	}

	function remote_filesize($url, $user = "", $pw = "") {
    	ob_start();
	    $ch = curl_init($url);
	    curl_setopt($ch, CURLOPT_HEADER, 1);
	    curl_setopt($ch, CURLOPT_NOBODY, 1);

	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    // if auth is needed, do it here    
	    if (!empty($user) && !empty($pw)) {    
	        $headers = array('Authorization: Basic ' . base64_encode($user.':'.$pw));    
	        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);    
	    }
	    $okay = curl_exec($ch);
	    
	    curl_close($ch);

	    $head = ob_get_contents();    
	    // clean the output buffer and return to previous    
	    // buffer settings    
	    ob_end_clean();  
	    
	    // gets you the numeric value from the Content-Length    
	    // field in the http header    
	    $regex = '/Content-Length:\s([0-9].+?)\s/';    
	    $count = preg_match($regex, $head, $matches);    
	    
	    // if there was a Content-Length field, its value    
	    // will now be in $matches[1]    
	    if (isset($matches[1])) {    
	        $size = $matches[1];    
	    } else {    
	        $size = 'unknown';    
	    }    
	    //$last=round($size/(1024*1024),3);    
	    //return $last.' MB';
	    return $size;
	}
}