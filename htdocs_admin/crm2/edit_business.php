<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $bid;
    private $businessInfo = array();
    private $bindCustomers = array(
            'data' => array(),
            'total' => 0
        );

    protected function getPara()
    {
        $this->bid = Tool_Input::clean('r', 'bid', TYPE_UINT);
    }

    protected function main()
    {
        if (!empty($this->bid))
        {
            $businessInfo = Business_Api::getBusinessInfoByBid($this->bid);

            foreach ($businessInfo['customers']['data'] as &$data)
            {
                $data['all_user_names'] = str_replace(',', '，', $data['all_user_names']);
                $data['all_user_mobiles'] = str_replace(',', '，', $data['all_user_mobiles']);
            }

            $this->businessInfo = $businessInfo['business'];
            $this->bindCustomers = $businessInfo['customers'];
        }

        $this->addFootJs(array(
                             'js/core/area.js',
                             'js/apps/business.js'
                         ));
        $this->addCss(array());
    }

    protected function outputBody()
    {
//        if ($this->checkPermission('crm2_update_customer_suser'))
//        {
            $recordSaleList = Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_SALES_NEW, FALSE);
            foreach ($recordSaleList as $suinfo)
            {
                if ($suinfo['status'] == Conf_Base::STATUS_NORMAL)
                {
                    $salesList[] = $suinfo;
                }
            }
            $this->smarty->assign('sales_list', $salesList);
            $this->smarty->assign('record_list', $recordSaleList);
//        }
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