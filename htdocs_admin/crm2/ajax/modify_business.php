<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $allOpTypes = array(
            'search_customers',
            'bind_customers',
            'unbind_customer',
            'reset_df_passwd',
        );
    private $otype;
    private $bid;
    private $searchCustomers;
    private $bindCids;
    private $unbindCid;
    private $response;

    protected function checkAuth()
    {
        $otype = isset($_REQUEST['otype'])? $_REQUEST['otype']: '';
        
        if ($otype == 'reset_df_passwd')
        {
            parent::checkAuth('hc_reset_business_passwd');
        }
        else
        {
            parent::checkAuth('/crm2/ajax/modify_business');
        }
    }

    protected function getPara()
    {
        $this->otype = Tool_Input::clean('r', 'otype', TYPE_STR);
        $this->bid = Tool_Input::clean('r', 'bid', TYPE_UINT);

        $this->searchCustomers = Tool_Input::clean('r', 'search_customers', TYPE_STR);
        $this->bindCids = Tool_Input::clean('r', 'bind_cids', TYPE_STR);
        $this->unbindCid = Tool_Input::clean('r', 'unbind_cid', TYPE_UINT);

        $this->response = array(
            'errno' => 0,
            'data' => array()
        );
    }

    protected function checkPara()
    {
        if (empty($this->bid))
        {
            throw new Exception('common:params error');
        }

        if (!in_array($this->otype, $this->allOpTypes))
        {
            throw new Exception('服务不存在！');
        }
    }

    protected function main()
    {
        switch ($this->otype)
        {
            case 'search_customers':
                $this->_searchCustomers();
                break;
            case 'bind_customers':
                $this->_bindCustomer2Business();
                break;
            case 'unbind_customer':
                $this->_unbindCustomer2Business();
                break;
            case 'reset_df_passwd':
                $this->_resetDfBusinessPasswd();
                break;

            default:
                break;
        }
    }

    protected function outputBody()
    {
        $response = new Response_Ajax();
        $response->setContent($this->response);
        $response->send();

        exit;
    }

    private function _searchCustomers()
    {
        if (empty($this->searchCustomers))
        {
            throw new Exception('搜索客户为空!');
        }

        $customers = Crm2_Api::searchCustomerWithCidsOrMobiles($this->searchCustomers);

        foreach ($customers as $key => &$item)
        {
            if ($item['status'] == Conf_Base::STATUS_DELETED)
            {
                unset($customers[$key]);
            }
        }

        $this->smarty->assign('customers', $customers);
        $html = $this->smarty->fetch('crm2/block/aj_search_customer.html');

        $this->response['data']['html'] = $html;
    }

    private function _bindCustomer2Business()
    {
        $cids = explode(',', $this->bindCids);
        if (empty($cids))
        {
            throw new Exception('绑定失败：客户列表为空！');
        }

        Business_Api::bindCustomers2Business($this->bid, $cids);
        Business_Api::bindBusiness($this->bid, $cids);

        $this->response['data']['ret'] = 1;
    }

    private function _unbindCustomer2Business()
    {
        if (empty($this->unbindCid))
        {
            throw new Exception('解绑失败：客户ID为空！');
        }

        Business_Api::unbindCustomer2Business($this->unbindCid);
        Business_Api::unBindBusiness($this->bid, $this->unbindCid);
        $this->response['data']['ret'] = 1;
    }

    private function _resetDfBusinessPasswd()
    {
        Business_Auth_Api::resetDefaultPasswd($this->bid);

        $this->response['data']['ret'] = 1;
    }
}

$app = new App();
$app->run();