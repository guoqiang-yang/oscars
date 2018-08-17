<?php
include_once ('../../global.php');

class App extends App_Admin_Page
{
    private $search_conf;

	protected function getPara()
	{
        $this->search_conf = array(
            'tid' => Tool_Input::clean('r', 'tid', TYPE_UINT),
        );
	}

	protected function main()
	{
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . 'coupon-order-' . date('Ymd') . '.csv');
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');


        $head = array(
            'oid', '应付金额', 'cid', '客户名', '仓库', '状态', '下单时间', '销售'
        );
        Data_Csv::send($head);

        $staffs = Admin_Api::getAllStaff();
        $staffsMap = Tool_Array::list2Map($staffs, 'suid');
        $oo = new Order_Order();
        $start = 0;
        $step = 1000;
        do
        {
            $data = $oo->getPromotionOrdersByCouponId($this->search_conf['tid'], FALSE, $start, $step);
            $orders = $data['list'];
            if (count($orders) <= 0)
            {
                break;
            }

            $cids = Tool_Array::getFields($orders, 'cid');
            $customers = Crm2_Api::getCustomers($cids);
            foreach ($orders as $order)
            {
                Order_Helper::formatOrder($order);
                $oid = $order['oid'];
                $cid = $order['cid'];
                $cname = $customers[$cid]['name'];
                $wid = $order['wid'];
                $wname = Conf_Warehouse::$WAREHOUSES[$wid];
                $stepDes = $order['_step'];
                $ctime = $order['ctime'];
                $salerSuid = $order['saler_suid'];
                $saleerName = $staffsMap[$salerSuid]['name'];
                $price = round($order['total_order_price'] / 100, 2);

                $arr = array($oid, $price, $cid, $cname, $wname, $stepDes, $ctime, $saleerName);
                Data_Csv::send($arr);
            }

            $start += $step;
        }
        while (count($orders) > 0);
	}

    protected function outputHead()
    {

    }

	protected function outputBody()
	{

    }

    protected function outputTail()
    {

    }
}

$app = new App('pri');
$app->run();

