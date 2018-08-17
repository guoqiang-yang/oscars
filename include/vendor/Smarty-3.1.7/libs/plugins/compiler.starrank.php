<?php
/*
 * Created on 2012-3-14
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 function smarty_compiler_starrank($params, $compiler)
{
    $str = 'array(';
    foreach ($params as $key => $value)
    {
        $str .= '"'.addslashes($key).'" => strval('.$value.'),';
    }
    $str .= ')';

    return '<?php echo Str_Html::getStarRankHtml('.$str.'); ?>';
}


