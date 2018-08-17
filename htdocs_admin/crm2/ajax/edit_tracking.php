<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $dueDate;
    private $cid;
    private $note;
    private $tid;
    private $needTracking;

    protected function checkAuth()
    {
        parent::checkAuth('/crm2/edit_customer_tracking');
    }

    protected function getPara()
    {
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
        $this->dueDate = Tool_Input::clean('r', 'due_date', TYPE_STR);
        $this->note = Tool_Input::clean('r', 'note', TYPE_STR);
        $this->tid = Tool_Input::clean('r', 'tid', TYPE_UINT);
        $this->needTracking = Tool_Input::clean('r', 'need_tracking', TYPE_UINT);
    }

    protected function main()
    {
        if ($this->needTracking == 0)
        {
            $this->dueDate = '1999-09-09';
        }

        Crm2_Api::updateCustomerInfo($this->cid, array('visit_due_date' => $this->dueDate));

        if (!empty($this->note))
        {
            Crm2_Api::saveCustomerTracking($this->tid, array(
                'cid' => $this->cid,
                'type' => 1,
                'edit_suid' => $this->_uid,
                'content' => $this->note
            ));
        }
    }

    protected function outputPage()
    {
        $result = array('res' => 'succ');

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App('pri');
$app->run();