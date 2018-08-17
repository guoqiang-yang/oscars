<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $searchConf;

    protected function getPara()
    {
        $this->searchConf = array(
            'customer_kind' => Tool_Input::clean('r', 'customer_kind', TYPE_UINT),
            'identity' => Tool_Input::clean('r', 'identity', TYPE_UINT),
            'level_for_saler' => Tool_Input::clean('r', 'level_for_saler', TYPE_UINT),
            'sale_status' => Tool_Input::clean('r', 'sale_status', TYPE_UINT),
            'sales_suid' => Tool_Input::clean('r', 'sales_suid', TYPE_UINT),
            'record_suid' => Tool_Input::clean('r', 'record_suid', TYPE_UINT),
            'status' => Tool_Input::clean('r', 'status', TYPE_UINT),
            'address' => Tool_Input::clean('r', 'address', TYPE_STR),
            'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
            'mobile' => Tool_Input::clean('r', 'mobile', TYPE_STR),
            'name' => Tool_Input::clean('r', 'name', TYPE_STR),
            'btime' => Tool_Input::clean('r', 'btime', TYPE_STR),
            'etime' => Tool_Input::clean('r', 'etime', TYPE_STR),
            'first_order' => Tool_Input::clean('r', 'first_order', TYPE_STR),
            'second_order' => Tool_Input::clean('r', 'second_order', TYPE_STR),
            'city_id' => Tool_Input::clean('r', 'city_id', TYPE_UINT),
            'start_ctime' => Tool_Input::clean('r', 'start_ctime', TYPE_STR),
            'end_ctime' => Tool_Input::clean('r', 'end_ctime', TYPE_STR),
        );

        if (isset($this->searchConf['first_order']) && $this->searchConf['first_order'] == 'on')
        {
            $this->searchConf['first_order_date']['btime'] = $this->searchConf['btime'];
            $this->searchConf['first_order_date']['etime'] = $this->searchConf['etime'];
        }
        if (isset($this->searchConf['second_order']) && $this->searchConf['second_order'] == 'on')
        {
            $this->searchConf['second_order_date']['btime'] = $this->searchConf['btime'];
            $this->searchConf['second_order_date']['etime'] = $this->searchConf['etime'];
        }
    }

    protected function main()
    {
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . 'customer-' . date('Ymd') . '.csv');
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');

        if ($this->_user['kind'] == Conf_Admin::JOB_KIND_PARTTIME)
        {
            $this->searchConf['staff_kind'] = Conf_Admin::JOB_KIND_PARTTIME;
        }

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
            '账期',
            '订单数',
            '首单时间',
            '最后下单',
            '均价',
            '间隔',
            '总消费',
            '除沙石类消费',
            '优惠券',
            '城市'
//            '预存总额(元)'
        );
        Data_Csv::send($head);

        $start = 0;
        $step = 1000;
        do
        {
            $customerList = Crm2_Api::getCustomerListForAdmin($this->searchConf, Str_Check::checkMobile($this->searchConf['mobile']) ? NULL : $this->_user, 'cid', $start, $step);
            $customers = $customerList['data'];
            if (count($customers) <= 0)
            {
                break;
            }

            Crm2_Api::appendConsumeAttr($customers);
            
//            $fca = new Finance_Customer_Amount();
            
            foreach ($customers as $customer)
            {
                $cid = $customer['cid'];
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
                $accountBalance = $customer['account_balance'] / 100;
                $paymentDays = $customer['payment_days'];
                $orderNum = $customer['order_num'];
                $lastOrderDate = $customer['last_order_date'];
                $pricePerOrder = $customer['_consume']['price_per_order'];
                $interval = $customer['_consume']['order_inter'];
                $totalAmount = $customer['total_amount'] / 100;
                $orderAmount = $customer['order_amount'] / 100;
                $firstOrderDate = $customer['first_order_date'];
                $coupon = '无';
                if ($customer['_coupon']['50'] > 0)
                {
                    $coupon = $customer['_coupon']['50'] . '张50元优惠券';
                }
                $city = Conf_City::getCityName($customer['city_id']);
//                $preAmount = 0;
//                $where = sprintf('status=%d and cid=%d and type=%d and price>=%d',
//                            Conf_Base::STATUS_NORMAL, $cid, Conf_Finance::CRM_AMOUNT_TYPE_PREPAY, 500000);
//                $sumField = array('sum(price)');
//                $preAmountRet = $fca->openGet($where, $sumField);
                
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
                    $paymentDays,
                    $orderNum,
                    $firstOrderDate,
                    $lastOrderDate,
                    $pricePerOrder,
                    $interval,
                    $totalAmount,
                    $orderAmount,
                    $coupon,
                    $city
//                    intval($preAmountRet['data'][0]['sum(price)']/10/10),
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

