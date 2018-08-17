<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $searchConf;

    protected function main()
    {
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . 'customer-' . date('Ymd') . '.csv');
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');

        $this->searchConf['has_payment_days'] = 2;

        $head = array(
            'cid',
            '名字',
            '手机号',
            '类型',
            '销售状态',
            '录入人',
            '加入时间',
            '销售',
            '欠款',
            '欠款订单数'
        );
        Data_Csv::send($head);

        $start = 0;
        $step = 1000;
        $oo = new Order_Order();
        do
        {
            $customerList = Crm2_Api::getCustomerListForAdmin($this->searchConf, Str_Check::checkMobile($this->searchConf['mobile']) ? NULL : $this->_user, 'cid', $start, $step);
            $customers = $customerList['data'];
            if (count($customers) <= 0)
            {
                break;
            }

            $cids = Tool_Array::getFields($customers, 'cid');
            $cids = array_filter(array_unique($cids));

            $where = sprintf(' cid in (%s) and status=%d and step=%d and paid!=%d', implode(',', $cids), Conf_Base::STATUS_NORMAL, Conf_Order::ORDER_STEP_FINISHED, Conf_Order::HAD_PAID);
            $orderList = $oo->getListRawWhere($where, $total, array(), 0, 0, array(
                'oid',
                'cid',
                'price',
                'freight',
                'customer_carriage',
                'privilege',
                'refund',
                'real_amount'
            ));
            $orderCustomerInfo = array();
            foreach ($orderList as $order)
            {
                $userNeedToPay = Order_Helper::calOrderNeedToPay($order);
                $orderCustomerInfo[$order['cid']]['total'] += $userNeedToPay;
                $orderCustomerInfo[$order['cid']]['num']++;
            }

            foreach ($customers as $customer)
            {
                $cid = $customer['cid'];

                if (empty($orderCustomerInfo[$cid]))
                {
                    continue;
                }

                $name = $customer['name'];
                $mobile = implode(' ', $customer['mobiles']);
                $identity = '无';
                if (!empty($customer['identity']))
                {
                    $identity = Conf_User::$Crm_Identity[$customer['identity']];
                }
                $saleStatus = Conf_User::$Customer_Sale_Status[$customer['sale_status']];
                $recorder = '自己注册';
                if (!empty($customer['_record_suid']['name']))
                {
                    $recorder = $customer['_record_suid']['name'];
                }
                $memberDate = $customer['member_date'];
                $salesName = '无';
                if (!empty($customer['_sales_suid']['name']))
                {
                    $salesName = $customer['_sales_suid']['name'];
                }
                $accountBalance = $orderCustomerInfo[$cid]['total'] / 100;
                $orderNum = $orderCustomerInfo[$cid]['num'];

                $arr = array(
                    $cid,
                    $name,
                    $mobile,
                    $identity,
                    $saleStatus,
                    $recorder,
                    $memberDate,
                    $salesName,
                    $accountBalance,
                    $orderNum
                );
                Data_Csv::send($arr);
            }

            $start += $step;
        }
        while (count($customers) > 0);
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

