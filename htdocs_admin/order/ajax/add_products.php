<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $oid;
    private $productStr;
    private $products = array();
    private $fromH5;
    private $price;

    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->productStr = Tool_Input::clean('r', 'product_str', TYPE_STR);
        $this->fromH5 = Tool_Input::clean('r', 'from_h5', TYPE_UINT);
    }

    protected function checkPara()
    {
        if (empty($this->oid))
        {
            throw new Exception('order:empty order id');
        }
        $msg = Order_Api::canEditOrderInfo($this->oid);
        if ($msg['error'] > 0)
        {
            throw new Exception($msg['errormsg']);
        }
    }

    private function parseProducts($str, $cid)
    {
        // 解析字符串
        $products = $pids = array();
        $items = array_filter(explode(',', $str));
        foreach ($items as $item)
        {
            list($pid, $num, $note) = explode(":", $item);
            $products[] = array(
                'pid' => $pid,
                'num' => $num,
                'oid' => $this->oid,
                'note' => $note
            );
            $pids[] = $pid;
        }

        if (empty($products))
        {
            return array();
        }

        // 补充price
        $productInfos = Shop_Api::getProductInfos($pids,Conf_Activity_Flash_Sale::PALTFORM_WECHAT, false, 0, $cid, 'real');

        foreach ($products as $idx => &$product)
        {
            $pid = $product['pid'];
            $productInfo = $productInfos[$pid];
            $product['ori_price'] = $productInfo['product']['ori_price'];
            $product['price'] = $productInfo['product']['sale_price'] ? $productInfo['product']['sale_price'] : $productInfo['product']['price'];
            $product['cost'] = $productInfo['product']['cost'];
            $product['sid'] = $productInfo['sku']['sid'];
        }

        return $products;
    }

    protected function checkAuth()
    {
        parent::checkAuth('/order/edit_order');
    }

    protected function main()
    {
        $order = Order_Api::getOrderInfo($this->oid);
        $productsOfOrder = Order_Api::getOrderProduct($this->oid);
        $this->products = $this->parseProducts($this->productStr, $order['cid']);

        $orderActivityProductDao = new Data_Dao('t_order_activity_product');
        $orderActivityProducts = $orderActivityProductDao->getListWhere(array('oid' => $this->oid));
        $activityPids = array();
        if(!empty($orderActivityProducts))
        {
            foreach ($orderActivityProducts as $product)
            {
                $activityPids[$product['pid']] += $product['num'];
            }
        }

        // 校验城市属性
        $this->_checkProductAndOrderCity($order, $productsOfOrder);

        // 校验商品的属性数据
        foreach ($this->products as &$pinfo)
        {
            if ($order['payment_type'] == Conf_Base::PT_CREDIT_PAY)
            {
                if ($pinfo['num'] > 0 && !array_key_exists($pinfo['pid'], $productsOfOrder['order']))
                {
                    throw new Exception('信用付款订单不能添加商品');
                }
                if ($pinfo['num'] > 0 && $pinfo['num'] > $productsOfOrder['order'][$pinfo['pid']]['num'])
                {
                    throw new Exception('信用付款订单不能增加商品数量');
                }
            }
            // 判断是否临采已采购
            if (array_key_exists($pinfo['pid'], $productsOfOrder['order']) && $productsOfOrder['order'][$pinfo['pid']]['tmp_inorder_num'] != 0)
            {
                throw new Exception('临采商品已经采购，不能再修改数量！请走补单！！');
            }

            // 为商品添加城市属性
            $pinfo['city_id'] = $order['city_id'];
            $pinfo['num'] += $activityPids[$pinfo['pid']];
        }
        $this->price = Order_Api::addProducts($this->oid, $this->products);

        Order_Api::updateOrderModify($this->oid, $this->price - $order['price']);

        if (!$this->fromH5 && $order['step'] > Conf_Order::ORDER_STEP_EMPTY)
        {
            $param = array('总价' => $this->price / 100);
            Admin_Api::addOrderActionLog($this->_uid, $this->oid, Conf_Order_Action_Log::ACTION_CHANGE_PRODUCTS, $param);
        }
    }

    protected function outputPage()
    {
        $result = array('oid' => $this->oid, 'price' => $this->price / 100);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }

    private function _checkProductAndOrderCity($orderInfo, $productsOfOrder)
    {
        // 获取添加商品的信息
        $sp = new Shop_Product();
        $pids = Tool_Array::getFields($this->products, 'pid');
        $newProductInfos = $sp->getBulk($pids);

        $ret = Order_Helper::checkOrderAndProductCity($orderInfo, $productsOfOrder['order'], $newProductInfos);

        if ($ret['errno'] != 0)
        {
            throw new Exception($ret['errmsg']);
        }
    }
}

$app = new App('pri');
$app->run();