<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    // cgi参数
    private $start;
    // 中间结果
    private $num = 20;
    private $total;
    private $products;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
    }

    protected function main()
    {

        $res = Shop_Api::getInvalidProductList('no_brand', $this->start, $this->num);
        $this->products = (array)$res['list'];
        $this->total = $res['total'];

        $this->addFootJs(array('js/apps/shop.js'));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $app = '/invalid_product/no_brand.php';
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('products', $this->products);
        $this->smarty->display('invalid_product/product_list.html');
    }
}

$app = new App('pri');
$app->run();

