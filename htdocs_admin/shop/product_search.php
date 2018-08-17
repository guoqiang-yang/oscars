<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $keyword;
    private $start;
    private $num = 20;
    private $total;
    private $products = array();
    private $brands;
    private $models;

    protected function getPara()
    {
        $this->keyword = Tool_Input::clean('r', 'keyword', TYPE_STR);
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
    }

    protected function main()
    {
        $this->brands = Shop_Api::getAllBrands();
        $this->models = Shop_Api::getAllModels();

        if ($this->keyword)
        {
            $statusTag = Conf_Product::PRODUCT_STATUS_ONLINE | Conf_Product::PRODUCT_STATUS_OFFLINE | Conf_Product::PRODUCT_STATUS_DELETED;
            $res = Shop_Api::searchProduct($this->keyword, $this->start, $this->num, $statusTag);
            $this->products = $res['list'];
            foreach ($this->products as &$pinfo)
            {
                $mids = array();
                if (!empty($pinfo['sku']['mids']))
                {
                    $mids = explode(',', $pinfo['sku']['mids']);
                }
                $pinfo['sku']['_mids'] = '';
                foreach ($mids as $mid)
                {
                    $pinfo['sku']['_mids'] .= $this->models[$mid]['name'];
                }
            }
            $this->total = $res['total'];
        }

        $this->addFootJs(array('js/apps/shop.js'));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $app = sprintf('/shop/product_search.php?keyword=%s', $this->keyword);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('keyword', $this->keyword);
        $this->smarty->assign('products', $this->products);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('city_list', Conf_City::$CITY);
        $this->smarty->assign('sales_types', Conf_Product::getSalesTypeDesc());
        $this->smarty->assign('buy_types', Conf_Product::getBuyTypeDesc());
        $this->smarty->assign('managing_modes', Conf_Base::getManagingModes());
        $this->smarty->assign('brands', $this->brands);
        $this->smarty->assign('models', $this->models);

        $this->smarty->display('shop/product_search.html');
    }
}

$app = new App('pri');
$app->run();

