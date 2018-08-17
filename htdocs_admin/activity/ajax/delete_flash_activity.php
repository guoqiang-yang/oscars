<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $fid;

    protected function getPara()
    {
        $this->fid = Tool_Input::clean('r', 'id', TYPE_UINT);
    }

    protected function checkAuth()
    {
        parent::checkAuth('/activity/add_activity_flash');
    }

    protected function main()
    {
        Activity_Flash_Api::delete($this->fid);
    }

    protected function outputPage()
    {
        $result = array('fid' => $this->fid);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }
}

$app = new App('pri');
$app->run();

