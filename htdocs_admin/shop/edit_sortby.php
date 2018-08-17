<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $sortby;
    private $sid;
    private $type;

    protected function getPara()
    {
        $this->sortby = Tool_Input::clean('r', 'sortby', TYPE_INT);
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
        $this->type = Tool_Input::clean('r', 'type', TYPE_STR);
    }

    protected function main()
    {
        if ($this->type == 'model')
        {
            $update = array('sortby' => $this->sortby);
            Shop_Api::updateModel($this->sid, $update);
        }
        else if ($this->type == 'brand')
        {
            $update = array('sortby' => $this->sortby);
            Shop_Api::updateCateBrand($this->sid, $update);
        }
        else
        {
            //我艹，其实是pid
            Shop_Api::updateProduct($this->sid, array('sortby' => $this->sortby));
        }

        $referer = $_SERVER['HTTP_REFERER'];
        header('Location: ' . $referer);
    }

    protected function outputBody()
    {

    }
}

$app = new App('pri');
$app->run();
