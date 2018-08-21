<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $cid;
    private $backUrl;
    private $customer;
    private $user;
    private $ajResponse;

    protected function checkAuth($permission='')
    {
        parent::checkAuth('/crm2/edit_customer');
    }

    protected function getPara()
    {
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
        $this->backUrl = Tool_Input::clean('r', 'url', TYPE_STR);
        $this->customer = array(
            'name' => Tool_Input::clean('r', 'name', TYPE_STR),
            'identity' => Tool_Input::clean('r', 'identity', TYPE_UINT),
            'source' => Tool_Input::clean('r', 'source', TYPE_UINT),
            'city_id' => Tool_Input::clean('r', 'city_id', TYPE_UINT),
            'note' => Tool_Input::clean('r', 'note', TYPE_STR),
            'sales_suid' => Tool_Input::clean('r', 'sales_suid', TYPE_UINT),
            'level_for_sys' => Tool_Input::clean('r', 'level_for_sys', TYPE_UINT),
            'tax_point' => Tool_Input::clean('r', 'tax_point', TYPE_UINT),
        );
        
        $this->user = array(
            'name' => Tool_Input::clean('r', 'user_name', TYPE_STR),
            'mobile' => Tool_Input::clean('r', 'identity_mobile', TYPE_STR),
            'hometown' => Tool_Input::clean('r', 'hometown', TYPE_STR),
        );

        $this->ajResponse = array(
            'st' => 0,
            'msg' => '',
        );
    }

    protected function checkPara()
    {
        if (empty($this->customer['name']))
        {
            $this->ajResponse['st'] = 10;
            $this->ajResponse['msg'] = '客户名称不能为空！';

            return;
        }

        if (empty($this->customer['identity']))
        {
            $this->ajResponse['st'] = 11;
            $this->ajResponse['msg'] = '请选择客户类型！';
        }

        if (empty($this->customer['source']))
        {
            $this->ajResponse['st'] = 13;
            $this->ajResponse['msg'] = '请选择客户来源！';

            return;
        }

        // 新注册用户
        if (empty($this->cid) && empty($this->user['mobile']))
        {
            $this->ajResponse['st'] = 14;
            $this->ajResponse['msg'] = '电话不能为空！';

            return;
        }
    }

    protected function main()
    {
        if ($this->ajResponse['st'] != 0)
        {
            return;
        }

        if (empty($this->cid))  //注册
        {
            $this->customer['sale_status'] = !empty($this->customer['sales_suid']) ? Conf_User::CRM_SALE_ST_PRIVATE : Conf_User::CRM_SALE_ST_PUBLIC;
            $this->customer['record_suid'] = $this->_uid;

            try
            {
                $ret = Crm2_Auth_Api::register($this->customer, $this->user);
            }
            catch (Exception $e)
            {
                $errmsg = $e->getMessage();

                if (strpos($errmsg, 'mobile occupied') !== FALSE)
                {
                    $this->ajResponse['st'] = 21;
                    $this->ajResponse['msg'] = '手机号已经注册！';
                }
                else if ($errmsg == 'common:mobile format error')
                {
                    $this->ajResponse['st'] = 23;
                    $this->ajResponse['msg'] = '手机号格式不正确，请检查';
                }
                else
                {
                    $this->ajResponse['st'] = 22;
                    $this->ajResponse['msg'] = '注册失败！'.$e->getMessage();
                }
                return;
            }

            $this->cid = $ret['cid'];
            
            // 重新backUrl
            if (empty($this->backUrl))
            {
                $this->backUrl = '/crm2/edit_customer.php?cid=' . $this->cid;
            }
        }
        else    // 更新
        {
            $customerInfo = Crm2_Api::getCustomerInfo($this->cid, FALSE, FALSE);
            $isMyCustomer = Crm2_Api::isMyCustomer($customerInfo['customer'], $this->_user);

            if (!$isMyCustomer['can_edit'])
            {
                $this->ajResponse['st'] = 20;
                $this->ajResponse['msg'] = '客户不属于你，不能修改用户信息';

                return;
            }

            if (isset($this->customer['sales_suid']) && $customerInfo['customer']['sales_suid'] != $this->customer['sales_suid'])
            {
                $this->customer['sale_status'] = Conf_User::CRM_SALE_ST_PRIVATE;
                $this->customer['chg_sstatus_time'] = date('Y-m-d H:i:s');
            }
            
            $ret = Crm2_Api::updateCustomerInfo($this->cid, $this->customer);
        }
    }

    protected function outputPage()
    {
        $this->ajResponse['cid'] = $this->cid;
        $this->ajResponse['url'] = $this->backUrl;

        $response = new Response_Ajax();
        $response->setContent($this->ajResponse);
        $response->send();
        exit;
    }
}

$app = new App('pri');
$app->run();

