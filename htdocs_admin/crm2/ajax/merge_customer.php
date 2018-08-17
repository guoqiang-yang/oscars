<?php

/**
 * 合并客户.
 *
 * 数据表：
 *   - t_customer, t_user
 *   - t_coupon, t_coupon_apply, t_sms_queue,t_construction_site, t_order, t_refund
 *   - t_money_in_history, t_customer_amount_history
 *   - t_cashback
 *   - t_cpoint_history, t_cpoint_order, t_cpoint_order_product
 */

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $masterMobile;
    private $slaveMobile1;
    private $masterCid;
    private $slaveCid1;
    private $masterSalesSuid;
    private $masterCustomer;
    private $slaveCustomer1;
    private $ajResponse;

    protected function checkAuth()
    {
        parent::checkAuth('/crm2/merge_customer');
    }

    protected function getPara()
    {
        $this->masterMobile = Tool_Input::clean('r', 'master_mobile', TYPE_STR);
        $this->slaveMobile1 = Tool_Input::clean('r', 'slave_mobile1', TYPE_STR);
        $this->masterCid = Tool_Input::clean('r', 'master_cid', TYPE_UINT);
        $this->slaveCid1 = Tool_Input::clean('r', 'slave_cid1', TYPE_UINT);
        $this->masterSalesSuid = Tool_Input::clean('r', 'master_sales_suid', TYPE_UINT);

        $this->ajResponse = array(
            'st' => 0,
            'msg' => '',
            'data' => array(),
        );
    }

    protected function checkPara()
    {
        if (empty($this->masterCid) || empty($this->slaveCid1) || empty($this->masterSalesSuid))
        {
            $this->ajResponse['st'] = 100;
            $this->ajResponse['msg'] = '参数错误，请检查！';

            return;
        }

        // 获取客户信息
        $this->masterCustomer = Crm2_Api::getCustomerInfo($this->masterCid, TRUE, FALSE);
        $this->slaveCustomer1 = Crm2_Api::getCustomerInfo($this->slaveCid1, TRUE, FALSE);

        if (strpos($this->masterCustomer['customer']['all_user_mobiles'], $this->masterMobile) === FALSE || strpos($this->slaveCustomer1['customer']['all_user_mobiles'], $this->slaveMobile1) === FALSE || $this->masterCustomer['customer']['sales_suid'] != $this->masterSalesSuid)
        {
            $this->ajResponse['st'] = 101;
            $this->ajResponse['msg'] = '数据异常，请查询！';

            return;
        }
    }

    protected function main()
    {
        if ($this->ajResponse['st'] != 0)
        {
            return;
        }

        try
        {
            Crm2_Api::mergeCustomers($this->masterCustomer, $this->slaveCustomer1, $this->_user);
        }
        catch (Exception $ex)
        {
            $this->ajResponse['st'] = 110;
            $this->ajResponse['msg'] = "code:" . $ex->getCode() . "\nerror:" . $ex->getMessage() . "\n" . var_export($ex->getTrace(), TRUE);
        }

        $this->ajResponse['data']['cid'] = $this->masterCustomer['customer']['cid'];
    }

    protected function outputBody()
    {
        $response = new Response_Ajax();
        $response->setContent($this->ajResponse);
        $response->send();

        exit;
    }
}

$app = new App();
$app->run();