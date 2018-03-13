<?php
namespace plugins\ancun;

class AospFile {

	/** 附件 */
	public $file;
	/** 附件全路径 */
	public $fileFullPath;
	/** 附件中文名 */
	public $fileName;
	/**加密算法*/
	public $encryptionAlgorithm;

	function __construct($fileFullPath, $fileName) {
		$this->fileFullPath = $fileFullPath;
		$this->fileName = $fileName;
	}

	public function getFileName() {
		return $this->fileName;
	}

	public function setFileName($fileName) {
		$this->fileName = $fileName;
	}

	public function getFile() {
		return $this->file;
	}

	public function setFile($file) {
		$this->file = $file;
	}

	public function getFileFullPath() {
		return $this->fileFullPath;
	}

	public function setFileFullPath($fileFullPath) {
		$this->fileFullPath = $fileFullPath;
	}

	public function getEncryptionAlgorithm() {
		return $this->encryptionAlgorithm;
	}

	public function setEncryptionAlgorithm($encryptionAlgorithm) {
		$this->encryptionAlgorithm = $encryptionAlgorithm;
	}

}
