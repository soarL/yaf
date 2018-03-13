<?php
use Admin as Controller;
use plugins\ueditor\Config;
use plugins\ueditor\Uploader;

class UeditorController extends Controller {
	public $ueditorConfig = [];

	public function uploadAction() {
		$CONFIG = Config::get('../../app_user/public', WEB_ASSET);
		$this->ueditorConfig = $CONFIG;
		$action = $_GET['action'];
		switch ($action) {
		    case 'config':
		        $result =  json_encode($CONFIG);
		        break;
		    /* 上传图片 */
		    case 'uploadimage':
		    /* 上传涂鸦 */
		    case 'uploadscrawl':
		    /* 上传视频 */
		    case 'uploadvideo':
		    /* 上传文件 */
		    case 'uploadfile':
		        $result = $this->uploadFile($CONFIG);
		        break;

		    /* 列出图片 */
		    case 'listimage':
		    /* 列出文件 */
		    case 'listfile':
		        $result = $this->listFile($CONFIG);
		        break;

		    /* 抓取远程文件 */
		    case 'catchimage':
		        $result = $this->catchImage($CONFIG);
		        break;

		    default:
		        $result = json_encode(array(
		            'state'=> '请求地址出错'
		        ));
		        break;
		}
		/* 输出结果 */
		if (isset($_GET["callback"])) {
		    if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
		        echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
		    } else {
		        echo json_encode(array(
		            'state'=> 'callback参数不合法'
		        ));
		    }
		} else {
		    echo $result;
		}
	}

	private function uploadFile($param) {
		$base64 = "upload";
		switch (htmlspecialchars($_GET['action'])) {
		    case 'uploadimage':
		        $config = array(
		            "pathFormat" => $param['imagePathFormat'],
		            "maxSize" => $param['imageMaxSize'],
		            "allowFiles" => $param['imageAllowFiles'],
		            "preUrl" => $param['preUrl']
		        );
		        $fieldName = $param['imageFieldName'];
		        break;
		    case 'uploadscrawl':
		        $config = array(
		            "pathFormat" => $param['scrawlPathFormat'],
		            "maxSize" => $param['scrawlMaxSize'],
		            "allowFiles" => $param['scrawlAllowFiles'],
		            "oriName" => "scrawl.png",
		            "preUrl" => $param['preUrl']
		        );
		        $fieldName = $param['scrawlFieldName'];
		        $base64 = "base64";
		        break;
		    case 'uploadvideo':
		        $config = array(
		            "pathFormat" => $param['videoPathFormat'],
		            "maxSize" => $param['videoMaxSize'],
		            "allowFiles" => $param['videoAllowFiles'],
		            "preUrl" => $param['preUrl']
		        );
		        $fieldName = $param['videoFieldName'];
		        break;
		    case 'uploadfile':
		    default:
		        $config = array(
		            "pathFormat" => $param['filePathFormat'],
		            "maxSize" => $param['fileMaxSize'],
		            "allowFiles" => $param['fileAllowFiles'],
		            "preUrl" => $param['preUrl']
		        );
		        $fieldName = $param['fileFieldName'];
		        break;
		}
		
		/* 生成上传实例对象并完成上传 */
		$up = new Uploader($fieldName, $config, $base64);

		/**
		 * 得到上传文件所对应的各个参数,数组结构
		 * array(
		 *     "state" => "",          //上传状态，上传成功时必须返回"SUCCESS"
		 *     "url" => "",            //返回的地址
		 *     "title" => "",          //新文件名
		 *     "original" => "",       //原始文件名
		 *     "type" => ""            //文件类型
		 *     "size" => "",           //文件大小
		 * )
		 */

		/* 返回数据 */
		return json_encode($up->getFileInfo());
	}

	private function listFile($param) {
		switch ($_GET['action']) {
		    /* 列出文件 */
		    case 'listfile':
		        $allowFiles = $param['fileManagerAllowFiles'];
		        $listSize = $param['fileManagerListSize'];
		        $path = $param['fileManagerListPath'];
		        break;
		    /* 列出图片 */
		    case 'listimage':
		    default:
		        $allowFiles = $param['imageManagerAllowFiles'];
		        $listSize = $param['imageManagerListSize'];
		        $path = $param['imageManagerListPath'];
		}
		$allowFiles = substr(str_replace(".", "|", join("", $allowFiles)), 1);

		/* 获取参数 */
		$size = isset($_GET['size']) ? htmlspecialchars($_GET['size']) : $listSize;
		$start = isset($_GET['start']) ? htmlspecialchars($_GET['start']) : 0;
		$end = $start + $size;

		/* 获取文件列表 */
		$path = $_SERVER['DOCUMENT_ROOT'] . (substr($path, 0, 1) == "/" ? "":"/") . $path;
		$files = $this->getfiles($path, $allowFiles);
		if (!count($files)) {
		    return json_encode(array(
		        "state" => "no match file",
		        "list" => array(),
		        "start" => $start,
		        "total" => count($files)
		    ));
		}

		/* 获取指定范围的列表 */
		$len = count($files);
		for ($i = min($end, $len) - 1, $list = array(); $i < $len && $i >= 0 && $i >= $start; $i--){
		    $list[] = $files[$i];
		}
		//倒序
		//for ($i = $end, $list = array(); $i < $len && $i < $end; $i++){
		//    $list[] = $files[$i];
		//}

		/* 返回数据 */
		$result = json_encode(array(
		    "state" => "SUCCESS",
		    "list" => $list,
		    "start" => $start,
		    "total" => count($files)
		));

		return $result;
	}

	private function catchImage($param) {
		set_time_limit(0);
		/* 上传配置 */
		$config = array(
		    "pathFormat" => $param['catcherPathFormat'],
		    "maxSize" => $param['catcherMaxSize'],
		    "allowFiles" => $param['catcherAllowFiles'],
		    "oriName" => "remote.png",
		    "preUrl" => $param['preUrl']
		);
		$fieldName = $param['catcherFieldName'];

		/* 抓取远程图片 */
		$list = array();
		if (isset($_POST[$fieldName])) {
		    $source = $_POST[$fieldName];
		} else {
		    $source = $_GET[$fieldName];
		}
		foreach ($source as $imgUrl) {
		    $item = new Uploader($imgUrl, $config, "remote");
		    $info = $item->getFileInfo();
		    array_push($list, array(
		        "state" => $info["state"],
		        "url" => $info["url"],
		        "size" => $info["size"],
		        "title" => htmlspecialchars($info["title"]),
		        "original" => htmlspecialchars($info["original"]),
		        "source" => htmlspecialchars($imgUrl)
		    ));
		}

		/* 返回抓取数据 */
		return json_encode(array(
		    'state'=> count($list) ? 'SUCCESS':'ERROR',
		    'list'=> $list
		));
	}

	/**
	 * 遍历获取目录下的指定类型的文件
	 * @param $path
	 * @param array $files
	 * @return array
	 */
	private function getfiles($path, $allowFiles, &$files = array()) {
	    if (!is_dir($path)) return null;
	    if(substr($path, strlen($path) - 1) != '/') $path .= '/';
	    $handle = opendir($path);
	    while (false !== ($file = readdir($handle))) {
	        if ($file != '.' && $file != '..') {
	            $path2 = $path . $file;
	            if (is_dir($path2)) {
	                $this->getfiles($path2, $allowFiles, $files);
	            } else {
	                if (preg_match("/\.(".$allowFiles.")$/i", $file)) {
	                	$fileUrl = str_replace($this->ueditorConfig["preUrl"], '', substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])));
	                    $files[] = array(
	                        'url'=> $fileUrl,
	                        'mtime'=> filemtime($path2)
	                    );
	                }
	            }
	        }
	    }
	    return $files;
	}
}
