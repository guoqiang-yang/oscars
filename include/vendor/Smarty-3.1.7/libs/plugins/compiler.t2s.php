<?php
function smarty_compiler_t2s($params, $compiler)
{
	return '<?php echo Tool_Str::t2s(strval('.$params["t"].'));?>';
}