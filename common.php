<?php

define('DEBUG_MODE', defined('IN_TEST') && IN_TEST || defined('IN_DEV') && IN_DEV );

define('SYS_CODE', 'abcdefghijklmnopqrstuvwxyz');

//路径
define('DATA_PATH',             ROOT_PATH . 'data/' );
define('INCLUDE_PATH',          ROOT_PATH . 'include/' );
define('ADMIN_HTDOCS_PATH',     ROOT_PATH . 'htdocs_admin/' );
define('PIC_HTDOCS_PATH',       ROOT_PATH . 'htdocs_pic/' );
define('ADMIN_TEMPLATE_PATH',   ROOT_PATH . 'template_admin/' );
define('MULTILOG_PATH',         LOG_PATH  . 'multilog/' );
define('PIC_FILE_PATH',         DATA_PATH . 'pic/' );
define('OSS_PIC_PATH',          'pic/');

//日志目录
define('ASSERT_LOG_PATH',       MULTILOG_PATH. 'assert/');
define('DEBUG_LOG_PATH',        MULTILOG_PATH. 'debug/');

//编码
define('DB_CHARSET',    'UTF-8');
define('SYS_CHARSET',   'UTF-8');

//通用类型
define('TYPE_INT',  'int');
define('TYPE_UINT', 'uint');
define('TYPE_NUM',  'num');
define('TYPE_STR',  'str');
define('TYPE_ARRAY','arr');
define('TYPE_FILE', 'file');
define('TYPE_HTML', 'html');
define('TYPE_RICHHTML', 'richhtml');
define('TYPE_NOCLEAN',  'noclean');

