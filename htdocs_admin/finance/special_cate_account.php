<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    // todo 更新数据表t_order_product 退单商品的wid字段：之前没有写wid，用t_refund中得wid字段更新
    // update t_order_product set wid=(select wid from t_refund where rid=t_order_product.rid) where rid!=0;

    // cgi参数
    private $start;
    private $bdate;  //时间 格式 YYYY-MM-DD
    private $edate;
    private $btype;    //采购类型 {0: 全部; 1: 实采; 2: 空采}
    private $pSearchCate;    //商品检索类型 {0: 全部; 1: 沙子水泥砖; 3: 其他}
    private $wid;        //仓库id
    private $num = 120;
    private $plist = array();    //商品列表

    protected function checkAuth()
    {
        parent::checkAuth('/order/special_cate_account');
    }

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->bdate = Tool_Input::clean('r', 'bdate', TYPE_STR);
        $this->edate = Tool_Input::clean('r', 'edate', TYPE_STR);
        $this->pSearchCate = Tool_Input::clean('r', 'product_search_cate', TYPE_UINT);
        $this->wid = $this->getWarehouseId();

        if (empty($this->bdate) || strlen($this->bdate) != 10 || count(explode('-', $this->bdate)) != 3)
        {
            $this->bdate = date('Y-m-d');
        }
        if (empty($this->edate) || strlen($this->edate) != 10 || count(explode('-', $this->edate)) != 3)
        {
            $this->edate = date('Y-m-d');
        }
        if (strtotime($this->edate) < strtotime($this->bdate))
        {
            $this->edate = $this->bdate;
        }
    }

    protected function main()
    {
        $oo = new Order_Order();

        $others = array(
            10264,
            10265,
            12554
        );
        $searchObj = array_merge(Conf_Order::$SAND_CEMENT_BRICK_PIDS, $others);

        $where = 'status=0 and pid in (' . implode(',', $searchObj) . ')';
        $bdate = $this->bdate . ' 00:00:00';
        $edate = $this->edate . ' 23:59:59';
        $where .= " and oid in (select oid from t_order where step>=" . Conf_Order::ORDER_STEP_SURE . " and delivery_date>='$bdate' and delivery_date<='$edate')";
        if (!empty($this->wid))
        {
            $where .= ' and wid=' . $this->wid;
        }

        $fields = array(
            '*',
            'sum(cost*num)',
            'sum(num)'
        );
        $this->plist = $oo->getOrderProductsByWhere($where, $this->start, $this->num, 'oid, rid', array(), $fields);
    }

    protected function outputBody()
    {
        $app = '/finance/special_cate_account.php?bdate=' . $this->bdate . '&edate=' . $this->edate . '&product_search_cate=' . $this->pSearchCate . '&wid=' . $this->wid;
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->plist['total'], $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('bdate', $this->bdate);
        $this->smarty->assign('edate', $this->edate);
        $this->smarty->assign('btype', $this->btype);
        $this->smarty->assign('pSearchCate', $this->pSearchCate);
        $this->smarty->assign('wid', $this->wid);
        $this->smarty->assign('pdatas', $this->plist['data']);
        $this->smarty->assign('total', $this->plist['total']);
        $this->smarty->assign('_warehouseList', Conf_Warehouse::getWarehouseByAttr('stock'));

        $this->smarty->display('order/special_cate_account.html');
    }
}

$app = new App('pri');
$app->run();
