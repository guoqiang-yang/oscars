<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $customer;
    private $user;
    private $cid;
    private $uid;

    protected function checkAuth()
    {
        parent::checkAuth('/crm2/new_customer');
    }

    protected function getPara()
    {
        $this->customer = array(
            'name' => 'HC_工长',
            'status' => Conf_Base::STATUS_NORMAL,
            'member_date' => date('Y-m-d'),
            'source' => 99,
            'is_auto_save' => 1,
        );

        $this->user = array(
            'name' => 'HC_工长',
            'mobile' => Tool_Input::clean('r', 'mobile', TYPE_STR),
        );
    }

    protected function checkPara()
    {

        if (empty($this->user['mobile']))
        {
            throw new Exception('customer:contact mobile');
        }
    }

    protected function main()
    {
        // 配置客户的来源，销售专员, 销售状态等信息
        $this->customer['sales_suid'] = 0;
        $this->customer['record_suid'] = $this->_uid;
        $this->customer['sale_status'] = Conf_User::CRM_SALE_ST_INNER;
        $this->customer['reg_source'] = Conf_User::CUSTOMER_REG_CS;

        $ret = Crm2_Auth_Api::register($this->customer, $this->user);
        $this->cid = $ret['cid'];
        $this->uid = $ret['uid'];

        if ($ret['cid'] != 0)
        {
            $params = array(
                'id' => $ret['cid'],
                'name' => $this->customer['name']
            );
            Admin_Api::addActionLog($this->_uid, Conf_Admin_Log::$ACTION_ADD_CUSTOMER, $params);
        }
    }

    protected function outputPage()
    {
        $result = array(
            'cid' => $this->cid,
            'uid' => $this->uid
        );

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App('pri');
$app->run();

