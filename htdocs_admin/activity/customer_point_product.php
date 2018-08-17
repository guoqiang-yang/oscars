<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $pageNum = 20;
    // 中间结果
    private $productList;
    private $staffList;
    private $total;
    private $searchConf;
    private $start;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->searchConf = array(
            'pid' => Tool_Input::clean('r', 'pid', TYPE_UINT),
            'title' => Tool_Input::clean('r', 'title', TYPE_STR),
            'status' => Tool_Input::clean('r', 'status', TYPE_UINT),
            'cate1' => Tool_Input::clean('r', 'cate1', TYPE_UINT),
        );
    }

    protected function main()
    {
        $res = Cpoint_Api::getProductList($this->searchConf, $this->start, $this->pageNum);
        $this->productList = $res['list'];
        $this->total = $res['total'];
        $staffs = Admin_Api::getStaffList();
        $this->staffList = Tool_Array::list2Map($staffs['list'], 'suid', 'name');
        $this->addFootJs(array('js/apps/picture.js'));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $app = '/activity/customer_point_product.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->pageNum, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('cate_list', Conf_Cpoint::getCate1());
        $this->smarty->assign('list', $this->productList);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('staff_list', $this->staffList);
        unset($this->searchConf['status']);
        $stepUrl = '/activity/customer_point_product.php?' . http_build_query($this->searchConf);
        $this->smarty->assign('step_url', $stepUrl);
        $this->smarty->display('activity/customer_point_product.html');
    }
}

$app = new App('pri');
$app->run();

