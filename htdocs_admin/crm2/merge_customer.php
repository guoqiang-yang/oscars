<?php

/**
 * 合并客户.
 *
 * 1 数据表
 *
 *   // 只更新表中的cid字段， uid字段不做修改
 *
 *   - t_customer, t_user
 *   - t_coupon, t_coupon_apply, t_sms_queue,t_construction_site, t_order, t_refund
 *   - t_money_in_history, t_customer_amount_history
 *   - t_cashback
 *
 * 2 交互
 *
 *  - 输入至少两个手机号
 *  - 获取用户信息
 *  - 判断是否可以合并     【如：多个手机号指向同一个cid， 不需要合并】
 *  - 判断是否需要选择 销售人员 【不同的销售】
 *  - 合并数据
 *
 */

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $masterMobile;
    private $slaveMobile1;
    private $mCustomerInfo;
    private $sCustomerInfos;
    private $suids;
    private $resStatus = array();

    protected function getPara()
    {
        $this->masterMobile = Tool_Input::clean('r', 'master_mobile', TYPE_STR);
        $this->slaveMobile1 = Tool_Input::clean('r', 'slave_mobile1', TYPE_STR);

        $this->resStatus = array(
            'st' => 0,
            'msg' => ''
        );
    }

    protected function main()
    {
        if (empty($this->masterMobile) || empty($this->slaveMobile1))
        {
            $this->resStatus['st'] = 100;
            $this->resStatus['msg'] = '请同时输入主副手机号！';

            return;
        }
        if (!Str_Check::checkMobile($this->masterMobile) || !Str_Check::checkMobile($this->slaveMobile1))
        {
            $this->resStatus['st'] = 101;
            $this->resStatus['msg'] = '输入的手机号不合法';

            return;
        }

        $this->mCustomerInfo = $this->_getCustomerInfoAndAllUsers($this->masterMobile);
        $this->sCustomerInfos = $this->_getCustomerInfoAndAllUsers($this->slaveMobile1);

        if (!empty($this->suids))
        {
            $suinfos = Tool_Array::list2Map(Admin_Api::getStaffs($this->suids), 'suid');

            $this->mCustomerInfo['customer']['_saler_suid'] = array_key_exists($this->mCustomerInfo['customer']['sales_suid'], $suinfos) ? $suinfos[$this->mCustomerInfo['customer']['sales_suid']] : array();
            $this->mCustomerInfo['customer']['_record_suid'] = array_key_exists($this->mCustomerInfo['customer']['record_suid'], $suinfos) ? $suinfos[$this->mCustomerInfo['customer']['record_suid']] : array();

            $this->sCustomerInfos['customer']['_saler_suid'] = array_key_exists($this->sCustomerInfos['customer']['sales_suid'], $suinfos) ? $suinfos[$this->sCustomerInfos['customer']['sales_suid']] : array();
            $this->sCustomerInfos['customer']['_record_suid'] = array_key_exists($this->sCustomerInfos['customer']['record_suid'], $suinfos) ? $suinfos[$this->sCustomerInfos['customer']['record_suid']] : array();
        }

        if ($this->mCustomerInfo['customer']['sales_suid'] == 0)
        {
            $this->resStatus['st'] = 104;
            $this->resStatus['msg'] = '主账号无销售人员，不能合并！';
        }

        if (empty($this->mCustomerInfo['customer']) || empty($this->sCustomerInfos['customer']))
        {
            $this->resStatus['st'] = 102;
            $this->resStatus['msg'] = '客户信息不能为空！';
        }

        if ($this->mCustomerInfo['customer']['cid'] == $this->sCustomerInfos['customer']['cid'])
        {
            $this->resStatus['st'] = 103;
            $this->resStatus['msg'] = '两个手机号是同一个客户，不需要合并！';
        }

        $this->addFootJs(array('js/apps/crm2.js'));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $this->smarty->assign('master_mobile', $this->masterMobile);
        $this->smarty->assign('slave_mobile1', $this->slaveMobile1);

        $this->smarty->assign('master_customer', $this->mCustomerInfo);
        $this->smarty->assign('slave_customer', $this->sCustomerInfos);

        $this->smarty->assign('res_status', $this->resStatus);

        $this->smarty->display('crm2/merge_customer.html');
    }

    protected function _getCustomerInfoAndAllUsers($mobile)
    {
        $_cinfo = Crm2_Api::getByMobile($mobile);

        if (isset($_cinfo['cid']) && !empty($_cinfo['cid']))
        {
            $cid = $_cinfo['cid'];

            $customerInfo = Crm2_Api::getCustomerInfo($cid, TRUE, FALSE);

            if (!empty($customerInfo['customer']['record_suid']))
            {
                $this->suids[] = $customerInfo['customer']['record_suid'];
            }
            if (!empty($customerInfo['customer']['sales_suid']))
            {
                $this->suids[] = $customerInfo['customer']['sales_suid'];
            }
        }

        return $customerInfo;
    }
}

$app = new App();
$app->run();