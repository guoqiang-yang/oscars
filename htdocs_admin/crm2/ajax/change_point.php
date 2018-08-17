<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $point;
    private $note;
    private $adjustUid;

    protected function checkAuth()
    {
        parent::checkAuth('/crm2/edit_user_point');
    }

    protected function getPara()
    {
        $this->adjustUid = Tool_Input::clean('r', 'uid', TYPE_UINT);
        $this->note = Tool_Input::clean('r', 'note', TYPE_STR);
        $this->point = Tool_Input::clean('r', 'point', TYPE_UINT);
    }

    protected function main()
    {
        $user = Crm2_Api::getUserInfo($this->adjustUid, false, false);
        Cpoint_Api::addPointByAdmin($user['user']['cid'], $this->adjustUid, $this->point, $this->_user, $this->note);
    }

    protected function outputPage()
    {
        $result = array('res' => 'succ');

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App('pri');
$app->run();