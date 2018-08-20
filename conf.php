<?php

define('IN_DEV', true);
define('ENV', 'dev');
define('NO_MEMCACHE', true);

define('HIDE_USELESS', true);
define('TITLE_PREFIX', '【国强】');
define('TITLE_SA', '运营系统');

//路径分隔符
if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

//域名
define('BASE_HOST', '.hc001.cn');

define('PIC_HOST', 'p.hc001.cn');

define('ADMIN_HOST', 'o.hc001.cn');
define('CSSJS_HOST', 'o.hc001.cn');
define('OSS_HOST', 'https://img.haocaisong.cn/');     //OSS里面配置的，就是这个，不根据域名变

//路径
define('ROOT_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('TMP_PATH', '/tmp/pic/');
define('CORE_DATA_PATH', ROOT_PATH. 'data/');
define('LOG_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

//config rds
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'oscar');

define('MEMCACHE_HOST', '127.0.0.1');
define('MEMCACHE_PORT', 11211);
