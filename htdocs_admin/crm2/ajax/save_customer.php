<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $cid;
    private $backUrl;
    private $customer;
    private $user;
    private $ajResponse;
    private $identity;

    protected function checkAuth()
    {
        parent::checkAuth('/crm2/edit_customer');
    }

    protected function getPara()
    {
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
        $this->backUrl = Tool_Input::clean('r', 'url', TYPE_STR);
        $this->customer = array(
            'name' => Tool_Input::clean('r', 'name', TYPE_STR),
            'nick_name' => Tool_Input::clean('r', 'nick_name', TYPE_STR),
//            'age' => Tool_Input::clean('r', 'age', TYPE_UINT),
//            'birthday' => Tool_Input::clean('r', 'birthday', TYPE_STR),
            'sex' => Tool_Input::clean('r', 'sex', TYPE_UINT),
            'identity' => Tool_Input::clean('r', 'identity', TYPE_UINT),
            'source' => Tool_Input::clean('r', 'source', TYPE_UINT),
            'city_id' => Tool_Input::clean('r', 'city_id', TYPE_UINT),
            'birth_place' => Tool_Input::clean('r', 'birth_place', TYPE_STR),
//            'work_age' => Tool_Input::clean('r', 'work_age', TYPE_UINT),
//            'interest' => Tool_Input::clean('r', 'interest', TYPE_STR),
//            'address' => Tool_Input::clean('r', 'address', TYPE_STR),
//            'work_area' => Tool_Input::clean('r', 'work_area', TYPE_STR),
//            'character_tag' => Tool_Input::clean('r', 'character_tag', TYPE_STR),
            'note' => Tool_Input::clean('r', 'note', TYPE_STR),
//            'qq' => Tool_Input::clean('r', 'qq', TYPE_UINT),
//            'weixin' => Tool_Input::clean('r', 'weixin', TYPE_STR),
//            'email' => Tool_Input::clean('r', 'email', TYPE_STR),

            'record_suid' => Tool_Input::clean('r', 'record_suid', TYPE_STR),
            'sales_suid' => Tool_Input::clean('r', 'sales_suid', TYPE_UINT),

            'rival_desc' => Tool_Input::clean('r', 'rival_desc', TYPE_UINT),
            'payment_days' => Tool_Input::clean('r', 'payment_days', TYPE_UINT),
            'status' => Tool_Input::clean('r', 'status', TYPE_UINT),
            'member_date' => Tool_Input::clean('r', 'member_date', TYPE_STR),


            'level_for_saler' => Tool_Input::clean('r', 'level_for_saler', TYPE_INT),
            'level_for_sys' => Tool_Input::clean('r', 'level_for_sys', TYPE_UINT),
            'is_auto_save' => 0,
            'has_duty' => Tool_Input::clean('r', 'has_duty', TYPE_UINT),
            'discount_ratio' => Tool_Input::clean('r', 'discount_ratio', TYPE_UINT),
        );

        $this->user = array(
            'name' => Tool_Input::clean('r', 'user_name', TYPE_STR),
            'mobile' => Tool_Input::clean('r', 'identity_mobile', TYPE_STR),
            'hometown' => Tool_Input::clean('r', 'hometown', TYPE_STR),
            'qq' => Tool_Input::clean('r', 'qq', TYPE_UINT),
            'weixin' => Tool_Input::clean('r', 'weixin', TYPE_STR),
        );

        $this->ajResponse = array(
            'st' => 0,
            'msg' => '',
        );
    }

    protected function checkPara()
    {
        if (empty($this->customer['name']))
        {
            $this->ajResponse['st'] = 10;
            $this->ajResponse['msg'] = '客户名称不能为空！';

            return;
        }

        if (empty($this->customer['identity']))
        {
            $this->ajResponse['st'] = 11;
            $this->ajResponse['msg'] = '请选择客户类型！';
        }

//        if ($this->customer['identity'] == Conf_User::CRM_IDENTITY_COMPANY &&  empty($this->customer['address']))
//        {
//            $this->ajResponse['st'] = 12;
//            $this->ajResponse['msg'] = '公司客户，地址不能为空！';
//        }

        if (empty($this->customer['source']))
        {
            $this->ajResponse['st'] = 13;
            $this->ajResponse['msg'] = '请选择客户来源！';

            return;
        }

        // 新注册用户
        if (empty($this->cid) && empty($this->user['mobile']))
        {
            $this->ajResponse['st'] = 14;
            $this->ajResponse['msg'] = '电话不能为空！';

            return;
        }
        if(!empty($this->customer['discount_ratio']) && ($this->customer['discount_ratio'] < 90 || $this->customer['discount_ratio'] >= 100))
        {
            $this->ajResponse['st'] = 15;
            $this->ajResponse['msg'] = '折扣比例只能在0,90<=x<100之间';
            return;
        }
    }

    protected function main()
    {
        if ($this->ajResponse['st'] != 0)
        {
            return;
        }

        if (empty($this->cid))  //注册
        {
            // 配置客户的来源，销售专员, 销售状态等信息 
            // 注册客户：销售 or 城市经理
            $belongMe = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_SALES_NEW) || 
                        Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_CITY_ADMIN_NEW);
            
            $this->customer['sales_suid'] = !empty($this->customer['sales_suid']) ? 
                    $this->customer['sales_suid'] : ($belongMe ? $this->_uid : 0);

            $this->customer['sale_status'] = !empty($this->customer['sales_suid']) ? Conf_User::CRM_SALE_ST_PRIVATE : Conf_User::CRM_SALE_ST_PUBLIC;

            $this->customer['record_suid'] = $this->_uid;

            $this->customer['reg_source'] = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_SALES_NEW) ? Conf_User::CUSTOMER_REG_SALER : Conf_User::CUSTOMER_REG_CS;

            try
            {
                $ret = Crm2_Auth_Api::register($this->customer, $this->user, $this->_user);
            }
            catch (Exception $e)
            {
                $errmsg = $e->getMessage();

                if (strpos($errmsg, 'mobile occupied') !== FALSE)
                {
                    $this->ajResponse['st'] = 21;
                    $this->ajResponse['msg'] = '手机号已经注册！';
                }
                else if ($errmsg == 'common:mobile format error')
                {
                    $this->ajResponse['st'] = 23;
                    $this->ajResponse['msg'] = '手机号格式不正确，请检查';
                }
                else
                {
                    $this->ajResponse['st'] = 22;
                    $this->ajResponse['msg'] = '注册失败！';
                }
            }

            $this->cid = $ret['cid'];

            if ($this->cid != 0)
            {
                $params = array(
                    'id' => $this->cid,
                    'name' => $this->customer['name']
                );
                Admin_Api::addActionLog($this->_uid, Conf_Admin_Log::$ACTION_ADD_CUSTOMER, $params);
            }

            // 重新backUrl
            if (empty($this->backUrl))
            {
                $this->backUrl = '/crm2/edit_customer.php?cid=' . $this->cid;
            }
        }
        else    // 更新
        {
            //todo: 临时限制欠款大户，谁看到这条就可以找王申确认下是否可以删掉了
            if (in_array($this->_uid, array(1039, 1437, 1089, 1320, 1496,1041,1603,1107,1378)))
            {
                return;
            }

            $customerInfo = Crm2_Api::getCustomerInfo($this->cid, FALSE, FALSE);
            $isMyCustomer = Crm2_Api::isMyCustomer($customerInfo['customer'], $this->_user);

            if (!Admin_Role_Api::isAdmin($this->_uid, $this->_user) &&
                !Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_SALES_DIRECTOR) &&
                $customerInfo['customer']['city_id'] > 0 &&
                $this->_user['city_id'] != $customerInfo['customer']['city_id']
            )
            {
                throw new Exception('只能修改本城市的客户!');
            }

            if (!$isMyCustomer['can_edit'] &&
                !Admin_Role_Api::isAdmin($this->_uid, $this->_user) &&
                !Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_CITY_ADMIN_NEW) &&
                !Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_SALES_DIRECTOR)
            )
            {
                $this->ajResponse['st'] = 20;
                $this->ajResponse['msg'] = '客户不属于你，不能修改用户信息';

                return;
            }

            if ($this->checkPermission('crm2_update_customer_suser'))
            {
                unset($this->customer['sales_suid']);
            }

            if ($this->checkPermission('crm2_update_customer_suser'))
            {
                unset($this->customer['record_suid']);
            }
