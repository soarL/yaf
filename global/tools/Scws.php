<?php
namespace tools;

/**
 * Scws类，封装scws类，用于中文分词
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Scws {

	public $scws;

	public function __construct($config=array()) {
		$this->scws = scws_new();
		if(isset($config['charset'])) {
			$this->setCharset($config['charset']);
		}
		if(isset($config['dict'])) {
			$this->addDict($config['dict']);
		} else {
			$dict = ini_get('scws.default.fpath') . '/dict.utf8.xdb';
			$this->addDict($dict);
		}
		if(isset($config['rule'])) {
			$this->setRule($config['rule']);
		} else {
			$rule = ini_get('scws.default.fpath') . '/rules.utf8.ini';
			$this->setRule($rule);
		}

		if(isset($config['ignore'])) {
			$this->setIgnore($config['ignore']);
		} else {
			$this->setIgnore(true);
		}
		if(isset($config['multi'])) {
			$this->setMulti($config['multi']);
		} else {
			$this->setMulti(false);
		}
		if(isset($config['duality'])) {
			$this->setDuality($config['duality']);
		} else {
			$this->setDuality(false);
		}
		if(isset($config['text'])) {
			$this->setText($config['text']);
		} else {
			throw new Exception('缺少参数text');
		}
	}

	/**
	 * 获取分词数组
	 * @return array 分词数组
	 */
    public function getWords() {
    	return $this->scws->get_result();
    }

    /**
     * 设定需要分词的句子
     * @param string $text
     */
    public function setText($text) {
    	$this->scws->send_text($text);
    }

    /**
     * 设定字符类型
     * @param string $charset 字符类型
     */
    public function setCharset($charset='utf-8') {
    	return $this->scws->set_charset($charset);
    }

    /**
     * 添加词库
     * @param string $file 词库文件路径
     */
    public function addDict($file) {
    	$this->scws->add_dict($file);
    }

    /**
     * 设置规则
     * @param string $file 规则文件路径
     */
    public function setRule($file) {
    	$this->scws->set_rule($file);
    }

    /**
     * 设定分词返回结果时是否去除一些特殊的标点符号
     * @param boolean $ignore
     */
    public function setIgnore($ignore=false) {
    	$this->scws->set_ignore($ignore);
    }

    /**
     * 设定分词返回结果时是否复式分割，如“中国人”返回“中国＋人＋中国人”三个词。
	 * 按位异或的 1 | 2 | 4 | 8 分别表示: 短词 | 二元 | 主要单字 | 所有单字
	 * 1,2,4,8 分别对应常量 SCWS_MULTI_SHORT SCWS_MULTI_DUALITY SCWS_MULTI_ZMAIN SCWS_MULTI_ZALL
     * @param boolean $multi
     */
    public function setMulti($multi=false) {
    	$this->scws->set_multi($multi);
    }

    /**
     * 设定是否将闲散文字自动以二字分词法聚合
     * @param boolean $duality
     */
    public function setDuality($duality=false) {
    	$this->scws->set_duality($duality);
    }

    /**
     * 关闭scws
     */
    public function close() {
    	$this->scws->close();
    }

    public function __destruct() {
    	$this->close();
    }
}