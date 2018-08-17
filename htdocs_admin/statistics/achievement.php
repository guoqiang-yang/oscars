<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 16/3/22
 * Time: 15:47
 */

include_once('../../global.php');

class App extends App_Admin_Page
{
    // cgi参数
    private $list;
    private $searchConf;
    private $order;
    private $dateList;
    private $res;
    private $staffList;
    private $salerLat;

    protected function checkAuth()
    {
        //换了菜单
        parent::checkAuth('/cs/achievement');
    }

    protected function getPara()
    {
        $this->order = Tool_Input::clean('r', 'order', TYPE_STR);
        $this->searchConf = array(
            'from_date' => Tool_Input::clean('r', 'from_date', TYPE_STR),
            'end_date' => Tool_Input::clean('r', 'end_date', TYPE_STR),
            'status' => Conf_Base::STATUS_NORMAL,
            'city_id' => Tool_Input::clean('r', 'city_id', TYPE_UINT),
        );
    }

    protected function checkPara()
    {
        if (empty($this->searchConf['from_date']))
        {
            $this->searchConf['from_date'] = date('Y-m-d', strtotime('-7 days'));
        }
        if (empty($this->searchConf['end_date']))
        {
            $this->searchConf['end_date'] = date('Y-m-d');
        }
    }

    protected function main()
    {
        $startTime = strtotime('-6 days');
        for ($i = 0; $i < 7; $i++)
        {
            $this->dateList[] = date('Y-m-d', $startTime + $i * 86400);
        }

        $this->res = Cs_Api::achievement($this->searchConf, $this->_user);

        if ($this->order == 'num')
        {
            uasort($this->res['list'][0], array(
                'self',
                '_sortByNum'
            ));
            uasort($this->res['list'][1], array(
                'self',
                '_sortByNum'
            ));
        }
        else if ($this->order == 'amount')
        {
            uasort($this->res['list'][0], array(
                'self',
                '_sortByAmount'
            ));
            uasort($this->res['list'][1], array(
                'self',
                '_sortByAmount'
            ));
        }
        else if ($this->order == 'product_num')
        {
            uasort($this->res['list'], array(
                'self',
                '_sortByPnum'
            ));
        }
        else
        {
            uasort($this->res['list'][0], array(
                'self',
                '_sortByNum'
            ));
            uasort($this->res['list'][1], array(
                'self',
                '_sortByNum'
            ));
        }

        $this->list = $this->res['list'][0];
        $this->salerLat = $this->res['list'][1];
        $staffSuid = array();
        if (!empty($this->list))
        {
            foreach ($this->list as $item)
            {
                foreach ($item['saler_order_info'] as $id =>$salerInfo)
                {
                    if (!in_array($id, $staffSuid))
                    {
                        $staffSuid[] = $id;
                    }
                }
            }
        }

        if (!empty($this->salerLat))
        {
            foreach ($this->salerLat as $info)
            {
                foreach ($info['service_staff_info'] as $ssiid => $serviceInfo)
                {
                    if (!in_array($ssiid, $staffSuid))
                    {
                        $staffSuid[] = $ssiid;
                    }
                }
            }
        }

        if (!empty($staffSuid))
        {
            $this->staffList = Tool_Array::list2Map(Admin_Api::getStaffs($staffSuid), 'suid');
        }
    }

    protected function outputBody()
    {
        $app = '/cs/achievement.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('date_list', $this->dateList);
        $this->smarty->assign('list', $this->list);
        $this->smarty->assign('salerLat', $this->salerLat);
//        $this->smarty->assign('max_date', date('Y-m-d'));
//        $this->smarty->assign('min_date', date('Y-m-d', strtotime('-3 month')));
        $this->smarty->assign('order_total', $this->res['order_total']);
        $this->smarty->assign('product_total', $this->res['product_total']);
        $this->smarty->assign('amount_total', $this->res['amount_total']);
        $this->smarty->assign('city_list', Conf_City::$CITY);
        $this->smarty->assign('staff_list', $this->staffList);
        $this->smarty->display('cs/achievement.html');
    }

    private static function _sortByNum($a, $b)
    {
        if ($a['total'] == $b['total'])
        {
            return 0;
        }

        return $a['total'] > $b['total'] ? -1 : 1;
    }

    private static function _sortByAmount($a, $b)
    {
        if ($a['amount'] == $b['amount'])
        {
            return 0;
        }

        return $a['amount'] > $b['amount'] ? -1 : 1;
    }

    private static function _sortByPnum($a, $b)
    {
        if ($a['product_num'] == $b['product_num'])
        {
            return 0;
        }

        return $a['product_num'] > $b['product_num'] ? -1 : 1;
    }
}

$app = new App('pri');
$app->run();
