<?php
function smarty_compiler_pinstring($params, $compiler)
{
	$params["filterAt"] = intval($params["filterAt"]);
	return '<?php echo Pin_Raw::parserPinDesc(intval('.$params["uid"].'),'.$params["str"].', intval('.$params["isTxt"].'), intval('.$params["len"].'), strval('.$params["ext"].'), intval('.$params["filterAt"].'));?>';

}