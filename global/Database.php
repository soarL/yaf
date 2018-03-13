<?php
use \Yaf\Registry;
use \helpers\ArrayHelper;
use \common\InvalidParamException;
use \common\SqlExecuteErrorException;
/**
 * 数据库类，需要使用mysql pdo, 并使用php5.4以上版本（已废弃）
 * @author elf <360197197@qq.com>
 */
class Database {
    const KEYWORD_AND = 'and';
    const KEYWORD_OR = 'or';
    const KEYWORD_IN = 'in';
    const KEYWORD_NOT_IN = 'not in';
    const KEYWORD_LIKE = 'like';
    const KEYWORD_NOT_LIKE = 'not like';
    const KEYWORD_OR_LIKE = 'or like';
    const KEYWORD_OR_NOT_LIKE = 'or not like';
    const KEYWORD_BETWEEN = 'between';
    const KEYWORD_NOT_BETWEEN = 'not between';
    const KEYWORD_EQUALS = '=';
    const KEYWORD_NOT_EQUALS = '<>';
    const KEYWORD_BIGGER = '>';
    const KEYWORD_NOT_SMALLER = '>=';
    const KEYWORD_SMALLER = '<';
    const KEYWORD_NOT_BIGGER = '<=';
    const KEYWORD_IS_NULL = 'is null';
    const KEYWORD_IS_NOT_NLL = 'is not null';
    const KEYWORD_LEFT_JOIN = 'left join';
    const KEYWORD_INNER_JOIN = 'inner join';
    const KEYWORD_RIGHT_JOIN = 'right join';

    const MARK_WHERE = '#WHERE#';
    const MARK_SELECT = '#SELECT#';
    const MARK_ORDER_BY = '#ORDERBY#';
    const MARK_LIMIT = '#LIMIT#';
    const MARK_GROUP_BY = '#GROUPBY#';
    const MARK_JOIN = '#JOIN#';
    const MARK_FOR = '#FOR#';


	private static $instances = [];
	private $_stmt;
	private $_pdo;
	private $_params = [];
    private $_updateParams = [];
    private $_insertParams = [];
    private $_where = '';
    private $_sql = '';
    private $_select = '*';
    private $_groupBy = '';
    private $_orderBy = '';

    private $_limit;
    private $_offset;
    private $_limitSql = '';
    private $_forSql = '';
    
    private $_rowCount;
    private $_insertId;
    private $_errorInfo;

    private $_charset;
    private $_host;
    private $_username;
    private $_password;
    private $_dbname;
    private $_port;

    private $_join = '';
    private $_isJoin = false;
    private $_logLevel = 1;

    private $_isLog = false;
    private $_sqlType = 'select';

    private $isGroupBy = false;


	static $conditionKeywords = [
        self::KEYWORD_AND,
        self::KEYWORD_OR,
        self::KEYWORD_IN,
        self::KEYWORD_NOT_IN,
        self::KEYWORD_LIKE,
        self::KEYWORD_NOT_LIKE,
        self::KEYWORD_OR_LIKE,
        self::KEYWORD_OR_NOT_LIKE,
        self::KEYWORD_BETWEEN,
        self::KEYWORD_NOT_BETWEEN,
        self::KEYWORD_EQUALS,
        self::KEYWORD_NOT_EQUALS,
        self::KEYWORD_BIGGER,
        self::KEYWORD_NOT_SMALLER,
        self::KEYWORD_SMALLER,
        self::KEYWORD_NOT_BIGGER,
        self::KEYWORD_IS_NULL,
        self::KEYWORD_IS_NOT_NLL,
    ];

    static $joinKeywords = [
        self::KEYWORD_LEFT_JOIN,
        self::KEYWORD_INNER_JOIN,
        self::KEYWORD_RIGHT_JOIN,
    ];

	private function __construct($db) {
		 try {
            //获取参数
            $dbConfig = Registry::get('config')->$db;
            $this->_host = $dbConfig['host'];
            $this->_username = $dbConfig['username'];
            $this->_password = $dbConfig['password'];
            $this->_dbname = $dbConfig['dbname'];
            $this->_port = $dbConfig['port'];
            $this->_isLog = $dbConfig['islog'];
            $this->_logLevel = $dbConfig['loglevel'];
            $dsn = 'mysql:host=' . $this->_host . ';dbname=' . $this->_dbname . ';port=' . $this->_port;
            // var_dump($dsn);die();
            //创建连接
            $this->_pdo = new PDO($dsn, $this->_username, $this->_password);
            //设置字符集
            $this->_charset = $dbConfig['charset'];
            $this->_pdo->query('set names ' . $this->_charset);
        } catch (PDOException $_pdoe) {
            die("数据库连接失败!".$_pdoe->getMessage());
        }
	}

	public static function getInstance($db) {
        if(!isset(self::$instances[$db])) {
            self::$instances[$db] = new self($db);
        }
        return self::$instances[$db];
    }

