<?php

class Conf_Memcache
{
    
    /**
     * Memcache Key.
     */
    const MEMKEY_STAFF_INFO = 'staff_info_%d';
    const MEMKEY_PERFORMANCE_INFO = 'sales_pfm_%d_%d';
    const PERFORMANCE_INFO_EXPIRE_TIME = 1200;
    const MEMKEY_STATISTICS_MONTH = 'stat_overview_month_%d';
    const MEMKEY_STATISTICS_DAY = 'stat_overview_day_%d';
    
    
    
    public static function getMemcacheKey($key, $values)
    {
        if (empty($key) || empty($values)) return '';
        
        if (is_array($values))
        {
            return vsprintf($key, $values);
        }
        else
        {
            return sprintf($key, $values);
        }
    }
    
    
}