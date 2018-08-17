<?php

function smarty_compiler_comment($params, $compiler)
{
    $str = 'array(';
    foreach ($params as $key => $value)
    {
        if($key == '_user')
        {
            $str .= '"'.addslashes($key).'" => (array)'.$value.',';
        }
        else
        {
            $str .= '"'.addslashes($key).'" => strval('.$value.'),';
        }
    }
    $str .= ')';

    return '<?php echo Str_Html::getCommentHtml('.$str.'); ?>';
}