    public function getPDOAttributes() {
    	$attributes = [];
    	$attributes['autocommit'] = $this->_pdo->getAttribute(PDO::ATTR_AUTOCOMMIT);
    	$attributes['case'] = $this->_pdo->getAttribute(PDO::ATTR_CASE);
    	$attributes['client_version'] = $this->_pdo->getAttribute(PDO::ATTR_CLIENT_VERSION);
    	$attributes['connection_status'] = $this->_pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS);
    	$attributes['driver_name'] = $this->_pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    	$attributes['errmode'] = $this->_pdo->getAttribute(PDO::ATTR_ERRMODE);
    	$attributes['oracle_nulls'] = $this->_pdo->getAttribute(PDO::ATTR_ORACLE_NULLS);
    	$attributes['persistent'] = $this->_pdo->getAttribute(PDO::ATTR_PERSISTENT);
    	// $attributes['prefetch'] = $this->_pdo->getAttribute(PDO::ATTR_PREFETCH);
    	$attributes['server_info'] = $this->_pdo->getAttribute(PDO::ATTR_SERVER_INFO);
    	$attributes['server_version'] = $this->_pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
    	// $attributes['timeout'] = $this->_pdo->getAttribute(PDO::ATTR_TIMEOUT);
        return $attributes;
    }

    /**
     * 获取PDO
     * @return resource PDO
     */
    public function getPDO() {
        return $this->_pdo;
    }

    /**
     * 插入数据
     * @param  string $tableName 表名
     * @param  array  $data      要插入的数据
     * @return boolean           是否插入成功
     */
    public function insert($tableName, $data) {
        $this->_sqlType = 'insert';
        $keys = array_keys($data);
        $values = array_values($data);

        $keyStr = $this->associateColumns($keys);
        $valueStr = '';
        foreach ($keys as $key) {
            $valueStr .= '?,';
        }
        $valueStr = rtrim($valueStr, ',');

        $this->_sql = 'insert into ' . $tableName . ' (' . $keyStr . ') VALUES (' . $valueStr . ')';

        $this->_stmt = $this->_pdo->prepare($this->_sql);
        $status = $this->_stmt->execute($values);
        $this->_insertParams = $values;
        if($this->_isLog) {
            $this->log();
        }

        if ($status) {
            $this->_rowCount = $this->_stmt->rowCount();
            $this->_insertId = $this->_pdo->lastInsertId();
            return true;
        } else {
            $error = $this->_stmt->errorInfo();
            throw new SqlExecuteErrorException("Sql Error $error[0]($error[1]):".$error[2]);
        }
    }

    /**
     * 删除数据
     * @param  string $tableName 表名
     * @param  array  $where     删除条件，解析详见where()方法
     * @return boolean           是否删除成功
     */
    public function delete($tableName, $where=array()) {
        $this->_where = '';
        $this->_sqlType = 'delete';
        $this->_sql = 'delete from ' . $tableName . ' ' . self::MARK_WHERE;
        $this->where($where);
        $this->replaceMark();
        $this->_stmt = $this->_pdo->prepare($this->_sql);
        $this->bindValue();
        $status = $this->_stmt->execute();
        if($this->_isLog) {
            $this->log();
        }
        return $status;
    }

     /**
     * 删除表数据
     * @param  string $tableName 表名
     * @return boolean           是否删除成功
     */
    public function truncate($tableName) {
        $this->_where = '';
        $this->_sqlType = 'truncate';
        $this->_sql = 'truncate table ' . $tableName;
        $this->_stmt = $this->_pdo->prepare($this->_sql);
        $status = $this->_stmt->execute();
        if($this->_isLog) {
            $this->log();
        }
        return $status;
    }

    /**
     * 修改数据
     * @param  string $tableName 表名
     * @param  array  $data      要修改的数据
     * @param  array  $where     更新条件，解析详见where()方法
     * @return boolean           是否更新成功
     */
    public function update($tableName, $data, $where=array()) {
        $this->_where = '';
        $this->_sqlType = 'update';
        $updateStr = '';
        $this->_updateParams = [];
        foreach ($data as $key => $value) {
            $paramKey = $this->generateRandomKey($key);
            $this->joinUpdateParam($paramKey, $value);
            $updateStr .= $key . '=' . $paramKey . ',';
        }
        $updateStr = rtrim($updateStr, ',');

        $this->_sql = 'update ' . $tableName . ' set ' . $updateStr . ' ' . self::MARK_WHERE;

        $this->where($where);
        $this->replaceMark();

        $this->_stmt = $this->_pdo->prepare($this->_sql);
        $this->bindValue();
        $this->bindUpdateValue();
        $status = $this->_stmt->execute();
        if($this->_isLog) {
            $this->log();
        }
        if ($status) {
            $this->_rowCount = $this->_stmt->rowCount();
            $this->_insertId = $this->_pdo->lastInsertId();
            return true;
        } else {
            $this->addError('update', $this->_sql, $this->_stmt);
            return false;
        }
    }

    /**
     * 查找数据，该方法需要结合one()，all()等方法一起使用，链式调用
     * @param  stirng $tableName 表名
     * @return Database          返回该对象自身
     */
    public function find($tableName) {
        $this->_sqlType = 'select';
        $this->_sql = 'select '.self::MARK_SELECT.' from '.$tableName.' '
            .self::MARK_JOIN.' '.self::MARK_WHERE.' '.self::MARK_GROUP_BY.' '
            .self::MARK_ORDER_BY.' '.self::MARK_LIMIT.' '.self::MARK_FOR;
        $this->_isJoin = false;
        $this->_join = '';
        $this->_params = [];
        $this->_where = '';
        $this->_select = '*';
        $this->_groupBy = '';
        $this->_orderBy = '';
        $this->_limitSql = '';
        $this->_limit = null;
        $this->_offset = null;
        $this->_forSql = '';
        return $this;
    }
    
    /**
     * 执行sql语句
     * @param  string  $sql    sql语句
     * @param  array   $params 占位参数（insert只能用索引数组（占位符？），其他用关联数组（占位符:key））
     * @return boolean         是否执行成功
     */
    public function execute($sql,$params=array()) {
        if(strpos($sql, 'select')===0) {
            $this->_sqlType = 'select';
        } else if(strpos($sql, 'insert')===0) {
            $this->_sqlType = 'execute-insert';
        } else if(strpos($sql, 'update')===0) {
            $this->_sqlType = 'update';
        } else if(strpos($sql, 'delete')===0) {
            $this->_sqlType = 'delete';
        } else if(strpos($sql, 'truncate')===0) {
            $this->_sqlType = 'truncate';
        } else if(strpos($sql, 'begin')===0) {
            $this->_sqlType = 'begin';
        } else if(strpos($sql, 'commit')===0) {
            $this->_sqlType = 'commit';
        } else if(strpos($sql, 'rollback')===0) {
            $this->_sqlType = 'rollback';
        } else {
            $this->_sqlType = '';
        }
        $this->_sql = $sql;
        $this->_params = $params;
        $this->replaceMark();
        $this->_stmt = $this->_pdo->prepare($this->_sql);
        $this->bindValue();
        $status = $this->_stmt->execute();
        if($this->_isLog) {
            $this->log();
        }
        if($status) {
            $this->_rowCount = $this->_stmt->rowCount();
            $this->_insertId = $this->_pdo->lastInsertId();
        } else {
            $error = $this->_stmt->errorInfo();
            throw new SqlExecuteErrorException("Sql Error $error[0]($error[1]):".$error[2]);
        }
        return $status;
    }

    public function query($sql) {
        $this->_sqlType = 'select';
        $this->_params = [];
        $this->_orderBy = '';
        $this->_limitSql = '';
        $this->_forSql = '';
        $this->_sql = $sql;
        $posForm = strpos($this->_sql, 'from');
        $posSelect = strpos($this->_sql, 'select');
        $selectStartPos = $posSelect + 6 + 1;
        $this->_select = substr($this->_sql, $selectStartPos, $posForm-$selectStartPos-1);
        $this->_sql = 'select ' . self::MARK_SELECT . ' '. substr($this->_sql, $posForm) . ' ' 
            . self::MARK_ORDER_BY . ' ' . self::MARK_LIMIT . ' ' . self::MARK_FOR;
        return $this;
    }

    public function getInsertId() {
        return $this->_insertId;
    }

    public function getRowCount() {
        return $this->_rowCount;
    }

    public function getError() {
        return $this->_errorInfo;
    }

    private function associateColumns($columns) {
        return '`'.implode('`,`',$columns).'`';
    }

    private function addError($operate, $sql, $stmt) {
        $errorInfo = $stmt->errorInfo();
        $this->_errorInfo .= '数据库错误信息：<br/>';
        $this->_errorInfo .= '执行' . $operate . '操作失败，SQL语句：' . $sql . '<br/>';
        $this->_errorInfo .= '影响行数：' . $this->getRowCount() . '<br/>';
        $this->_errorInfo .= '错误码：' . $errorInfo[0] . '<br/>';
        $this->_errorInfo .= '错误信息：' . $errorInfo[2] . '<br/>';
    }

    public function one() {
        $this->replaceMark();
        $this->_stmt = $this->_pdo->prepare($this->_sql);
        $this->bindValue();
        $status = $this->_stmt->execute();

        if($this->_isLog&&$this->_logLevel==1) {
            $this->log();
        }

        if(!$status) {
            $error = $this->_stmt->errorInfo();
            throw new SqlExecuteErrorException("Sql Error $error[0]($error[1]):".$error[2]);
        }
    	return $this->_stmt->fetch(PDO::FETCH_ASSOC);
        
    }

    public function lock($operate) {
        $this->_forSql = 'for ' . $operate;
        return $this;
    }

    public function all() {
        $this->replaceMark();
        $this->_stmt = $this->_pdo->prepare($this->_sql);
        $this->bindValue();
        $status = $this->_stmt->execute();
        if($this->_isLog&&$this->_logLevel==1) {
            $this->log();
        }

        if(!$status) {
            $error = $this->_stmt->errorInfo();
            throw new SqlExecuteErrorException("Sql Error $error[0]($error[1]):".$error[2]);
        }
    	return $this->_stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function val($column) {
        if(is_string($column)) {
            $this->_select = $column;
        } else {
            return false;
        }
        
        $this->replaceMark();
        $this->_stmt = $this->_pdo->prepare($this->_sql);
        $this->bindValue();
        $status = $this->_stmt->execute();

        if($this->_isLog&&$this->_logLevel==1) {
            $this->log();
        }

        if(!$status) {
            $error = $this->_stmt->errorInfo();
            throw new SqlExecuteErrorException("Sql Error $error[0]($error[1]):".$error[2]);
        }
        return $this->_stmt->fetchColumn();
    }

    public function min($column) {
        if(is_string($column)) {
            $this->_select = 'min('.$column.')';
        } else {
            return false;
        }
        $this->replaceMark();
        $this->_stmt = $this->_pdo->prepare($this->_sql);
        $this->bindValue();
        $status = $this->_stmt->execute();

        if($this->_isLog&&$this->_logLevel==1) {
            $this->log();
        }

        if(!$status) {
            $error = $this->_stmt->errorInfo();
            throw new SqlExecuteErrorException("Sql Error $error[0]($error[1]):".$error[2]);
        }
        return $this->_stmt->fetchColumn();
    }

    public function max($column) {
        if(is_string($column)) {
            $this->_select = 'max('.$column.')';
        } else {
            return false;
        }
        
        $this->replaceMark();
        $this->_stmt = $this->_pdo->prepare($this->_sql);
        $this->bindValue();
        $status = $this->_stmt->execute();

        if($this->_isLog&&$this->_logLevel==1) {
            $this->log();
        }

        if(!$status) {
            $error = $this->_stmt->errorInfo();
            throw new SqlExecuteErrorException("Sql Error $error[0]($error[1]):".$error[2]);
        }
        return $this->_stmt->fetchColumn();
    }

    public function sum($column) {
        if(is_string($column)) {
            $this->_select = 'sum('.$column.')';
        } else {
            return false;
        }
        
        $this->replaceMark();
        $this->_stmt = $this->_pdo->prepare($this->_sql);
        $this->bindValue();
        $status = $this->_stmt->execute();

        if($this->_isLog&&$this->_logLevel==1) {
            $this->log();
        }

        if(!$status) {
            $error = $this->_stmt->errorInfo();
            throw new SqlExecuteErrorException("Sql Error $error[0]($error[1]):".$error[2]);
        }
        $sum = $this->_stmt->fetchColumn();
        if($sum==null) {
            return 0;
        } else {
            return $sum;
        }
    }

    public function count($column='') {
        if(is_string($column)) {
            $reColumn = '*';
            if($column!='') {
                $reColumn = $column;
            }
            $this->_select = 'count('.$reColumn.')';
        } else {
            return false;
        }
        
        $this->replaceMark();
        $this->_stmt = $this->_pdo->prepare($this->_sql);
        $this->bindValue();
        $status = $this->_stmt->execute();

        if($this->_isLog&&$this->_logLevel==1) {
            $this->log();
        }

        if(!$status) {
            $error = $this->_stmt->errorInfo();
            throw new SqlExecuteErrorException("Sql Error $error[0]($error[1]):".$error[2]);
        }
        return $this->_stmt->fetchColumn();
    }

    public function select($select) {
        $tempSelect = '';
        if(is_string($select)) {
            $tempSelect = $select;
        } else if(is_array($select)) {
            if(!ArrayHelper::isAssoc($select)) {
                foreach ($select as $column) {
                    if(is_string($column)) {
                        $tempSelect .= $column . ',';
                    }
                }
            }
        }
        $this->_select = trim($tempSelect,',');
        return $this;
    }

    public function groupBy($groupBy) {
        $tempGroupBy = '';
        $this->isGroupBy = true;
        if(is_string($groupBy)) {
            $tempGroupBy = $groupBy;
        } else if(is_array($groupBy)) {
            if(!ArrayHelper::isAssoc($groupBy)) {
                foreach ($groupBy as $column) {
                    $tempGroupBy .= $column . ',';
                }
            }
        }
        if($tempGroupBy!='') {
            $this->_groupBy = 'group by ' . trim($tempGroupBy,',');
        }
        return $this;
    }

    public function orderBy($orderBy) {
        if(is_string($orderBy)) {
            $this->_orderBy = 'order by ' . $orderBy;
        }
        return $this;
    }

    public function limit($limit) {
        if(is_int($limit)) {
            if($this->_offset==null) {
                $this->_offset=0;
            }
            $this->_limit = $limit;
            $this->_limitSql = 'limit ' . $this->_offset . ',' . $this->_limit;
        }
        return $this;
    }

    public function offset($offset) {
        if(is_int($offset)) {
            $this->_offset = $offset;
            if($this->_limit!=null) {
                $this->_limitSql = 'limit ' . $this->_offset . ',' . $this->_limit;
            }
        }
        return $this;
    }

    public function join($joinData) {
        $joinSql = '';
        if(isset($joinData['type'])&&in_array($joinData['type'], self::$joinKeywords)) {
            $joinSql = $joinSql . $joinData['type'];
        } else {
            $joinSql = $joinSql . self::KEYWORD_LEFT_JOIN;
        }
        if(!isset($joinData['table'])) {
            throw new Exception('必须设置要join的表！');
        } else {
            if(is_string($joinData['table'])) {
                $joinSql = $joinSql . ' ' . $joinData['table'];
            } else {
                throw new Exception('join参数类型错误！');
            }
        }
        if(!isset($joinData['on'])) {
            throw new Exception('必须设置要join的条件！');
        } else {
            if(is_string($joinData['on'])) {
                $joinSql = $joinSql . ' on ' . $joinData['on'];
            } else if(ArrayHelper::isAssoc($joinData['on'])) {
                $joinSql = $joinSql . ' on ';
                foreach ($joinData['on'] as $key => $value) {
                    // var_dump($key.':'.$value);
                    $joinSql .= $key . '=' . $value . ' and ';
                }
                $joinSql = preg_replace('/\sand\s$/', '', $joinSql);
            } else {
                throw new Exception('join参数类型错误！');   
            }
        }
        $this->_join .= ' ' . $joinSql;
        $this->_isJoin = true;
        return $this;
    }

    /**
     * 事务处理之开始事务
     * @return void
     */
    public function begin() {
        $this->_sqlType = 'begin';
        $this->_sql = 'begin';
        $this->_pdo->beginTransaction();
        if($this->_isLog) {
            $this->log();
        }
    }

    /**
     * 事务处理之回滚事务
     * @return void
     */
    public function rollback() {
        $this->_sqlType = 'rollback';
        $this->_sql = 'rollback';
        $this->_pdo->rollBack();
        if($this->_isLog) {
            $this->log();
        }
    }

    /**
     * 事务处理之提交事务
     * @return void
     */
    public function commit() {
        $this->_sqlType = 'commit';
        $this->_sql = 'commit';
        $this->_pdo->commit();
        if($this->_isLog) {
            $this->log();
        }
    }

    /**
     * 事务操作
     * @param  array  $data  事务集
     * [
     *     [type=>sql, sql=>***, data=>[]]
     *     [type=>operate, operate=>***, tableName=>***, where=>***, data=>[]]
     * ]
     * @return boolean       是否执行成功
     */
    public function transaction($data) {
        if(is_array($data)&&count($data)>0) {
            try {
                $this->begin();
                foreach ($data as $key => $option) {
                    if($option['type']=='sql') {
                        $params = isset($option['data'])?$option['data']:[];
                        $bool = $this->execute($option['sql'], $params);
                        if (!$bool) {
                            throw new Exception('执行错误！');
                        }
                    } else if($option['type']=='operate') {
                        $bool = $this->operate($option);
                        if (!$bool) {
                            throw new Exception('执行错误！');
                        }
                    } else {
                        throw new Exception('参数错误！');
                    }
                }
                $this->commit();
            } catch (Exception $error) {
                $this->rollback();
                $this->_errorInfo .= '执行事务操作失败，事务已经回滚。<br>';
                return false;
            }
        } else {
            throw new Exception('参数错误！');
        }
    }

    /**
     * 事务操作之单项执行
     * @param  array  $operateData  操作项
     * @return boolean              是否操作成功
     */
    private function operate($operateData) {
        $bool = false;
        switch ($operateData['operate']) {
            case 'insert': //添加数据
                $tableName = $operateData['tableName'];
                $data = $operateData['data'];
                $bool = $this->insert($tableName, $data);
                break;
            case 'delete': //删除数据
                $tableName = $operateData['tableName'];
                $where = $operateData['where'];
                $bool = $this->delete($tableName, $where);
                break;
            case 'update': //修改数据
                $tableName = $operateData['tableName'];
                $data = $operateData['data'];
                $where = $operateData['where'];
                $bool = $this->update($tableName, $data, $where);
                break;
            default:
                break;
        }
        return $bool;
    }

    /**
     * 解析where条件
     * @param  mixed   $where  where条件数组或字符串
     * @return Database        对象自身
     */
    public function where($where) {
        $sql = '';
        $this->_params = [];
        if(is_string($where)) {
            $sql = $where;
        } else if(is_array($where)&&count($where)>0) {
            $sql = $this->parseArrayWhere($where);
        } else {
            return $this;
        }
        /*$time2 = microtime();
        $time = (preg_replace('/\s*.$/', '', $time2) - preg_replace('/\s*.$/', '', $time1))*1000;
        var_dump($time.'ms');*/
        $this->_where = 'where ' . $sql;
        return $this;
    }

    /**
     * 解析数组的where条件
     * @param  array   $where  where条件数组
     * @return string          sql条件语句
     */
    private function parseArrayWhere($where) {
        $sql = '';
        if(ArrayHelper::isAssoc($where)) {
            $sql = $this->parseAssocArrayWhere($where);
        } else {
            $sql = $this->parseIndexArrayWhere($where);
        }
        return $sql;
    }

    /**
     * 解析索引数组的where条件
     * @param  array   $where  where条件数组
     * @return string          sql条件语句
     */
    private function parseIndexArrayWhere($where) {
        $sql = '';
        if(in_array($where[0], self::$conditionKeywords)) {
            if($where[0]==self::KEYWORD_AND||$where[0]==self::KEYWORD_OR) {
                $values = [];
                for ($i=1; $i < count($where); $i++) { 
                    $values[] = $where[$i];
                }
                $sql = $this->parseWhereByKeyword($where[0], null, $values);
            } else if($where[0]==self::KEYWORD_BETWEEN||$where[0]==self::KEYWORD_NOT_BETWEEN) {
                $values = [];
                $values[] = $where[2];
                $values[] = $where[3];
                $sql = $this->parseWhereByKeyword($where[0], $where[1], $values);
            } else if($where[0]==self::KEYWORD_LIKE||$where[0]==self::KEYWORD_NOT_LIKE||$where[0]==self::KEYWORD_OR_LIKE||$where[0]==self::KEYWORD_OR_NOT_LIKE) {
                if(isset($where[3])&&is_bool($where[3])) {
                    $sql = $this->parseWhereByKeyword($where[0], $where[1], $where[2], $where[3]);
                } else {
                    $sql = $this->parseWhereByKeyword($where[0], $where[1], $where[2]);
                }
            } else {
                $sql = $this->parseWhereByKeyword($where[0], $where[1], $where[2]);
            }
        }
        return $sql;
    }

    /**
     * 解析关联数组的where条件
     * @param  array   $where  where条件数组
     * @return string          sql条件语句
     */
    private function parseAssocArrayWhere($where) {
        $values = [];
        foreach ($where as $key => $value) {
            if(is_string($key)) {
                if(is_array($value)) {
                    if(!ArrayHelper::isAssoc($value)) {
                        $partSql = $this->parseWhereByKeyword(self::KEYWORD_IN, $key, $value);
                        $values[] = $partSql;
                    }
                } else {
                    if(is_null($value)) {
                        $values[] = $key.' is null';
                    } else {
                        $paramKey = $this->generateRandomKey($key);
                        $values[] = $key.'='.$paramKey;
                        $this->joinParam($paramKey, $value);
                    }
                }
            }
        }
        $sql = $this->parseWhereByKeyword(self::KEYWORD_AND, null, $values);
        return $sql;
    }
    /**
     * 按照关键字解析where语句
     * @param  string  $keyword 关键字
     * @param  string  $column  列名
     * @param  array   $values  列值
     * @param  boolean $other   
     * @return string           sql条件语句
     */
    private function parseWhereByKeyword($keyword, $column, $values, $other=false) {
        $sql = '(CONDITION)';
        $condition = '';
        if($keyword==self::KEYWORD_IN||$keyword==self::KEYWORD_NOT_IN) {
            $condition = $column . ' '. $keyword . '(SUB_CONDITION)';
            $subCondition = '';
            if(is_array($values)) {
                foreach ($values as $key => $value) {
                    $paramKey = $this->generateRandomKey($column);
                    $this->joinParam($paramKey, $value);
                    $subCondition .= $paramKey . ',';
                }
            } else {
                $condition = '';
            }
            if($condition!='') {
                $subCondition = rtrim($subCondition, ',');
                $condition = str_replace('SUB_CONDITION', $subCondition, $condition);
            }
            
        } else if($keyword==self::KEYWORD_BETWEEN||$keyword==self::KEYWORD_NOT_BETWEEN) {
            /** |mode| ['between', 'age', 20, 29] **/
            $condition = $column . ' '. $keyword . ' SUB_CONDITION';
            $subCondition = '';
            if(isset($values[0])&&isset($values[1])) {
                $paramKey1 = $this->generateRandomKey($column);
                $paramKey2 = $this->generateRandomKey($column);
                $subCondition = $paramKey1 . ' and ' . $paramKey2;
                $this->joinParam($paramKey1, $values[0]);
                $this->joinParam($paramKey2, $values[1]);
                $condition = str_replace('SUB_CONDITION', $subCondition, $condition);
            } else {
                $condition = '';
            }
        } else if($keyword==self::KEYWORD_LIKE||$keyword==self::KEYWORD_OR_LIKE||$keyword==self::KEYWORD_NOT_LIKE||$keyword==self::KEYWORD_OR_NOT_LIKE) {
            $condition = $column . ' '. $keyword . ' SUB_CONDITION';
            $subCondition = '';
            if(is_string($values)) {
                $subCondition = '%' . $values . '%';
                if($other==true) {
                    $subCondition = $values;
                }
                $paramKey = $this->generateRandomKey($column);
                $this->joinParam($paramKey, $subCondition);
                $condition = str_replace('SUB_CONDITION', $paramKey, $condition);
            } else if(is_array($values)) {
                $partCondition = '';
                if($keyword==self::KEYWORD_LIKE||$keyword==self::KEYWORD_OR_LIKE) {
                    $partCondition = $column . ' like SUB_CONDITION';
                } else {
                    $partCondition = $column . ' not like SUB_CONDITION';
                }
                $condition = '';
                foreach ($values as $key => $value) {
                    $partCondition1 = $partCondition;
                    if(is_string($value)) {
                        $subCondition = '%' . $value . '%';
                        if($other==true) {
                            $subCondition = $value;
                        }
                        $paramKey = $this->generateRandomKey($column);
                        $this->joinParam($paramKey, $subCondition);
                        $subCondition = $paramKey;
                        $partCondition1 = str_replace('SUB_CONDITION', $subCondition, $partCondition1);
                        if($keyword==self::KEYWORD_OR_LIKE||$keyword==self::KEYWORD_OR_NOT_LIKE) {
                            $condition .= ' or ' . $partCondition1;
                        } else {
                            $condition .= ' and ' . $partCondition1;
                        }
                    }
                }
                $condition = preg_replace('/^\sand\s/', '', $condition);
                $condition = preg_replace('/^\sor\s/', '', $condition);
            } else {
                throw new InvalidParamException();
            }
        } else if($keyword==self::KEYWORD_EQUALS||$keyword==self::KEYWORD_BIGGER||$keyword==self::KEYWORD_NOT_SMALLER||$keyword==self::KEYWORD_SMALLER||$keyword==self::KEYWORD_NOT_BIGGER) {
            $condition = $column . $keyword . 'SUB_CONDITION';
            $subCondition = '';
            if(is_numeric($values)||is_string($values)) {
                $paramKey = $this->generateRandomKey($column);
                $this->joinParam($paramKey, $values);
                $subCondition = $paramKey;
                
            } else {
                $condition = '';
            }
            if($condition!='') {
                $condition = str_replace('SUB_CONDITION', $subCondition, $condition);
            }
        } else if($keyword==self::KEYWORD_AND||$keyword==self::KEYWORD_OR) {
            $condition = '';
            if(is_array($values)) {
                if(ArrayHelper::isAssoc($values)) {
                    $partSql = $this->parseAssocArrayWhere($values);
                    $condition .= ' ' . $keyword . ' ' . $partSql;
                    $condition = preg_replace('/^\sand\s/', '', $condition);
                    $condition = preg_replace('/^\sor\s/', '', $condition);
                } else {
                    foreach ($values as $key => $value) {
                        if(is_string($value)) {
                            $condition .= ' ' . $keyword . ' ' . $value;
                        } else {
                            
                            /* 开始判断递归执行 */
                            $partSql = $this->parseArrayWhere($value);
                            /* 结束递归执行 */

                            $condition .= ' ' . $keyword . ' ' . $partSql;
                        }
                    }
                    $condition = preg_replace('/^\sand\s/', '', $condition);
                    $condition = preg_replace('/^\sor\s/', '', $condition);
                }
            } else {
                $condition = '';
            }
        }
        if($condition=='') {
            return '';
        }
        $sql = str_replace('CONDITION', $condition, $sql);
        return $sql;
    }

    /**
     * 绑定一个值到用作预处理的where语句中的对应命名占位符或问号占位符
     */
    private function bindValue() {
        if(count($this->_params)>0) {
            $paramsType = 0;
            if(ArrayHelper::isAssoc($this->_params)) {
                $paramsType = 1;
            }
            foreach ($this->_params as $key => $value) {
                $reKey = $key;
                if($paramsType==0) {
                    $reKey ++;
                }
                $this->_stmt->bindValue($reKey,$value);
            }
        }

    }

    /**
     * 绑定一个值到用作预处理的update语句中的对应命名占位符或问号占位符
     */
    private function bindUpdateValue() {
        if(count($this->_updateParams)>0) {
            $paramsType = 0;
            if(ArrayHelper::isAssoc($this->_updateParams)) {
                $paramsType = 1;
            }
            foreach ($this->_updateParams as $key => $value) {
                $reKey = $key;
                if($paramsType==0) {
                    $reKey ++;
                }
                $this->_stmt->bindValue($reKey,$value);
            }
        }
    }

    /**
     * 替换sql语句中各个部位的占位符
     */
    private function replaceMark() {
        $this->_sql = str_replace(self::MARK_SELECT, trim($this->_select), $this->_sql);
        if($this->_join!='') {
            $this->_sql = str_replace(self::MARK_JOIN, trim($this->_join), $this->_sql);
        } else {
            $this->_sql = str_replace(' '.self::MARK_JOIN, trim($this->_join), $this->_sql);
        }
        if($this->_where!='') {
            $this->_sql = str_replace(self::MARK_WHERE, trim($this->_where), $this->_sql);
        } else {
            $this->_sql = str_replace(' '.self::MARK_WHERE, trim($this->_where), $this->_sql);
        }
        if($this->_groupBy!='') {
            $this->_sql = str_replace(self::MARK_GROUP_BY, trim($this->_groupBy), $this->_sql);
        } else {
            $this->_sql = str_replace(' '.self::MARK_GROUP_BY, trim($this->_groupBy), $this->_sql);
        }
        if($this->_orderBy!='') {
            $this->_sql = str_replace(self::MARK_ORDER_BY, trim($this->_orderBy), $this->_sql);
        } else {
            $this->_sql = str_replace(' '.self::MARK_ORDER_BY, trim($this->_orderBy), $this->_sql);
        }
        if($this->_limitSql!='') {
            $this->_sql = str_replace(self::MARK_LIMIT, trim($this->_limitSql), $this->_sql);
        } else {
            $this->_sql = str_replace(' '.self::MARK_LIMIT, trim($this->_limitSql), $this->_sql);
        }
        if($this->_forSql!='') {
            $this->_sql = str_replace(self::MARK_FOR, trim($this->_forSql), $this->_sql);
        } else {
            $this->_sql = str_replace(' '.self::MARK_FOR, trim($this->_forSql), $this->_sql);
        }
    }

    /**
     * 加入搜索表时的数据，用作于bindValue()时设置参数。需要手工调用，常用语当where($where)的参数$where是字符串时
     * @param  array $params   参数数组
     * @return Database        返回自身
     */
    public function joinParams($params) {
        if(is_array($params)&&ArrayHelper::isAssoc($params)) {
            foreach ($params as $key => $value) {
                $this->_params[$key] = $value;
            }
        } else {
            throw new InvalidParamException();
        }
        return $this;
    }

    /**
     * 加入搜索表时的数据，用作于bindValue()时设置参数。
     * @param  sting $key   参数名
     * @param  mixed $value 参数值
     */
    private function joinParam($key, $value) {
        $this->_params[$key] = $value;
    }

    /**
     * 加入更新表时的数据，用作于bindValue()时设置参数。
     * @param  sting $key   参数名
     * @param  mixed $value 参数值
     */
    private function joinUpdateParam($key, $value) {
        $this->_updateParams[$key] = $value;
    }

    /**
     * 生成随机字符KEY，用作where参数的临时储存
     * @param  string $prefix 前缀
     * @return string         生成的key
     */
    private function generateRandomKey($prefix) {
        $strLength = 10;
        $prepareStrArr = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
            'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
            '0','1','2','3','4','5','6','7','8','9','!','@','#','$','%','^','&','*','(',')'];
        $count = count($prepareStrArr);
        $randomStr = '';
        for ($i=0; $i < $strLength; $i++) { 
            $position = rand(0,$count-1);
            $randomStr .= $prepareStrArr[$position];
        }

        $md5 = substr(md5($prefix.$randomStr.microtime().count($this->_params)),8,16);
        $prefixArr = explode('.', $prefix);
        return ':'.$prefixArr[count($prefixArr)-1].$md5;
    }

    public function log() {
        $sql = '';
        if($this->_sqlType=='insert') {
            $insertValues = '';
            foreach ($this->_insertParams as $value) {
                $insertValues .= '\'' . $value . '\',';
            }
            $insertValues = '(' . rtrim($insertValues, ',') . ')';
            $sql = preg_replace('/\((\?,)*\?\)/', $insertValues, $this->_sql);
        } else {
            $params = [];
            foreach ($this->_params as $key => $value) {
                $params[$key] = '\'' . $value . '\'';
            }
            $sql = strtr($this->_sql, $params);
            if($this->_sqlType=='update') {
                $updateParams = [];
                foreach ($this->_updateParams as $key => $value) {
                    $updateParams[$key] = '\'' . $value . '\'';
                }
                $sql = strtr($sql, $updateParams);
            }
        }
        Log::write($sql, 'sql');
    }
}