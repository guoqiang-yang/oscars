<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $num = 20;
    private $start;
    private $search;
    private $sort;
    private $couponList;

    protected function getPara()
    {
        $this->search = array(
            'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
            'type' => Tool_Input::clean('r', 'type', TYPE_UINT),
            'btime' => Tool_Input::clean('r', 'btime', TYPE_STR),
            'etime' => Tool_Input::clean('r', 'etime', TYPE_STR)
        );
        $this->search['used'] = isset($_REQUEST['used']) ? $_REQUEST['used'] : -1;
        $this->sort = Tool_Input::clean('r', 'sort', TYPE_STR);
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
    }

    protected function checkPara()
    {
        if(!empty($this->search['btime']))
        {
            $this->search['btime'] .= ' 00:00:00';
        }
        if(!empty($this->search['etime']))
        {
            $this->search['etime'] .= ' 23:59:59';
        }
    }

    protected function main()
    {
        $this->couponList = Coupon_Api::getMyCustomerCouponList($this->search, $this->sort, $this->_user, $this->start, $this->num);
        if (!empty($this->couponList))
        {
            foreach ($this->couponList['data'] as &$item)
            {
                if ($item['deadline'] <= date('Y-m-d 00:00:00'))
                {
                    $item['overdate'] = 1;
                }
                else if ($item['deadline'] <= date('Y-m-d', strtotime('+7 days')))
                {
                    $item['overdate'] = 2;
                }
            }
        }
    }

    protected function outputBody()
    {
        if(!empty($this->search['btime']))
        {
            $this->search['btime'] = date("Y-m-d", strtotime($this->search['btime']));
        }
        if(!empty($this->search['etime']))
        {
            $this->search['etime'] = date("Y-m-d", strtotime($this->search['etime']));
        }
        $_app = '/crm2/coupon_list.php?' . http_build_query($this->search);
        $app = $_app . "&sort=" . $this->sort;
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->couponList['total'], $app);

        $this->smarty->assign('query_url', $_app);
        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('couponTypes', Conf_Coupon::$couponTypes);
        $this->smarty->assign('search_conf', $this->search);
        $this->smarty->assign('coupon_list', $this->couponList['data']);
        $this->smarty->assign('total', $this->couponList['total']);

        $this->smarty->display('crm2/coupon_list.html');
    }
}

$app = new App();
$app->run();