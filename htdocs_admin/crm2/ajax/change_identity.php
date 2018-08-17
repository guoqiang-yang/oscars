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
    private $cid;
    private $reason;
    private $method;
    private $ajResponse;

    protected function checkAuth()
    {
        parent::checkAuth('/crm2/edit_customer');
    }

    protected function getPara()
    {
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
        $this->reason = Tool_Input::clean('r', 'reason', TYPE_STR);
        $this->method = Tool_Input::clean('r', 'method', TYPE_STR);

        $this->ajResponse = array(
            'st' => 0,
            'msg' => '',
            'data' => array(),
        );
    }

    protected function checkPara()
    {
        if (empty($this->cid) || empty($this->method) || !in_array($this->method, array('pass', 'fail')))
        {
            $this->ajResponse['st'] = 100;
            $this->ajResponse['msg'] = '参数错误，请检查！';

            return;
        }

        // 获取客户信息
        $check_identity = Crm2_Certification_Api::getUndealItem($this->_uid, $this->cid);
        if(empty($check_identity))
        {
            $this->ajResponse['st'] = 101;
            $this->ajResponse['msg'] = '你没有权限审核！';
            return;
        }
    }

    protected function main()
    {
        try
        {
            switch ($this->method)
            {
                case 'pass':
                    try{
                        Crm2_Certification_Api::salerPass($this->_uid, $this->cid);
                    }catch (Exception $e)
                    {
                        $this->ajResponse['st'] = 102;
                        $this->ajResponse['msg'] = $e->getMessage();
                    }
                    break;
                case 'fail':
                    Crm2_Certification_Api::salerDeny($this->_uid, $this->cid, $this->reason);
                    break;
            }
        }
        catch (Exception $ex)
        {
            $this->ajResponse['st'] = 110;
            $this->ajResponse['msg'] = "code:" . $ex->getCode() . "\nerror:" . $ex->getMessage() . "\n" . var_export($ex->getTrace(), TRUE);
        }

        $this->ajResponse['data']['cid'] = $this->cid;
    }

    protected function outputBody()
    {
        $response = new Response_Ajax();
        $response->setContent($this->ajResponse);
        $response->send();

        exit;
    }
}

$app = new App('pri');
$app->run();