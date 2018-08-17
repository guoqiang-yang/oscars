<?php
include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $cate2;
    private $mid;
    private $bid;
    private $brands;

    protected function getPara()
    {
        $this->cate2 = Tool_Input::clean('r', 'cate2', TYPE_UINT);
        $this->mid = Tool_Input::clean('r', 'mid', TYPE_UINT);
        $this->bid = Tool_Input::clean('r', 'bid', TYPE_UINT);
    }

    protected function main()
    {
        $this->brands = Shop_Api::getBrandList($this->cate2);
    }

    private function getSelectList()
    {
        $list = array();
        foreach ($this->brands as $brand)
        {
            $selected = ($brand['bid'] == $this->bid) ? 1 : 0;
            $list[] = array(
                'id' => $brand['bid'],
                'name' => $brand['name'],
                'selected' => $selected
            );
        }

        return $list;
    }

    protected function outputPage()
    {
        $result = array(
            'list' => $this->getSelectList(),
        );

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App('pub');
$app->run();

