<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    // cgi参数
    private $cid;
    // 中间结果
    private $customer;
    private $users;
    /**
     * 是否为当前管理员的客户.
     * 作用主要是：限制销售人员操作；只有自己的客户才可进行某些操作
     */
    private $isYourCustomer = FALSE;

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
        if (!empty($oldInfo)) {
            $this->customer['real_name'] = $oldInfo['real_name'];
            $this->customer['id_number'] = $oldInfo['id_number'];
            $this->customer['band_card_number'] = $oldInfo['bank_card_number'];
            $this->customer['company_name'] = $oldInfo['company_name'];
            $this->customer['legal_person_name'] = $oldInfo['legal_person_name'];
            $this->customer['legal_person_id_number'] = $oldInfo['legal_person_id_number'];
            $this->customer['social_credit_number'] = $oldInfo['social_credit_number'];
            $certificationDesc = Conf_User::getCertificationDesc();
            $this->customer['identity_step'] = $certificationDesc[$oldInfo['step']];
            if ($oldInfo['step'] == Conf_User::CERTIFICATE_IN_PROCESS) {
                $adminInfo = Crm2_Certification_Api::getUndealItem($this->_uid, $this->cid);
                $suidInfo = Admin_Api::getStaff($adminInfo['suid']);
                $this->customer['identity_step'] .= '(需要 ' . $suidInfo['name'] . ' 认证)';
            }
        } else {
            $this->customer['identity_step'] = '未认证';
        }
        $this->users = $ret['users'];

        $sources = Conf_User::$Introduce_Source;
        $this->customer['sourceName'] = isset($sources[$this->customer['source']]) ? $sources[$this->customer['source']] : '';
        $this->customer['identity_name'] = Conf_User::$Crm_Identity[$this->customer['identity']];

        // isYourCustomer
        $this->_isYourCustomer();

        if ($this->customer['sale_status'] != Conf_User::CRM_SALE_ST_PUBLIC && !$this->isYourCustomer) {
            throw new Exception('customer:customer not belong to you');
        }

        //取市场专员信息
        if ($this->customer['sales_suid']) {
            $this->customer['_sales_suid'] = Admin_Api::getStaff($this->customer['sales_suid']);
        }

        //取市场专员信息
        if ($this->customer['record_suid']) {
            $this->customer['_record_suid'] = Admin_Api::getStaff($this->customer['record_suid']);
        }

        //回访记录
        $res = Crm2_Api::getCustomerTrackingList(array('cid' => $this->cid), 0, 0);
        $this->list = $res['list'];

        //取退款单数
        $or = new Order_Refund();
        $this->customer['refund_summary'] = $or->getSummaryOfCustomer($this->cid);

        //最近7天得下单数
        $oo = new Order_Order();
        $date = date('Y-m-d', time() - 24 * 3600 * 6);
        $this->customer['order_summary'] = $oo->getSummaryOfCustomer($this->cid, $date);

        $this->addFootJs(array(
            'js/apps/crm2.js',
            'js/apps/sale_schedule.js',
            'js/apps/tracking.js'
        ));
        if (!empty($this->_user['ce_agent_num'])) {
            $this->addHeadJs(
                array(
                    'js/jquery.md5.js',
                    'js/callcenter/jquery.wincall.v2.js',
                    'js/callcenter/socket.io.js'
                )
            );
            $this->addFootJs(
                array(
                    'js/callcenter/call.js',
                )
            );
        }

        $this->addCss(array());
    }

    protected function outputBody()
    {
        $ccia = new Crm2_Customer_Identity_Apply();
        $checkCanAudit = $ccia->checkCanAudit($this->_uid, $this->cid);
        if ($checkCanAudit) {
            $cciaInfo = $ccia->getByCid($this->cid);
            $this->customer['identity_name'] .= '（To:' . Conf_User::$Crm_Identity[$cciaInfo['identity']] . '）';
        }
        $this->smarty->assign('customer', $this->customer);
        $this->smarty->assign('rival_descs', Conf_User::$Desc_In_Rival);
        $this->smarty->assign('is_your_customer', $this->isYourCustomer);
        $this->smarty->assign('users', $this->users);
        $this->smarty->assign('all_sale_status', Conf_User::$Customer_Sale_Status);
        $this->smarty->assign('levels_by_sale', Conf_User::$Crm_Level_BySaler);
        $this->smarty->assign('levels_by_sys', Conf_User::$Customer_Sys_Level_Descs);
        //是否为销售总监
        $this->smarty->assign('is_chief_saler', Admin_Role_Api::isChiefSaler($this->_user));
        $this->smarty->assign('remind_tags', Conf_Crm::getRemindList());
        $this->smarty->assign('province', Conf_Area::$Province);
        $this->smarty->assign('visit_list', Crm2_Customer_Visit_Api::getCustomerVisitList($this->cid, 0, 5));
        $crrInfo = Crm2_Certification_Api::getUndealItem($this->_uid, $this->cid);
        $ccr = new Crm2_Certification_Request();
        $total = $ccr->getTotal(array('cid' => $this->cid, 'suid' => $this->_uid));
        $isAdmin = Admin_Role_Api::isAdmin($this->_uid);
        if (empty($crrInfo) || (!$isAdmin && (($crrInfo['suid'] <> $this->_uid && $this->_uid == $this->customer['sales_suid'] && $total > 0) || ($this->_uid <> $this->customer['sales_suid'] && $crrInfo['suid'] <> $this->_uid)))) {
            $this->smarty->assign('check_identity', 0);
        } else {
            $this->smarty->assign('check_identity', 1);
        }
        $this->smarty->assign('check_can_audit', $checkCanAudit);
        $this->smarty->display('crm2/customer_detail.html');
    }

    private function _isYourCustomer()
    {
        // 管理员角色判断
        $_isSaler = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_SALES_NEW);
        if ($_isSaler) {
            if ($this->customer['sales_suid'] == $this->_uid || in_array($this->customer['sales_suid'], $this->_user['team_member'])) {
                $this->isYourCustomer = TRUE;
            }
        } else {
            $this->isYourCustomer = TRUE;
        }
    }
}

$app = new App();
$app->run();