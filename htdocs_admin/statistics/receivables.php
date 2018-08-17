<?php
/**
 * Created by PhpStorm.
 *
 * 应收金额统计
 *
 * User: zouliangwei
 * Date: 16/10/28
 * Time: 下午1:12
 */

include_once('../../global.php');

class App extends App_Admin_Page
{
    const DATE_MODE = 'date', MONTH_MODE = 'month';
    private $start;
    private $data;
    private $startDate;
    private $endDate;
    private $viewMode;
    private $num = 20;
    private $total;
    private $sumData;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->startDate = Tool_Input::clean('r', 'start_date', TYPE_STR);
        $this->endDate = Tool_Input::clean('r', 'end_date', TYPE_STR);
        $this->viewMode = Tool_Input::clean('r', 'view_mode', TYPE_STR);
    }

    protected function checkPara()
    {
        if (empty($this->viewMode))
        {
            $this->viewMode = self::MONTH_MODE;
        }
        if (empty($this->startDate))
        {
            $this->startDate = date('Y-m-d', strtotime('-30 days'));
        }
        if (empty($this->endDate))
        {
            $this->endDate = date('Y-m-d', time());
        }
    }

    protected function main()
    {
        $this->addHeadJs(array(
                             'js/jquery.min.js',
                         ));

        $this->addFootJs(array());
        $this->addCss(array());
        if ($this->viewMode == self::MONTH_MODE)
        {
            $this->data = Statistics_Api::getReceivablesOfAllMonth();
            $this->sumData = Statistics_Api::getSumRecivablesByTime(FALSE, FALSE);
        }
        else
        {
            $res = Statistics_Api::getReceivablesOfListDay($this->start, $this->num, $this->startDate, $this->endDate);
            $this->data = $res['list'];
            $this->total = $res['total'];
            $this->sumData = Statistics_Api::getSumRecivablesByTime($this->startDate, $this->endDate);
        }
    }

    protected function outputBody()
    {
        $app = '/statistics/receivables.php?' . http_build_query(array(
                                                                     'start_date' => $this->startDate,
                                                                     'end_date' => $this->endDate,
                                                                     'view_mode' => $this->viewMode
                                                                 ));
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('data_list', $this->data);
        $this->smarty->assign('start_date', $this->startDate);
        $this->smarty->assign('end_date', $this->endDate);
        $this->smarty->assign('view_mode', $this->viewMode);
        $this->smarty->assign('sum_data', $this->sumData);
        $this->smarty->display('statistics/receivable.html');
    }
}

$app = new App('pri');
$app->run();
