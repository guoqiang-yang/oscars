<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $model;
    private $mid;

    protected function getPara()
    {
        $this->model = array(
            'name' => Tool_Input::clean('r', 'name', TYPE_STR),
            'cate2' => Tool_Input::clean('r', 'cate2', TYPE_UINT),
            'cate3' => Tool_Input::clean('r', 'cate3', TYPE_UINT),
        );
        $this->mid = Tool_Input::clean('r', 'mid', TYPE_UINT);
    }

    protected function checkPara()
    {
        if (empty($this->model['name']))
        {
            throw new Exception('shop:empty model name');
        }
        if (empty($this->mid))
        {
            if (empty($this->model['cate2']))
            {
                throw new Exception('shop:empty cate2');
            }
        }
    }

    protected function main()
    {
        if (empty($this->mid))
        {
            $this->mid = Shop_Api::addModel($this->model);;
        }
        else
        {
            $info = array('name' => $this->model['name']);
            Shop_Api::updateModel($this->mid, $info);
        }
    }

    protected function outputPage()
    {
        $result = array('mid' => $this->mid);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App('pri');
$app->run();

