<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $cate1;
    private $cate2;
    private $cate3;
    private $bid;
    private $mid;
    private $start;
    private $searchConf;
    private $num = 20;
    private $total;
    private $list;
    private $brands;
    private $models;
    private $online;
    private $defaultWid;

    protected function getPara()
    {
        $this->cate1 = Tool_Input::clean('r', 'cate1', TYPE_UINT);
        $this->cate2 = Tool_Input::clean('r', 'cate2', TYPE_UINT);
        $this->cate3 = Tool_Input::clean('r', 'cate3', TYPE_UINT);
        $this->bid = Tool_Input::clean('r', 'bid', TYPE_UINT);
        $this->mid = Tool_Input::clean('r', 'mid', TYPE_UINT);
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->online = Tool_Input::clean('r', 'online', TYPE_UINT);
    }

    protected function checkPara()
    {
        if (empty($this->cate1))
        {
            $this->cate1 = 1;
        }
        if (empty($this->cate2))
        {
            $cate2List = Conf_Sku::$CATE2[$this->cate1];
            $this->cate2 = array_shift(array_keys($cate2List));
        }
        if (empty($this->cate3))
        {
            $cate3List = Conf_Sku::$CATE3[$this->cate2];
            $this->cate3 = array_shift(array_keys($cate3List));
        }
    }

    protected function main()
    {
        $this->searchConf = array(
            'cate1' => $this->cate1,
            'cate2' => $this->cate2,
            'cate3' => $this->cate3,
            'bid' => $this->bid,
            'mid' => $this->mid,
        );
        $res = Shop_Api::getSkuList($this->searchConf, $this->start, $this->num);

        $this->list = $res['list'];
        $this->total = $res['total'];
        $this->brands = Shop_Api::getBrandList($this->cate2);
        $this->models = Shop_Api::getModelList($this->cate2);

        $cityInfo = City_Api::getCity();
        $cityId = $cityInfo['city_id'];
        $this->defaultWid = Conf_Warehouse::$WAREHOUSE_CITY[$cityId][0];

        $sids = Tool_Array::getFields($this->list, 'sid');
        $statusTag = Conf_Product::PRODUCT_STATUS_ONLINE | Conf_Product::PRODUCT_STATUS_OFFLINE;
        $products = Shop_Api::getProductsBySids($sids, $cityId, $statusTag);
        $newProducts = Tool_Array::list2Map($products, 'sid');
        foreach ($this->list as &$item)
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
        $app = '/shop/sku_list.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('cate1_list', Conf_Sku::$CATE1 + Conf_Sku::$CATE1_VIRTUAL);
        $this->smarty->assign('cate2_list', Conf_Sku::$CATE2[$this->searchConf['cate1']]);
        $this->smarty->assign('cate3_list', Conf_Sku::$CATE3[$this->searchConf['cate2']]);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('list', $this->list);
        $this->smarty->assign('brands', $this->brands);
        $this->smarty->assign('models', $this->models);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('wid', $this->defaultWid);

        $this->smarty->display('shop/sku_list.html');
    }
}

$app = new App('pri');
$app->run();