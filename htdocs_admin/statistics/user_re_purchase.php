<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    const ALL = 'all', SELF = 'self', SALES = 'sales';
    private $userType;
    private $fromMonth;
    private $endMonth;
    private $headerList;
    private $dataList;
    private $action;

    protected function getPara()
    {
        $this->userType = Tool_Input::clean('r', 'user_type', TYPE_STR);
        $this->fromMonth = Tool_Input::clean('r', 'from_month', TYPE_STR);
        $this->endMonth = Tool_Input::clean('r', 'end_month', TYPE_STR);
        $this->action = Tool_Input::clean('r', 'action', TYPE_STR);
    }

    protected function checkPara()
    {
        if (empty($this->userType))
        {
            $this->userType = self::ALL;
        }
        if (empty($this->fromMonth))
        {
            $this->fromMonth = date("Y-m", strtotime('-6 month'));
            $this->endMonth = date("Y-m", strtotime('-1 day'));
        }
    }

    protected function main()
    {
        if ($this->fromMonth >= $this->endMonth)
            return;

        $scv = new Statistics_Common_View();
        $rawData = $scv->getRePuerchaseInfoBetween($this->fromMonth, $this->endMonth);

        $this->headerList = $this->_getHeaderList($this->fromMonth, $this->endMonth);

        $monthNum = count($this->headerList) - 1;
        $this->dataList = $this->_getDataList($rawData, $monthNum, $this->fromMonth, $this->endMonth);

        if ($this->action == 'download')
        {
            Statistics_Api::exportForWeb(Conf_Statics::TYPE_USER_RE_PURCHASE, $this->headerList, $this->dataList);
            exit;
        }
    }

    private function _getHeaderList($fromMonth, $endMonth)
    {
        $res = array('月');
        $seq = 0;
        for ($month = $fromMonth; $month <= $endMonth; $month = date('Y-m', strtotime($month) + 86400 * 32))
        {
            if ($seq == 0)
            {
                $res[] = '首单用户';
                $res[] = '首单用户本月复购率';
            }
            elseif ($seq == 1)
            {
                $res[] = '次月购买用户';
            }
            else
            {
                $res[] = sprintf('第%d月购买用户', $seq + 1);
            }
            $seq++;
        }

        return $res;
    }

    private function _getDataList($rawData, $monthNum, $fromMonth, $endMonth)
    {
        $res = array();
        for ($month = $fromMonth; $month <= $endMonth; $month = date('Y-m', strtotime($month) + 86400 * 32))
        {
            $oneMonthRes = array($month);

            //获取原始数据
            $dataOfThisMonth = $rawData[$month];
            switch ($this->userType)
            {
                case self::SALES:
                    $cids = explode(',', $dataOfThisMonth['sales_reg_cids']);
                    break;
                case self::SELF:
                    $cids = explode(',', $dataOfThisMonth['weixin_reg_cids']);
                    $cids = array_merge($cids, explode(',', $dataOfThisMonth['app_reg_cids']));
                    $cids = array_merge($cids, explode(',', $dataOfThisMonth['other_reg_cids']));
                    break;
                case self::ALL:
                default:
                    $cids = explode(',', $dataOfThisMonth['cids']);
                    break;
            }
            $cids = array_intersect($cids, explode(',', $dataOfThisMonth['new_cids']));
            $oneMonthRes[] = count($cids);

            //记录复购率
            $i = 0;
            $rePurchaseMonth = $month;
            while ($i < $monthNum)
            {
                if ($i == 0)
                {
                    $dataOfPurchaseMonth = $rawData[$rePurchaseMonth];
                    $cidsOfPurchaseMonth = explode(',', $dataOfPurchaseMonth['new_rebuy_cids']);
                    $rePurchaseCids = explode(',', $dataOfPurchaseMonth['cids']);
                    $ratio = count($cidsOfPurchaseMonth) / count($rePurchaseCids);
                    $oneMonthRes[] = sprintf("%d(%.2f%%)", count($cidsOfPurchaseMonth), $ratio * 100);
                }
                else
                {
                    if ($rePurchaseMonth <= $endMonth)
                    {
                        $dataOfPurchaseMonth = $rawData[$rePurchaseMonth];
                        $cidsOfPurchaseMonth = explode(',', $dataOfPurchaseMonth['cids']);
                        $rePurchaseCids = array_intersect($cids, $cidsOfPurchaseMonth);
                        $ratio = count($rePurchaseCids) / count($cids);
                        $oneMonthRes[] = sprintf("%d(%.2f%%)", count($rePurchaseCids), $ratio * 100);
                    }
                    else
                    {
                        $oneMonthRes[] = '-';
                    }
                }

                $rePurchaseMonth = date('Y-m', strtotime($rePurchaseMonth) + 86400 * 32);
                $i++;
            }

            $res[] = $oneMonthRes;
        }

        return $res;
    }

    protected function outputBody()
    {
        $monthList = array();
        for ($month = '2015-08'; $month <= date('Y-m', strtotime('-1 day')); $month = date('Y-m', strtotime($month) + 86400 * 32))
        {
            $monthList[] = $month;
        }
        $this->smarty->assign('header_list', $this->headerList);
        $this->smarty->assign('data_list', $this->dataList);
        $this->smarty->assign('month_list', $monthList);
        $this->smarty->assign('from_month', $this->fromMonth);
        $this->smarty->assign('end_month', $this->endMonth);
        $this->smarty->assign('user_type', $this->userType);

        $this->smarty->display('statistics/user_re_purchase.html');
    }
}

$app = new App('pri');
$app->run();
