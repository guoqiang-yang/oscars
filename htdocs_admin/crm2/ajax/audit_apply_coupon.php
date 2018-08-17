<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $id;
    private $status;

    protected function checkAuth()
    {
        parent::checkAuth('/crm2/apply_coupon_list');
    }

    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->status = Tool_Input::clean('r', 'status', TYPE_UINT);
    }

    protected function main()
    {
        if ($this->status == Conf_Coupon::ST_PASS)
        {
            Coupon_Api::agreeApply($this->id, $this->_uid);
        }
        else
        {
            Coupon_Api::denyApply($this->id, $this->_uid);
        }
    }

    protected function outputBody()
    {
        $result = array('id' => $this->id);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App();
$app->run();