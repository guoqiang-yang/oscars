<?php

/**
 * 单表数据库CURD
 */
class Data_One
{
    private static $_prx = NULL;
    private $dbProxy;

    private function __construct()
    {
        $this->_resetProxy();
    }

    public function setKProxyMode()
    {
        $this->dbProxy = Ms_DBMan::getInstance();
        return $this;
    }

    public function setDBMode()
    {
        $this->dbProxy = Data_DB::getInstance();
        return $this;
    }

    private function _resetProxy()
    {
        if (defined('USE_KPROXY') && USE_KPROXY)
        {
            $this->dbProxy = Ms_DBMan::getInstance();
        }
        else
        {
            $this->dbProxy = Data_DB::getInstance();
        }
    }

    public function setSlaveDB()
    {
        $this->dbProxy->setSlaveDB();
        return $this;
    }

    public function setDefaultDB()
    {
        $this->dbProxy->setDefaultDB();
        return $this;
    }

    public static function getInstance()
    {
        if (empty(self::$_prx))
        {
            self::$_prx = new Data_One();
        }
        return self::$_prx;
    }

    public function insert($kind, $insertField, $updateField = array(), $changeField = array(), $notEscFields = array())
    {
        //TODO: 输入检查 ==> warnning 日志
        if (empty($insertField))
            return false;

        $startTime = microtime(true);
        $arr = array();

        $sql = Tool_Sql::insert($kind, $insertField, $updateField, $changeField, $notEscFields);
        $arr['sql'] = $sql;
        $res = $this->dbProxy->sQuery($kind, 1, $sql);

        $end = microtime(true);
        $arr['extime'] = $end - $startTime;
        global $HC_SQL_EXTIMES;
        $HC_SQL_EXTIMES[] = $arr;

        $this->_resetProxy();
        return $res;
    }

    public function batchInsert($kind, $insertField, $notEscFields = array())
    {
        $sql = Tool_Sql::batchInsert($kind, $insertField, $notEscFields);
        $res = $this->dbProxy->sQuery($kind, 1, $sql);
        $this->_resetProxy();
        return $res;
    }

    public function delete($kind, $where)
    {
        $startTime = microtime(true);
        $arr = array();

        if (is_array($where))
        {
            $sql = Tool_Sql::delete($kind, $where);
        } else if (is_string($where))
        {
            if (strlen($where) == 0)
            {
                assert(0); //不允许删除所有
                return false;
            }
            $sql = "delete from	" . $kind . " where	" . $where;
        } else
        {
            assert(0);
        }
        $arr['sql'] = $sql;

        $res = $this->dbProxy->sQuery($kind, 1, $sql);

        $end = microtime(true);
        $arr['extime'] = $end - $startTime;
        global $HC_SQL_EXTIMES;
        $HC_SQL_EXTIMES[] = $arr;

        $this->_resetProxy();
        return $res;
    }

    public function update($kind, $updateField, $changeField = array(), $where, $notEscFields = array())
    {
        $startTime = microtime(true);
        $arr = array();

        $sql = Tool_Sql::update($kind, $updateField, $changeField, $where, $notEscFields);
        if (empty($sql))
        {
            return false;
        }
        $arr['sql'] = $sql;
        $res = $this->dbProxy->sQuery($kind, 1, $sql);

        $end = microtime(true);
        $arr['extime'] = $end - $startTime;
        global $HC_SQL_EXTIMES;
        $HC_SQL_EXTIMES[] = $arr;

        $this->_resetProxy();

        return $res;
    }

    public function select($kind, $selectField, $where, $order = "", $start = 0, $num = 0, $cacheTime = 0)
    {
        $startTime = microtime(true);
        $arr = array();

        if (is_array($where))
        {
            $sql = Tool_Sql::select($kind, $selectField, $where, $order, $start, $num);

        } else if (is_string($where))
        {
            $sql = Tool_Sql::selectRawWhere($kind, $selectField, $where, $order, $start, $num);
        } else
        {
            assert(false);
        }
        $arr['sql'] = $sql;
        $res = $this->dbProxy->sQuery($kind, 1, $sql, $cacheTime);

        $end = microtime(true);
        $arr['extime'] = $end - $startTime;
        global $HC_SQL_EXTIMES;
        $HC_SQL_EXTIMES[] = $arr;

        $this->_resetProxy();
        return $res;
    }
}
