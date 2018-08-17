<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $tid;
    private $cid;
    private $content;
    private $submit;
    private $dueDate;
    private $needTracking;
    private $tracking;
    private $customerInfo;
    private $today;

    protected function getPara()
    {
        $this->tid = Tool_Input::clean('r', 'tid', TYPE_UINT);
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
        $this->content = Tool_Input::clean('r', 'content', TYPE_STR);
        $this->submit = Tool_Input::clean('r', 'submit', TYPE_STR);
        $this->dueDate = Tool_Input::clean('r', 'due_date', TYPE_STR);
        $this->needTracking = Tool_Input::clean('r', 'need_tracking', TYPE_UINT);
    }

    protected function main()
    {
        if (!empty($this->submit))
        {
            if ($this->needTracking == 0)
            {
                $this->dueDate = '1999-09-09';
            }

            if (!empty($this->dueDate))
            {
                Crm2_Api::updateCustomerInfo($this->cid, array('visit_due_date' => $this->dueDate));
            }

            if (!empty($this->content))
            {
                $info = array(
                    'cid' => $this->cid,
                    'content' => $this->content,
                    'edit_suid' => $this->_uid,
                );

                Crm2_Api::saveCustomerTracking($this->tid, $info);
            }

            header('Location: /crm2/customer_tracking_list.php');
            exit;
        }

        if ($this->tid > 0)
        {
            $this->tracking = Crm2_Api::getTrackingInfo($this->tid);
        }
        else
        {
            $this->customerInfo = Crm2_Api::getCustomerInfo($this->cid, FALSE, FALSE);

            $this->tracking = array();
        }

        $this->today = date('Y-m-d');
        $this->dueDate = date('Y-m-d', strtotime('today') + 7 * 86400);

        $this->addFootJs(array('js/apps/tracking.js'));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $this->smarty->assign('tracking', $this->tracking);
        $this->smarty->assign('customer_info', $this->customerInfo);
        $this->smarty->assign('cid', $this->cid);
        $this->smarty->assign('tid', $this->tid);
        $this->smarty->assign('today', $this->today);
        $this->smarty->assign('visit_due_date', $this->dueDate);

        $this->smarty->display('crm2/edit_customer_tracking.html');
    }
}

$app = new App('pri');
$app->run();
