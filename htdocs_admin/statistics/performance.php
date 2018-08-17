<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $fromDate;
    private $endDate;
    private $orderBy;
    private $download;
    private $cityId;
    private $leaderSuid;
    private $performanceList;
    private $tooEarly = 0;
    private $cities;
    private $leaders;
    private $totalOrderCustomer;
    private $nocache = 0;
    
    private $balanceDue = array();

    protected function checkAuth()
    {
       parent::checkAuth('/crm2/performance');
    }

    protected function getPara()
    {
        $this->fromDate = Tool_Input::clean('r', 'from_date', TYPE_STR);
        $this->endDate = Tool_Input::clean('r', 'end_date', TYPE_STR);
        $this->orderBy = Tool_Input::clean('r', 'order_by', TYPE_STR);
        $this->leaderSuid = Tool_Input::clean('r', 'leader_suid', TYPE_UINT);
        $this->download = Tool_Input::clean('r', 'download', TYPE_UINT);
        $this->cityId = Tool_Input::clean('r', 'city_id', TYPE_UINT);
        $this->nocache = Tool_Input::clean('r', 'nocache', TYPE_UINT);
        $this->cities = Conf_City::$CITY;
        unset($this->cities[Conf_City::XIANGHE]);
    }

    protected function checkPara()
    {
        if (empty($this->fromDate))
        {
            $this->fromDate = date("Y-m-01");
            $this->endDate = date("Y-m-d");
        }
        if ($this->fromDate < "2016-02-01")
        {
            $this->tooEarly = 1;
        }
        if (empty($this->orderBy))
        {
            $this->orderBy = 'spending_amount';
        }
    }

    protected function main()
    {
        //区分销售类型
        if (!empty($this->_user['city_id']))
        {
            $this->cityId = $this->_user['city_id'];
        }

        //获取销售列表
        $roles = array(Conf_Admin::ROLE_SALES_NEW, Conf_Admin::ROLE_SALES_KA);
        $susers = Admin_Role_Api::getStaffOfRole($roles);
        
        //读取业绩
        $suids = Tool_Array::getFields($susers, 'suid');
        
        $_pfmMemKeyParams = array(
            date('Ymd', strtotime($this->fromDate)), 
            date('Ymd', strtotime($this->endDate))
        );
        $pfmMemKey = Conf_Memcache::getMemcacheKey(Conf_Memcache::MEMKEY_PERFORMANCE_INFO, $_pfmMemKeyParams);

        //获取缓存的数据
        if ($_SERVER['SERVER_ADDR'] != '127.0.0.1' && empty($this->nocache))
        {
            $pfmList = json_decode(Data_Memcache::getInstance()->get($pfmMemKey), true);

            if (empty($pfmList))
            {
                $pfmList = Crm2_Api::getPerformanceList($suids, $this->fromDate, $this->endDate);
                self::_appendCustomerFields($suids, $pfmList);
                Data_Memcache::getInstance()->set($pfmMemKey, json_encode($pfmList), Conf_Memcache::PERFORMANCE_INFO_EXPIRE_TIME);
            }
        }
        else
        {
            $pfmList = Crm2_Api::getPerformanceList($suids, $this->fromDate, $this->endDate);
            self::_appendCustomerFields($suids, $pfmList);
        }

        //根据组长筛选
        if (!empty($this->leaderSuid))
        {
            foreach($pfmList as $_suid => $pfmInfo)
            {
                if ($pfmInfo['_suser']['leader_suid']!=$this->leaderSuid)
                {
                    unset($pfmList[$_suid]);
                }
            }
        }
        //根据城市筛选
        else if (!empty($this->cityId))
        {
            foreach($pfmList as $_suid => $pfmInfo)
            {
                if ($pfmInfo['_suser']['city_id']!=$this->cityId)
                {
                    unset($pfmList[$_suid]);
                }
            }
        }
        $this->performanceList = $pfmList;

        //过滤掉离职销售
        $this->_filterLeft($this->performanceList, $susers);

        //排序
        if ($this->orderBy)
        {
            uasort($this->performanceList, array(
                $this,
                '_cmp'
            ));
        }

        // 总欠款
        $sr = new Statistics_Receiverables();
        $this->balanceDue = $sr->statBalanceDueBySales();
        
        $this->addCss(array());

        //导出文件
        if ($this->download)
        {
            header("Content-type:text/csv");
            header("Content-Disposition:attachment;filename=" . 'customer-' . date('Ymd') . '.csv');
            header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
            header('Expires:0');
            header('Pragma:public');

            $head = array(
                '姓名',
                '线索录入',
                '新下单客户',
                '销售额',
                '欠款额',
                '自助下单',
                '非自助下单',
                '自有下单',
                '非自有下单',
                '新客',
                '老客',
                '预付款',
                '客户拜访',
                '订单数',
                '退款额',
                '二次下单客户'
            );
            Data_Csv::send($head);

            foreach ($this->performanceList as $suid => $performance)
            {
                $arr = array(
                    $performance['_suser']['name'],
                    $performance['input'],
                    $performance['new_order_customer']['all'],
                    $performance['spending_amount']['all'],
                    $performance['spending_amount']['debt'],
                    $performance['spending_amount']['self'],
                    $performance['spending_amount']['all'] - $performance['spending_amount']['self'],
                    $performance['spending_amount']['haocai'],
                    $performance['spending_amount']['not_haocai'],
                    $performance['spending_amount']['new_customer'],
                    $performance['spending_amount']['all'] - $performance['spending_amount']['new_customer'],
                    $performance['sales_amount']['pre'],
                    $performance['call'],
                    $performance['order'],
                    $performance['refund_stat']['refund'],
                    $performance['second_order_customer']
                );
                Data_Csv::send($arr);
            }
            exit;
        }
        $this->leaders = Admin_Api::getSalesLeaders();
    }

    /**
     * 向列表补充字段
     * @author libaolong
     * @param $suids
     * @param $pfmList
     */
    private function _appendCustomerFields($suids, &$pfmList)
    {
        $oo = new Order_Order();
        $where = sprintf('status=%d and step>=%d and delivery_date>= "%s 00:00:00" and delivery_date<="%s 23:59:59" and saler_suid in (%s) group by saler_suid',
            Conf_Base::STATUS_NORMAL, Conf_Order::ORDER_STEP_SURE, $this->fromDate, $this->endDate, implode(',', $suids));
        $this->totalOrderCustomer = Tool_Array::list2Map($oo->getListRawWhere($where, $toatal, '', 0, 0, array('saler_suid', 'count(distinct(cid)) as num'), false), 'saler_suid');

        foreach ($pfmList as $saler_suid => &$item)
        {
            $item['order_customer_num'] = $this->totalOrderCustomer[$saler_suid]['num'];
        }
    }

    private function _filterLeft(&$performanceList, $susers)
    {
        $susers = Tool_Array::list2Map($susers, 'suid');
        foreach ($performanceList as $suid => $performance)
        {
            $suser = isset($susers[$suid]) ? $susers[$suid]:array();
            if ($performance['spending_amount']['all']<=0.001 && $suser['status']<>0)
            {
                unset($performanceList[$suid]);
            }
        }
    }

    private function _cmp($item1, $item2)
    {
        $orderBy = $this->orderBy;

        if (!isset($item[$orderBy]))
        {
            $value1 = $value2 = 0;
            switch ($orderBy)
            {
                case 'new_order_customer':
                case 'spending_amount':
                    $value1 = $item1[$orderBy]['all'];
                    $value2 = $item2[$orderBy]['all'];
                    break;
                case 'pre':
                    $value1 = $item1['sales_amount']['pre'];
                    $value2 = $item2['sales_amount']['pre'];
                    break;
                case 'sales_amount':
                    $value1 = $item1[$orderBy]['pre'];
                    $value2 = $item2[$orderBy]['pre'];
                    break;
                default:
                    $value1 = $item1[$orderBy];
                    $value2 = $item2[$orderBy];
                    break;
            }

            if ($value1 > $value2)
            {
                return -1;
            }
            elseif ($value1 < $value2)
            {
                return 1;
            }
            else
            {
                return 0;
            }
        }

        return 0;
    }

    protected function outputBody()
    {
        $this->smarty->assign('performance_list', $this->performanceList);
        $this->smarty->assign('total_order_customer_list', $this->totalOrderCustomer);
        $this->smarty->assign('from_date', $this->fromDate);
        $this->smarty->assign('end_date', $this->endDate);
        $this->smarty->assign('order_by', $this->orderBy);
        $this->smarty->assign('search_city_id', $this->cityId);
        $this->smarty->assign('search_leader_suid', $this->leaderSuid);
        $this->smarty->assign('too_early', $this->tooEarly);
        $this->smarty->assign('cities', $this->cities);
        $this->smarty->assign('leaders', $this->leaders);
        $this->smarty->assign('balance_due', $this->balanceDue);
        $this->smarty->assign('total_balance_due', array_sum($this->balanceDue));

        $this->smarty->display('crm2/performance.html');
    }
}

$app = new App('pri');
$app->run();

