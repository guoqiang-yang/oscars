<?php

define('IN_DEV', true);

define('ENV', 'test');
define('TITLE_PREFIX', '【测试】');

define('PIC_HOST', 'p.prepub.haocaisong.cn');

//域名
define('BASE_HOST', '.prepub.haocaisong.cn');
define('WWW_HOST', 'shop.prepub.haocaisong.cn');
define('C_H5_MAIN_HOST', 'shop.prepub.haocaisong.cn');
define('C_H5_WWW_HOST', 'shop.prepub.haocaisong.cn');
define('C_H5_IMG_HOST', 'shop.prepub.haocaisong.cn');

define('ADMIN_HOST_H5', 'm.sa.prepub.haocaisong.cn');

define('ADMIN_HOST', 'sa.prepub.haocaisong.cn');
define('CSSJS_HOST', 'sa.prepub.haocaisong.cn');
define('ADMIN_IMG_HOST', 'sa.prepub.haocaisong.cn');
define('OSS_HOST', 'http://img.haocaisong.cn/'); //OSS里面配置的，就是这个，不根据域名变

define('COOPWORDER_H5_HOST', 'm.co.prepub.haocaisong.cn'); //第三方合作工人

//路径
define('ROOT_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('TMP_PATH', '/tmp/');
define('CORE_DATA_PATH', ROOT_PATH . 'data/');
define('SYSLOG_PATH', '/logs/haocai_syslog/');
define('LOG_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

//config db
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'haocai_test');

define('DB_HOST_SLAVE', '127.0.0.1');
define('DB_USER_SLAVE', 'root');
define('DB_PASS_SLAVE', '');
define('DB_NAME_SLAVE', 'haocai_test');

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

define('MS', 'http://172.16.205.233:1201');

