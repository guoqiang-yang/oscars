<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $ids;
    private $batch;
    private $statementInfo;
    private $totalPrice;

    protected $headTmpl = 'head/head_none.html';
    protected $tailTmpl = 'tail/tail_none.html';

    protected function getPara()
    {
        $this->ids = Tool_Input::clean('r', 'ids', TYPE_STR);
    }

    protected function checkPara()
    {
        if (empty($this->ids))
        {
            throw  new Exception('没有可打印内容！');
        }
    }

    protected function main()
    {
        $ids = explode(',', $this->ids);
        $lcs = new Logistics_Coopworker_Statement();
        $this->statementInfo = $lcs->getById($ids);

        $batch = array_unique(Tool_Array::getFields($this->statementInfo, 'batch'));
        $this->batch = $batch[0];
        $dids = array_unique(Tool_Array::getFields($this->statementInfo, 'cuid'));

        $ld = new Logistics_Driver();
        $drivers = $ld->getByDids($dids);
        $driverInfo = Tool_Array::list2Map($drivers, 'did');

        foreach ($this->statementInfo as &$statement)
        {
            $statement['real_name'] = empty($driverInfo[$statement['cuid']]['real_name'])?'':$driverInfo[$statement['cuid']]['real_name'];
            $statement['bank_info'] = empty($driverInfo[$statement['cuid']]['bank_info'])?'':$driverInfo[$statement['cuid']]['bank_info'];
            $statement['card_num'] = empty($driverInfo[$statement['cuid']]['card_num'])?'':$driverInfo[$statement['cuid']]['card_num'];
            $this->totalPrice += $statement['price'];
        }
    }

    protected function outputBody()
    {
        $chineseTotal = Str_Chinese::getChineseNum($this->totalPrice / 100);
        $this->smarty->assign('chinese_total', $chineseTotal);
        $this->smarty->assign('total_price', $this->totalPrice / 100);
        $this->smarty->assign('batch', $this->batch);
        $this->smarty->assign('statement_info', $this->statementInfo);
        $this->smarty->display('order/print_statement_finance.html');
    }
}

$app = new App();
$app->run();