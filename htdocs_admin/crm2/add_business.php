<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $bid;
    private $businessInfo = array();
    private $bindCustomers
        = array(
            'data' => array(),
            'total' => 0
        );

    protected function getPara()
    {
        $this->bid = Tool_Input::clean('r', 'bid', TYPE_UINT);
    }

    protected function main()
    {
        $this->addFootJs(array(
                             'js/core/area.js',
                             'js/apps/business.js'
                         ));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $recordSaleList = Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_SALES_NEW, FALSE);
        foreach ($recordSaleList as $suinfo)
        {
            if ($suinfo['status'] == Conf_Base::STATUS_NORMAL)
            {
                $salesList[] = $suinfo;
            }
        }
        
        // 如果是销售，创建时自动写 录入/销售专员信息
        $business = array();
        if (Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_SALES_NEW))
        {
            $business = array('record_suid'=>$this->_uid, 'sales_suid'=>$this->_uid);
        }
        
        $this->smarty->assign('business_info', $business);
        $this->smarty->assign('sales_list', $salesList);
        $this->smarty->assign('record_list', $recordSaleList);
        $this->smarty->assign('city', Tool_Array::jsonEncode(Conf_Area::$CITY));
        $this->smarty->assign('distinct', Tool_Array::jsonEncode(Conf_Area::$DISTRICT));
        $this->smarty->assign('area', Tool_Array::jsonEncode(Conf_Area::$AREA));
        $this->smarty->assign('bid', $this->bid);
        $this->smarty->assign('business_info', $this->businessInfo);
        $this->smarty->assign('bind_customers', $this->bindCustomers);

        $this->smarty->display('crm2/edit_business.html');
    }
}

$app = new App();
$app->run();