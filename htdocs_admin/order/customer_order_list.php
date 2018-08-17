<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    // cgi参数
    private $start;
    private $mobile;
    private $searchConf;
    private $hideConf;
    private $orders;
    private $num = 20;
    private $total;
    private $supplement;
    private $orderNum;
    private $salesList = array();
    private $sureList = array();
    private $order;
    private $sort;
    private $afterList;
    private $sum;
    private $priceTotal;
    private $deliverTime;
    private $orderSandPrice;
    private $orderList4Sales = 1;

    protected function getPara()
    {
//        $this->orderList4Sales = Tool_Input::clean('r', 'order_list_4_sale', TYPE_UINT);
        $this->order = Tool_Input::clean('r', 'order', TYPE_STR);
        $this->sort = Tool_Input::clean('r', 'sort', TYPE_STR);
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->mobile = Tool_Input::clean('r', 'mobile', TYPE_STR);
        $this->hideConf = Tool_Input::clean('r', 'hide_conf', TYPE_UINT);
        $oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $_wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
        $wid = $oid ? 0 : ($_wid ? $_wid : $this->_user['wid']);
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
            'wid' => $wid,
            'bid' => Tool_Input::clean('r', 'bid', TYPE_UINT),
            'saler_suid' => Tool_Input::clean('r', 'saler_suid', TYPE_UINT),
            'sure_suid' => Tool_Input::clean('r', 'sure_suid', TYPE_UINT),
            'print' => Tool_Input::clean('r', 'print', TYPE_INT),
            'source' => Tool_Input::clean('r', 'source', TYPE_UINT),
            'has_pdays' => Tool_Input::clean('r', 'has_pdays', TYPE_UINT),
            'is_guaranteed' => Tool_Input::clean('r', 'is_guaranteed', TYPE_UINT),
            'from_time' => Tool_Input::clean('r', 'from_time', TYPE_UINT),
            'end_time' => Tool_Input::clean('r', 'end_time', TYPE_UINT),
            'city_id' => Tool_Input::clean('r', 'city_id', TYPE_UINT),
            'community_id' => Tool_Input::clean('r', 'community_id', TYPE_UINT),
            'back_unpaid' => Tool_Input::clean('r', 'back_unpaid', TYPE_UINT),
            'order_list_4_sale' => $this->orderList4Sales,
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

        $this->deliverTime = Order_Api::getDeliveryTime4Admin();
    }

    private function _setSearchConfForSaler()
    {
        if (!Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_SALES_NEW) || $this->_uid < 1004)
        {
            return;
        }

        if (!empty($this->searchConf['oid']))
        {
            $this->searchConf['saler_suid'] = 0;

            return;
        }

        // 兼职 去掉兼职逻辑
        //if ($this->_user['kind'] == Conf_Admin::JOB_KIND_PARTTIME)
        if (false)
        {
            $this->searchConf['_raw_conf'] = sprintf('cid in (select cid from t_customer where (sales_suid=%d or record_suid=%d) )', $this->_uid, $this->_uid);
            $this->searchConf['saler_suid'] = 0;
        }
        else
        {
            $this->searchConf['saler_suid'] = !empty($this->searchConf['saler_suid']) ? $this->searchConf['saler_suid'] : $this->_uid;

            if (!empty($this->searchConf['cid']))
            {
                $customerInfo = Crm2_Api::getCustomerInfo($this->searchConf['cid'], FALSE, FALSE);
                if (in_array($customerInfo['customer']['sales_suid'], $this->_user['team_member']))
                {
                    $this->searchConf['saler_suid'] = 0;
                }
            }

            if (in_array($this->searchConf['saler_suid'], $this->_user['team_member']))
            {
                //nothing
            }
            else if (!empty($this->searchConf['saler_suid']))
            {
                $this->searchConf['saler_suid'] = $this->_uid;
            }
        }
    }

    protected function main()
    {
        // 按订单的销售筛选：销售可以看自己，或组员的客户的订单,组员只能看自己的订单; 兼职销售特殊逻辑
        $this->_setSearchConfForSaler();

        empty($this->order) && $this->order = 'oid';
        empty($this->sort) && $this->sort = 'desc';

        if ($this->orderList4Sales)
        {
            $this->searchConf['wid'] = 0;
        }
        else
        {
            if ($this->_user['wid'] > 0)
            {
                $this->searchConf['saler_suid'] = 0;
            }
        }

        $res = Order_Api::getOrderList($this->searchConf, array($this->order, $this->sort), $this->start, $this->num, $this->_user);

        $this->orders = $res['list'];
        $this->total = $res['total'];
        $this->supplement = $res['supplement'];
        $this->sum = $res['sum'];
        $this->priceTotal = $res['price_total'];
        $this->orderNum = $res['order_num'];

        $oids = Tool_Array::getFields($this->orders, 'oid');
        if (!empty($oids))
        {
            $skuList = Shop_Api::getAllSku(array('sid', 'cate1', 'cate2'));
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
                        $this->orderSandPrice[$oid] += $product['num'] * $product['price'];
                    }
                }
            }
        }

        if (Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_SALES_NEW))
        {
            if (count($this->_user['team_member']) == 1)
            {
                $this->salesList[] = $this->_user;
            }
            else
            {
                $this->salesList = Admin_Api::getStaffs($this->_user['team_member']);
            }
        }
        else if (Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_CITY_ADMIN_NEW))
        {
            $this->salesList = Admin_Api::getStaffByCityAndRole($this->_user['city_id'], Conf_Admin::ROLE_SALES_NEW);
        }
        else
        {
            $this->salesList = Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_SALES_NEW, Conf_Base::STATUS_NORMAL);
        }
        $this->sureList = Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_CS_NEW, Conf_Base::STATUS_NORMAL);

        // 任务提示
        $oids = Tool_Array::getFields($this->orders, 'oid');
        $this->afterList = Aftersale_Api::getListByObjids($oids, Conf_Aftersale::OBJTYPE_ORDER);

        $this->addFootJs(array(
                             'js/apps/order.js',
                             'js/apps/new_order_alert.js',
                             'js/apps/admin_task.js',
                             'js/apps/delivery_date_check.js'
                         ));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $queryStr = http_build_query($this->searchConf) . '&order=' . $this->order . '&sort=' . $this->sort;
        $app = '/order/customer_order_list.php?' . $queryStr;
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('query_str', $queryStr);
        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('supplement', $this->supplement);
        $this->smarty->assign('order_num', $this->orderNum);
        $this->smarty->assign('mobile', $this->mobile);
        $this->smarty->assign('searchConf', $this->searchConf);
        $this->smarty->assign('order_steps', Conf_Order::getOrderStepNames());
        $this->smarty->assign('order_status', Conf_Base::getOrderStatusList());
        $this->smarty->assign('orders', $this->orders);
        $this->smarty->assign('sales_list', $this->salesList);
        $this->smarty->assign('sure_list', $this->sureList);
        $this->smarty->assign('saler_suid_list', Tool_Array::getFields($this->salesList, 'suid'));
        $this->smarty->assign('query', http_build_query($this->searchConf));
        $this->smarty->assign('after_list', $this->afterList);
        $this->smarty->assign('sum', $this->sum);
        $this->smarty->assign('order_source', Conf_Order::$SOURCE_DESC);
        $this->smarty->assign('price_total', $this->priceTotal);
        $this->smarty->assign('delivery_time', $this->deliverTime);
        $this->smarty->assign('source_after_sale', Conf_Order::SOURCE_AFTER_SALE);
        $this->smarty->assign('after_sale_place_order', Conf_Aftersale::$AFTER_SALE_PLACE_ORDER);
        $this->smarty->assign('hide_conf', $this->hideConf);
        $cityList = City_Api::getCityList(TRUE);
        $this->smarty->assign('city_list', $cityList);
        $this->smarty->assign('_warehouseList', Conf_Warehouse::getWarehouseByAttr('ext_customer'));
        $this->smarty->assign('order_sand_price', $this->orderSandPrice);
        $this->smarty->assign('order_list_4_sales', $this->orderList4Sales);

        $this->smarty->display('order/order_list.html');
    }
}

$app = new App('pri');
$app->run();

