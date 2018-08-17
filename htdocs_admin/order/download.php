<?php
include_once ('../../global.php');

class App extends App_Admin_Page
{
    private $searchConf;

	protected function getPara()
	{
        $oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->searchConf = array(
            'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
            'oid' => $oid,
            'driver_phone' => Tool_Input::clean('r', 'driver_phone', TYPE_STR),
            'from_date' => Tool_Input::clean('r', 'from_date', TYPE_STR),
            'end_date' => Tool_Input::clean('r', 'end_date', TYPE_STR),
            'from_ctime' => Tool_Input::clean('r', 'from_ctime', TYPE_STR),
            'end_ctime' => Tool_Input::clean('r', 'end_ctime', TYPE_STR),
            'delivery_date' => Tool_Input::clean('r', 'delivery_date', TYPE_STR),
            'construction' => Tool_Input::clean('r', 'construction', TYPE_STR),
            'maybe_late' => Tool_Input::clean('r', 'maybe_late', TYPE_UINT),
            'wid' => $oid ? 0 : $this->getWarehouseId(),
            'bid' => Tool_Input::clean('r', 'bid', TYPE_UINT),
            'saler_suid' => Tool_Input::clean('r', 'saler_suid', TYPE_UINT),
            'print' => Tool_Input::clean('r', 'print', TYPE_INT),
            'mark' => Tool_Input::clean('r', 'mark', TYPE_UINT),
            'source' => Tool_Input::clean('r', 'source', TYPE_UINT),
	        'has_pdays' => Tool_Input::clean('r', 'has_pdays', TYPE_UINT),
            'is_guaranteed' => Tool_Input::clean('r', 'is_guaranteed', TYPE_UINT),
        );

        if (isset($_REQUEST['has_paid']))
        {
            $this->searchConf['has_paid'] = Tool_Input::clean('r', 'has_paid', TYPE_UINT);
        }
        else
        {
            $this->searchConf['has_paid'] = 999;
        }

        if (isset($_REQUEST['step']))
        {
            $this->searchConf['step'] = Tool_Input::clean('r', 'step', TYPE_UINT);
        }
        else
        {
            $this->searchConf['step'] = Conf_Order::ORDER_STEP_ALL_SURE;
        }
        if ($this->searchConf['step'] == Conf_Order::ORDER_STEP_ALL)
        {
            unset($this->searchConf['step']);
        }

        if (isset($_REQUEST['status']))
        {
            $this->searchConf['status'] = Tool_Input::clean('r', 'status', TYPE_UINT);
        }
        else
        {
            $this->searchConf['status'] = Conf_Base::STATUS_NORMAL;
        }
        if ($this->searchConf['status'] == Conf_Base::STATUS_ALL)
        {
            unset($this->searchConf['status']);
        }
	}

	protected function main()
	{
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . 'order-' . date('Ymd') . '.csv');
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');

