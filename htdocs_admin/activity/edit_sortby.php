<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $info;
    private $id;

    protected function getPara()
    {
        $this->info = array(
            'display_order' => Tool_Input::clean('r', 'sortby', TYPE_INT),
            'suid' => $this->_uid,
        );
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
    }

    protected function main()
    {
        if ($this->id)
        {
            Activity_Api::updatePicture($this->id, $this->info);
        }
        $referer = $_SERVER['HTTP_REFERER'];
        header('Location: ' . $referer);
        exit;
    }

    protected function outputBody()
    {
    }
}

$app = new App('pri');
$app->run();
