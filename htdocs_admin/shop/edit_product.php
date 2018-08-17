<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $pid;
    private $product;
    private $canEditCost = FALSE;
    private $cityList;

    protected function getPara()
    {
        $this->pid = Tool_Input::clean('r', 'pid', TYPE_UINT);
    }

    protected function main()
    {
        $this->product = Shop_Api::getProductInfo($this->pid);
        
        //$this->canEditCost = Warehouse_Api::isSkuInStock($this->product['sku']['sid']);
        $this->canEditCost = $this->product['product']['cost']>0? false: true;

        //原始图片信息
        $pictag = $this->product['sku']['pic_ids'];
        if ($pictag)
        {
            $picInfo = Shop_Picture_Api::getPicInfo($pictag);
            if ($picInfo['srcinfo']['pic'])
            {
                $spictag = $picInfo['srcinfo']['pic'];
                $picInfo['_srcpic'] = array(
                    'small' => Data_Pic::getPicUrl($spictag, 'small'),
                    'middle' => Data_Pic::getPicUrl($spictag, 'middle'),
                    'big' => Data_Pic::getPicUrl($spictag, 'big'),
                    'src' => Data_Pic::getPicUrl($spictag, ''),
                );
            }

            $this->product['sku']['_picinfo'] = $picInfo;
        }

        $this->cityList = Conf_City::$CITY;
        $salesList = Shop_Api::getLowestPrice($this->product['product']['city_id'], Conf_Activity_Flash_Sale::PALTFORM_BOTH);
        
        $this->product['product']['activity_price'] = array_key_exists($this->pid, $salesList)? 
                                                        intval($salesList[$this->pid]['sale_price']): 0;
        
        $this->addFootJs(array(
                             'js/core/cate.js',
                             'js/apps/shop.js',
                         ));
    }

    protected function outputBody()
    {
        $this->smarty->assign('product', $this->product);
        $this->smarty->assign('can_edit_cost', $this->canEditCost);
        $this->smarty->assign('city_list', $this->cityList);
        $this->smarty->assign('buy_types_desc', Conf_Product::getBuyTypeDesc());
        $this->smarty->assign('managing_modes', Conf_Base::getManagingModes());

        $this->smarty->display('shop/edit_product.html');
    }
}

$app = new App('pri');
$app->run();
