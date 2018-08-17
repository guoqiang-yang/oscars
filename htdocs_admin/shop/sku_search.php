<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    // cgi参数
    private $keyword;
    private $start;
    // 中间结果
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
        if ($this->keyword)
        {
            $res = Shop_Api::searchSku($this->keyword, $this->start, $this->num);
            $this->products = $res['list'];
            $this->total = $res['total'];
        }
        $sids = Tool_Array::getFields($this->products, 'sid');

        $cityInfo = City_Api::getCity();
        $cityId = $cityInfo['city_id'];

        $statusTag = Conf_Product::PRODUCT_STATUS_ONLINE | Conf_Product::PRODUCT_STATUS_OFFLINE;
        $products = Shop_Api::getProductsBySids($sids, $cityId, $statusTag);
        $newProducts = Tool_Array::list2Map($products, 'sid');

        $this->brands = Shop_Api::getAllBrands();
        $this->models = Shop_Api::getAllModels();
        foreach ($this->products as &$item)
        {
            $item['product_id'] = 0;
            if (!empty($newProducts[$item['sid']]))
            {
                $item['product_id'] = $newProducts[$item['sid']]['pid'];
            }

            $item['_models'] = array();
            if (!empty($item['mids']))
            {
                $mids = explode(',', $item['mids']);
                foreach ($mids as $mid)
                {
                    $item['_models'][] = $this->models[$mid]['name'];
                }

            }
        }

        $this->addFootJs(array('js/apps/sku.js'));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $app = sprintf('/shop/sku_search.php?keyword=%s', $this->keyword);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('keyword', $this->keyword);
        $this->smarty->assign('list', $this->products);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('brands', $this->brands);
        $this->smarty->assign('models', $this->models);

        $this->smarty->display('shop/sku_search.html');
    }
}

$app = new App('pri');
$app->run();

