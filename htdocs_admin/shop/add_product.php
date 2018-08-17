<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $cate1 = 1;
    private $sid = 0;
    private $product;
    private $canEditCost;
    private $cityList;
    private $cityId;

    protected function getPara()
    {
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
    }

    protected function main()
    {
        $sku = Shop_Api::getSkuInfo($this->sid);
        $this->product['sku'] = $sku;

        //$this->canEditCost = Warehouse_Api::isSkuInStock($this->product['sku']['sid']);
        $this->canEditCost = true; //新建可以编辑成本
        
        $this->cityList = Conf_City::$CITY;
        if (!empty($this->_user['city_id']))
        {
            $cityIds = explode(',', $this->_user['city_id']);
            foreach ($this->cityList as $cityId => $city)
            {
                if (!in_array($cityId, $cityIds))
                {
                    unset($this->cityList[$cityId]);
                }
            }
        }

        $cityInfo = City_Api::getCity();
        $this->cityId = $cityInfo['city_id'];

        $this->addFootJs(array(
                             'js/core/cate.js',
                             'js/apps/shop.js',
                         ));
    }

    protected function outputBody()
    {
        $this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
        $this->smarty->assign('cate2_list', Conf_Sku::$CATE2[$this->cate1]);
        $this->smarty->assign('sid', $this->sid);
        $this->smarty->assign('product', $this->product);
        $this->smarty->assign('can_edit_cost', $this->canEditCost);
        $this->smarty->assign('city_list', $this->cityList);
        $this->smarty->assign('cur_city_id', $this->cityId);
        $this->smarty->assign('buy_types_desc', Conf_Product::getBuyTypeDesc());
        $this->smarty->assign('managing_modes', Conf_Base::getManagingModes());

        $this->smarty->display('shop/add_product.html');
    }
}

$app = new App('pri');
$app->run();