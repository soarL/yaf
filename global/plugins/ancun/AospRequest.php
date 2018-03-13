<?php
namespace plugins\ancun;

class AospRequest {
	/** 接入事项Key */
	public $itemKey;
	/** 保全号 */
	public $recordNo;
	/** 流程编号 */
	public $flowNo;
	/** 每页多少条 */
	public $pageSize;
	/** 页码 */
	public $pageNo;
	/** md5码 */
	public $md5;
	/**个人用户*/
	public $clientUser;
	/**企业用户*/
	public $enterprise;
	/**是否需要投资人签章*/
	public $needInvestor = false;
	/**是否需要借款人签章*/
	public $needLender = false;
	/**合作方组织机构代码*/
	public $collaboratorIdentNos = array ();

	/** 要保全的文件列表 */
	public $list = array ();
	/** 要保全的非文件类型数据 */
	public $data = array ();
	
	public function setCollaboratorIdentNos($collaboratorIdentNos) {
		$this->collaboratorIdentNos = $collaboratorIdentNos;
	}
	public function getCollaboratorIdentNos() {
		return $this->collaboratorIdentNos;
	}
	public function addCollaboratorIdentNo($collaboratorIdentNo) {
		if(!in_array($collaboratorIdentNo, $this-> collaboratorIdentNos)) {
			array_push($this-> collaboratorIdentNos, $collaboratorIdentNo);
		}
		return $this-> collaboratorIdentNos;
	}
	public function setData($data) {
		$this->data = $data;
	}
	public function getData() {
		return $this->data;
	}
	public function setList($list) {
		$this->list = $list;
	}
	public function getList() {
		return $this->list;
	}
	public function setNeedInvestor($needInvestor) {
		$this->needInvestor = $needInvestor;
	}
	public function getNeedInvestor() {
		return $this->needInvestor;
	}

	public function setNeedLender($needLender) {
		$this->needLender = $needLender;
	}
	public function getNeedLender() {
		return $this->needLender;
	}
	public function setItemKey($itemKey) {
		$this->itemKey = $itemKey;
	}
	public function getItemKey() {
		return $this->itemKey;
	}
	public function setRecordNo($recordNo) {
		$this->recordNo = $recordNo;
	}

	public function getRecordNo() {
		return $this->recordNo;
	}
	public function setFlowNo($flowNo) {
		$this->flowNo = $flowNo;
	}
	public function getFlowNo() {
		return $this->flowNo;
	}
	public function setPageSize($pageSize) {
		$this->pageSize = $pageSize;
	}
	public function getPageSize() {
		return $this->pageSize;
	}
	public function setPageNo($pageNo) {
		$this->pageNo = $pageNo;
	}
	public function getPageNo() {
		return $this->pageNo;
	}
	public function setMd5($md5) {
		$this->md5 = $md5;
	}
	public function getMd5() {
		return $this->md5;
	}
	public function setClientUser($clientUser) {
		$this->clientUser = $clientUser;
	}
	public function getClientUser() {
		return $this->clientUser;
	}
	public function setEnterprise($enterprise) {
		$this->enterprise = $enterprise;
	}
	public function getEnterprise() {
		return $this->enterprise;
	}

	public function addFile($fileFullPath, $fileName) {
		$aospfile = new AospFile($fileFullPath, $fileName);
		array_push($this->list, $aospfile);
		return $this->list;
	}
}