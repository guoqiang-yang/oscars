<?php
function smarty_compiler_filterstring($params, $compiler)
{
	return '<?php echo Str_Chinese::filterSpaceLine(strval('.$params["str"].'), intval('.$params["len"].'), strval('.$params["ext"].'));?>';

}