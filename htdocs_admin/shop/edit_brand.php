<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    // cgi参数
    private $bid;
    private $cate1;
    private $cate2;
    private $cate3;
    // 中间结果
    private $brand;

    protected function getPara()
    {
        $this->bid = Tool_Input::clean('r', 'bid', TYPE_UINT);
        $this->cate1 = Tool_Input::clean('r', 'cate1', TYPE_UINT);
        $this->cate2 = Tool_Input::clean('r', 'cate2', TYPE_UINT);
        $this->cate3 = Tool_Input::clean('r', 'cate3', TYPE_UINT);
    }

    protected function main()
    {
        $res = Shop_Api::getBrand($this->bid, $this->cate2, $this->cate3);
        $this->brand = $res;

        $this->addFootJs(array('js/apps/shop.js'));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $this->smarty->assign('brand', $this->brand);
        $this->smarty->assign('cate1', $this->cate1);
        $this->smarty->assign('cate2', $this->cate2);
        $this->smarty->assign('cate3', $this->cate3);
        $this->smarty->display('shop/edit_brand.html');
    }
}

$app = new App('pri');
$app->run();

