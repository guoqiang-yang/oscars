<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    // cgi参数
    private $cid;
    // 中间结果
    private $customer;
    private $users;
    private $cities;

    protected function getPara()
    {
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
    }

    protected function main()
    {
        $ret = Crm2_Api::getCustomerInfo($this->cid);
        $this->customer = $ret['customer'];
        $cc = new Crm2_Certification();
        $oldInfo = $cc->getByCid($this->cid);
        $this->customer['canIdentity'] = true;
        if(!empty($oldInfo))
        {
            $this->customer['cert_type'] = $oldInfo['type'];
            $this->customer['real_name'] = $oldInfo['real_name'];
            $this->customer['id_number'] = $oldInfo['id_number'];
            $this->customer['identity_mobile'] = $oldInfo['mobile'];
            $this->customer['band_card_number'] = $oldInfo['band_card_number'];
            $this->customer['company_name'] = $oldInfo['company_name'];
            $this->customer['legal_person_name'] = $oldInfo['legal_person_name'];
            $this->customer['legal_person_id_number'] = $oldInfo['legal_person_id_number'];
            $this->customer['social_credit_number'] = $oldInfo['social_credit_number'];
            $identity_desc = Conf_User::getCertificationDesc();
            $this->customer['identity_step'] = $identity_desc[$oldInfo['step']];
            $this->customer['canIdentity'] = ($oldInfo['step'] <= Conf_User::CERTIFICATE_NEW ? true : false);
        }
        $this->users = $ret['users'];

        $this->cities = Conf_City::$CITY;
        unset($this->cities[Conf_City::XIANGHE]);

        $this->addFootJs(array(
                             'js/apps/crm2.js',
                             'js/core/area.js'
                         ));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        if (!$this->checkPermission('crm2_update_customer_suser'))
        {
            $recordSaleList = Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_SALES_NEW, 0);
            $salesList = Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_SALES_NEW, 0);
            $teamMember = $this->_user['team_member'];
            foreach ($salesList as $item)
            {
                if (in_array($item['suid'], $teamMember))
                {
                    $realList[$item['suid']] = array('suid' => $item['suid'], 'name' => $item['name']);
                }
            }
            if (in_array($this->_user['suid'], Conf_Admin::$SUPER_ADMINER))
            {
                $realList = $salesList;
            }
            $this->smarty->assign('record_salelist', $recordSaleList);
            $this->smarty->assign('sales_list', $realList);
        }

//        if (array_key_exists(Conf_Admin::ROLE_SALES, $this->_user['level']) && $this->_user['level'][Conf_Admin::ROLE_SALES] == Conf_Admin::LEVEL_1)
//        {
//            $salesList = Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_SALES, 0);
//            $this->smarty->assign('sales_list', $salesList);
//            $this->smarty->assign('isCityAdmin', 1);
//        }

        $this->smarty->assign('cities', $this->cities);
        $this->smarty->assign('city', Tool_Array::jsonEncode(Conf_Area::$CITY));
        $this->smarty->assign('distinct', Tool_Array::jsonEncode(Conf_Area::$DISTRICT));
        $this->smarty->assign('area', Tool_Array::jsonEncode(Conf_Area::$AREA));
        $this->smarty->assign('identitys', Conf_User::$Crm_Identity);
        $this->smarty->assign('province', Conf_Area::$Province);
        $this->smarty->assign('user_source', Conf_User::$Introduce_Source);
        $this->smarty->assign('rival_descs', Conf_User::$Desc_In_Rival);
        $this->smarty->assign('customer', $this->customer);
        $this->smarty->assign('users', $this->users);
        $this->smarty->assign('customer_levels', Conf_User::$Crm_Level_BySaler);
        $this->smarty->assign('sys_levels', Conf_User::$Customer_Sys_Level_Descs);
        $isMyCustomer = Crm2_Api::isMyCustomer($this->customer, $this->_user);
        $this->smarty->assign('can_edit', $isMyCustomer['can_edit']);
        $this->smarty->assign('friend_types', Conf_Crm::getRelationList());
        $this->smarty->assign('friend_list', Crm2_Relative_Api::getList($this->cid));
        $this->smarty->display('crm2/edit_customer.html');
    }
}

$app = new App('pri');
$app->run();
