<?php
namespace plugins\ancun;

class AospResponse {
	/** 返回信息编号 */
	public $Code;

	/** 返回信息 */
	public $Msg;

	/** 日志信息 */
	public $Logno;

	/** 保全开放平台版本号 */
	public $Serversion;

	public $Data = array ();

	public function setData($Data) {
		$this->Data = $Data;
	}
	public function getData() {
		return $this->Data;
	}
	//        public function AospResponse() {
	//            $this->Code = 100000;
	//            $this->Msg = "成功";
	//        }

	public function setCode($Code) {
		$this->Code = $Code;
	}
	public function getCode() {
		return $this->Code;
	}
	public function setMsg($Msg) {
		$this->Msg = $Msg;
	}
	public function getMsg() {
		return $this->Msg;
	}
	public function setLogno($Logno) {
		$this->Logno = $Logno;
	}
	public function getLogno() {
		return $this->Logno;
	}
	public function setServersion($Serversion) {
		$this->Serversion = $Serversion;
	}
	public function getServersion() {
		return $this->Serversion;
	}

}