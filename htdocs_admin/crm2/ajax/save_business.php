<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $bid;
    private $businessInfo;

    protected function checkAuth()
    {
        parent::checkAuth('/crm2/edit_business');
    }

    protected function getPara()
    {
        $this->bid = Tool_Input::clean('r', 'bid', TYPE_UINT);

        $this->businessInfo = array(
            'bname' => Tool_Input::clean('r', 'bname', TYPE_STR),
            'cname' => Tool_Input::clean('r', 'cname', TYPE_STR),
            'mobile' => Tool_Input::clean('r', 'mobile', TYPE_STR),
            'city' => Tool_Input::clean('r', 'city', TYPE_UINT),
            'district' => Tool_Input::clean('r', 'district', TYPE_UINT),
            'area' => Tool_Input::clean('r', 'area', TYPE_UINT),
            'address' => Tool_Input::clean('r', 'address', TYPE_STR),
            'products' => Tool_Input::clean('r', 'products', TYPE_STR),
            'note' => Tool_Input::clean('r', 'note', TYPE_STR),
        );

        $this->businessInfo['record_suid'] = Tool_Input::clean('r', 'record_suid', TYPE_UINT);
        $this->businessInfo['sales_suid'] = Tool_Input::clean('r', 'sales_suid', TYPE_UINT);
        $this->businessInfo['is_pay'] = Tool_Input::clean('r', 'is_pay', TYPE_UINT);
    }

    protected function checkPara()
    {
        if (empty($this->businessInfo['bname']) || empty($this->businessInfo['mobile']))
        {
            throw new Exception('common:params error');
        }

        if (!Str_Check::checkMobile($this->businessInfo['mobile']))
        {
            throw new Exception('common:mobile format error');
        }
    }

    protected function main()
    {
        $this->bid = Business_Api::saveBusinessInfo($this->bid, $this->businessInfo);
    }

    protected function outputBody()
    {
        $res = array('bid' => $this->bid);

        $response = new Response_Ajax();
        $response->setContent($res);
        $response->send();

        exit;
    }
}

$app = new App();
$app->run();