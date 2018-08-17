<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $id;
    private $type;

    protected function checkAuth()
    {
        parent::checkAuth('/activity/add_picture');
    }

    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->type = Tool_Input::clean('r', 'type', TYPE_STR);
    }

    protected function main()
    {
        $status = array('status' => Conf_Base::STATUS_OFFLINE);
        if ($this->type == 'online')
        {
            $status = array('status' => Conf_Base::STATUS_NORMAL);
        }
        Activity_Api::updatePicture($this->id, $status);
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

