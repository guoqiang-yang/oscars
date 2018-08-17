<?php

/**
 * DBMan
 * 1.LCache功能
 * 2.强制主库查询
 * 3.数据格式转换
 */
class Ms_DBMan
{
    private static $instances = array();
    private static $servantName = 'DBMan';
    private static $jproxy;

    private $_prx;    //实际中间层访问句柄

    private function __construct($prx)
    {
        $this->_prx = $prx;
    }

    public static function getInstance($group = '')
    {
        if (empty(self::$instances[$group]))
        {
            if (empty(self::$jproxy))
            {
                self::$jproxy = new Ms_JProxy();
            }
            if (empty($group))
            {
                $servantName = self::$servantName;
            } else
            {
                $servantName = self::$servantName . "#" . $group;
            }
            $prx = self::$jproxy->createProxy($servantName);

            self::$instances[$group] = new Ms_DBMan($prx);
        }

        return self::$instances[$group];
    }

    /**
     * 单条sql查询
     * @param $kind
     * @param $hintId
     * @param $sql
     * @param $cacheTime : <0 - 强制更新LCache，读主库;
     *                    0 - 不缓存到LCache;
     *                   >0 - 缓存到LCache，时长为$cacheTime
     * @return array
     */
    public function sQuery($kind, $hintId, $sql, $cacheTime = 0)
    {
        $ctx = self::$jproxy->ctxCache($cacheTime);
        $para = array(
            'kind' => $kind, 'hintId' => intval($hintId), 'sql' => $sql, 'convert' => true, 'master' => $cacheTime < 0,
        );
        $result = $this->_prx->invoke('sQuery', $para, $ctx);
        return $this->formatSResult($result);
    }

    /**
     * 多条sql查询
     */
    public function mQuery($kind, $hintId, $sqls, $cacheTime = 0)
    {
        $ctx = self::$jproxy->ctxCache($cacheTime);
        $para = array(
            'kind' => $kind, 'hintId' => intval($hintId), 'sqls' => $sqls, 'convert' => true, 'master' => $cacheTime < 0,
        );
        $result = $this->_prx->invoke('mQuery', $para, $ctx);
        return $this->formatMResult($result);
    }

    /**
     * 格式化sQuery结果
     */
    private function formatSResult($sResult)
    {
        $data = array();
        $insertId = intval($sResult['insertId']);
        $affectedRowNumber = intval($sResult['affectedRowNumber']);
        if (isset($sResult['rows']))
        {
            $rownum = count($sResult['rows']);
            foreach ($sResult['rows'] as $i => $row)
            {
                $array = array();
                foreach ($sResult['fields'] as $id => $field)
                {
                    $array[$field] = $row[$id];
                }
                $data[$i] = $array;
            }
        } else
        {
            $rownum = 0;
        }
        return array(
            'data' => $data, 'rownum' => $rownum, 'insertid' => $insertId, 'affectedrows' => $affectedRowNumber
        );
    }

    /**
     * 格式化mQuery结果
     */
    private function formatMResult($mResult)
    {
        $data = array();
        foreach ($mResult['results'] as $sResult)
        {
            $data[] = $this->formatSResult($sResult);
        }
        return $data;
    }

    public function setSlaveDB()
    {
        //todo:
        return $this;
    }

    public function setDefaultDB()
    {
        //todo:
        return $this;
    }
}
