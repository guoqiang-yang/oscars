<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $pid;
    private $start;
    private $num = 20;
    private $total;
    private $productLogs;

    protected function getPara()
    {
        $this->pid = Tool_Input::clean('r', 'pid', TYPE_UINT);
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);

    }

    protected function checkPara()
    {
        if (empty($this->pid))
        {
            throw new Exception('common: param error');
        }
    }

    protected function checkAuth()
    {
        parent::checkAuth('/activity/show_customer_point_product');
    }

    protected function main()
    {
        $cp = new Cpoint_Stock_History();
        $res = $cp->getListByPid($this->pid,$this->start, $this->num);
        $this->productLogs = $res['list'];
        $this->total = $res['total'];
    }

    protected function outputPage()
    {
        $this->smarty->assign('product_logs', $this->productLogs);
        $pageHtml = Str_Html::getJsPagehtml2($this->start, $this->num, $this->total, 'cpoint_product_stock_log_pagetruning');
        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('total', $this->total);
        $html = $this->smarty->fetch('activity/dlg_product_stock_log_list.html');
        $result = array('html' => $html);
        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }
}

$app = new App('pri');
$app->run();