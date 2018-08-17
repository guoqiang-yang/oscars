<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 16/7/13
 * Time: 16:35
 */
include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $suid;
    private $roles;

    protected function checkAuth()
    {
        parent::checkAuth('/admin/edit_staff_role');
    }

    protected function getPara()
    {
        $this->suid = Tool_Input::clean('r', 'suid', TYPE_UINT);
        $this->roles = Tool_Input::clean('r', 'roles', TYPE_ARRAY);
    }

    protected function main()
    {
        $update = array(
            'roles' => implode(',', $this->roles),
        );
        Admin_Api::updateStaff($this->suid, $update);
    }

    protected function outputPage()
    {
        $result = array(
            'id' => $this->id,
            'is_new' => $this->isNew
        );

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }
}

$app = new App('pri');
$app->run();