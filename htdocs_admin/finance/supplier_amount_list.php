<?php
/**
 * Created by PhpStorm.
 * User: libaolong
 * Date: 2018/5/14
 * Time: 下午1:12
 */
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $num = 20;
    private $start;
    private $supplier;
    private $searchConf;
    private $amountList;
    private $total;

    protected function checkAuth()
    {
        parent::checkAuth('/finance/supplier_amount_list');
    }

    protected function getPara()
    {
        $this->searchConf = array(
            'sid' => Tool_Input::clean('r', 'sid', TYPE_UINT),
            'type' => Tool_Input::clean('r', 'type', TYPE_UINT),
            'btime' => Tool_Input::clean('r', 'btime', TYPE_STR),
            'etime' => Tool_Input::clean('r', 'etime', TYPE_STR),
        );
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
    }

    protected function main()
    {
        $ws = new Warehouse_Supplier();
        $data = $ws->getSupplierAmountList($this->searchConf, $this->start, $this->num);
        $this->amountList = $data['list'];
        $this->total = $data['total'];
        $this->supplier = $data['supplier'];

        $this->addFootJs(array('js/apps/supplier.js'));
    }

    protected function outputBody()
    {
        $staffList = Tool_Array::list2Map(Admin_Api::getAllStaff(true), 'suid');
        $queryStr = http_build_query($this->searchConf);
        $app = '/finance/supplier_amount_list.php?' . $queryStr;
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('queryStr', $queryStr);
        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('payment_types', Conf_Finance::$MONEY_OUT_PAID_TYPES);
        $this->smarty->assign('supplier', $this->supplier);
        $this->smarty->assign('bill_type', Conf_Finance::getAmountType());
        $this->smarty->assign('amountList', $this->amountList);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('staffList', $staffList);
        $this->smarty->assign('search', $this->searchConf);
        $this->smarty->assign('supplier', $this->supplier);
        $this->smarty->assign('city_list', Conf_City::$CITY);
        $this->smarty->assign('user_city', explode(',', $this->_user['cities']));

        $this->smarty->display('finance/supplier_amount_list.html');
    }
}

$app = new App('pri');
$app->run();