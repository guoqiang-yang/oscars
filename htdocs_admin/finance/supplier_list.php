<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $num = 20;
    private $sid;
    private $mobile;
    private $generalName;
    private $cate1;
    private $start;
    private $sum;
    private $total;
    private $supplierList;
    private $city;

    protected function getPara()
    {
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
        $this->mobile = Tool_Input::clean('r', 'mobile', TYPE_UINT);
        $this->generalName = Tool_Input::clean('r', 'general_name', TYPE_STR);
        $this->cate1 = Tool_Input::clean('r', 'cate1', TYPE_UINT);
        $this->city = Tool_Input::clean('r', 'city', TYPE_UINT);

        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
    }

    protected function main()
    {
        if (!empty($this->sid))
        {
            $this->supplierList[] = Warehouse_Api::getSupplier($this->sid);
            $this->total = 1;
            $this->sum = $this->supplierList[0]['account_balance'] / 100;
        }
        else
        {
            $searchConf = array();

            if (!empty($this->cate1))
            {
                $searchConf['cate1'] = $this->cate1;
            }
            if (!empty($this->mobile))
            {
                $searchConf['phone'] = $this->mobile;
            }
            if (!empty($this->generalName))
            {
                $searchConf['keyword'] = $this->generalName;
            }
            if (!empty($this->city))
            {
                $searchConf['city'] = $this->city;
            }

            $order = 'order by account_balance desc';
            $_supplierList = Warehouse_Api::getSupplierList($searchConf, $order, $this->start, $this->num);

            $this->total = $_supplierList['total'];
            $this->supplierList = $_supplierList['list'];
            $this->sum = $_supplierList['sum'];
        }
    }

    protected function outputBody()
    {
        $httpQueryParams = 'sid=' . $this->sid . '&mobile=' . $this->mobile . '&general_name=' . $this->generalName . '&cate1=' . $this->cate1;
        $app = '/finance/supplier_list.php?' . $httpQueryParams;
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('sid', $this->sid);
        $this->smarty->assign('mobile', $this->mobile);
        $this->smarty->assign('general_name', $this->generalName);
        $this->smarty->assign('cate1', $this->cate1);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('sum', $this->sum);
        $this->smarty->assign('supplier_list', $this->supplierList);
        $this->smarty->assign('all_cate1', Conf_Sku::$CATE1);
        $this->smarty->assign('city_list', Conf_City::$CITY);
        $this->smarty->assign('city', $this->city);
        $this->smarty->display('finance/supplier_list.html');
    }
}

$app = new App('pri');
$app->run();