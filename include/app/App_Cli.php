<?php
/**
 * 后台程序/命令行(command line interface)程序基类
 */

// 设定时区
date_default_timezone_set('PRC');

// 报错模式
error_reporting(E_ERROR);

class App_Cli extends Base_Func
{
    protected $responseData = array();
    
    private static $sku2Cate1Infos = array();
    private static $customerInfo = array();

    function run()
    {
        try
        {
            $this->getPara();
            $this->main();
        }
        catch (Exception $ex)
        {
            $this->showError($ex);
        }
    }

    protected function getPara()
    {
    }

    protected function main()
    {
    }

    protected function showError($ex)
    {
        $error = "[" . $ex->getCode() . "]: " . $ex->getMessage();
        if ($ex->reason)
        {
            $error .= ' ' . $ex->reason;
        }

        echo $error . "\n";
        print_r($ex->getTrace());
        echo "\n";
    }

    protected function _trace($textFormat /*$param*/)
    {
        $format = '[' . date('Y-m-d H:i:s') . '] [Trace] ' . $textFormat . "\n";
        $params = array_slice(func_get_args(), 1);
        $log = vsprintf($format, $params);
        echo $log;
    }

    protected function _error($textFormat /*$param*/)
    {
        $format = '[' . date('Y-m-d H:i:s') . '] [Error] ' . $textFormat . "\n";
        $params = array_slice(func_get_args(), 1);
        $log = vsprintf($format, $params);
        echo $log;
    }
    
    /**
     * 读取db数据, 并通过自定义回调处理数据.
     */
    protected function getDbDatas($table, $callbackName, $where, $field, $start, $num, $order)
    {
        if (empty($table) || empty($callbackName) || empty($where)) 
        {
            echo "Fatal Error! Plase Check Params!\n"; exit;
        }
        
        $hDao = new Data_Dao($table);
        
        if (empty($hDao))
        {
            echo "Fatal Error! $table no defined in Conf_Dao!\n"; exit;
        }
        
        while (true)
        {
            $dataList = $hDao->setFields($field)
                             ->order($order)
                             ->limit($start, $num)
                             ->getListWhere($where);
            
            if (empty($dataList)) break;
            
            $this->$callbackName($dataList);
            
            $start += $num;
        }
    }

    /**
     * 获取skuid的分类1数据.
     */
    public static function getCate1BySids($sids)
    {
        if (!is_array($sids))
        {
            $sids = array($sids);
        }
        
        $sid2Cate1 = array();
        $unMarchSids = array();
        
        foreach($sids as $_sid)
        {
            if (array_key_exists($_sid, self::$sku2Cate1Infos))
            {
                $sid2Cate1[$_sid] = self::$sku2Cate1Infos[$_sid];
            }
            else
            {
                $unMarchSids[] = $_sid;
            }
        }
        
        if (!empty($unMarchSids))
        {
            $hSku = new Data_Dao('t_sku');
            $skuInfos = $hSku->setFields(array('sid', 'cate1'))->getList($unMarchSids);
            
            foreach($skuInfos as $item)
            {
                self::$sku2Cate1Infos[$item['sid']] = $item['cate1'];
                $sid2Cate1[$item['sid']] = $item['cate1'];
            }
        }
        
        return $sid2Cate1;
    }

    public static function getCustomerInfo($cids)
    {
        $res = array();
        $leftCids = array();
        
        foreach($cids as $_cid)
        {
            if (!array_key_exists($_cid, self::$customerInfo)) 
            {
                $leftCids[] = $_cid;
            }
            else
            {
                $res[$_cid] = self::$customerInfo[$_cid];
            }
        }
        
        if (empty($leftCids)) return $res;
        
        $hCustomer = new Data_Dao('t_customer');
        $leftCustomers = $hCustomer->setFields(array('cid', 'name'))->getList($leftCids);
        
        foreach($leftCustomers as $item)
        {
            $res[$item['cid']] = $item;
            self::$customerInfo[$item['cid']] = $item;
        }
        
        return $res;
    }

    protected function getStaffList($suids=array())
    {
        $where = empty($suids)? '1=1': 'suid in ('.implode(',', $suids).')';
        
        $hStaff = new Data_Dao('t_staff_user');
        $staffList = $hStaff->setFields(array('suid', 'name'))->getListWhere($where);
        
        return $staffList;
    }

    /**
     * @NOTICE!!!
     * 
     *  老方法，不推荐使用！！！！
     *  addby guoqiang/2018-05-11 
     */
    protected function getDbAllDatas($table, $filed, $where, $order = '', $start = 0, $num = 100, $isUpStart = TRUE)
    {
        do
        {
            $ret = $this->one->select($table, $filed, $where, $order, $start, $num);

            if (!empty($ret['data']))
            {
                $hasUpdateRecord = $this->callbackGetAllDatas($ret['data']);
            }
            
            // 是否更新$start游标；一般是要更新的，不更新改字段
            // 在up全表数据是，因为更新了表数据，在取数据时，会导致更新数据丢失
            if ($isUpStart)
            {
                $start += $num;
            }
            else
            {
                if (!$hasUpdateRecord || empty($hasUpdateRecord))
                {
                    break;
                }
            }
        }
        while (!empty($ret['data']));

        return $this->responseData;
    }

    protected function callbackGetAllDatas($ret = array())
    {
        $this->responseData = array_merge($this->responseData, $ret);
    }
}
