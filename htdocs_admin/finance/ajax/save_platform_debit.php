<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $cid;
    private $oids;

    protected function checkAuth()
    {
        parent::checkAuth('/finance/platform_debit');
    }

    protected function getPara()
    {
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);

        $oidInfos = json_decode(Tool_Input::clean('r', 'oids', TYPE_STR), TRUE);

        foreach ($oidInfos as $oidInfo)
        {
            $infos = explode(':', $oidInfo);
            if (count($infos) != 3)
            {
                throw new Exception('订单数据异常！');
            }

            if ($infos[1] != 0)
            {
                $this->oids[$infos[0]] = array(
                    'oid' => $infos[0],
                    'paid' => $infos[1] * 100,
                    'moling' => $infos[2] * 100,
                );
            }
        }
    }

    protected function checkPara()
    {
        if (empty($this->cid) || empty($this->oids))
        {
            throw new Exception('common:params error');
        }
    }

    protected function main()
    {
        Finance_Api::paidPlatformDebit($this->cid, $this->oids, $this->_uid);
    }

    protected function outputBody()
    {
        $result = array('st' => 1);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App();
$app->run();