        if (Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_SALES_NEW) && !in_array($this->_uid, Conf_Admin::$SUPER_SALES))
        {
            $this->searchConf['_raw_conf'] = sprintf('cid in (select cid from t_customer where sales_suid=%d)', $this->_uid);
            /*
            if ($this->_user['kind'] == Conf_Admin::JOB_KIND_PARTTIME)
            {
                $this->searchConf['_raw_conf'] = sprintf('cid in (select cid from t_customer where record_suid=%d)', $this->_uid);
            }
            */
        }

        $head = array(
            'oid','cid', '联系人', /*'联系电话',*/ '城市', '城区', '工地', '商品金额', '沙石砖金额', '搬运费',
            '运费', '优惠', '退款', '应付金额', /*'支付方式',*/ '支付状态', '付款时间', '下单方式', '下单时间', /*'配送日期',*/ '配送时间', '仓库', '距离(公里)',
            /*'司机','司机电话',*/ '司机运费', /*'搬运', '搬运电话',*/ '搬运费', '状态', '录入人',
            '确认人', '销售', '内部备注', '打印备注'
        );
        Data_Csv::send($head);

        $skuList = Shop_Api::getAllSku(array('sid', 'cate1', 'cate2'));
        $start = 0;
        $step = 1000;
        do
        {
            $res = Order_Api::getOrderList($this->searchConf, array(), $start, $step, $this->_user);

            $orders = $res['list'];
            if (count($orders) <= 0)
            {
                break;
            }

            //获取小区到仓库之间的距离
            foreach ($orders as $item)
            {
                $cmidWidArr[] = array('cmid' => $item['community_id'], 'wid' => $item['wid']);
            }
            $distances = Order_Community_Api::getDistancesBetweenCommunityAndWid($cmidWidArr);

            $lc = new Logistics_Coopworker();
            $oids = Tool_Array::getFields($orders, 'oid');
            $where = sprintf(' status=0 and oid in (%s) group by oid,type ', implode(',',  $oids),Conf_Coopworker::OBJ_TYPE_ORDER);
            $ret = $lc->getListByWhere($where, array('*,sum(price) total_fee'));
            $coopworkerOfOrder = array();
            if (!empty($ret))
            {
                foreach ($ret as $one)
                {
                    $coopworkerOfOrder[$one['obj_id']][] = $one;
                }
            }

            $oids = Tool_Array::getFields($orders, 'oid');
            $orderSandPrice = array();
            if (!empty($oids))
            {
                $orderProducts = Order_Api::getProductByOids($oids, array('oid', 'pid', 'rid', 'sid', 'num', 'price'));
                if (!empty($orderProducts))
                {
                    foreach ($orderProducts as $product)
                    {
                        if ($product['rid'] > 0)
                        {
                            continue;
                        }

                        $sid = $product['sid'];
                        $sku = $skuList[$sid];
                        $oid = $product['oid'];
                        if (Shop_Api::isSandCementBrickBySkuinfo($sku))
                        {
                            $orderSandPrice[$oid] += $product['num'] * $product['price'];
                        }
                    }
                }
            }

            foreach ($orders as $order)
            {
                $drivers = $driverPhones = $carriers = $carrierPhones = array();
                $oid = $order['oid'];
                $name = trim(mysql_escape_string($order['_customer']['name']));
                if (!empty($order['contact_name']))
                {
                    $name = $order['contact_name'];
                }
	            $name = str_replace('喆','zhe',$name);
                $mobile = $this->_hideMobile($order['contact_phone']);
	            $city = $order['_city'];
	            $district = $order['_district'];
                $address = $order['address'];
                $price = $order['price'] / 100;
                $customerCarriage = $order['customer_carriage'] / 100;
                $freight = $order['freight'] / 100;
                $privilege = $order['privilege'] / 100;
                $refund = $order['refund'] / 100;
                $userNeedToPay = $order['user_need_to_pay'] / 100;
                $ctime = $order['ctime'];
                $deliveryTime = $order['_delivery_date'];
	            //$deliveryDate = date('Y年m月d日', strtotime($order['delivery_date']));
                $payTime = $order['pay_time'];
                $warehouse = $order['_warehouse_name'];
	            $distance = round($distances[$order['community_id'].'.'.$order['wid']] / 1000, 1);
	            //$driver = '-';
                //$driverPhone = '-';
                //$carrier = '-';
                //$carrierPhone = '-';
	            $driverMoney = 0;
                $carrierMoney = 0;
	            //$paymentType = Conf_Base::$PAYMENT_TYPES[$order['payment_type']];
	            $payStatus = Conf_Order::$PAY_STATUS[$order['paid']];
                if (!empty($coopworkerOfOrder[$oid]))
                {
                    foreach ($coopworkerOfOrder[$oid] as $cooworker)
                    {
                        if ($cooworker['type'] == 1)
                        {
                            //$drivers[] = $cooworker['info']['name'];
                            //$driverPhones[] = $cooworker['info']['mobile'];
	                        $driverMoney = $cooworker['total_fee']/100;
                        }
                        else
                        {
                            //$carriers[] = $cooworker['info']['name'];
                            //$carrierPhones[] = $cooworker['info']['mobile'];
                            $carrierMoney  = $cooworker['total_fee']/100;
                        }
                    }
//                    if (!empty($drivers))
//                    {
//                        $driver = implode(':', $drivers);
//                        $driverPhone = implode(':', $driverPhones);
//                    }
//                    if (!empty($carriers))
//                    {
//                        $carrier = implode(':', $carriers);
//                        $carrierPhone = implode(':', $carrierPhones);
//                    }
                }
                $status = $order['_step'];
                if ($order['source'] == 10002 || $order['source'] == 10003 || $order['source'] == 10004)
                {
                    $recorder = '客户提交';
                }
                else
                {
                    $recorder = $order['_suer']['name'];
                }
                $source = Conf_Order::$SOURCE_DESC[$order['source']];
                $surer = $order['_sure_suid']['name'];
                $saler = '无';
                if (!empty($order['_saler_suid']['name']))
                {
                    $saler = $order['_saler_suid']['name'];
                }
                $sandPrice = round($orderSandPrice[$oid] / 100, 2);

                $arr = array(
                    $oid, $order['cid'], $name, /*$mobile,*/ $city, $district, $address, $price, $sandPrice, $customerCarriage, $freight,
                    $privilege, $refund, $userNeedToPay, /*$paymentType,*/ $payStatus, $payTime, $source, $ctime, /*$deliveryDate,*/ $deliveryTime, $warehouse, $distance,
                    /*$driver, $driverPhone,*/ $driverMoney, /*$carrier, $carrierPhone,*/ $carrierMoney, $status, $recorder, $surer, $saler, $order['note'], $order['customer_note']
                );
                Data_Csv::send($arr);
            }

            $start += $step;
        }
        while (count($orders) > 0);
	}

    private function _hideMobile($mobile)
    {
        $len = strlen($mobile);
        for ($i=0; $i<4 && $i<$len; $i++)
        {
            $mobile[$len-$i-1] = '*';
        }
        return $mobile;
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

