<?php
/**
 * Api 基础类： 只有API暴露给htdocs等控制层）
 */

class Base_Api
{
    protected static function setResponse($code, $data=array(), $msg='')
    {
        return array(
            'errno' => $code,
            'errmsg' => $msg,
            'data' => $data,
        );
    }
    
    
}
