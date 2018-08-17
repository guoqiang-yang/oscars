<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/11/18
 * Time: 下午5:06
 */
include_once ('../../../global.php');;

class App extends App_Admin_Ajax
{
    private $oidAndDrivers;

    protected function getPara()
    {
        $this->oidAndDrivers = Tool_Input::clean('r', 'oids', TYPE_STR);
        $this->line_id = Tool_Input::clean('r', 'line_id', TYPE_UINT);
        $this->remark = Tool_Input::clean('r', 'remark', TYPE_STR);

    }

    protected function checkPara()
    {
        if (empty($this->oidAndDrivers)|| empty($this->line_id))
        {
            throw new Exception('参数错误！');
        }
        if (empty($this->remark))
        {
            throw new Exception('请填写备注！');
        }

    }

    protected function checkAuth()
    {
        parent::checkAuth('/logistics/ajax/get_order_line_info');
    }

    protected function main()
    {
        $upData = array('arrival_time' => date('Y-m-d H:i:s'));

        $oidAndDrivers = explode(',', rtrim($this->oidAndDrivers, ','));
        $oids = array();
        foreach ($oidAndDrivers as $oidAndDriver)
        {
            $arr = explode('-', $oidAndDriver);
            $oids[] = $arr[0];
        }

        $orderDetails  = Logistics_Coopworker_Api::getByOids($oids);
        $drivers = array();
        foreach ($orderDetails as $orderDetail)
        {
            if ($orderDetail['obj_type'] == 1 && $orderDetail['user_type'] == 1 && in_array($orderDetail['obj_id'].'-'.$orderDetail['cuid'], $oidAndDrivers))
            {
                Logistics_Coopworker_Api::updateOrderOfWorker($orderDetail['cuid'], $orderDetail['oid'], Conf_Base::COOPWORKER_DRIVER, $upData);
                //订单号{oid}，姓名{name}，操作{action}
                Logistics_Api::addActionLog($this->_uid, $orderDetail['cuid'], Conf_Base::COOPWORKER_DRIVER,
                    Conf_Logistics_Action_Log::ACTION_DRIVER_ARRIVE_ORDER, $orderDetail['oid'], array('remark' => $this->remark), $this->line_id);
                $drivers[] = $orderDetail['cuid'];
            }

            /*$param = array('oid' => $orderDetail['oid'], 'name' => $this->_user['name'], 'action' => '送达');
            Admin_Api::addOrderActionLog($this->_uid, $orderDetail['cuid'], Conf_Order_Action_Log::ACTION_ORDER_ARRIVE, $param);*/
            //获取所有订单，如果全部到达，则完成
        }

        $orders  = Logistics_Coopworker_Api::getByOids($oids);
        $drivers = array_unique($drivers);
        foreach ($drivers as  $driver)
        {
            $can_finish = 1;
            foreach ($orders as  $order)
            {
                if ($order['arrival_time'] == '0000-00-00 00:00:00' && $order['cuid'] == $driver && $order['user_type'] == Conf_Base::COOPWORKER_DRIVER
                && $order['type'] == 1)
                {
                    $can_finish = 2;
                }
            }
            if ($can_finish == 1)
            {
                Logistics_Api::finishOrder($driver);
            }
        }


    }

    protected function outputBody()
    {
        $response = new Response_Ajax();
        $response->setContent(array('ret'=>1));
        $response->send();

        exit;
    }

}

$app = new App('pri');
$app->run();