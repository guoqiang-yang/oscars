<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
 
    private $num = 20;
    private $start;
    private $total;
    
    private $cid;
    private $afterSaleId;
    private $orderId;
    private $searchConf;
    private $salesSuid;
    private $sale_list;
    private $otherCustomer = array();
    
    private $errmsg = '';

    private $userList = array();

    protected function getPara()
    {
        $this->searchConf = array(
//            'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
            'mobile' => Tool_Input::clean('r', 'mobile', TYPE_STR),
//            'name' => Tool_Input::clean('r', 'name', TYPE_STR),
        );
        
        $this->salesSuid = Tool_Input::clean('r', 'sales_suid', TYPE_UINT);

        $this->start = Tool_Input::clean('r', 'start', TYPE_INT);
        
        if (!empty($this->searchConf['mobile']) && !Str_Check::checkMobile($this->searchConf['mobile']))
        {
            $this->searchConf['mobile'] = '';
        }
        
        // 按照cid查询，并检查cid来源的有效性
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
        $this->afterSaleId = Tool_Input::clean('r', 'asid', TYPE_UINT);
        $this->orderId = Tool_Input::clean('r', 'oid', TYPE_UINT);
        if (!empty($this->cid) && !empty($this->afterSaleId))
        {
            $aa = new Aftersale_Func();
            $info = $aa->get($this->afterSaleId);
            
            if ($info['fb_uid'] == $this->cid)
            {
                $this->searchConf['cid'] = $this->cid;
            }
        }
        else if (!empty($this->cid) && !empty($this->orderId))
        {
            $oo = new Order_Order();
            $info = $oo->get($this->orderId);
            
            if ($info['cid'] == $this->cid)
            {
                $this->searchConf['cid'] = $this->cid;
            }
        }
        
        // 按名称搜素
        $name = Tool_Input::clean('r', 'name', TYPE_STR);
        
        if (ENV == 'online')
        {
            $this->searchConf['name'] = (mb_strlen($name)<2||strpos($name, '工长')!==false||strpos($name, 'HC')!==false)? '': $name;
        }
        else
        {
            $this->searchConf['name'] = $name;
        }  
        
        // 来自客户列表页
        if (!empty($this->cid) && strpos($_SERVER['HTTP_REFERER'], 'crm2/customer_list')!==false)
        {
            $this->searchConf['cid'] = $this->cid;
        }
    }
    
    
    protected function main()
    {
        $sales = Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_SALES_NEW, 0);
        $this->sale_list = Tool_Array::list2Map($sales, 'suid', 'name');

        if (!Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_CS_NEW) && Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_SALES_NEW))
        {
            if (!empty($this->salesSuid) && !in_array($this->salesSuid, $this->_user['team_member']))
            {
                $this->errmsg = '非当前销售客户！请查询！';
                return;
            }
            
            if (empty($this->salesSuid))
            {
                $this->searchConf['sales_suid'] = $this->_user['team_member'];
            }
            else
            { 
                $this->searchConf['sales_suid'] = array($this->salesSuid);
            }
        }

        if (!empty($this->searchConf['mobile'])||!empty($this->searchConf['cid'])||!empty($this->searchConf['name']))
        {
            $this->userList = Crm2_Api::getUserList($this->searchConf, $this->start, $this->num);
            
            if (empty($this->userList['data']) && !empty($this->searchConf['sales_suid']))
            {
                unset($this->searchConf['sales_suid']);
                
                $this->otherCustomer = Crm2_Api::getUserList($this->searchConf, $this->start, $this->num);
                foreach($this->otherCustomer['data'] as &$item)
                {
                    $item['_sales'] = !empty($this->sale_list[$item['_customer']['sales_suid']])?
                                $this->sale_list[$item['_customer']['sales_suid']]: '暂无销售';
                }
                
            }
        }
        else
        {
            $this->searchConf['mobile'] = Tool_Input::clean('r', 'mobile');
            $this->searchConf['name'] = Tool_Input::clean('r', 'name');
            
            if (!empty($this->searchConf['mobile']))
            {
                $this->errmsg = '请输入正确的手机号！';
            }
            else if (!empty ($this->searchConf['name']))
            {
                $this->errmsg = '请输入工长的合法姓名：至少两个字，搜索名字不能包含 工长！';
            }
            
            $this->userList = array('total' => 0, 'data' => array());
        }
        
        $this->total = $this->userList['total'];
        $this->searchConf['sales_suid'] = $this->salesSuid;
		$this->addFootJs(array('js/apps/crm2.js'));
		$this->addCss(array());
    }
    
    
    protected function outputBody()
    {
        $app = '/order/customer_list_cs.php?'. http_build_query($this->searchConf);
		$pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

		$this->smarty->assign('pageHtml', $pageHtml);

        $this->smarty->assign('sale_list', $this->sale_list);
        $this->smarty->assign('memberList', $this->_user['team_member']);

		$this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('salesSuid', empty($this->salesSuid)?'0':$this->salesSuid);
        $this->smarty->assign('user_list', $this->userList['data']);
        $this->smarty->assign('other_customer', $this->otherCustomer);
        $this->smarty->assign('crm_sale_status', Conf_User::$Customer_Sale_Status);
		$this->smarty->assign('total', $this->total);
        $this->smarty->assign('after_sale_place_order', Conf_Aftersale::$AFTER_SALE_PLACE_ORDER);
        $this->smarty->assign('current_city', City_Api::getCity());
        $this->smarty->assign('errmsg', $this->errmsg);
        $this->smarty->assign('sys_levels', Conf_User::$Customer_Sys_Level_Descs);
        $this->smarty->assign('customer_levels', Conf_User::$Crm_Level_BySaler);

		$this->smarty->display('order/customer_list_cs.html');
    }
    
}

$app = new App();
$app->run();