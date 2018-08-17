<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $statement;
    private $orders;
    private $totalPrice = 0;

    protected function getPara()
    {
        $this->statement = array(
            'statement_id' => Tool_Input::clean('r', 'id', TYPE_UINT),
        );
    }

    protected function checkAuth()
    {
        parent::checkAuth('/order/ajax/check_statement');
    }

    protected function main()
    {
        $data = Logistics_Coopworker_Api::getStatementDetail($this->statement);

        if (empty($data))
        {
            return $data;
        }

        $oids = Tool_Array::getFields($data, 'oid');
        $cuids = Tool_Array::getFields($data, 'cuid');

        // 补充订单信息
        $oo = new Order_Order();
        $orderInfos = Tool_Array::list2Map($oo->getBulk($oids), 'oid');

        // 补充司机信息
        $ld = new Logistics_Driver();
        $driverInfos = Tool_Array::list2Map($ld->getByDids(array_unique($cuids)), 'did');

        foreach ($data as &$_order)
        {
            $_order['_order'] = $orderInfos[$_order['oid']];
            $_order['_worker'] = $driverInfos[$_order['cuid']];
        }

        $orderInfos = array();
        foreach ($data as $order)
        {
            $orderInfos[] = array('cmid' => $order['_order']['community_id'], 'wid' => $order['wid']);
        }
        $communityFees = Order_Community_Api::getBlukCommunityFees($orderInfos);

        foreach ($data as &$orderInfo)
        {
            $orderInfo['community_fee'] = $communityFees[$orderInfo['_order']['community_id'].'#'.$orderInfo['wid']]['fee'];
            if ($orderInfo['type'] == Conf_Base::COOPWORKER_DRIVER)
            {
                $_model = empty($orderInfo['car_model'])? $orderInfo['_worker']['car_model']: $orderInfo['car_model'];
                $orderInfo['fee'] = $communityFees[$orderInfo['_order']['community_id'] . '#' . $orderInfo['wid']]['fee'][$_model];
            }
            elseif ($orderInfo['type'] == Conf_Base::COOPWORKER_CARRIER)
            {
                // if ($orderInfo['_order']['service'] == 1)
                // {
                //     $carryFees = Logistics_Api::calCarryFee4Carrier($orderInfo['oid']);
                //     //$orderInfo['fee'] = $carryFees['ele'];
                //     $orderInfo['fee'] = $carryFees['worker']['ele'];
                // }
                // else if ($orderInfo['_order']['service'] == 2)
                // {
                //     $carryFees = Logistics_Api::calCarryFee4Carrier($orderInfo['oid']);
                //     //$orderInfo['fee'] = $carryFees['common'] * $orderInfo['_order']['floor_num'];
                //     $orderInfo['fee'] = $carryFees['worker']['common'] * $orderInfo['_order']['floor_num'];
                // }
                // else if ($orderInfo['_order']['service'] == 0)
                // {
                //     $orderInfo['fee'] = 0;
                // }
                //
                $client = new Yar_Client(MS . "/cmpt/order/fees");
                $result = $client->AdminCarryFee($orderInfo['oid'], null , null);
                $orderInfo['fee'] = 0;
                if ( isset($result['worker']) ) {
                    $orderInfo['fee'] = $result['worker'];
                }
            }
            $this->totalPrice += $orderInfo['price'];
        }
        $this->orders = $data;
    }

    protected function outputPage()
    {
        $this->smarty->assign('orders', $this->orders);
        $this->smarty->assign('total_price', $this->totalPrice);
        $this->smarty->assign('fee_types', Conf_Coopworker::$Coopworker_Fee_Types);
        $this->smarty->assign('car_models', Conf_Driver::$CAR_MODEL);
        $result = array('id' => $this->statement['statement_id'], 'html' => $this->smarty->fetch('order/block_check_statement.html'));

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }
}

$app = new App();
$app->run();

