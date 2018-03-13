<?php
namespace task;

/**
 * Handler
 * 处理者
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
abstract class Handler {
    protected $OT = 1;
    protected $params = [];

    /**
     * handler构造函数
     * @param array   $params handler运行需要的数据
     * @param integer $OT     该handler的时间复杂度，0表示以handler内置为准
     */
    function __construct($params, $OT=0) {
        $this->params = $params;
        if($OT!=0) {
            $this->OT = $OT;
        }
        $this->init();
    }

    /**
     * 初始化方法
     */
    public function init() {

    }

    /**
     * 获取该handler时间复杂度
     * @return integer
     */
    public function getOT() {
        return $this->OT;
    }

    /**
     * 具体处理函数
     * @return array 返回信息
     */
    abstract function handle();
}