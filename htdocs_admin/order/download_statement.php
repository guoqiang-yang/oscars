<?php
include_once ('../../global.php');

class App extends App_Admin_Page
{
    private $statementIds;

    protected function getPara()
    {
        $this->statementIds = Tool_Input::clean('r', 'statement_ids', TYPE_STR);
    }

    protected function checkPara()
    {
        if (empty($this->statementIds))
        {
            throw new Exception('请勾选结算单');
        }
    }

    protected function main()
    {
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . '结算单-' . date('Ymd') . '.csv');
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        $ids = explode(',', $this->statementIds);
        $lcs = new Logistics_Coopworker_Statement();
        $statementInfo = $lcs->getById($ids);
        $userType = array_unique(Tool_Array::getFields($statementInfo, 'user_type'));
        $batch = array_unique(Tool_Array::getFields($statementInfo, 'batch'));
        $ids = array_unique(Tool_Array::getFields($statementInfo, 'cuid'));

        if ($userType[0] == Conf_Base::COOPWORKER_DRIVER)
        {
            $ld = new Logistics_Driver();
            $drivers = $ld->getByDids($ids);
            $coopWorkerInfo = Tool_Array::list2Map($drivers, 'did');
        }
        else if ($userType[0] == Conf_Base::COOPWORKER_CARRIER)
        {
            $ld = new Logistics_Carrier();
            $carriers = $ld->getByCids($ids);
            $coopWorkerInfo = Tool_Array::list2Map($carriers, 'cid');
        }

        $head1 = array('批次号', 'HC-'.$batch[0]);
        $head2 = array('', '', '', '支出凭证');
        $head3 = array('', '', '', date('Y年m月d日'));
        $head4 = array('序号', '结算单ID', 'ID', '姓名', '仓库', '收款人', '收款卡号', '开户行', '金额');
        Data_Csv::send($head1);
        Data_Csv::send($head2);
        Data_Csv::send($head3);
        Data_Csv::send($head4);

        $widList = Conf_Warehouse::$WAREHOUSES;
        $i = 1;
        $totalPrice = 0;
        foreach ($statementInfo as $statement) {
            $body = array($i,
                empty($statement['id'])?'':$statement['id'],
                empty($statement['cuid'])?'':$statement['cuid'],
                empty($coopWorkerInfo[$statement['cuid']]['name'])?'':$coopWorkerInfo[$statement['cuid']]['name'],
                empty($widList[$statement['wid']])?'':$widList[$statement['wid']],
                empty($coopWorkerInfo[$statement['cuid']]['real_name'])?'':$coopWorkerInfo[$statement['cuid']]['real_name'],
                empty($coopWorkerInfo[$statement['cuid']]['card_num'])?'':$coopWorkerInfo[$statement['cuid']]['card_num'] . "\t",
                empty($coopWorkerInfo[$statement['cuid']]['bank_info'])?'':$coopWorkerInfo[$statement['cuid']]['bank_info'],
                $statement['price'] / 100);
            Data_Csv::send($body);
            $i++;
            $totalPrice += $statement['price'];
        }
        $chineseTotal = Str_Chinese::getChineseNum($totalPrice / 100);
        $total = array('合计', '计人民币: ' . $chineseTotal , '(付款方式:暂无)', $totalPrice/100);
        Data_Csv::send($total);
    }

    protected function outputHead()
    {

    }

    protected function outputBody()
    {

    }

    protected function outputTail()
    {

    }
}

$app = new App('pri');
$app->run();