//            if ($this->checkPermission('crm2_update_customer_other_info'))
//            {
                unset($this->customer['payment_days']);
//            }
//            if ($this->checkPermission('crm2_update_customer_level_for_saler'))
//            {
                unset($this->customer['level_for_saler']);
//            }
//            if ($this->checkPermission('crm2_update_customer_other_info'))
//            {
                unset($this->customer['level_for_sys']);
//            }
            unset($this->customer['has_duty']);

            if ($this->checkPermission('crm2_update_customer_other_info'))
            {
                unset($this->customer['discount_ratio']);
            }

            if (isset($this->customer['sales_suid']) || isset($this->customer['record_suid']))
            {
                $params['name'] = $this->customer['name'];
                $params['cid'] = $this->cid;
                $params['desc'] = isset($this->customer['sales_suid']) ? '销售' : '录入';

                $oldCustomerInfo = Crm2_Api::getCustomerInfo($this->cid, FALSE, FALSE);

                if (isset($this->customer['sales_suid']) && $oldCustomerInfo['customer']['sales_suid'] != $this->customer['sales_suid'])
                {
                    $this->customer['sale_status'] = Conf_User::CRM_SALE_ST_PRIVATE;
                    $this->customer['chg_sstatus_time'] = date('Y-m-d H:i:s');

                    $params['desc'] = '销售';
                    $params['suid1'] = $oldCustomerInfo['customer']['sales_suid'];
                    $params['suid2'] = $this->customer['sales_suid'];
                    Admin_Api::addActionLog($this->_uid, Conf_Admin_Log::$ACTION_CHG_CUSTOMER_SALER, $params);
                }
                if (isset($this->customer['record_suid']) && $oldCustomerInfo['customer']['record_suid'] != $this->customer['record_suid'])
                {
                    $params['desc'] = '录入';
                    $params['suid1'] = $oldCustomerInfo['customer']['record_suid'];
                    $params['suid2'] = $this->customer['record_suid'];
                    Admin_Api::addActionLog($this->_uid, Conf_Admin_Log::$ACTION_CHG_CUSTOMER_SALER, $params);
                }
            }

            // 修改了客户状态，记录日志
            if ($this->customer['status'] != $customerInfo['customer']['status'])
            {
                $allSt = Conf_Base::getCustomerStatus();
                $actlog['customer_info'] = $customerInfo['customer']['name'] . '#' . $customerInfo['customer']['cid'];
                $actlog['fr_status'] = array_key_exists($customerInfo['customer']['status'], $allSt) ? $allSt[$customerInfo['customer']['status']] : '暂无';
                $actlog['fr_status'] .= '#' . $customerInfo['customer']['status'];
                $actlog['to_status'] .= array_key_exists($this->customer['status'], $allSt) ? $allSt[$this->customer['status']] : '暂无';
                $actlog['to_status'] .= '#' . $this->customer['status'];

                Admin_Api::addActionLog($this->_uid, Conf_Admin_Log::$ACTION_CHG_CUSTOMER_STATUS, $actlog);
            }
            $identity = $this->customer['identity'];
            if(isset($this->customer['identity']) && $this->customer['identity'] != $customerInfo['customer']['identity'])
            {
                $this->customer['identity'] = $customerInfo['customer']['identity'];
            }
            $ret = Crm2_Api::updateCustomerInfo($this->cid, $this->customer, array(), $this->_user);
            if($identity != $customerInfo['customer']['identity'])
            {
                $customerInfo = Crm2_Api::getCustomerInfo($this->cid);
                $saleInfo = array();
                if ($customerInfo['customer']['sales_suid'] > 0)
                {
                    $saleInfo = Admin_Api::getStaff($customerInfo['customer']['sales_suid']);
                }
                $leader_suid = $saleInfo['leader_suid'] > 0 ? $saleInfo['leader_suid'] : 1073;
                $ccia = new Crm2_Customer_Identity_Apply();
                $dataInfo = array(
                    'identity' => $identity,
                    'suid' => $leader_suid,
                    'step' => 1
                );
                $needCertCity = Conf_Crm::getNeedCertCity();
                if (!$needCertCity[$this->customer['city_id']])
                {
                    $cc = new Crm2_Customer();
                    $cc->update($this->cid, array('identity' => $identity));
                    $dataInfo['identity'] = $identity;
                    $dataInfo['step'] = 2;
                }
                $applyInfo = $ccia->getByCid($this->cid);
                if (!empty($applyInfo))
                {
                    $ccia->updateInfo($this->cid, $dataInfo);
                }
                else
                {
                    $dataInfo['cid'] = $this->cid;
                    $ccia->add($dataInfo);
                }
                if ($needCertCity[$this->customer['city_id']])
                {
                    $leaderInfo = Admin_Api::getStaff($leader_suid);
                    Tool_DingTalk::sendNotice4LeaderSaleMessage($leaderInfo['ding_id'], $this->cid);
                }
            }
            //更新销售leader
            if ($this->customer['sales_suid'] > 0)
            {
                $customerInfo = Crm2_Api::getCustomerInfo($this->cid);
                $saleInfo = array();
                if ($customerInfo['customer']['sales_suid'] > 0)
                {
                    $saleInfo = Admin_Api::getStaff($customerInfo['customer']['sales_suid']);
                }
                $leader_suid = $saleInfo['leader_suid'] > 0 ? $saleInfo['leader_suid'] : 1073;
                $ccia = new Crm2_Customer_Identity_Apply();
                $info = array('suid' => $leader_suid);
                $ccia->updateInfo($this->cid, $info);
            }
        }
    }

    protected function outputPage()
    {
        $this->ajResponse['cid'] = $this->cid;
        $this->ajResponse['url'] = $this->backUrl;

        $response = new Response_Ajax();
        $response->setContent($this->ajResponse);
        $response->send();
        exit;
    }
}

$app = new App('pri');
$app->run();

