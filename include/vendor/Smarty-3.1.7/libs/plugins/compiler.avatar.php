<?php

function smarty_compiler_avatar($params, $compiler)
{
    $str = 'array(';
    foreach ($params as $key => $value)
    {
        if($key == 'uinfo')
        {
            $str .= '"'.addslashes($key).'" => (array)'.$value.',';
        }
        else 
        {
            $str .= '"'.addslashes($key).'" => strval('.$value.'),';
        }
    }
    $str .= ')';

    return '<?php echo Str_Html::getAvatarHtml('.$str.'); ?>';
}