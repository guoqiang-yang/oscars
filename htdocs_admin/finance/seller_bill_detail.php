<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $bid;
    private $type;
    private $start;
    private $num = 20;
    private $total;
    private $billInfo;
    private $orderList;

    protected function getPara()
    {
        $this->bid = Tool_Input::clean('r', 'bid', TYPE_UINT);
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->type = Tool_Input::clean('r', 'type', TYPE_STR);
    }

    protected function checkPara()
    {
        if(empty($this->bid))
        {
            throw new Exception('common: param error');
        }
    }

    protected function main()
    {
        $sellerBillDao = new Finance_Seller_Bill();
        $this->billInfo = $sellerBillDao->getSellerBillInfo($this->bid);
        if(empty($this->type))
        {
            $conf = array(
                'bid' => $this->bid,
                'objtype' => 1,
            );
            $this->orderList = $sellerBillDao->getSellerBillReceiptList($conf, $this->total, $this->start, $this->num);
        }elseif($this->type == 'refund')
        {
            $conf = array(
                'bid' => $this->bid,
                'objtype' => 2,
            );
            $this->orderList = $sellerBillDao->getSellerBillReceiptList($conf, $this->total, $this->start, $this->num);
        }elseif($this->type == 'no')
        {
            $conf = array(
                'wid' => $this->billInfo['wid'],
                'start_time' => $this->billInfo['start_time'],
                'end_time' => $this->billInfo['end_time'],
            );
            $this->orderList = Order_Api::getOrderListWithNoSellerBill($this->bid, $conf, $this->total, $this->start, $this->num);
        }
    }

    protected function outputBody()
    {
        $app = '/finance/seller_bill_detail.php?' . http_build_query(array('type' => $this->type, 'bid' => $this->bid));
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('order_list', $this->orderList);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('bill_info', $this->billInfo);
        $this->smarty->assign('type', $this->type);
        $this->smarty->assign('warehouse', Conf_Warehouse::getSellerWarehouse());
        $this->smarty->assign('payment_list', Conf_Base::$PAYMENT_TYPES);
        $this->smarty->display('finance/seller_bill_detail.html');
    }
}

$app = new App('pri');
$app->run();
