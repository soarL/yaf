<?php
namespace tools;

use \Flash;
use \Tag;

/**
 * Layer 配合提前端的通知插件
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Layer {
    private $message;
    private $type;

    function __construct($data=[]) {
        $this->message = isset($data['message'])?$data['message']:'';
        $this->type = isset($data['type'])?$data['type']:'success';
    }

    public static function html($data=[]) {
        $message = isset($data['message'])?$data['message']:'';
        $type = isset($data['type'])?$data['type']:'success';

        $flashDiv = new Tag('div');
        $flashDiv->addClass('flash-data')->setAttribute('style', 'display:none;');
        $textDiv = new Tag('div');
        $textDiv->addClass('flash-data-message')->setContent($message);
        $typeDiv = new Tag('div');
        $typeDiv->addClass('flash-data-type')->setContent($type);
        $flashDiv->setContent($textDiv.$typeDiv);
        return $flashDiv;
    }

    public static function flash() {
        if(Flash::has()) {
            $flashData= Flash::get();
            $data = [];
            $data['message'] = $flashData['info'];
            $data['type'] = $flashData['type'];
            echo self::html($data);
        }
    }

    public function show() {
        $data = [];
        $data['message'] = $this->message;
        $data['type'] = $this->type;
        echo self::html($data);
    }

    public function setMessage($message) {
        $this->message = $message;
    }

    public function setType($type) {
        $this->type = $type;
    }
}