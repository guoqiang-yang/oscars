<?php
function smarty_compiler_substring($params, $compiler)
{
	$nofitler = intval($params['nofilter']);
	if ($nofitler)
	{
		return '<?php echo Tool_Str::subString(strval('.$params["str"].'), intval('.$params["len"].'), strval('.$params["ext"].'));?>';
	}
	else
	{
		return '<?php echo htmlspecialchars(Tool_Str::subString(strval('.$params["str"].'), intval('.$params["len"].'), strval('.$params["ext"].')));?>';
	}
}