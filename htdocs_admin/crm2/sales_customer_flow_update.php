<?php
include_once('../../global.php');
/**
 * title 销售客户转接更新
 * @author wangxuemin
 */
class App extends App_Admin_Page
{
    /* 转出销售suid */
    private $sales_suid;
    /* 转入销售auid数组 */
    private $sales_flow_suid = array();
    /* 客户转出后所处理的结果 */
    private $result = array();

    /**
     * (non-PHPdoc)
     * @see Base_App::getPara()
     */
    protected function getPara()
    {
        $this->sales_suid = Tool_Input::clean('r', 'sales_suid', TYPE_INT);
        $this->sales_flow_suid = Tool_Input::clean('r', 'sales_flow_suid', TYPE_ARRAY);
    }
    
    /**
     * (non-PHPdoc)
     * @see Base_App::main()
     */
    protected function main()
    {
        $cc = new Crm2_Sales_Customer_Flow();
        $this->result = $cc->getUpdateCustomer($this->sales_suid, $this->sales_flow_suid);
    }
    
    /**
     * (non-PHPdoc)
     * @see App_Admin_Page::checkAuth()
     */
    protected function checkAuth()
    {
        parent::checkAuth('/crm2/sales_customer_flow_update');
    }

    /**
     * (non-PHPdoc)
     * @see Base_App::outputBody()
     */
    protected function outputBody()
    {
        /* 转出销售 */
        $this->smarty->assign('sales_suid', $this->sales_suid);
        /* 转入销售 */
        $this->smarty->assign('sales_flow_suid', $this->sales_flow_suid);
        /* 输出处理结果 */
        $this->smarty->assign('result', $this->result);
        /* 模板输出 */
        $this->smarty->display('crm2/sales_customer_flow_update.html');
    }
}
$app = new App('pri');
$app->run();