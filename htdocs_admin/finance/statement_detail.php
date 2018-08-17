<?php
include_once ('../../global.php');

class App extends App_Admin_Page
{
    private $statement;
    private $total;
    private $orders;
    private $sourceList;
    private $productsPrice;
    private $start;
    private $num = 120;

    protected function checkAuth()
    {
        parent::checkAuth('/order/statement_detail');
    }

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->statement =array(
            'statement_id' => Tool_Input::clean('r', 'statement_id', TYPE_UINT),
            'user_type' => Tool_Input::clean('r', 'user_type', TYPE_UINT),
        );
    }

    protected function checkPara()
    {
        if (Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_YUNNIAO))
        {
            $this->sourceList['list'] = array(3 => '云鸟');
            $this->statement['source'] = 3;
        }
        else
        {
            $this->sourceList = Logistics_Api::getSourceList();
        }
    }

    protected function main()
    {

        if ($this->statement['user_type'] == 1)
        {
            //司机信息
            $data = Logistics_Coopworker_Api::getDriverOrderList($this->statement, $this->start, $this->num);
        }
        elseif ($this->statement['user_type'] == 2)
        {
            //搬运工信息
            $data = Logistics_Coopworker_Api::getCarrierOrderList($this->statement, $this->start, $this->num);
        }
        $this->orders = $data['list'];
        $orderInfos = array();
        foreach ($this->orders as $order)
        {
            $orderInfos[] = array('cmid' => $order['_order']['community_id'], 'wid' => $order['wid']);
        }
        $communityFees = Order_Community_Api::getBlukCommunityFees($orderInfos);
        foreach ($this->orders as &$order)
        {
            $car_model = empty($order['car_model'])? $order['_worker']['car_model']: $order['car_model'];
            $key = $order['_order']['community_id'].'#'.$order['wid'];
            if (array_key_exists($key, $communityFees))
            {
                $order['all_dfee'] = $communityFees[$key]['fee'];
            }
            
            if (in_array($car_model, array(2,4)))
            {
                $order['fee'] = $communityFees[$key]['fee'][$car_model];
            }
        }

        $this->total = $data['total'];
        $this->productsPrice = $data['products_price'];

        $this->addFootJs(array('js/apps/coopworker.js'));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $app = '/order/statement_detail.php?' . http_build_query($this->statement);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);
        
        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('products_price', $this->productsPrice);
        $this->smarty->assign('orders', $this->orders);
        $this->smarty->assign('source_list', $this->sourceList['list']);
        $this->smarty->assign('warehouse', Conf_Warehouse::$WAREHOUSES);
        $this->smarty->assign('order_steps', Conf_Order::$ORDER_STEPS);
        $this->smarty->assign('paid_status', Conf_Order::$PAY_STATUS);
        $this->smarty->assign('car_models', Conf_Driver::$CAR_MODEL);
        $this->smarty->assign('statement', $this->statement);

        $this->smarty->display('order/statement_detail.html');
    }
}

$app = new App('pri');
$app->run();

