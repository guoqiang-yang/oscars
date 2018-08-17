<?php

define('IN_DEV', true);
define('ENV', 'dev');
define('NO_MEMCACHE', true);

define('HIDE_USELESS', true);
define('TITLE_PREFIX', '【国强】');

define('PIC_HOST', 'p.haocai001.cn');

//域名
define('BASE_HOST', '.haocai001.cn');
define('WWW_HOST', 'shop.haocai001.cn');
define('C_H5_MAIN_HOST', 'm.haocai001.cn');
define('C_H5_WWW_HOST', 'm.haocai001.cn');
define('C_H5_IMG_HOST', 'm.haocai001.cn');
define('FRANCHISEE_HOST', 'fsa.haocai001.cn');
define('DRIVER_HOST', 'driver.api.haocai001.cn');

define('ADMIN_HOST_H5', 'm.sa.haocai001.cn');

define('ADMIN_HOST', 'sa.haocai001.cn');
define('CSSJS_HOST', 'sa.haocai001.cn');
define('ADMIN_IMG_HOST', 'sa.haocai001.cn');
define('OSS_HOST', 'https://img.haocaisong.cn/');     //OSS里面配置的，就是这个，不根据域名变


define('COOPWORDER_H5_HOST', 'm.co.haocai001.cn');
define('QY_HOST', 'qy.haocai001.cn');              //企业号域名
define('SCAN_H5_HOST', 'scan.haocai001.cn');
define('CRM_HOST', 'crm.haocai001.cn');

define('WEB_CORP_HOST', 'www.haocai001.cn');
define('WAP_CORP_HOST', 'm.haocai001.cn');
define('QINGYUN_HOST', 'http://yun.haocaisong.cn');

//路径
define('ROOT_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('TMP_PATH', '/tmp/pic/');
define('CORE_DATA_PATH', ROOT_PATH. 'data/');
define('SYSLOG_PATH', '/Users/guoqiang00/workspace/haocai/hc/multilog/assert/');
define('LOG_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

//config rds
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'haocai');

define('MEMCACHE_HOST', '127.0.0.1');
define('MEMCACHE_PORT', 11211);

define('WX_APP_ID', 'wxc229c7682f1d2284');
define('WX_APP_SECRET', 'ea446c0b26c0364d2fc2f48c05847850');
define('WX_MCID', '1284445501');
define('WX_MKEY', '21ca9eb94d5e984bb03872b09eb441d6');

define('WX_APP_APP_ID', 'wx00c192b586a56ead');
define('WX_APP_APP_MKEY', '440cb5f7586ce6254b4cef1816054ba6');
define('WX_APP_MCID', '1336772501');

define('WX_COOPWORKER_APP_ID', 'wxc80b7f0cd61c4a87');
define('WX_COOPWORKER_SECRET', '6ea66d972370963ac790ba3eb2774d67');
