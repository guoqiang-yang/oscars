<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $id;

    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
    }

    protected function checkAuth()
    {
        parent::checkAuth('/activity/add_picture');
    }

    protected function main()
    {
        Activity_Api::deletePicture($this->id);
    }

    protected function outputPage()
    {
        $result = array('id' => $this->id);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }
}

$app = new App('pri');
$app->run();