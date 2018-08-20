<?php
include_once dirname(__FILE__) . "/conf.php";
include_once dirname(__FILE__) . "/common.php";

spl_autoload_register('__autoload');

function __autoload($className) 
{    
	$pos = strpos($className, '_');
	if (!$pos) 
    {
		return false;
	}
    
	$dir = strtolower(substr($className, 0, $pos));

	//加载
	$classFile = INCLUDE_PATH . $dir . '/' . $className . '.php';

	if (is_file($classFile)) 
    {
		require_once $classFile;
        
		return true;
	}
    
	return false;
}

function my_assert_handler($file, $line, $code) 
{
	$detailLog = _assertDetailLog($file, $line, $code);
	$simpleLog = _assertSimpleLog($file, $line);

	Tool_Log::addFileLog('assert_simple.log', $simpleLog);
	Tool_Log::addLog(ASSERT_LOG_PATH . 'assert_' . date('ymd'), $detailLog);

	throw new Exception('common:system error');
}

// Set up the callback
assert_options(ASSERT_CALLBACK, 'my_assert_handler');

function _assertDetailLog($file, $line, $code) 
{
	$info = "\n<!--Assertion Failed:
        File '$file'<br />
        Line '$line'<br />
        Code '$code'<br />";

	$trace = debug_backtrace();
	foreach ($trace as &$item) {
		unset($item['object']);
	}

	$info .= var_export($trace, true) . "\n";

	return $info;
}

function _assertSimpleLog($file, $line) 
{
	$info = "\nAssert Position:\tFile: " . str_replace(ROOT_PATH, '', $file) . "\t##Line: $line\n";

	$traces = array();
	foreach (debug_backtrace() as $item) {
		if (empty($item['file'])) {
			$traces = array();
			continue;
		}

		$traces[] = str_replace(ROOT_PATH, '', $item['file']) . "\t##" . $item['line'];
	}

	//last one is the visited page
	$info .= "Visited Page:\t" . str_replace(ROOT_PATH, '', $item['file']) . "\n";

	$info .= "CallStack: \n\t" . implode("\n\t", $traces) . "\n";

	return $info;
}