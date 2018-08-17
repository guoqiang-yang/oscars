<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $bid;
    private $name;
    private $cate1;
    private $cate2;
    private $cate3;

    protected function getPara()
    {
        $this->name = Tool_Input::clean('r', 'name', TYPE_STR);
        $this->bid = Tool_Input::clean('r', 'bid', TYPE_UINT);
        $this->cate1 = Tool_Input::clean('r', 'cate1', TYPE_UINT);
        $this->cate2 = Tool_Input::clean('r', 'cate2', TYPE_UINT);
        $this->cate3 = Tool_Input::clean('r', 'cate3', TYPE_UINT);
    }

    protected function checkAuth()
    {
        parent::checkAuth('/shop/edit_brand');
    }
    
    protected function main()
    {
        if (empty($this->bid))
        {
            $this->bid = Shop_Api::addBrand($this->name, $this->cate2);
        }
        else
        {
            $info = array('name' => $this->name);
            Shop_Api::updateBrand($this->bid, $info);
        }
    }

    protected function outputPage()
    {
        $result = array(
            'cate1' => $this->cate1,
            'cate2' => $this->cate2,
            'cate3' => $this->cate3,
            'bid' => $this->bid,
        );

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App('pri');
$app->run();

