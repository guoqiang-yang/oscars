<?php

/**
 * 管理员操作日志.
 * 
 */


class Admin_Log_Common extends Base_Func
{
    private $table;
    
    function __construct($year)
    {
        assert(!empty($year) && $year>=2017);
        
        parent::__construct();
        
        //$this->table = 't_admin_log_'.$year;
        $this->table = 't_admin_log_2017';
    }
    
    public function add(array $info)
	{
		assert( !empty($info) );

        if (!empty($info['wid']))
        {
            $wid2Cityid = Conf_Warehouse::$WAREHOUSE_CITY_MAPPING;
            $info['city_id'] = $wid2Cityid[$info['wid']];
        }
        
		$res = $this->one->insert($this->table, $info);
        
		return $res['insertid'];
	}
    
    public function getByWhere($conf, $start=0, $num=20)
    {
        assert( !empty($conf['obj_id']) || !empty($conf['obj_type']));
        
        $where = $this->_genWhere($conf);
        
        $order = 'order by lid desc';
        $ret = $this->one->select($this->table, array('*'), $where, $order, $start, $num);
        
        $formats = Conf_Admin_Log::getFormatByObjType($conf['obj_type']);
        $citys = Conf_City::$CITY;
        $warehouses = Conf_Warehouse::$WAREHOUSES;
        
        $logs = $ret['data'];
        foreach($logs as &$log)
        {
            $format = $formats[$log['action_type']];
            $log['action_name'] = $format['name'];
            $log['content'] = $this->_parseContentByObjtype($log, $format['format']);
            $log['city_name'] = array_key_exists($log['city_id'], $citys)? $citys[$log['city_id']]: '-';
            $log['wid_name'] = array_key_exists($log['wid'], $warehouses)? $warehouses[$log['wid']]: '-';
        }
        
        return $logs;
    }
    
    public function getSumByWhere($conf)
    {
        assert( !empty($conf['obj_id']) || !empty($conf['obj_type']));
        
        $where = $this->_genWhere($conf);
        
        $ret = $this->one->select($this->table, array('count(1)'), $where);
        
        return intval($ret['data'][0]['count(1)']);
    }
    
    private function _parseContentByObjtype($log, $format)
    {
        $params = json_decode($log['params'], true);
        
        $content = '-';
        if (!empty($params))
        {
            $_search = array();
            $_replace = array();
            foreach($params as $k => $v)
            {
                $_search[] = '{'. $k .'}';
                $_replace[] = $v;
            }
            
            $content = str_replace($_search, $_replace, $format);
        }
        else if (!empty($format))
        {
            $content = $format;
        }
        
        return $content;
    }
    
    private function _genWhere($conf)
    {
        $where = sprintf('obj_id=%d and obj_type=%d', $conf['obj_id'], $conf['obj_type']);
        
        if ($this->is($conf['action_type']))
        {
            $where .= ' and action_type='. $conf['action_type'];
        }
        if ($this->is($conf['city_id']))
        {
            $where .= ' and city_id='. $conf['city_id'];
        }
        if ($this->is($conf['wid']))
        {
            $where .= ' and wid='.$conf['wid'];
        }
        
        return $where;
    }
    
}