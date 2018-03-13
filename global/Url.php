<?php
class Url {
	public static function to($url, $params=array()) {
		$newUrl = self::generateRewrite($url, $params);
		if($newUrl=='') {
			$newUrl = self::generateRegex($url, $params);
			if($newUrl=='') {
				$newUrl = self::generateNormal($url, $params);
			}
		}
		return $newUrl;
	}

	public static function addParams($url, $params=array()) {
		$newUrl = rtrim($url,'?');
		$newUrl = rtrim($url,'&');
		if(strpos($newUrl, '?')===false) {
			$newUrl .= '?';
		} else {
			$newUrl .= '&';
		}
		foreach ($params as $key => $value) {
			$newUrl .= $key . '=' . $value . '&';
		}
		$newUrl = rtrim($newUrl,'?');
		$newUrl = rtrim($newUrl,'&');
		return $newUrl;
	}


	public static function generateNormal($url, $params) {
		$newUrl = $url;
		foreach ($params as $key => $value) {
			$newUrl .= '/' . $key . '/' . $value;
		}
		return $newUrl;
	}

	public static function generateRewrite($url, $params) {
		$newUrl = '';
		$routes = \Data::get('rewrite');
        $rewriteRoutes = $routes['rewrite'];
		foreach ($rewriteRoutes as $key => $rewriteRoute) {
			if('/'.$rewriteRoute['route']==$url) {
				$ruleArray = explode('/', $rewriteRoute['rule']);
				$uriParams = [];
				$isAllMatch = true;
				foreach ($ruleArray as $uriStr) {
					if(self::isParam($uriStr)) {
						$paramName = ltrim($uriStr, ':');
						$uriParams[] = $paramName;
						if(!isset($params[$paramName])) {
							$isAllMatch = false;
							$newUrl = '';
						} else {
							$newUrl .= '/' . $params[$paramName];
						}
					} else {
						$newUrl .= '/' . $uriStr;
					}
				}
				if(count($params)!=count($uriParams)) {
					$newUrl = '';
					$isAllMatch = false;
				}
				if($isAllMatch) {
					return $newUrl;
				}
			}
		}
		return $newUrl;
	}

	public static function generateRegex($url, $params) {
		$newUrl = '';
		$isAllMatch = false;
		$routes = \Data::get('rewrite');
        $regexRoutes = $routes['regex'];
        foreach ($regexRoutes as $key => $regexRoute) {
			if('/'.$regexRoute['route']==$url) {
				$urlStr = trim($regexRoute['rule'], '#');
				$count = preg_match_all('/\(.*?\)/', $urlStr, $matches);
				for($i=0; $i<$count; $i++) {
					$paramName = $i;
					if(isset($regexRoute['params'])&&isset($regexRoute['params'][$i])) {
						$paramName = $regexRoute['params'][$i];
					}
					$subRegex = '/' . trim(trim($matches[0][$i], '('), ')') . '/';
					if(isset($params[$paramName])&&preg_match($subRegex, $params[$paramName])) {
						$urlStr = preg_replace('/\(.*?\)/', $params[$paramName], $urlStr);
						$isAllMatch = true;
					} else {
						break;
					}
				}
				if($isAllMatch) {
					$newUrl = $urlStr;
					break;
				}
			}
		}
		if($isAllMatch) {
			return $newUrl;
		} else {
			return '';
		}
	}

	public static function isParam($uriStr) {
		if(substr($uriStr, 0, 1)==':') {
			return true;
		}
		return false;
	}
}