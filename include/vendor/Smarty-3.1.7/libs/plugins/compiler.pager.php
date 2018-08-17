<?php
function smarty_compiler_pager($params, $compiler)
{
	if (! isset($params["format"]))
	{
		$params["format"] = '';
	}
	$_pager_method = $params["format"]."PageHtml";
	if (! method_exists("Str_Html", $_pager_method) ) {
		$_pager_method = "ajaxPageHtml";
	}
	$method = "Str_Html::".$_pager_method;
	return '<?php echo call_user_func_array("'.$method.'",array(intval('.$params["start"].'), intval('.$params["num"].'), intval('.$params["total"].'), strval('.$params["app"].'))); ?>';
}