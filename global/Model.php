<?php
use \Yaf\Registry;
use \helpers\StringHelper;

/**
 * Model
 * 数据模型基类（已废弃）
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Model {

	public static function getDb() {
		return Registry::get('db');
	}

	public static function tableName() {
		return Registry::get('config')->database->params->tablePrefix.StringHelper::modelToTable(preg_replace('/(?<=[a-z])Model$/', '', get_called_class()));
	}

	public static function col($col='*') {
		$colStr = '';
		if(is_array($col)) {
			foreach ($col as $c) {
				$colStr .= static::tableName() . '.' . $c . ',';
			}
			$colStr = rtrim($colStr, ',');
		} else if(is_string($col)) {
			$colStr = static::tableName().'.'.$col;
		}
		return $colStr;
	}

	public static function insert($data) {
		return static::getDb()->insert(static::tableName(), $data);
	}

	public static function delete($where=array()) {
		return static::getDb()->delete(static::tableName(), $where);
	}

	public static function truncate() {
		return static::getDb()->truncate(static::tableName());
	}

	public static function update($data, $where=array()) {
		return static::getDb()->update(static::tableName(), $data, $where);
	}

	public static function find() {
		return static::getDb()->find(static::tableName());
	}

	public static function execute($sql, $params = array()) {
		return static::getDb()->execute($sql, $params);
	}

	public static function query($sql, $params = array()) {
		return static::getDb()->query($sql, $params);
    }
	public static function transaction($data) {
		return static::getDb()->transaction($data);
	}

	public static function getInsertId() {
        return static::getDb()->getInsertId();
    }

    public static function getRowCount() {
        return static::getDb()->getRowCount();
    }

    public static function getError() {
        return static::getDb()->getError();
    }

}