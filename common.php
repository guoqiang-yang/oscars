<?php

define('DEBUG_MODE', defined('IN_TEST') && IN_TEST || defined('IN_DEV') && IN_DEV );

//路径
define('DATA_PATH', ROOT_PATH . 'data/' );
define('INCLUDE_PATH', ROOT_PATH . 'include/' );
define('C_H5_HTDOCS_PATH', ROOT_PATH	. 'htdocs_client_h5/' );
define('C_H5_TEMPLATE_PATH',	ROOT_PATH . 'template_client_h5/' );
define('ADMIN_HTDOCS_PATH',	ROOT_PATH . 'htdocs_admin/' );
define('COOPWORDER_H5_PATH', ROOT_PATH. 'htdocs_coopworker_h5/');
define('QY_PATH', ROOT_PATH. 'htdocs_qiye/');
define('PIC_HTDOCS_PATH',	ROOT_PATH . 'htdocs_pic/' );
define('API_HTDOCS_PATH',   ROOT_PATH . 'htdocs_openapi/');
define('APPAPI_HTDOCS_PATH',   ROOT_PATH . 'htdocs_appapi/');
define('ADMIN_TEMPLATE_PATH', ROOT_PATH	. 'template_admin/' );
define('ADMIN_TEMPLATE_H5_PATH', ROOT_PATH	. 'template_admin_h5/' );
define('COOPWORKER_TPL_H5_PATH', ROOT_PATH.'template_coopworker_h5/');
define('QY_TPL_PATH', ROOT_PATH. 'template_qiye/');
define('FSA_TPL_PATH', ROOT_PATH. 'template_franchisee/');
define('MULTILOG_PATH',	LOG_PATH . 'multilog/' );
define('PIC_FILE_PATH', DATA_PATH . 'pic/' );
define('WX_DOWNLOAD_PIC_PATH', DATA_PATH. 'wx_pic/');
define('OSS_PIC_PATH', 'pic/');
define('QRCODE_FILE_PATH', ADMIN_HTDOCS_PATH . 'i/qrcode/');
define('APP_API_HTDOCS_PATH',   ROOT_PATH . 'htdocs_app_api/');
define('DRIVER_API_HTDOCS_PATH',   ROOT_PATH . 'htdocs_driver_api/');
define('CRM_HTDOCS_PATH',   ROOT_PATH . 'htdocs_crm/');
define('CRM_TEMPLATE_PATH', ROOT_PATH . 'template_crm/' );
define('TOC_H5_HTDOCS_PATH',   ROOT_PATH . 'htdocs_toc_h5/');
define('TOC_H5_TEMPLATE_PATH', ROOT_PATH . 'template_toc_h5/' );
define('CORP_TEMPLATE_PATH', ROOT_PATH . 'template_corp/' );

//日志目录
define('ASSERT_LOG_PATH', SYSLOG_PATH. 'assert/');
define('DEBUG_LOG_PATH', SYSLOG_PATH. 'debug/');

//密码
define('SYS_CODE', 'abcdefghijklmnopqrstuvwxyz');

//编码
define('DB_CHARSET', 'UTF-8');
define('SYS_CHARSET', 'UTF-8');

//通用类型
define('TYPE_INT', 'int');
define('TYPE_UINT', 'uint');
define('TYPE_NUM', 'num');
define('TYPE_STR', 'str');
define('TYPE_ARRAY', 'arr');
define('TYPE_FILE', 'file');
define('TYPE_HTML', 'html');
define('TYPE_RICHHTML', 'richhtml');
define('TYPE_NOCLEAN', 'noclean');

//路径分隔符
if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

define('APP_VERSION', 'v2.0');
define('DRIVER_VERSION', 'v1.0');
define('CRM_VERSION', 'v1.0');
