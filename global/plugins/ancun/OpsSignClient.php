<?php
namespace plugins\ancun;

class OpsSignClient {

    /**证件号*/
    public $identNo;
    /**签章关键字*/
    public $keyWord;
    /**签章文件*/
    public $fileName;

    function __construct($identNo, $keyWord,$fileName) {
        $this->identNo = $identNo;
        $this->keyWord = $keyWord;
        $this->fileName = $fileName;
    }

    public function getIdentNo() {
        return $this->identNo;
    }

    public function setIdentNo($identNo) {
        $this->identNo = $identNo;
    }

    public function getkeyWord() {
        return $this->keyWord;
    }

    public function setKeyWord($keyWord) {
        $this->keyWord = $keyWord;
    }

    public function getFileName() {
        return $this->fileName;
    }

    public function setFileName($fileName) {
        $this->fileName = $fileName;
    }
}