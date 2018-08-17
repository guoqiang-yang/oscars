<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $bid;
    private $cate2;
    private $cate3;

    protected function getPara()
    {
        $this->bid = Tool_Input::clean('r', 'bid', TYPE_UINT);
        $this->cate2 = Tool_Input::clean('r', 'cate2', TYPE_UINT);
        $this->cate3 = Tool_Input::clean('r', 'cate3', TYPE_UINT);
    }

    protected function main()
    {
        if (empty($this->bid))
        {
            throw new Exception('common:params error');
        }

        Shop_Api::deleteBrand($this->bid, $this->cate2);
    }

    protected function outputPage()
    {
        $result = array(
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

