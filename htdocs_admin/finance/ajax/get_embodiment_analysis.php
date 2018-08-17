<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $cid;
    private $presentTime;

    protected function getPara()
    {
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);

    }

    protected function checkPara()
    {
        if (empty($this->cid))
        {
            throw new Exception('参数错误，请查询！');
        }
    }

    protected function checkAuth()
    {
        parent::checkAuth('/finance/customer_amount_list');
    }

    protected function main()
    {
        $FinanceCustomerAmount = new Finance_Customer_Amount();
        $financeCustomerOne = $FinanceCustomerAmount->getRecentOfUser($this->cid);

        //判断当前余额是否为0
        if ($financeCustomerOne['amount'] > 0)
        {
            $this->balance = $financeCustomerOne['amount'];
            $getRecentOfBalance0 = $FinanceCustomerAmount->getRecentOfBalance0($this->cid);

            //找到最近一条余额为0的数据进行分析
            if($getRecentOfBalance0)
            {
                $this->presentTime = $getRecentOfBalance0['ctime'];
                $etime = date('Y-m-d H:i:s');
                $search = array(
                    'cid'=>$this->cid,
                    'btime'=>$this->presentTime,
                    'etime'=>$etime,
                );
            }

            //余额没有为0的数据,进行所有数据查询
            else
            {
                $search = array(
                    'cid'=>$this->cid,
                );

                $financeCustomerLastOne = $FinanceCustomerAmount->getList($search,0,1,'order by ctime asc');
                $this->presentTime = $financeCustomerLastOne['data']['ctime'];
            }

            $analysisWhere = $this->getEmbodimentAnalysis($search).' group by type';

            $this->embodyAnalysisData = $FinanceCustomerAmount->openGet($analysisWhere,$field = array('type','sum(price) money'));

            foreach ($this->embodyAnalysisData['data'] as $key=>$value)
            {
                $this->embodyAnalysisData['data'][$key]['type'] = Conf_Finance::$Crm_AMOUNT_TYPE_DESCS[$value['type']];
            }
        }

        //余额为0的情况显示
        else
        {
            $this->embodyAnalysisData['data'] = '您的余额为0 不可提现';
        }
    }

    protected function getEmbodimentAnalysis($search)
    {
        $where = 'status=' . Conf_Base::STATUS_NORMAL;

        if ($search['cid'])
        {
            $where .= ' and cid=' . $search['cid'];
        }

        if ($search['btime'])
        {
            $where .= ' and date(ctime) > date("'.$search['btime'].' ")';
        }
        if ($search['etime'])
        {
            $where .= ' and date(ctime) <= date("'.$search['etime'].' ")';
        }

        return $where;
    }


    protected function outputBody()
    {
        $this->smarty->assign('EmbodyAnalysisData', $this->embodyAnalysisData['data']);
        $this->smarty->assign('balance', $this->balance);
        $this->smarty->assign('presentTime', $this->presentTime);

        $html = $this->smarty->fetch('finance/get_embodiment_analysis.html');
        $result = array('html' =>$html );

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App();
$app->run();