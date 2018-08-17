<?php

define('IN_DEV', true);

define('ENV', 'dev');

define('PIC_HOST', 'p.haocai001.cn');

//域名
define('BASE_HOST', '.haocai001.cn');
define('WWW_HOST', 'm.haocai001.cn');
define('C_H5_MAIN_HOST', 'm.haocai001.cn');
define('C_H5_WWW_HOST', 'm.haocai001.cn');
define('C_H5_IMG_HOST', 'm.haocai001.cn');

define('ADMIN_HOST_H5', 'm.sa.haocai001.cn');

define('ADMIN_HOST', 'sa.haocai001.cn');
define('CSSJS_HOST', 'sa.haocai001.cn');
define('ADMIN_IMG_HOST', 'sa.haocai001.cn');
define('OSS_HOST', 'http://img.haocaisong.cn/'); //OSS里面配置的，就是这个，不根据域名变

//路径
define('ROOT_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('SYSLOG_PATH', '/logs/haocai_syslog/');
define('TMP_PATH', '/tmp/');

//config db
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'dev');
define('DB_PASS', 'zhuoyue001');
define('DB_NAME', 'haocai');

define('MEMCACHE_HOST', '127.0.0.1');
define('MEMCACHE_PORT', 11211);

define('WX_APP_ID', 'wxc229c7682f1d2284');
define('WX_APP_SECRET', 'ea446c0b26c0364d2fc2f48c05847850');
define('WX_MCID', '1284445501');
define('WX_MKEY', '21ca9eb94d5e984bb03872b09eb441d6');

define('WX_APP_APP_ID', 'wx00c192b586a56ead');
define('WX_APP_APP_SECRET', '65c4bb9289c72507bc3f1a6c357898a7');
define('WX_APP_MCID', '1336772501');

define('MS', 'http://localhost:8080');