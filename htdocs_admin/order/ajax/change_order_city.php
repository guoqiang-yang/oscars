<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $oid;
	private $cityId;
    private $model;
    private $chkRet = true; 
    
    private $orderInfo;
    
    private $noSaleProducts = array();
    private $result = array('errno'=>0);

	protected function getPara()
	{
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
		$this->cityId = Tool_Input::clean('r', 'city_id', TYPE_UINT);
        $this->model = Tool_Input::clean('r', 'model', TYPE_STR);
	}

	protected function checkPara()
	{
		if (empty($this->oid) || empty($this->cityId) || empty($this->model))
		{
			throw new Exception('common:params error');
		}
	}
    
    protected function main()
    {
        $this->_check();
        
        if ($this->result['errno'] > 0) return;
        
        switch ($this->model)
        {
            case 'show_product':
                $this->_checkProducts();
                break;
            
            case 'confirm_chg':
                $this->_changeCity();
                break;
            
            default:
                throw new Exception('操作违法！！');
        }
    }
    
    protected function outputPage()
	{
        $response = new Response_Ajax();
		$response->setContent($this->result);
		$response->send();
		exit;
	}
    
    private function _checkProducts()
    {
        $oo = new Order_Order();
        $orderProduct = $oo->getProductsOfOrder($this->oid);
        $sids = Tool_Array::getFields($orderProduct, 'sid');
        
        $statusTag = Conf_Product::PRODUCT_STATUS_ONLINE | Conf_Product::PRODUCT_STATUS_OFFLINE | Conf_Product::PRODUCT_STATUS_DELETED;
        $newProducts = Tool_Array::list2Map(Shop_Api::getProductsBySids($sids, $this->cityId, $statusTag), 'sid');
        
        $ss = new Shop_Sku();
        $skuInfo = $ss->getBulk($sids);
        
        foreach($orderProduct as &$pinfo)
        {
            $sid = $pinfo['sid'];
            $pinfo['_sku_info'] = $skuInfo[$sid];
            
            $pinfo['_sku_info']['sku_st_desc'] = '正常';
            if (array_key_exists($sid, $newProducts))
            {
                $pinfo['pid'] = $newProducts[$sid]['pid'];
                $pinfo['_sku_info']['sku_st'] = $newProducts[$sid]['status'];
                if($newProducts[$sid]['status'] != Conf_Base::STATUS_NORMAL)
                {
                    $this->chkRet = false;
                    $pinfo['_sku_info']['sku_st_desc'] = 'sku状态异常';
                }
                
                if ($newProducts[$sid]['status'] == Conf_Base::STATUS_OFFLINE)
                {
                    $pinfo['_sku_info']['sku_st_desc'] = '已下线';
                }
                else if ($newProducts[$sid]['status'] == Conf_Base::STATUS_DELETED)
                {
                    $pinfo['_sku_info']['sku_st_desc'] = '已删除';
                }
            }
            else
            {
                $this->chkRet = false;
                $pinfo['_sku_info']['sku_st'] = -1;
                $pinfo['_sku_info']['sku_st_desc'] = 'sku不存在';
            }
        }
        
        $this->smarty->assign('order_products', $orderProduct);
        $this->smarty->assign('chk_ret', $this->chkRet);
        $this->result['data']['html'] = $this->smarty->fetch('order/aj_change_order_city.html');
        $this->result['data']['chk_ret'] = $this->chkRet;
    }
    
    private function _changeCity()
    {
        // 获取订单商品
        $oo = new Order_Order();
        $orderAllProducts = $oo->getProductsOfOrder($this->oid, 0, Conf_Base::STATUS_ALL);
        $allSidsWithCount = array_count_values(Tool_Array::getFields($orderAllProducts, 'sid'));
        
        $orderProduct = array();
        $sids = array();
        // 获取合法(未删除的)的订单商品；并删除sid重复的商品
        // 切换城市会导致产生重复的sid
        foreach($orderAllProducts as &$product)
        {
            if ($product['status'] == Conf_Base::STATUS_NORMAL)
            {
                $orderProduct[] = $product;
                $sids[] = $product['sid'];
                continue;
            }
            
            // 重复&&是删除状态的sid
            if ($allSidsWithCount[$product['sid']]>1)
            {
                $oo->deleteOrderProduct($this->oid, $product['pid'], true);
            }
        }
         
        $statusTag = Conf_Product::PRODUCT_STATUS_ONLINE;
        $newProducts = Tool_Array::list2Map(Shop_Api::getProductsBySids($sids, $this->cityId, $statusTag), 'sid');
        
        // 处理订单商品
        $orderProductPrice = 0;
        $productNum = 0;
        foreach($orderProduct as $opinfo)
        {
            $_sid = $opinfo['sid'];
            if (array_key_exists($_sid, $newProducts))
            {
                $orderProductPrice += $newProducts[$_sid]['price']*$opinfo['num'];
                $productNum ++;
                $upOProduct = array(
                    'pid' => $newProducts[$_sid]['pid'],
                    'price' => $newProducts[$_sid]['price'],
                    'cost' => $newProducts[$_sid]['cost'],
                    'wid' => 0,
                    'location' => '',
                    'picked' => 0,
                    'city_id' => $this->cityId,
                );
                if (Conf_Base::switchForManagingMode())
                {
                    $upOProduct['managing_mode'] = $newProducts[$_sid]['managing_mode'];
                }
                else
                {
                    $upOProduct['managing_mode'] = Conf_Base::MANAGING_MODE_SELF;
                }
                
                $oo->updateOrderProductBySid($this->oid, 0, $_sid, $upOProduct);
            }
            else
            {
                $oo->updateOrderProductBySid($this->oid, 0, $_sid, array('status'=>Conf_Base::STATUS_DELETED));
            }
        }
        
        // 更新订单信息：城市，仓库，价钱，小区相关
        $upOrder = array(
            'city' => $this->cityId,
            'address' => '',
            'district' => 0,
            'construction' => 0,
            'community_id' => 0,
            'wid' => 0,
            'product_num' => $productNum,
            'price' => $orderProductPrice,
            'line_id' => 0,
            'city_id' => $this->cityId,
        );
        $oo->update($this->oid, $upOrder);
        
        // 订单安排了司机，需求取消
//        $lc = new Logistics_Coopworker();
//        $lc->updateByWhere(array('oid'=>$this->oid), array('status'=>Conf_Base::STATUS_DELETED));
        
        // 切换城市
        $cityId = City_Api::setCity($this->cityId);
        setcookie('shop_city_id', $cityId, 0, '/');

        // 日志
        Admin_Api::addOrderActionLog($this->_uid, $this->oid, Conf_Order_Action_Log::ACTION_CHANGE_CITY_ORDER, 
                array('old_city'=>$this->orderInfo['city_id'],'new_city'=>$this->cityId));
    }
    
    private function _check()
    {
        $currCity = City_Api::getCity();
        
//        if ($currCity['city_id'] == $this->cityId)
//        {
//            $this->result['errno'] = 10;
//            $this->result['errmsg'] = '切换失败：城市未被修改';
//        }
        
        $this->orderInfo = Order_Api::getOrderInfo($this->oid);
        
        $selfCities = Conf_City::getSelfCities(true);
        if (!array_key_exists($this->orderInfo['city_id'], $selfCities))
        {
            $this->result['errno'] = 14;
            $this->result['errmsg'] = '加盟商城市订单，不能切换城市；请联系加盟商，或取消订单';
        }
        if (!array_key_exists($this->cityId, $selfCities))
        {
            $this->result['errno'] = 15;
            $this->result['errmsg'] = '自营城市订单，不能切换为加盟商城市；如需修改，请取消订单重新下单';
        }
        
        if ($this->orderInfo['city_id'] == $this->cityId)
        {
            $this->result['errno'] = 13;
            $this->result['errmsg'] = '与当前订单城市属性一致，请通过页面左上角处切换到城市：'. Conf_City::$CITY[$this->cityId];
        }
        if ($this->orderInfo['line_id'])
        {
            throw new Exception('订单已排线，请联系调度处理！');
        }
        else
        {
            $lc = new Logistics_Coopworker();
            $coopworkerOrders = $lc->getByOid($this->oid, 0, 0, 0, Conf_Coopworker::OBJ_TYPE_ORDER);
            if (!empty($coopworkerOrders))
            {
                throw new Exception('该订单已经安排司机或者搬运工，请联系调度处理！');
            }
        }
        if ($this->orderInfo['step'] >= Conf_Order::ORDER_STEP_SURE)
        {
            $this->result['errno'] = 11;
            $this->result['errmsg'] = '切换失败：先回滚订单在切换';
        }
        if ($this->orderInfo['status'] != Conf_Base::STATUS_NORMAL)
        {
            $this->result['errno'] = 12;
            $this->result['errmsg'] = '切换失败：删除订单不能切换';
        }
    }


    protected function _main()
	{
        $productsOfOrder = Order_Api::getOrderProducts($this->oid);
        $order_info = Order_Api::getOrderInfo($this->oid);
        $sids = Tool_Array::getFields($productsOfOrder['products'], 'sid');
        $statusTag = Conf_Product::PRODUCT_STATUS_ONLINE | Conf_Product::PRODUCT_STATUS_OFFLINE;
        $newProducts = Shop_Api::getProductsBySids($sids, $this->cityId, $statusTag);
        $newSids = Tool_Array::getFields($newProducts, 'sid');
        $newProducts = Tool_Array::list2Map($newProducts, 'sid');
        if($this->model == 'check')
        {
            $cate1 = Conf_Sku::$CATE1;
            $cate2 = Conf_Sku::$CATE2;
            foreach ($productsOfOrder['products'] as $product)
            {
                if(!in_array($product['sid'],$newSids))
                {
                    $this->noSaleProducts[] = array(
                        'pid' => $product['pid'],
                        'sid' => $product['sid'],
                        'pname' => $product['sku']['title'],
                        'cate' => $cate1[$product['sku']['cate1']]['name'].'-'.$cate2[$product['sku']['cate1']][$product['sku']['cate2']]['name'],
                        'num' => $product['num']
                    );
                }
            }
            if(empty($this->noSaleProducts))
            {
                $this->result = array('status' => true);
            }
            else
            {
                $this->result = array('status' => false, 'products' => Tool_Array::jsonEncode($this->noSaleProducts));
            }
        }
        elseif($this->model == 'done')
        {
            //更新商品备注
            $oo = new Order_Order();
            $price = 0;
            foreach ($productsOfOrder['products'] as $product)
            {
                if(in_array($product['sid'],$newSids))
                {
                    $info = $newProducts[$product['sid']];
                    $price += $info['price']*$product['num'];
                    $oo->updateOrderProductBySid($this->oid, 0, $product['sid'], array('pid'=>$info['pid'],'price'=>$info['price'],'cost'=>$info['cost'],'wid'=>0,'city_id'=>$this->cityId));
                }
            }
            $oo->update($this->oid, array('wid'=>0, 'step'=>Conf_Order::ORDER_STEP_NEW, 'city_id'=>$this->cityId, 'price'=>$price, 'line_id'=>0, 'city'=>0, 'district'=>0, 'area'=>0, 'address'=>'', 'construction'=>0));
            Admin_Api::addOrderActionLog($this->_uid, $this->oid, Conf_Order_Action_Log::ACTION_CHANGE_CITY_ORDER, array('old_city'=>$order_info['city_id'],'new_city'=>$this->cityId));
            $this->result = array('status' => true);
        }

	}

	
}

$app = new App('pri');
$app->run();

