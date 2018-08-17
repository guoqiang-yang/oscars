<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 17/11/1
 * Time: 16:47
 */
class Admin_Login_Log extends Base_New_Func
{
    public function __construct()
    {
        parent::__construct('t_admin_login_log');
    }

    protected function genWhereByArr($searchConfArr)
    {
        $where = '1=1';

        if (!empty($searchConfArr['suid']))
        {
            $where .= sprintf(' AND suid=%d', $searchConfArr['suid']);
        }

        return $where;
    }
}