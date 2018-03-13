<?php
/**
 * Factory
 * 工厂类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
namespace base;

interface Factory {
	public static function create($params=[]);
}