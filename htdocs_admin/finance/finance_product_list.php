<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $cate1;
    private $start;
    private $searchConf;
    private $num = 20;
    private $total;
    private $products;
    private $status;

    protected function getPara()
    {
        $this->cate1 = Tool_Input::clean('r', 'cate1', TYPE_UINT);
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->status = Tool_Input::clean('r', 'status', TYPE_STR);
        $this->searchConf = array(
            'cate1' => $this->cate1,
            'pid' => Tool_Input::clean('r', 'pid', TYPE_UINT),
            'title' => Tool_Input::clean('r', 'title', TYPE_STR),
            'city_id' => Tool_Input::clean('r', 'city_id', TYPE_STR),
        );
    }

    protected function checkPara()
    {

    }

    protected function main()
    {


        if ($this->status == 'all')
        {

        }
        else if ($this->status == 'del')
        {
            $this->searchConf['status'] = Conf_Base::STATUS_DELETED;
        }
        else
        {
            $this->searchConf['status'] = Conf_Base::STATUS_NORMAL;
        }

        $this->products = Invoice_Api::getProductList($this->searchConf, $this->total ,$this->start, $this->num);

        $this->addFootJs(array('js/apps/invoice_product.js'));
    }

    protected function outputBody()
    {
        $page_url = '/finance/finance_product_list.php?' . http_build_query($this->searchConf);
        $this->searchConf['status'] = $this->status;
        $app = '/finance/finance_product_list.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('products_list', $this->products);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('city_list', Conf_City::$CITY);
        $this->smarty->assign('page_url', $page_url);
        $this->smarty->assign('status', $this->status);
        $this->smarty->assign('status_list', Conf_Base::$STATUS);
        $this->smarty->display('finance/finance_product_list.html');
    }
}

$app = new App('pri');
$app->run();