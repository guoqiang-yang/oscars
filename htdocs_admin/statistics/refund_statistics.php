<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $startTime;
    private $endTime;
    private $refundNum;
    private $orderNum;
    private $orderPrice;
    private $refundPrice;
    private $widRefundNum;
    private $widOrderNum;
    private $refundResons;
    private $reasonData;
    private $reasonDetailData;
    private $refundType;
    private $reasonTypeData;
    private $widRefundData;
    private $refundProductData;
    private $refundProductTotal;
    private $refundBrandProducts;

    protected function getPara()
    {
        $this->startTime = Tool_Input::clean('r', 'start_time', TYPE_STR);
        $this->endTime = Tool_Input::clean('r', 'end_time', TYPE_STR);
    }

    protected function checkPara()
    {
        if (empty($this->endTime))
        {
            $this->endTime = date('Y-m-d', strtotime('yesterday'));
        }
        if (empty($this->startTime))
        {
            $this->startTime = date('Y-m-d', strtotime('yesterday'));
        }
    }

    protected function main()
    {
        $skuList = Shop_Api::getAllSku(array('sid', 'bid', 'title'));
        $brandList = Shop_Api::getAllBrands();

        $conf = array();
        $conf['from_date'] = $this->startTime;
        $conf['end_date'] = $this->endTime;
        $list = Order_Api::getRefundList($conf, 0, 0);
        $warehouses = Conf_Warehouse::getWarehouseByAttr('customer');
        if (!empty($list['list']))
        {
            foreach ($list['list'] as $item)
            {
                $this->refundNum++;
                if ($item['paid'] == Conf_Order::HAD_PAID)
                {
                    $this->refundPrice += $item['price'];
                }
                $wid = $item['wid'];
                $this->widRefundNum[$wid]++;

                $reason1 = $item['reason_type'];
                $reason2 = $item['reason'];
                $this->refundResons[$reason1][$reason2]++;

                $type = $item['type'];
                $this->refundType[$type]++;
            }

            $fields = array('oid', 'price', 'wid');
            $where = sprintf("status=%d AND step>=%d AND ship_time>='%s 00:00:00' AND ship_time<='%s 23:59:59'", Conf_Base::STATUS_NORMAL, Conf_Order::ORDER_STEP_PICKED, $this->startTime, $this->endTime);
            $orderList = Order_Api::getOrderListByWhere($where, array(), 0, 0, $fields);
            if (!empty($orderList))
            {
                foreach ($orderList as $order)
                {
                    $this->orderNum++;
                    $this->orderPrice += $order['price'];
                    $wid = $order['wid'];
                    $this->widOrderNum[$wid]++;
                }

                $oids = Tool_Array::getFields($orderList, 'oid');
                $orderProducts = Order_Api::getProductByOids($oids, array('oid', 'pid', 'sid', 'rid', 'num'));
                if (!empty($orderProducts))
                {
                    foreach ($orderProducts as $product)
                    {
                        $sid = $product['sid'];
                        $bid = $skuList[$sid]['bid'];
                        if (empty($this->refundProductData[$bid]))
                        {
                            $this->refundProductData[$bid] = array(
                                'bid' => $bid,
                                'name' => $bid > 0 ? $brandList[$bid]['name'] : '无品牌',
                                'refund_num' => 0,
                                'sale_num' => 0,
                            );
                        }

                        if ($product['rid'] > 0)
                        {
                            $this->refundProductData[$bid]['refund_num'] += $product['num'];
                            $this->refundProductTotal += $product['num'];
                        }
                        else
                        {
                            $this->refundProductData[$bid]['sale_num'] += $product['num'];
                        }
                    }
                }
            }
        }
        else
        {
            //没有退款单，那么数量和金额都是0
            //没有退款单，百分比也就是0，没必要去统计订单数量和订单金额，赋一个假值1
            $this->refundNum = 0;
            $this->orderNum = 1;
            $this->refundPrice = 0;
            $this->orderPrice = 1;
            foreach ($warehouses as $wid)
            {
                $this->widRefundNum[$wid] = 0;
                $this->widOrderNum[$wid] = 1;
            }
        }

        //格式化数据
        $reason1Idx = 0;
        foreach ($this->refundResons as $reason1 => $list)
        {
            $reason1Num = 0;
            foreach ($list as $reason2 => $num)
            {
                $reason1Num += $num;
            }

            foreach ($list as $reason2 => $num)
            {
                $this->reasonDetailData[$reason1Idx][] = array(
                    'name' => Conf_Refund::$REFUND_REASON_DETAIL[$reason1][$reason2],
                    'y' => round($num * 100 / $reason1Num, 2),
                    'reason_type' => $reason1,
                    'reason' => $reason2,
                    'from_date' => $this->startTime,
                    'end_date' => $this->endTime,
                    'num' => $num,
                );
            }

            $this->reasonData[$reason1Idx] = array(
                'name' => Conf_Refund::$REFUND_REASON_TYPE[$reason1],
                'y' => round($reason1Num * 100 / $this->refundNum, 2),
                'reason_type' => $reason1,
                'from_date' => $this->startTime,
                'end_date' => $this->endTime,
                'num' => $reason1Num,
            );

            $reason1Idx++;
        }

        foreach ($this->refundType as $type => $num)
        {
            $this->reasonTypeData[] = array(
                'name' => Conf_Refund::$REFUND_TYPES[$type],
                'y' => round($num * 100 / $this->refundNum, 2),
                'type' => $type,
                'num' => $num,
            );
        }

        foreach ($this->widRefundNum as $wid => $num)
        {
            $wname = Conf_Warehouse::$WAREHOUSES[$wid];
            $this->widRefundData[$wname] = round($num * 100 / $this->widOrderNum[$wid], 2);
        }

        foreach ($this->refundProductData as $bid => &$info)
        {
            $info['refund_rate'] = round($info['refund_num'] * 100 / $this->refundProductTotal, 2);
            $info['refund_sale_rate'] = round($info['refund_num'] * 100 / $info['sale_num'], 2);
        }
        uasort($this->refundProductData, 'sortByRate');
        $this->refundProductData = array_slice($this->refundProductData, 0, 10);

        $bids = Tool_Array::getFields($this->refundProductData, 'bid');
        if (!empty($orderProducts))
        {
            foreach ($orderProducts as $product)
            {
                $sid = $product['sid'];
                $pid = $product['pid'];
                $bid = $skuList[$sid]['bid'];
                if (!in_array($bid, $bids))
                {
                    continue;
                }

                $this->refundBrandProducts[$bid][$pid]['pid'] = $pid;
                $this->refundBrandProducts[$bid][$pid]['pname'] = $skuList[$sid]['title'];
                if ($product['rid'] > 0)
                {
                    $this->refundBrandProducts[$bid][$pid]['refund_num'] += $product['num'];
                }
                else
                {
                    $this->refundBrandProducts[$bid][$pid]['sale_num'] += $product['num'];
                }
            }
        }

        foreach ($this->refundBrandProducts as $bid => $list)
        {
            uasort($list, 'sortBrandProduct');
            $listNew = array_slice($list, 0, 10);

            $this->refundBrandProducts[$bid] = $listNew;
        }

        $this->addHeadJs(array(
                             'js/jquery.min.js',
                             'js/highcharts/highcharts.js',
                             'js/highcharts/modules/exporting.js',
                             'js/highcharts/modules/data.js',
                             'js/highcharts/modules/drilldown.js',
                             'js/highcharts/themes/dark_unica.js',
                         ));

        $this->addFootJs(array(
                             'js/apps/highcharts/refund.js',
                         ));
        $this->addCss(array(
                          'css/highcharts/unica_one.css'
                      ));
    }

    protected function outputBody()
    {
        $this->smarty->assign('start_time', $this->startTime);
        $this->smarty->assign('end_time', $this->endTime);
        $this->smarty->assign('refund_total', $this->refundNum);
        $this->smarty->assign('order_total', $this->orderNum);
        $this->smarty->assign('refund_price', $this->refundPrice);
        $this->smarty->assign('order_price', $this->orderPrice);
        $this->smarty->assign('reason_data', json_encode($this->reasonData));
        $this->smarty->assign('reason_detail_data', json_encode($this->reasonDetailData));
        $this->smarty->assign('reason_type_data', json_encode($this->reasonTypeData));
        $this->smarty->assign('wid_refund_data_key', json_encode(array_keys($this->widRefundData)));
        $this->smarty->assign('wid_refund_data_val', json_encode(array_values($this->widRefundData)));
        $this->smarty->assign('refund_product_data', $this->refundProductData);
        $this->smarty->assign('refund_brand_products', $this->refundBrandProducts);
        $this->smarty->assign('refund_products_total', $this->refundProductTotal);

        $this->smarty->display('statistics/refund_statistics.html');
    }
}

$app = new App('pri');
$app->run();


function sortByRate($a, $b)
{
    if ($a['refund_rate'] == $b['refund_rate'])
    {
        return 0;
    }

    return ($a['refund_rate'] > $b['refund_rate']) ? -1 : 1;
}

function sortBrandProduct($a, $b)
{
    if ($a['refund_num'] == $b['refund_num'])
    {
        return 0;
    }

    return ($a['refund_num'] > $b['refund_num']) ? -1 : 1;
}
