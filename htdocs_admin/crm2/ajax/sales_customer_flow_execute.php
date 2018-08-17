<?php
include_once('../../../global.php');
/**
 * 处理销售客户转移
 * @author wangxuemin
 *
 */
class App extends App_Admin_Ajax
{
    /* 转出销售suid */
    private $sales_suid;
    /* 转入销售auid数组 */
    private $sales_flow_suid = array();
    /* 更新时间 */
    private $update_date;
    /* 处理的结果 */
    private $result = array();
    
    /**
     * (non-PHPdoc)
     * @see Base_App::getPara()
     */
    protected function getPara()
    {
        $this->sales_suid = Tool_Input::clean('r', 'sales_suid', TYPE_INT);
        $this->sales_flow_suid = Tool_Input::clean('r', 'sales_flow_suid', TYPE_ARRAY);
        $this->update_date = Tool_Input::clean('r', 'update_date', TYPE_ARRAY);
    }
    
    /**
     * (non-PHPdoc)
     * @see Base_App::main()
     */
    protected function main()
    {
        $cc = new Crm2_Sales_Customer_Flow();
        $result = $cc->executeUpdateCustomer($this->sales_suid, $this->sales_flow_suid, $this->update_date);
        $this->result['flow_num'] = $result['flow_num'];
        if ($result['flow_num'] > 0) {
            $this->result['code'] = 0;
        } else {
            $this->result['code'] = 1;
        }
    }
    
    /**
     * (non-PHPdoc)
     * @see App_Admin_Page::checkAuth()
     */
    protected function checkAuth()
    {
        parent::checkAuth('/crm2/ajax/sales_customer_flow_execute');
    }
    
    /**
     * (non-PHPdoc)
     * @see Base_App::outputBody()
     */
    protected function outputBody()
    {
        $response = new Response_Ajax();
        $response->setContent($this->result);
        $response->send();
        exit;
    }
}
$app = new App('pri');
$app->run();