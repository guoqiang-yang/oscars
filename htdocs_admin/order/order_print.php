<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $oid;
    private $order;
    private $hiddenMoney;
    private $orderProducts;
    private $customer;
    private $saler;
    private $hiddenPrivilege;
    private $isFirstOrder;
    protected $headTmpl = 'head/head_none.html';
    protected $tailTmpl = 'tail/tail_none.html';
    private $lotteryRes;
    private $orderType = '';
    private $hasNizi;
    private $hasWeixingProduct;
    private $errorMsg;
    private $communityInfo = array();
    private $canJoinAfternoonActivity = FALSE;
    private $needDoublePrint = FALSE;
    private $noPrivilegePrint = FALSE;
    private $activityProducts;
    private $productPrivilege = 0;

    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->hiddenMoney = Tool_Input::clean('r', 'hidden_money', TYPE_UINT);
        $this->hiddenPrivilege = Tool_Input::clean('r', 'hidden_privilege', TYPE_UINT);
    }

    protected function main()
    {
        $res = Order_Api::checkSupplementList($this->oid);
        if ($res > 0)
        {
            $this->errorMsg = "补单{$res}尚未完成扫货！";
        }

        $this->order = Order_Api::getOrderInfo($this->oid);
        $this->order['_op_note'] = Order_Api::parseOpNote($this->order['op_note']);
        if (isset($this->order['_op_note']['nopprice']) && $this->order['_op_note']['nopprice'] == 1)
        {
            $this->hiddenMoney = 1;
        }

        $orderProducts = Order_Api::getOrderProducts($this->oid);
        $this->orderProducts = Privilege_Api::getRealBuyProducts($orderProducts['products'], $this->oid);
        foreach ($this->orderProducts as $item)
        {
            $this->productPrivilege += ($item['ori_price'] - $item['price']) * $item['num'];
        }
        $orderActivityProductDao = new Data_Dao('t_order_activity_product');
        $this->activityProducts = $orderActivityProductDao->getListWhere(array('oid' => $this->oid));
        if(!empty($this->activityProducts))
        {
            $pids = Tool_Array::getFields($this->activityProducts, 'pid');
            $productInfos = Shop_Api::getProductInfos($pids, Conf_Activity_Flash_Sale::PALTFORM_WECHAT, true);
            foreach ($this->activityProducts as &$product)
            {
                $product['title'] = $productInfos[$product['pid']]['sku']['title'];
                $product['unit'] = $productInfos[$product['pid']]['sku']['unit'];
            }
        }

        Shop_Helper::sortProductsForPrint($this->orderProducts);
        $this->isFirstOrder = Order_Api::isFristOrder($this->oid, $this->order);

        $this->hasWeixingProduct = Order_Api::hasWeixingProduct($this->orderProducts);

        if (!empty($this->order['community_id']))
        {
            $this->communityInfo = Order_Community_Api::get($this->order['community_id']);
            $this->order['print_address'] = $this->communityInfo['district'] . ' ' . $this->communityInfo['name'] . ' ' . $this->order['_address'] . '（' . $this->communityInfo['address'] . '）';
        }
        else
        {
            $this->order['print_address'] = $this->order['_district'] . ' ' . $this->order['address'];
        }

        if ($this->order['customer_carriage'] == 0 && $this->order['service'] > 0)
        {
            // $carrierFeeInfo = Logistics_Api::calCarryFee4Carrier($this->oid);
            // if ($this->order['service'] == 1)
            // {
            //     $this->order['_carriage_fee'] = $carrierFeeInfo['ele'];
            // }
            // else
            // {
            //     $this->order['_carriage_fee'] = $carrierFeeInfo['common'];
            // }
            $client = new Yar_Client(MS . "/cmpt/order/fees");
            $result = $client->AdminCarryFee($this->oid, null, null);
            $this->order['_carriage_fee'] = 0;
            if ( isset($result['worker']) ) {
                $this->order['_carriage_fee'] = $result['worker'];
            }
        }

        //客户信息
        if ($this->order['uid'])
        {
            $this->customer = Crm2_Api::getCustomerInfoByCidUid($this->order['cid'], $this->order['uid']);
        }
        else
        {
            $this->customer = Crm2_Api::getCustomerInfo($this->order['cid'], FALSE, FALSE);
            $this->customer = $this->customer['customer'];
        }

        if ($this->customer['sales_suid'])
        {
            $this->saler = Admin_Api::getStaff($this->customer['sales_suid']);
        }

        // 获取订单的第三方工人（司机，搬运工）
        $coopworders = Logistics_Coopworker_Api::getOrderOfWorkers($this->oid, 0, TRUE);
        $arr = array();
        foreach ($coopworders as $oner)
        {
            if ($oner['type'] == Conf_Base::COOPWORKER_DRIVER)
            {
                $arr[] = $oner['info']['name'] . ' ' . $oner['info']['mobile'];
            }
        }
        $this->order['driver_names'] = '';
        if (!empty($arr))
        {
            $this->order['driver_names'] = implode(',', $arr);
        }

        // 录单人
        $suid = $this->order['suid'];
        if ($suid)
        {
            $this->order['_suser'] = Admin_Api::getStaff($suid);
        }

        //已回单并且是一个月之前配送
        if($this->order['step'] == Conf_Order::ORDER_STEP_FINISHED && $this->order['delivery_date']< date('Y-m-d H:i:s', strtotime('-1 month')))
        {
            $this->order['contact_phone'] = Str_Number::hideMobile($this->order['contact_phone']);
        }
        $this->needDoublePrint = in_array($this->order['cid'], Conf_User::$DOUBLE_PRINT_CIDS);
        $this->noPrivilegePrint = in_array($this->order['cid'], Conf_User::$NO_PRIVILEGE_PRINT_CIDS);
        $this->orderType = $this->_getOrderType();
        $this->hasNizi = $this->_hasNizi();

        $t = time();
        $r = rand(100000, 999999);
        $s = rand(222222, 888888);
        $signStr = $this->oid . $t;
        $fmd5 = md5($signStr . 'hcsd8762j211%&31k}[//111!<>??:%$#@*(((^%%%');
        $sign = md5($fmd5 . $r);
        $payDetailUrl = sprintf("http://%s/order/detail_4_pay.php?oid=%d&st=%s&sr=%s&ss=%s&ssign=%s",
                                      C_H5_MAIN_HOST, $this->oid, $t, $r, $s, $sign);
        //$this->payImgSrc = sprintf('https://pan.baidu.com/share/qrcode?w=300&h=300&url=%s', urlencode($payDetailUrl));
        $this->payImgSrc = sprintf('https://b.bshare.cn/barCode?site=weixin&url=%s', urlencode($payDetailUrl));

        $this->addFootJs(array('js/apps/order_print.js'));
        $this->addCss(array());
    }

    private function _getOrderType()
    {
        $orderType = '';
        foreach ($this->orderProducts as $product)
        {
            if (13080 == $product['sid'] || 13081 == $product['sid'])
            {
                $orderType = 'trash_clean';
                break;
            }
        }

        return $orderType;
    }

    private function  _hasNizi()
    {
        foreach ($this->orderProducts as $product)
        {
            if (in_array($product['pid'], Conf_Order::$NAISHUINIZI_PIDS))
            {
                return TRUE;
            }
        }

        return FALSE;
    }

    protected function outputBody()
    {

        $this->order['total_order_price'] = Order_Helper::calOrderPayableTotalPrice($this->order)-$this->order['refund'];
        $chineseTotal = Str_Chinese::getChineseNum($this->order['total_order_price'] / 100);
        if ($this->hiddenPrivilege)
        {
            $this->order['total_order_price'] = $this->order['total_order_price'] + $this->order['privilege'] + $this->productPrivilege;
            $chineseTotal = Str_Chinese::getChineseNum($this->order['total_order_price'] / 100);
        }

        $this->smarty->assign('payment_types', Conf_Base::getPaymentTypes());
        $this->smarty->assign('order', $this->order);
        $this->smarty->assign('hidden_money', $this->hiddenMoney);
        $this->smarty->assign('chineseTotal', $chineseTotal);
        $this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
        $this->smarty->assign('cate2_list_all', Conf_Sku::$CATE2);
        $this->smarty->assign('order_products', $this->orderProducts);
        $this->smarty->assign('customer', $this->customer);
        $this->smarty->assign('saler', $this->saler);
        $this->smarty->assign('hidden_privilege', $this->hiddenPrivilege);
        $this->smarty->assign('product_privilege', $this->productPrivilege);
        $this->smarty->assign('is_first_order', $this->isFirstOrder);
        $this->smarty->assign('lottery_res', $this->lotteryRes);
        $this->smarty->assign('order_type', $this->orderType);
        $this->smarty->assign('has_nizi', $this->hasNizi);
        $this->smarty->assign('has_weixing_product', $this->hasWeixingProduct);
        $this->smarty->assign('errmsg', $this->errorMsg);
        $this->smarty->assign('can_join_afternoon_activity', $this->canJoinAfternoonActivity);
        $this->smarty->assign('need_double_print', $this->needDoublePrint);
        $this->smarty->assign('no_privilege_print', $this->noPrivilegePrint);
        $this->smarty->assign('pay_img_src', $this->payImgSrc);
        $this->smarty->assign('activity_products', $this->activityProducts);

        $this->smarty->display('order/order_print.html');
    }
}

$app = new App('pri');
$app->run();
