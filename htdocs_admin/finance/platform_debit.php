<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $cid;
    private $oids;
    private $orderInfos;

    protected function getPara()
    {
        $this->cid = 1001; //抢工长，现在只有一个，写死

        $oids = Tool_Input::clean('r', 'oids', TYPE_STR);
        $oids = preg_replace('#\s+#', '', $oids);

        $this->oids = str_replace('，', ',', $oids);
    }

    protected function checkPara()
    {
    }

    protected function main()
    {
        
        return;
        
        $oids = explode(',', $this->oids);

        $this->orderInfos = Finance_Api::getPlatformDebitInfo($this->cid, $oids);

        $this->addFootJs('js/apps/finance.js');
    }

    protected function outputBody()
    {
        $this->smarty->assign('cid', $this->cid);
        $this->smarty->assign('oids', $this->oids);
        $this->smarty->assign('cid_conf', Conf_Finance::$Platform_Debits[$this->cid]);
        $this->smarty->assign('order_info', $this->orderInfos);

        $this->smarty->display('finance/platform_debit.html');
    }
}

$app = new App();
$app->run();