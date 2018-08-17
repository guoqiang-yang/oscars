<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $list;
    private $cid;

    protected function checkAuth()
    {
        parent::checkAuth('/crm2/edit_customer_tracking');
    }

    protected function getPara()
    {
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
    }

    protected function main()
    {
        //回访记录
        $res = Crm2_Api::getCustomerTrackingList(array('cid' => $this->cid), 0, 20);

        $this->list = $res['data'];
    }

    protected function outputPage()
    {
        $this->smarty->assign('list', $this->list);
        $html = $this->smarty->fetch('crm2/dlg_note_history.html');
        $result = array('html' => $html);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App('pri');
$app->run();