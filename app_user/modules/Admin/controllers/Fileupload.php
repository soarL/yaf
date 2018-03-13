<?php
use Admin as Controller;
use plugins\fileupload\UploadHandler;
use tools\Uploader;

class FileuploadController extends Controller {
    
    public function indexAction() {
        $handler = new UploadHandler();
    }

    public function imageAction() {
        $uploadTime = time();
        $fileTypeName = 'images';
        $params = [
            'param_name' => 'files',
            'script_url' => '/admin/fileupload/image/',
            'upload_dir' => '../../app_user/public/uploads/'.$fileTypeName.'/'.date('Ymd', $uploadTime).'/',
            'upload_url' => '/uploads/'.$fileTypeName.'/'.date('Ymd', $uploadTime).'/',
            'accept_file_types' => '/\.(gif|jpe?g|png)$/i',
            'upload_time' => $uploadTime,
        ];

        $handler = new UploadHandler($params);
    }

    public function deleteImageAction() {
        $uploadTime = time();
        $fileTypeName = 'images';
        
        $fileArray = explode('/', trim($_REQUEST['file'], '/'));
        
        $dateFolder = date('Ymd', $uploadTime);
        if(count($fileArray)==2) {
            $dateFolder = $fileArray[0];
        }

        $params = [
            'script_url' => '/admin/fileupload/image/',
            'upload_dir' => '../../app_user/public/uploads/'.$fileTypeName.'/'.$dateFolder.'/',
            'upload_url' => '/uploads/'.$fileTypeName.'/'.$dateFolder.'/',
            'accept_file_types' => '/\.(gif|jpe?g|png)$/i',
            'upload_time' => $uploadTime,
        ];
        $handler = new UploadHandler($params);
    }

    public function documentAction() {
        $handler = new UploadHandler();
    }

    public function videoAction() {
        $handler = new UploadHandler();
    }

    public function editorImgAction() {
        $callback = $this->getQuery('CKEditorFuncNum');
        $path = dirname(APP_PATH) . '/app_user/public/uploads/images/';
        $uploader = new Uploader();
        $uploader->set('path', $path);
        $uploader->set('maxsize', 2000000);
        $uploader->set('allowtype', ['gif', 'png', 'jpg', 'jpeg']);
        $uploader->set('israndname', true);
        if($uploader->upload('upload')) {
            $imageName = $uploader->getFileName();
            echo $imageName;
        } else {
            echo '上传失败！';
        } 

        echo '<script type="text/javascript">';
        echo 'window.parent.CKEDITOR.tools.callFunction("' . $callback . '", "'.WEB_ASSET.'/uploads/images/'.$imageName . '", "")';
        echo '</script>';
    }
}
