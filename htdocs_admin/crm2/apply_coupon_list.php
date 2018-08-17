<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $num = 20;
    private $start;
    private $searchConf;
    private $salerList;
    private $applyList;

    protected function getPara()
    {
        $this->searchConf = array(
            'sales_suid' => Tool_Input::clean('r', 'sales_suid', TYPE_UINT),
            'status' => Tool_Input::clean('r', 'status', TYPE_UINT),
        );
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
    }

    protected function main()
    {

        $this->applyList = Coupon_Api::getApplyList($this->searchConf, $this->start, $this->num);
        $this->salerList = Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_SALES_NEW);

        $this->addFootJs(array('js/apps/crm2.js'));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $total = $this->applyList['total'];
        $app = '/crm2/apply_coupon_list.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('total', $total);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('applyList', $this->applyList['datas']);
        $this->smarty->assign('salemanList', $this->salerList);
        $this->smarty->assign('applyCouponSt', Conf_Coupon::$applyCouponStatus);

        $this->smarty->display('crm2/apply_coupon_list.html');
    }
}

$app = new App();
$app->run();