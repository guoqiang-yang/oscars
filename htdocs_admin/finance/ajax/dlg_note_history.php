<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $list;
    private $cid;

    protected function getPara()
    {
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
    }

    protected function main()
    {
        //回访记录
        $res = Crm2_Api::getCustomerTrackingList(array(
                                                     'cid' => $this->cid,
                                                     'type' => 2
                                                 ), 0, 20);
        $this->list = $res['list'];
    }

    protected function outputPage()
    {
        $this->smarty->assign('list', $this->list);
        $html = $this->smarty->fetch('finance/dlg_note_history.html');
        $result = array('html' => $html);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App('pri');
$app->run();