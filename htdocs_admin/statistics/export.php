<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $type;
    private $startTime;
    private $endTime;
    private $typeList;
    private $submit;
    private $warehouses;
    private $wid;
    private $city;
    /* 当前操作者所能操作的城市 */
    private $citys = array();
    //文件提取码
    private $code;
    //下载请求
    private $submitDownload;
    //导出数据数据任务参数
    private $info;

    protected function getPara()
    {
        $this->startTime = Tool_Input::clean('r', 'start_time', TYPE_STR);
        $this->endTime = Tool_Input::clean('r', 'end_time', TYPE_STR);
        $this->type = Tool_Input::clean('r', 'type', TYPE_UINT);
        $this->submit = Tool_Input::clean('r', 'submit', TYPE_STR);
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
        $this->city = Tool_Input::clean('r', 'city', TYPE_UINT);
        $this->code = Tool_Input::clean('r', 'code', TYPE_STR);
        $this->submitDownload = Tool_Input::clean('r', 'submit_download', TYPE_STR);
    }

    protected function checkPara()
    {
        if (empty($this->endTime))
        {
            $this->endTime = date('Y-m-d');
        }

        if (empty($this->startTime))
        {
            $this->startTime = date('Y-m-01');
        }
        
        if (!empty($this->wid)){
            $this->wid = array(
                0 => $this->wid
            );
        } else {
            $this->wid = $this->getAllAllowedWids4User($this->city, true);
        }
        if (!empty($this->city)){
            $this->city = array(
                0 => $this->city
            );
        } else {
            $this->city = explode(',', $this->_user['cities']);
        }
        if (empty($this->wid) && empty($this->city)){
            $this->city = explode(',', $this->_user['cities']);
            $this->wid = array();
            /* 取所能操作的全部仓库 */
            $t = 0;
            $cities = explode(',', $this->_user['cities']);
            for ($i = 0; $i < count($cities); $i++){
                for ($s = 0; $s < count($this->_user['city_wid_map'][$cities[$i]]); $s++) {
                    $this->wid[$t] = $this->_user['city_wid_map'][$cities[$i]][$s];
                    $t = $t + 1;
                }
            }
        }
        
        if (in_array(Conf_City::BEIJING, $this->city))
        {
            $this->city[] = Conf_City::OTHER;
        }
        
        //下载
        if (!empty($this->submitDownload)){
            
            $fullFileName = Queue_Task_Api::getDownloadFile($this->_uid, $this->code);
            
            Data_Csv::download($fullFileName);
            
            exit;
        }
        
        //准备导出数据数据任务参数
        $this->info = array(
            'title' => Conf_Statics::$TYPE[$this->type],
            'startTime' => $this->startTime,
            'endTime' => $this->endTime,
            'city' => implode($this->city, ','),
            'wid' => implode($this->wid, ',')
        );
    }

    protected function main()
    {
        $this->_getAllowedTypes();
        
        /* 取城市名称 */
        $cities = explode(',', $this->_user['cities']);
        for ($i = 0; $i < count($cities); $i++)
        {
            $this->citys[$i]['city_id'] = $cities[$i];
            $this->citys[$i]['city_name'] = Conf_City::getCityName($cities[$i]);
        }
        
    }

    protected function outputBody()
    {
        $this->smarty->assign('citys', $this->citys);
        $this->smarty->assign('start_time', $this->startTime);
        $this->smarty->assign('end_time', $this->endTime);
        $this->smarty->assign('type', $this->type);
        $this->smarty->assign('type_list', $this->typeList);
        $this->smarty->assign('warehouses', $this->warehouses);
        $this->smarty->assign('code', $this->code);
        $this->smarty->display('statistics/export.html');
    }
    
    private function _getAllowedTypes()
    {
//        $isAdmin = Admin_Role_Api::isAdmin($this->_uid);
//        
//        foreach (Conf_Statics::$TYPE as $type => $desc)
//        {
//            if (!$isAdmin && !in_array($this->_uid, Conf_Statics::$TYPE_USER[$type]))
//            {
//                continue;
//            }
//
//            $this->typeList[$type] = $desc;
//        }
//        
//        return $this->typeList;
        
        
        
        //后台配置：后续上线
        $this->typeList = Conf_Statics::$TYPE;
        
        if (!empty($this->submit))
        {
            switch ($this->type)
            {
                //导出订单物流时间点
                case Conf_Statics::TYPE_ORDER_LOGISTICS:
                    parent::checkAuth('/statistics/export_order_logistics_time');
                    break;
                case Conf_Statics::TYPE_ORDER_LOGISTICS_FEE:
                    parent::checkAuth('/statistics/export_order_logistics_money');
                    break;
                case Conf_Statics::TYPE_SALES_DETAIL:
                    parent::checkAuth('/statistics/export_sku_sale_detail');
                    break;
                case Conf_Statics::TYPE_STOCK_IN_DETAIL:
                    parent::checkAuth('/statistics/export_sku_stock_in_detail');
                    break;
                case Conf_Statics::TYPE_STOCK_SHIFT_DETAIL:
                    parent::checkAuth('/statistics/export_sku_shift_detail');
                    break;
                case Conf_Statics::TYPE_INVENTORY_DETAIL:
                    parent::checkAuth('/statistics/export_sku_inventory_detail');
                    break;
                case Conf_Statics::TYPE_PRODUCT_DETAIL:
                    parent::checkAuth('/statistics/export_product_info_city');
                    break;
                case Conf_Statics::TYPE_UNPAID_ORDER:
                    parent::checkAuth('/statistics/export_order_arrears');
                    break;
                case Conf_Statics::TYPE_FIRST_ORDER:
                    parent::checkAuth('/statistics/export_order_first');
                    break;
                case Conf_Statics::TYPE_SECURITY_STOCK:
                    parent::checkAuth('/statistics/export_safety_stock');
                    break;
                case Conf_Statics::TYPE_SALES_PERFORMANCE_KA:
                    parent::checkAuth('/statistics/export_ka_sales_performance');
                    break;
                case Conf_Statics::TYPE_OHTER_STOCK_OUT_ORDER_SELF_USE:
                    parent::checkAuth('/statistics/export_personal_detail');
                    break;
                case Conf_Statics::TYPE_OHTER_STOCK_OUT_ORDER_BROKEN:
                    parent::checkAuth('/statistics/export_reported_loss_detail');
                    break;
                case Conf_Statics::TYPE_NOT_BACK_INTERVAL:
                    parent::checkAuth('/statistics/export_no_receipt_statistics');
                    break;
                case Conf_Statics::TYPE_CUSTOMER_SCORE:
                    parent::checkAuth('/statistics/export_user_point');
                    break;
                case Conf_Statics::TYPE_CUSTOMER_SCORE_DETAIL:
                    parent::checkAuth('/statistics/export_user_point_detail');
                    break;
                case Conf_Statics::TYPE_SKU_REFUND_DETAIL:
                    parent::checkAuth('/statistics/export_sku_refund_detail');
                    break;
                case Conf_Statics::TYPE_OTHER_STOCK_PRODUCT:
                    parent::checkAuth('/statistics/export_other_stock_product');
                    break;
                case Conf_Statics::TYPE_STOCK_IN_REFUND_PRODUCT:
                    parent::checkAuth('/statistics/export_stock_in_refund_product');
                    break;
                case Conf_Statics::TYPE_NO_STOCK_SKU:
                    parent::checkAuth('/statistics/export_no_stock_sku');
                    break;
                case Conf_Statics::TYPE_IN_OUT_DIFF:
                    parent::checkAuth('/statistics/export_warehouse_in_out_difference');
                    break;
                case Conf_Statics::TYPE_KA_CUSTOMERS:
                    parent::checkAuth('/statistics/export_ka_customer_info');
                    break;
                case Conf_Statics::TYPE_SKU_LAST_IN:
                    parent::checkAuth('/statistics/export_sku_last_purchase_info');
                    break;
                case Conf_Statics::TYPE_SKU_DELIVERY_DETAIL:
                    parent::checkAuth('/statistics/export_north_south_product_out_detail');
                    break;
                default:
                    exit;
                    //nothing
            }
            Statistics_Api::exportForWeb(
                $this->type, 
                $this->startTime, 
                $this->endTime, 
                array('city' => $this->city,'wid' => $this->wid),
                $this->info,
                $this->_user
            );
            
            if ($this->type!=Conf_Statics::TYPE_SALES_DETAIL)
            {
                exit;
            }
            return;
        }
        else
        {
            $_permissions = $this->permissions;
            
            foreach ($this->typeList as $type => $name)
            {
                switch ($type)
                {
                    //导出订单物流时间点
                    case Conf_Statics::TYPE_ORDER_LOGISTICS:
                        if(empty($_permissions['/statistics/export_order_logistics_time']))
                        {
                            unset($this->typeList[$type]);
                        }
                        break;
                    case Conf_Statics::TYPE_ORDER_LOGISTICS_FEE:
                        if(empty($_permissions['/statistics/export_order_logistics_money']))
                        {
                            unset($this->typeList[$type]);
                        }
                        break;
                    case Conf_Statics::TYPE_SALES_DETAIL:
                        if(empty($_permissions['/statistics/export_sku_sale_detail']))
                        {
                            unset($this->typeList[$type]);
                        }
                        break;
                    case Conf_Statics::TYPE_STOCK_IN_DETAIL:
                        if(empty($_permissions['/statistics/export_sku_stock_in_detail']))
                        {
                            unset($this->typeList[$type]);
                        }
                        break;
                    case Conf_Statics::TYPE_STOCK_SHIFT_DETAIL:
                        if(empty($_permissions['/statistics/export_sku_shift_detail']))
                        {
                            unset($this->typeList[$type]);
                        }
                        break;
                    case Conf_Statics::TYPE_INVENTORY_DETAIL:
                        if(empty($_permissions['/statistics/export_sku_inventory_detail']))
                        {
                            unset($this->typeList[$type]);
                        }
                        break;
                    case Conf_Statics::TYPE_PRODUCT_DETAIL:
                        if(empty($_permissions['/statistics/export_product_info_city']))
                        {
                            unset($this->typeList[$type]);
                        }
                        break;
                    case Conf_Statics::TYPE_UNPAID_ORDER:
                        if(empty($_permissions['/statistics/export_order_arrears']))
                        {
                            unset($this->typeList[$type]);
                        }
                        break;
                    case Conf_Statics::TYPE_FIRST_ORDER:
                        if(empty($_permissions['/statistics/export_order_first']))
                        {
                            unset($this->typeList[$type]);
                        }
                        break;
                    case Conf_Statics::TYPE_SECURITY_STOCK:
                        if(empty($_permissions['/statistics/export_safety_stock']))
                        {
                            unset($this->typeList[$type]);
                        }
                        break;
                    case Conf_Statics::TYPE_SALES_PERFORMANCE_KA:
                        if(empty($_permissions['/statistics/export_ka_sales_performance']))
                        {
                            unset($this->typeList[$type]);
                        }
                        break;
                    case Conf_Statics::TYPE_OHTER_STOCK_OUT_ORDER_SELF_USE:
                        if(empty($_permissions['/statistics/export_personal_detail']))
                        {
                            unset($this->typeList[$type]);
                        }
                        break;
                    case Conf_Statics::TYPE_OHTER_STOCK_OUT_ORDER_BROKEN:
                        if(empty($_permissions['/statistics/export_reported_loss_detail']))
                        {
                            unset($this->typeList[$type]);
                        }
                        break;
                    case Conf_Statics::TYPE_NOT_BACK_INTERVAL:
                        if(empty($_permissions['/statistics/export_no_receipt_statistics']))
                        {
                            unset($this->typeList[$type]);
                        }
                        break;
                    case Conf_Statics::TYPE_CUSTOMER_SCORE:
                        if(empty($_permissions['/statistics/export_user_point']))
                        {
                            unset($this->typeList[$type]);
                        }
                        break;
                    case Conf_Statics::TYPE_CUSTOMER_SCORE_DETAIL:
                        if(empty($_permissions['/statistics/export_user_point_detail']))
                        {
                            unset($this->typeList[$type]);
                        }
                        break;
                    case Conf_Statics::TYPE_SKU_REFUND_DETAIL:
                        if(empty($_permissions['/statistics/export_sku_refund_detail']))
                        {
                            unset($this->typeList[$type]);
                        }
                        break;
                    case Conf_Statics::TYPE_OTHER_STOCK_PRODUCT:
                        if(empty($_permissions['/statistics/export_other_stock_product']))
                        {
                            unset($this->typeList[$type]);
                        }
                        break;
                    case Conf_Statics::TYPE_STOCK_IN_REFUND_PRODUCT:
                        if(empty($_permissions['/statistics/export_stock_in_refund_product']))
                        {
                            unset($this->typeList[$type]);
                        }
                        break;
                    case Conf_Statics::TYPE_NO_STOCK_SKU:
                        if(empty($_permissions['/statistics/export_no_stock_sku']))
                        {
                            unset($this->typeList[$type]);
                        }
                        break;
                    case Conf_Statics::TYPE_IN_OUT_DIFF:
                        if(empty($_permissions['/statistics/export_warehouse_in_out_difference']))
                        {
                            unset($this->typeList[$type]);
                        }
                        break;
                    case Conf_Statics::TYPE_KA_CUSTOMERS:
                        if(empty($_permissions['/statistics/export_ka_customer_info']))
                        {
                            unset($this->typeList[$type]);
                        }
                        break;
                    case Conf_Statics::TYPE_SKU_LAST_IN:
                        if(empty($_permissions['/statistics/export_sku_last_purchase_info']))
                        {
                            unset($this->typeList[$type]);
                        }
                        break;
                    case Conf_Statics::TYPE_SKU_DELIVERY_DETAIL:
                        if(empty($_permissions['/statistics/export_north_south_product_out_detail']))
                        {
                            unset($this->typeList[$type]);
                        }
                        break;
                    default:
                        //nothing
                }
            }
        }
    }
}

$app = new App('pri');
$app->run();
