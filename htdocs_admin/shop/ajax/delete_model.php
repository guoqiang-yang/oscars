<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $mid;

    protected function getPara()
    {
        $this->mid = Tool_Input::clean('r', 'mid', TYPE_UINT);
    }

    protected function main()
    {
        Shop_Api::deleteModel($this->mid);
    }

    protected function outputPage()
    {
        $result = array('cid' => $this->cid);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App('pri');
$app->run();

