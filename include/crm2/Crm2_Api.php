<?php

/**
 * CRM相关接口
 */
class Crm2_Api extends Base_Api
{
    /**
     * 获取客户信息.
     *
     * @param int $cid
     */
    public static function getCustomerInfo($cid, $needUsers = TRUE)
    {
        $cc = new Crm2_Customer();
        $cu = new Crm2_User();

        $customer = array($cc->get($cid));

        // customer绑定的用户
        $users = array();
        if ($needUsers)
        {
            $users = $cu->getUsersOfCustomer($cid);
        }

        return array('customer' => $customer[0], 'users' => $users);
    }

    /**
     * 获取用户信息.
     *
     * @param int  $uid
     * @param bool $needCustomerInfo
     * @param bool $needCoupon
     */
    public static function getUserInfo($uid, $needCustomerInfo = TRUE)
    {
        $cc = new Crm2_Customer();
        $cu = new Crm2_User();

        $user = $cu->get($uid);

        $customer = array();
        if ($needCustomerInfo && isset($user['cid']))
        {
            $customer = array($cc->get($user['cid']));
        }

        return array('customer' => $customer[0], 'user' => $user);
    }

    public static function getCustomerInfoByCidUid($cid, $uid)
    {
        if (empty($cid) || empty($uid))
        {
            return -1;
        }

        $cc = new Crm2_Customer();
        $cu = new Crm2_User();

        $customerInfo = $cc->get($cid);
        $userInfo = $cu->get($uid);

        if (empty($customerInfo) || empty($userInfo) || $cid != $userInfo['cid'])
        {
            return -2;
        }

        $customerInfo['_user'] = $userInfo;

        return $customerInfo;
    }

    /**
     * 更新客户信息.
     *
     * @param int   $cid
     * @param array $customerInfo
     */
    public static function updateCustomerInfo($cid, $customerInfo, $chgData = array())
    {
        $cc = new Crm2_Customer();
        
        return $cc->update($cid, $customerInfo, $chgData);
    }

    public static function updateUserInfo($uid, $cid, $upUserInfo)
    {
        if (empty($uid) || empty($upUserInfo) || empty($cid))
        {
            return -1;
        }

        try
        {
            $cc = new Crm2_Customer();
            $cu = new Crm2_User();

            $ret = $cu->update($uid, $upUserInfo);

            // 更新客户中冗余的用户的信息
            if ($ret)
            {
                $cc->updateRedundantCustomerInfo($cid);
            }
        }
        catch (Exception $e)
        {

            $eMsg = $e->getMessage();

            return strpos($eMsg, 'mobile occupied') === FALSE ? -2 : -10;
        }

        return 1;
    }

    /**
     * 客户分离.
     *
     *
     */
    public static function separateCustomers()
    {
    }

    /**
     * 合并两个客户.
     *
     * @param array $masterCustomer
     * @param array $slaveCustomer
     */
    public static function mergeCustomers($masterCustomer, $slaveCustomer, $adminInfo)
    {
        if (empty($masterCustomer['customer']) || empty($slaveCustomer['customer']))
        {
            return -1;
        }

        $cc = new Crm2_Customer();
        $cu = new Crm2_User();

        $masterCid = $masterCustomer['customer']['cid'];
        $slaveCid = $slaveCustomer['customer']['cid'];

        $_log = "masterCid: $masterCid (" . $masterCustomer['customer']['all_user_mobiles'] . ")\t" . "slaveCid: $slaveCid (" . $slaveCustomer['customer']['all_user_mobiles'] . ")\t By_Operator：" . $adminInfo['name'];
        Tool_Log::addFileLog('merge_customer.log', "\n###合并客户：$_log");

        // 1 slave_customer::_users 的cid 设置个为masterCustomer::cid
        $cu->updateByCid($slaveCid, array('cid' => $masterCid));
        Tool_Log::addFileLog('merge_customer.log', "合并User, 指向Cid:$masterCid");

        // 2 更新customer中冗余的names, mobiles
        $cc->updateRedundantCustomerInfo($masterCid);
        Tool_Log::addFileLog('merge_customer.log', "将所有的User name/mobile冗余到$masterCid ");

        // 3 slave_customer::customer设置无效
        $cc->update($slaveCid, array('status' => Conf_Base::STATUS_DELETED));
        Tool_Log::addFileLog('merge_customer.log', "将cid:$slaveCid 设置无效");

        // 4 更新用户相关的数据表
        $cc->mergeCustomersForRelationTables($masterCid, $slaveCid);
        Tool_Log::addFileLog('merge_customer.log', "更新cid:$masterCid 关联的数据表");

        // 5 合并用户的 “返现” cid在cash_back中是主键，单独处理
        $ccash = new Crm2_Cashback();
        $ccash->merge($masterCid, $slaveCid);
        Tool_Log::addFileLog('merge_customer.log', "更新Cid:$masterCid 的返现数据");

        // 6 更新客户的应收明细
        Finance_Api::rebuildCustomerFinancialHistory($masterCid);
        Tool_Log::addFileLog('merge_customer.log', "重建Cid:$masterCid 的应收列表并更新账户金额");

        // 7 更新客户的余额明细
        Finance_Api::rebuildCustomerAmountHistory($masterCid);
        Tool_Log::addFileLog('merge_customer.log', "更新Cid:$masterCid 的余额列表并更新账户余额");

        // 8 更新客户的customer中订单，财务等数据
        //        $customerOrderInfos = Order_Api::statisticsCustomerOrder($masterCid);
        //        Crm2_Api::updateCustomerInfo($masterCid, $customerOrderInfos);
        $customerOrderInfos = Crm2_Stat_Api::statAllConsume4Customer($masterCid);
        $cc->update($masterCid, $customerOrderInfos);
        Tool_Log::addFileLog('merge_customer.log', "更新Cid:$masterCid 的订单，财务等相关数据");

        // 9 写一条tracking
        $trackingInfo = array(
            'cid' => $masterCid, 'edit_suid' => $adminInfo['suid'], 'content' => "合并客户:$masterCid\t$slaveCid", 'type' => Conf_User::CT_MERGE_CUSTOMER,
        );
        Crm2_Api::saveCustomerTracking(0, $trackingInfo);
        Tool_Log::addFileLog('merge_customer.log', "更新成功！\n\n");
    }

    /**
     * 绑定 - 创建一个User与Customer绑定.
     *
     * @param type $cid
     */
    public static function bindUserWithCustomer($cid, $userInfo)
    {
        if (empty($cid) || empty($userInfo) || empty($userInfo['mobile']) || empty($userInfo['name']))
        {
            return -1;
        }

        try
        {
            $cc = new Crm2_Customer();
            $cu = new Crm2_User();

            $ret = $cu->add($cid, $userInfo);
            
            // 更新客户中冗余的用户的信息
            if ($ret)
            {
                $cc->updateRedundantCustomerInfo($cid);
            }
        }
        catch (Exception $e)
        {
            $eMsg = $e->getMessage();

            return strpos($eMsg, 'mobile occupied') === FALSE ? -2 : -10;
        }

        return is_array($ret) ? 1 : -3;
    }

    /**
     * 解除绑定 - User解除与Customer的绑定.
     *
     * 解除步骤：
     *      1. 使用userinfo信息生成一个新的customer_new
     *      2. 将user同customer_new绑定
     *      3. 更新原customer的冗余信息
     *      4. @todo 分离订单/财务等信息
     *
     * @param int   $cid
     * @param array $userInfo
     *
     */
    public static function unbindUserFromCustomer($cid, $userInfo)
    {
        if (empty($cid) || empty($userInfo))
        {
            return -1;
        }
        if ($userInfo['cid'] != $cid)
        {
            return -2;
        }

        $cc = new Crm2_Customer();
        $cu = new Crm2_User();

        // 1. 生成一个新的customer_new
        $cInfo = array(
            'name' => $userInfo['name'], 'all_user_names' => $userInfo['name'], 'all_user_mobiles' => $userInfo['mobile'],
        );
        $newCid = $cc->add($cInfo);

        if ($newCid == 0)
        {
            return -3;
        }

        // 2. 将user同新customer_new绑定
        $userUpData = array(
            'cid' => $newCid,
        );
        $cu->update($userInfo['uid'], $userUpData);

        // 3. 更新原customer的冗余信息
        $cc->updateRedundantCustomerInfo($cid);

        // 4. 分离user的订单，财务等数据   @todo

        return 1;
    }

    /**
     * 通过手机号获取客户，每个手机号对应唯一客户.
     *
     * @param string $mobile
     */
    public static function getByMobile($mobile)
    {
        $cc = new Crm2_Customer();
        $cu = new Crm2_User();

        $userInfo = $cu->getByMobile($mobile);

        $customerInfo = array();

        if (!empty($userInfo) && count($userInfo) == 1)
        {
            $userInfo = current($userInfo);
            $cid = $userInfo['cid'];

            $customerInfo = $cc->get($cid);

            $customerInfo['_user'] = $userInfo;
        }

        return $customerInfo;
    }

    /**
     * 获取用户列表.
     *
     * @param array  $searchConf 搜索字段
     * @param array  $adminor    管理员信息
     * @param string $orderBy    排序方法 db_field
     * @param int    $start
     * @param int    $num
     * @param strint $sort
     */
    public static function getCustomerList($searchConf, $adminor, $orderBy = 'cid', $start = 0, $num = 20, $sort = 'desc')
    {
        $cc = new Crm2_Customer();
        
        $_mobile = !empty($searchConf['mobile'])? $searchConf['mobile']: '';
        self::_setRoleForSearchCustomer($searchConf, (Str_Check::checkMobile($_mobile) ? NULL : $adminor));

        $customerList = $cc->search($searchConf, array('*'), $start, $num, $orderBy, $sort);

        //补充市场专员信息
        Admin_Api::appendStaffInfos($customerList['data'], 'sales_suid');

        //补全客户基本信息
        $cids = array_keys($customerList['data']);
        if (!empty($cids))
        {
            foreach ($customerList['data'] as &$cinfo)
            {
                if (Admin_Role_Api::isAdmin($adminor['suid'], $adminor) || 
                    $adminor['suid'] == $cinfo['sales_suid'])
                {
                    $cinfo['is_show_mobile'] = 1;
                }
                else
                {
                    $cinfo['is_show_mobile'] = 0;
                }
            }
        }

        return $customerList;
    }


    /**
     * 获取用户列表.
     *
     * @param array  $searchConf 搜索字段
     * @param array  $adminor    管理员信息
     * @param string $orderBy    排序方法 db_field
     * @param int    $start
     * @param int    $num
     * @param strint $sort
     */
    public static function getCustomerList4Payment($searchConf, $orderBy = 'cid', $start = 0, $num = 20, $sort = 'desc')
    {
        $cc = new Data_Dao('t_customer');
        $where = sprintf('status=%d AND (payment_days>0 OR payment_amount>0) and contract_btime>0 and contract_etime>0', Conf_Base::STATUS_NORMAL);
        if(!empty($searchConf['sales_suid']))
        {
            $where .= is_array($searchConf['sales_suid']) ? sprintf(' AND sales_suid IN(%s)', implode(',', $searchConf['sales_suid'])) : sprintf(' AND sales_suid=%d', $searchConf['sales_suid']);
        }
        if(!empty($searchConf['city_id']))
        {
            $where .= sprintf(' AND city_id=%d', $searchConf['city_id']);
        }
        if(!empty($searchConf['cid']))
        {
            $where .= sprintf(' AND cid=%d', $searchConf['cid']);
        }
        if(!empty($searchConf['payment_due_date']))
        {
            $where .= sprintf(' AND payment_due_date="%s"', $searchConf['payment_due_date']);
        }
        $total = $cc->getTotal($where);
        $datas = array();
        if($total > 0)
        {
            $datas = $cc
                ->setFields(array('cid','name','sales_suid','account_balance','contract_btime','payment_days','payment_amount','payment_due_date'))
                ->order($orderBy, $sort)
                ->limit($start, $num)
                ->getListWhere($where);
        }

        return array('total'=>$total, 'data'=>$datas);
    }

    /**
     * 按照进入公海的尺度搜索客户.
     *
     * @param string $type
     * @param int    $salerSuid
     * @param int    $start
     * @param int    $num
     */
    public static function searchWithPublicScale($type, $salerSuid = 0, $start = 0, $num = 20)
    {
        $cc2 = new Crm2_Customer();

        $customerList = $cc2->searchWithPublicScale($type, $salerSuid, $start, $num);

        //补充市场专员信息
        $as = new Admin_Staff();
        $as->appendSuers($customerList['data'], 'sales_suid', 'record_suid', TRUE);

        //补充优惠券信息
        $cco = new Coupon_Coupon();
        $cco->appendCoupon($customerList['data'], 'cid');

        return $customerList;
    }

    public static function getAllDebtOfCustomer($searchConf, $adminor)
    {
        $cc = new Crm2_Customer();
        //$cu = new Crm2_User();

        self::_setRoleForSearchCustomer($searchConf, $adminor);
        $ret = $cc->search($searchConf, array('sum(account_balance) as s'));
        $ret = array_values($ret['data']);

        return $ret[0]['s'] / 100;
    }

    public static function getPaymentDaysDebt($searchConf, $adminor)
    {
        $cc = new Crm2_Customer();

        $searchConf['has_payment_days'] = 1;
        self::_setRoleForSearchCustomer($searchConf, $adminor);
        $ret = $cc->search($searchConf, array('sum(account_balance) as s'));
        $ret = array_values($ret['data']);

        return $ret[0]['s'] / 100;
    }

    public static function getNoPaymentDaysDebt($searchConf, $adminor)
    {
        $cc = new Crm2_Customer();

        $searchConf['has_payment_days'] = 2;
        self::_setRoleForSearchCustomer($searchConf, $adminor);
        $ret = $cc->search($searchConf, array('sum(account_balance) as s'));
        $ret = array_values($ret['data']);

        return $ret[0]['s'] / 100;
    }

    public static function getBalanceAmount($searchConf, $adminor)
    {
        $cc = new Crm2_Customer();

        $searchConf['has_payment_days'] = 1;
        self::_setRoleForSearchCustomer($searchConf, $adminor);
        $ret = $cc->search($searchConf, array('cid'), 0, 0);
        $cids = Tool_Array::getFields($ret['data'], 'cid');
        $cids = array_unique(array_filter($cids));

        $oo = new Order_Order();
        $where1 = sprintf('status=%d and step=%d and cid not in ("%s") and paid!=%d', Conf_Base::STATUS_NORMAL, Conf_Order::ORDER_STEP_PICKED, implode('","', $cids), Conf_Order::HAD_PAID);
        $notBackAmount = $oo->getSumByWhere($where1, 'price+freight+customer_carriage-privilege-refund-real_amount');

        $where1 = sprintf('status=%d and step=%d and cid not in ("%s") and paid!=%d', Conf_Base::STATUS_NORMAL, Conf_Order::ORDER_STEP_FINISHED, implode('","', $cids), Conf_Order::HAD_PAID);
        $backAmount = $oo->getSumByWhere($where1, 'price+freight+customer_carriage-privilege-refund-real_amount');

        return array('back_amount' => $backAmount, 'not_back_amount' => $notBackAmount);
    }

    /**
     * 搜索客户时，设置搜索权限.
     */
    private static function _setRoleForSearchCustomer(&$searchConf, $adminor = NULL)
    {
        if (isset($searchConf['mobile']) && Str_Check::checkMobile($searchConf['mobile']))
        {
            $searchConf['sales_suid'] = 0;
        }
        if (empty($adminor))
        {
            return;
        }

        if (Admin_Role_Api::hasRole($adminor, Conf_Admin::ROLE_SALES_NEW))
        {
            if ($searchConf['sale_status'] == Conf_User::CRM_SALE_ST_PUBLIC)
            {
                $searchConf['city_id'] = $adminor['_city_ids'];
            }
            else if ($searchConf['sale_status'] == Conf_User::CRM_SALE_ST_PRIVATE && isset($searchConf['city_id']))
            {
                unset($searchConf['city_id']);
            }
        }

        // 销售人员&&非总监级别，搜索权限的限制
        if (Admin_Role_Api::hasRole($adminor, Conf_Admin::ROLE_SALES_NEW) && !Admin_Role_Api::isAdmin($adminor['suid'], $adminor))
        {
            // 对于：sales_suid - 默认 搜自己；或 搜team中成员
            $sales_suid = $adminor['suid'];

            if (isset($searchConf['sales_suid']) && !empty($searchConf['sales_suid']) && in_array($searchConf['sales_suid'], $adminor['team_member']))
            {
                $sales_suid = $searchConf['sales_suid'];
            }
        }
        else
        {
            $sales_suid = 0;
            if (isset($searchConf['sales_suid']) && !empty($searchConf['sales_suid']))
            {
                $sales_suid = $searchConf['sales_suid'];
            }
        }

        $searchConf['sales_suid'] = $sales_suid;
    }

    /**
     * 通过cid或手机号混合搜索客户, 半角逗号分隔.
     *
     * @param type $searchContents
     */
    public static function searchCustomerWithCidsOrMobiles($searchContents)
    {
        if (!is_string($searchContents) || trim($searchContents) == '')
        {
            return array();
        }

        $searchContents = str_replace(array("，", "\n"), array(",", ""), $searchContents);
        $customers = explode(',', $searchContents);

        $cids = $mobiles = array();
        foreach ($customers as $one)
        {
            $one = trim($one);
            if (Str_Check::checkMobile($one))
            {
                $mobiles[] = $one;
            }
            else if (is_numeric($one) && intval($one) >= 6000)
            {
                $cids[] = $one;
            }
        }

        $customerInfos = array();
        $cc = new Crm2_Customer();
        if (!empty($cids))
        {
            $customerInfos = $cc->getBulk(array_unique($cids));
        }

        if (!empty($mobiles))
        {
            $customerInfos = array_merge($customerInfos, $cc->searchCustomerWithMobiles($mobiles));
        }

        return $customerInfos;
    }

    /**
     * 获取用户列表.
     */
    public static function getUserList($search, $start = 0, $num = 20, $field = array('*'), $orderBy = 'uid')
    {
        // 获取User-List
        $cu = new Crm2_User();
        $userList = $cu->search($search, $field, $start, $num, $orderBy);

        if ($userList['total'] == 0)
        {
            return $userList;
        }

        // 补充信息
        $cc = new Crm2_Customer();
        $cc->appendInfos($userList['data'], 'cid');

        //补充优惠券信息
        $cco = new Crm2_Coupon();
        $cco->appendCoupon($userList['data'], 'cid');

        return $userList;
    }

    ////////////////////////////////////// 客户回访 ///////////////////////////////////////

    public static function getTrackingsBaseInfo($searchConf, $start = 0, $num = 20)
    {
        $cct = new Crm2_Customer_Tracking();

        return $cct->getList($searchConf, $start, $num);
    }

    public static function getCustomerTrackingList($searchConf, $start, $num)
    {
        $cct = new Crm2_Customer_Tracking();
        $trackingList = $cct->getList($searchConf, $start, $num);

        if ($trackingList['total'] != 0)
        {
            // 补齐客户信息
            $cc = new Crm2_Customer();
            $cc->appendInfos($trackingList['data']);

            // 市场专员 录入专员信息
            foreach ($trackingList['data'] as &$_content)
            {
                $_content['sales_suid'] = $_content['_customer']['sales_suid'];
            }

            $as = new Admin_Staff();
            $as->appendSuers($trackingList['data'], 'sales_suid', 'edit_suid', TRUE);
        }

        return $trackingList;
    }

    /**
     * 客户的流转历史.
     *
     * @param type $cid
     * @param type $start
     * @param type $num
     */
    public static function getCustoemrTransHistory($cid, $start = 0, $num = 20)
    {
        $cct = new Crm2_Customer_Tracking();
        $searchConf = array(
            'cid' => $cid, 'type' => Conf_User::CT_CHG_SALE_ST,
        );
        $trackingList = $cct->getList($searchConf, $start, $num);
        if ($trackingList['total'] > 0)
        {
            $as = new Admin_Staff();
            $salesSuids = Tool_Array::getFields($trackingList['data'], 'edit_suid');
            $staffs = Tool_Array::list2Map($as->getUsers($salesSuids, array('suid', 'name')), 'suid', 'name');

            foreach ($trackingList['data'] as &$info)
            {
                $info['edit_suid_name'] = $staffs[$info['edit_suid']];
            }
        }

        return $trackingList;
    }

    public static function getTrackingInfo($tid)
    {
        $cc = new Crm2_Customer_Tracking();
        $info = $cc->get($tid);

        // 补充信息
        if (!empty($info))
        {
            // 补齐客户信息
            $cc = new Crm2_Customer();
            $cc->appendInfo($info);

            // 市场专员 录入专员信息
            $info['sales_suid'] = $info['_customer']['sales_suid'];

            $as = new Admin_Staff();
            $_info = array($info);
            $as->appendSuers($_info, 'sales_suid', 'edit_suid');
        }

        return $_info[0];
    }

    public static function saveCustomerTracking($tid, array $info)
    {
        $cct = new Crm2_Customer_Tracking();

        if ($tid > 0)
        {
            $cct->update($tid, $info);
        }
        else
        {
            $cct->add($info);
        }

        return TRUE;
    }

    /**
     * 是否为该管理员客户，以及是否可以编辑该客户
     *
     * @param type $adminInfo
     */
    public static function isMyCustomer($customerInfo, $adminInfo)
    {
        $ret['is_my'] = FALSE;      // 是否是admin的客户（仅适用于销售）
        $ret['can_edit'] = FALSE;   // 是否可以编辑客户信息
        
        // 角色，权限判断
        $roles = Admin_Role_Api::getRoleLevels($adminInfo['suid'], $adminInfo);
        foreach ($roles as $role => $level)
        {
            if ($role == Conf_Admin::ROLE_ADMIN_NEW)
            {
                $ret['can_edit'] = TRUE;
                break;
            }
            else if ($role == Conf_Admin::ROLE_SALES_NEW)
            {
                if (in_array($customerInfo['sales_suid'], $adminInfo['team_member']))
                {
                    $ret['is_my'] = TRUE;
                    $ret['can_edit'] = TRUE;
                }
                break;
            }
        }

        return $ret;
    }

    public static function getCustomers($cids)
    {
        $cc = new Crm2_Customer();

        return $cc->getBulk($cids);
    }

    public static function getCumulativeHistory($searchConf, $start = 0, $num = 20)
    {
        $ccl = new Crm2_Cumulative_Log();
        $data = $ccl->getList($searchConf, $start, $num);

        $cc = new Crm2_Customer();
        $cc->appendInfos($data['list']);

        return array('total' => $data['total'], 'list' => $data['list']);
    }

    //////////////////////////// ConstructionSite ///////////////////////////////////////

    public static function saveConstructionSite(array $info)
    {

        assert($info['cid']);

        $cid = $info['cid'];
        $id = isset($info['id']) && !empty($info['id']) ? $info['id'] : 0;
        $address = isset($info['address']) && !empty($info['address']) ? $info['address'] : 0;

        $ch = new Crm2_Construction();

        if (!empty($id) || !empty($address))
        {
            $site = !empty($id) ? $ch->get($id) : $ch->getByAddress($cid, $address);
            $id = !empty($site) ? $site['id'] : 0;
        }

        if (!empty($id))
        {
            if ($site['cid'] != $cid)
            {
                return -1;
            }
            $ch->update($id, $info);
        }
        else
        {
            $id = $ch->add($info);
        }

        return $id;
    }

    public static function searchConstructionSite($search, $start = 0, $num = 20)
    {
        $cc = new Crm2_Construction();
        $cList = $cc->search($search, $start, $num);

        // append more info
        if (!empty($cList['data']))
        {
            $cCustomer = new Crm2_Customer();
            $cCustomer->appendInfos($cList['data']);

            $citys = Conf_Area::$CITY;
            $districts = Conf_Area::$DISTRICT;

            foreach ($cList['data'] as &$data)
            {
                $data['city_name'] = $citys[$data['city']];
                $data['district_name'] = $districts[$data['city']][$data['district']];
            }
        }

        return $cList;
    }

    public static function updateConstructionSite($id, array $info, array $change = array())
    {
        $cc = new Crm2_Construction();

        return $cc->update($id, $info, $change);
    }

    public static function getConstructionSite($id)
    {
        if ($id <= 0)
        {
            return array();
        }
        $cc = new Crm2_Construction();
        $info = $cc->get($id);
        $address = explode(Conf_Area::Separator_Construction, $info['address']);
        if (count($address) == 2)
        {
            $info['address'] = $address[1];
            $info['_community_name'] = $address[0];
        }

        $info['_address'] = Conf_Area::$CITY[$info['city']] . Conf_Area::$DISTRICT[$info['city']][$info['district']] .
                            Conf_Area::$AREA[$info['district']][$info['ring_road']] . $info['community_name'] . $info['address'];

        return $info;
    }

    /**
     * 获取客户的工地地址
     *
     * @param int   $cid
     * @param int   $uid    uid==0,查询全部
     * @param array $cities cities==array(),查询全部
     * @param int   $start
     * @param int   $num
     */
    public static function getConstructionListByCitys4Customer($cid, $uid = 0, $cities = array(), $start = 0, $num = 20)
    {
        if (empty($cid))
            return array();

        $ch = new Crm2_Construction();
        $where = array(
            'status' => Conf_Base::STATUS_NORMAL, 'cid' => $cid,
        );
        if (!empty($uid))
        {
            $where['uid'] = $uid;
        }
        if (!empty($cities))
        {
            $where ['city'] = $cities;
        }

        $list = array();
        $total = $ch->getTotalByWhere($where);
        if ($total > 0)
        {
            $list = $ch->getListByWhere($where, array('*'), $start, $num);
        }

        foreach ($list as &$one)
        {
            $_city = Conf_Area::$CITY[$one['city']];
            $_district = Conf_Area::$DISTRICT[$one['city']][$one['district']];
            $_ringroad = isset(Conf_Area::$AREA[$one['district']][$one['ring_road']]) ? Conf_Area::$AREA[$one['district']][$one['ring_road']] : '';
            if (strpos($one['address'], Conf_Area::Separator_Construction) !== false)
            {
                list($communityName, $addrDetail) = explode(Conf_Area::Separator_Construction, $one['address']);
                $address = $one['community_name'] . $addrDetail;
            }
            else if (!empty($one['community_name']))
            {
                $address = $one['community_name'] . $one['address'];
            }
            else
            {
                $address = $one['address'];
            }

            $one['address'] = $address;
            $one['_address'] = $_city . $_district . $_ringroad . $address;
        }

        $hasMore = $total > $start + $num;

        return array('list' => $list, 'total' => $total, 'has_more' => $hasMore);
    }

    ////////////////////// Business //////////////////////

    public static function getAllBusiness()
    {
        $cb = new Crm2_Business();

        return $cb->getAll();
    }

    ////////////////////// Old //////////////////////

    public static function getConstructionSitesOfCustomer($cid, $start = 0, $num = 20, $all = FALSE)
    {
        $ch = new Crm2_Construction();
        $list = $ch->getListOfCustomer($cid, $total, $start, $num, $all);

        foreach ($list as &$one)
        {
            list($communityName, $addrDetail) = explode(Conf_Area::Separator_Construction, $one['address']);
            $one['address'] = $communityName . $addrDetail;

            $_city = Conf_Area::$CITY[$one['city']];
            $_district = Conf_Area::$DISTRICT[$one['city']][$one['district']];
            $_ringroad = isset(Conf_Area::$AREA[$one['district']][$one['ring_road']]) ? Conf_Area::$AREA[$one['district']][$one['ring_road']] : '';
            $_community = !empty($one['community_name']) ? $one['community_name'] : $communityName;
            $_addrDetail = empty($addrDetail) && !empty($one['community_name']) ? $communityName : '';

            $one['_address'] = $_city . $_district . $_ringroad . $_community . $_addrDetail;
        }

        $hasMore = $total > $start + $num;

        return array('list' => $list, 'total' => $total, 'has_more' => $hasMore);
    }

    public static function getConstructionList()
    {
        $cc = new Crm2_Construction();

        return $cc->getAll();
    }

    public static function delConstructionSite($cid, $id)
    {
        $cc = new Crm2_Construction();

        $constructionInfo = $cc->get($id);

        if ($cid != $constructionInfo['cid'])
        {
            throw new Exception('customer:address not belong to you');
        }

        return $cc->delete($id);
    }

    public static function getLastConstructionSitesOfCustomer($cid)
    {
        $cc = new Crm2_Construction();
        $info = $cc->getLastConstructionSitesOfCustomer($cid);
        if (empty($info))
        {
            return array();
        }
        $info['_address'] = $info['community_addr'] . $info['address'];

        return $info;
    }

    public static function getFrequentlyBuy($cid, $cate1, $start = 0, $num = 10, $cityId = Conf_City::BEIJING)
    {
        $pids = array();
        $hasMore = FALSE;

        $oo = new Order_Order();
        $orderList = $oo->getCustomerOrderList($cid, $total, '', 0, 0);
        $oids = array();
        foreach ($orderList as $order)
        {
            if ($order['city_id'] == $cityId)
            {
                $oids[] = $order['oid'];
            }
        }
        if (!empty($oids))
        {
            $orderProduct = $oo->getProductsByOids($oids, array(), array('*'), $cityId);
            if (!empty($orderProduct))
            {
                foreach ($orderProduct as $product)
                {
                    $pids[$product['pid']]++;
                }
            }

            arsort($pids);

            $pids = array_keys($pids);
            $statusTag = Conf_Product::PRODUCT_STATUS_ONLINE;
            $products = Shop_Api::getProductList(array('cate1' => $cate1, 'city_id' => $cityId), 0, 0, 0, $statusTag);
            $catePids = array();
            foreach ($products['list'] as $k => $info)
            {
                $pid = $info['product']['pid'];
                if (in_array($pid, $pids))
                {
                    $catePids[] = $pid;
                }
            }

            if (count($catePids) > $start + $num)
            {
                $hasMore = TRUE;
                $catePids = array_slice($catePids, $start, $num, TRUE);
            }

            $info = City_Api::getCity();
            $salesList = Shop_Api::getLowestPrice($info['city_id'], Conf_Activity_Flash_Sale::PALTFORM_WECHAT);

            $data = array();

            foreach ($catePids as $pid)
            {
                $product = $products['list'][$pid];
                if (!empty($salesList[$pid]) && $salesList[$pid]['sale_price'] < $product['price'] && $salesList[$pid]['end_time'] > time())
                {
                    $product['sale_price'] = $salesList[$pid]['sale_price'];
                }
                $data[] = $product;
            }
        }

        return array('products' => $data, 'has_more' => $hasMore);
    }

    public static function getFrequentlyBuyItem($cid, $cityId, $conf)
    {
        if (empty($cid))
            return array();
        $pids = array();

        $oo = new Order_Order();
        $orderList = $oo->getCustomerOrderList($cid, $total, '', 0, 0);
        if (!empty($orderList))
        {
            $oids = Tool_Array::getFields($orderList, 'oid');
            $orderProduct = $oo->getProductsByOids($oids, array(), array('*'), $cityId);
            if (!empty($orderProduct))
            {
                foreach ($orderProduct as $product)
                {
                    $pids[$product['pid']]++;
                }
            }

            arsort($pids);

            $ss = new Shop_Sku();
            $skuInfos = $ss->getList($conf, $total, 0, 0);
            if (empty($skuInfos))
            {
                return array();
            }
            $sids = Tool_Array::getFields($skuInfos, 'sid');
            $sp = new Shop_Product();
            $pconf = array('sid' => $sids, 'city_id' => $cityId);
            $statusTag = Conf_Product::PRODUCT_STATUS_ONLINE;
            $products = $sp->getList($pconf, $total, 0, 0, $statusTag);
            foreach ($pids as $pid => $num)
            {
                if (isset($products[$pid]))
                {
                    return $products[$pid];
                }
            }
        }

        return array();
    }

    public static function getOftenBuyPids($cid)
    {
        $pids = array();
        $hasMore = FALSE;

        $oo = new Order_Order();
        $orderList = $oo->getCustomerOrderList($cid, $total, '', 0, 0);
        if (!empty($orderList))
        {
            $oids = Tool_Array::getFields($orderList, 'oid');
            $orderProduct = $oo->getProductsByOids($oids);
            if (!empty($orderProduct))
            {
                foreach ($orderProduct as $product)
                {
                    $pids[$product['pid']]++;
                }
            }

            arsort($pids);
            $pids = array_keys($pids);
        }

        return $pids;
    }

    /**
     * @param array $conf
     * @param       $order
     * @param int   $start
     * @param int   $num
     *
     * @return array
     *
     * 获取应催账的用户列表
     * 应催账用户具有以下几个条件：
     * 1、应结账日期（t_customer表中的payment_due_date）早于当前时间
     * 2、有欠款未结清（t_customer表中的account_balance大于0）
     */
    public static function getShouldRemindCustomerList(array $conf, $orderBy, $start = 0, $num = 20)
    {
        if ($conf['sales_suid'])
        {
            $conf['sales_suid'] = intval($conf['sales_suid']);
        }

        //客户列表
        $cc = new Crm2_Customer();
        $conf['account_balance'] = 1;
        $conf['payment_due_date'] = 1;
        $conf['bid'] = 0;
        if ($conf['payment_due_cate'] == 2)
        {
            $cret = $cc->search($conf, array('*'), 0, 0);
            $total = $cret['total'];
            $list = $cret['data'];

            $oo = new Order_Order();
            $where = ' paid != 1 and step > ' . Conf_Order::ORDER_STEP_NEW . ' and status = ' . Conf_Base::STATUS_NORMAL;
            $order = $oo->getListRawWhere($where, $t, '', 0, 0);
            $cids = Tool_Array::getFields($order, 'cid');

            foreach ($list as $key => $item)
            {
                if (in_array($item['cid'], $cids))
                {
                    unset($list[$key]);
                    $total--;
                }
            }

            $list = array_slice($list, $start, $num);
        }
        else
        {
            $cret = $cc->search($conf, array('*'), $start, $num);
            $list = $cret['data'];
            $total = $cret['total'];
        }

        $hasMore = $total > $start + $num;

        //补充市场专员信息
        $as = new Admin_Staff();
        $as->appendSuers($list, 'sales_suid', 'sales_suid2');

        $staff = $as->getAll();
        $staffMap = Tool_Array::list2Map($staff, 'suid');

        //补充最后备注信息
        $cids = Tool_Array::getFields($list, 'cid');
        $cct = new Crm2_Customer_Tracking();
        $latestTracking = $cct->getCustomerLatestTracking($cids, Conf_User::CT_FINANCE_RECORD, $orderBy);

        foreach ($list as &$customer)
        {
            $cid = $customer['cid'];
            $customer['latest_tracking'] = $latestTracking[$cid]['content'];
            $customer['latest_tracking_user'] = $staffMap[$latestTracking[$cid]['edit_suid']]['name'];
            $customer['last_remind_suer'] = $staffMap[$customer['last_remind_suid']]['name'];
        }

        return array('list' => $list, 'total' => $total, 'has_more' => $hasMore);
    }

    /**
     * @param array $conf
     * @param int   $start
     * @param int   $num
     *
     * @return array
     *
     * 获取应回访的用户列表
     * 应回访用户具有以下几个条件：
     * 1、回访日期（t_customer表中的visit_due_date）早于当前时间
     */
    public static function getNeeTrackingCustomerList(array $conf, $start = 0, $num = 20)
    {
        if ($conf['sales_suid'])
        {
            $conf['sales_suid'] = intval($conf['sales_suid']);
        }

        //客户列表
        $cc = new Crm2_Customer();
        $cret = $cc->search($conf, array('*'), $start, $num, 'visit_due_date', 'asc');
        $list = $cret['data'];
        $hasMore = $cret['total'] > $start + $num;

        //补充市场专员信息
        $as = new Admin_Staff();
        $as->appendSuers($list, 'sales_suid', 'sales_suid2');

        $staff = $as->getAll();
        $staffMap = Tool_Array::list2Map($staff, 'suid');

        //补充最后备注信息
        $cids = Tool_Array::getFields($list, 'cid');
        $cct = new Crm2_Customer_Tracking();
        $latestTracking = $cct->getCustomerLatestTracking($cids);
        if (!empty($latestTracking))
        {
            foreach ($list as &$customer)
            {
                $cid = $customer['cid'];
                $customer['latest_tracking'] = $latestTracking[$cid]['content'];
                $customer['latest_tracking_user'] = $staffMap[$latestTracking[$cid]['edit_suid']]['name'];
            }
        }

        return array('list' => $list, 'total' => $cret['total'], 'has_more' => $hasMore);
    }

    public static function addBusiness(array $info)
    {
        if (!empty($info['contract_phone']) && !Str_Check::checkMobile($info['contract_phone']))
        {
            throw new Exception('common:mobile format error');
        }
        if (!empty($info['contract_phone2']) && !Str_Check::checkMobile($info['contract_phone2']))
        {
            throw new Exception('common:mobile format error');
        }

        $cb = new Crm2_Business();

        $existBusiness = $cb->getByFields(array('name' => $info['name']));
        // 检查条件
        if (!empty($existBusiness))
        {
            throw new Exception('business: name exist');
        }

        // 添加客户
        $bid = $cb->add($info);

        return $bid;
    }

    public static function getBusinessList(array $conf, $start = 0, $num = 20)
    {
        //客户列表
        $cb = new Crm2_Business();
        $order = 'order by bid desc';
        $businessList = $cb->getList($conf, $order, $start, $num);
        $total = $businessList['total'];
        $list = $businessList['list'];
        $hasMore = $total > $start + $num;

        //补充市场专员信息
        $as = new Admin_Staff();
        $as->appendSuers($list, 'sales_suid', 'sales_suid2');

        return array('list' => $list, 'total' => $total, 'has_more' => $hasMore);
    }

    public static function getBusiness($bid)
    {
        if ($bid <= 0)
        {
            return array();
        }

        $cb = new Crm2_Business();
        $info = $cb->get($bid);

        return $info;
    }

    public static function updateBusiness($bid, array $info, array $change = array())
    {
        if (isset($info['contract_phone']) && !Str_Check::checkMobile($info['contract_phone']))
        {
            throw new Exception('common:mobile format error');
        }
        if (isset($info['contract_phone2']) && !Str_Check::checkMobile($info['contract_phone2']))
        {
            throw new Exception('common:mobile format error');
        }

        $cb = new Crm2_Business();

        $oldBusiness = $cb->get($bid);

        // 更新客户信息
        $updateRow = $cb->update($bid, $info, $change);
        if ($updateRow == -1)
        {
            return FALSE;
        }

        if ((isset($info['sales_suid']) && $oldBusiness['sales_suid'] != $info['sales_suid']) || (isset($info['sales_suid2']) && $oldBusiness['sales_suid2'] != $info['sales_suid2']))
        {
            $info2 = array(
                'sales_suid' => $info['sales_suid'], 'sales_suid2' => $info['sales_suid2'],
            );
            $cc = new Crm2_Customer();
            $cc->updateByWhere($info2, array(), array('bid' => $bid));
        }

        return TRUE;
    }

    public static function isCustomerHasSite($cid)
    {
        $cc = new Crm2_Construction();

        return $cc->getCustomerSiteNum($cid);
    }

    public static function getLimit($cid, $key)
    {
        $cl = new Crm2_Limit();
        $data = $cl->getByWhere(array('cid' => $cid, 'lkey' => $key));
        $res = array_shift($data);

        return $res['val'];
    }

    public static function checkLimit($cid, $key, $val = 1)
    {
        $cl = new Crm2_Limit();
        $data = $cl->getByWhere(array('cid' => $cid, 'lkey' => $key));
        $res = array_shift($data);
        if ($res['val'] >= $val)
        {
            return FALSE;
        }

        return TRUE;
    }

    public static function delLimit($cid, $key)
    {
        $cl = new Crm2_Limit();

        $where = array('cid' => $cid, 'lkey' => $key);

        return $cl->deleteByWhere($where);
    }

    public static function setLimit($cid, $key, $val = 1)
    {
        $now = date('Y-m-d H:i:s');
        $cl = new Crm2_Limit();

        $data = $cl->getByWhere(array('cid' => $cid, 'lkey' => $key));
        if (empty($data))
        {
            $info = array(
                'cid' => $cid, 'lkey' => $key, 'val' => $val, 'ctime' => $now,
            );

            $cl->add($info);
        }
        else
        {
            $item = array_shift($data);
            $cl->update($item['lid'], array(), array('val' => $val));
        }
    }

    /**
     * 获取一个销售人员的绩效相关数据
     */
    public static function calculatePerformanceOfSalesman($suid, $date)
    {
        $cp = new Crm2_Performance();

        $refundStat = $cp->getRefund($suid, $date, 'all');

        $performance = array(
            'total_customer' => $cp->getTotalCustomerNum($suid), 'total_order_customer' => $cp->getTotalOrderCustomerNum($suid, $date), 'new_order_customer' => $cp->getNewOrderCustomerNum($suid, $date), 'second_order_customer' => $cp->getSecondOrderCustomerNum($suid, $date), 'sales_amount' => $cp->getSalesAmount($suid, $date), 'spending_amount' => $cp->getSpendingAmount($suid, $date), 'call' => $cp->getCallNum($suid, $date), 'input' => $cp->getInputNum($suid, $date),
            'order' => $cp->getOrderNum($suid, $date), 'refund' => $refundStat['price'], 'refund_stat' => $refundStat,
        );

        return $performance;
    }

    /**
     * 查询某些销售人员,某段时间的绩效相关数据
     */
    public static function getPerformanceList(array $suids, $fromDate, $endDate)
    {
        $cp = new Crm2_Performance();
        $performanceList = $cp->getPerformanceList($suids, $fromDate, $endDate);

        foreach($performanceList as &$item)
        {
            $_staffInfo = $item['_suser'];
            $item['_suser'] = array(
                'suid' => $_staffInfo['suid'],
                'name' => $_staffInfo['name'],
                'city_id' => $_staffInfo['city_id'],
                'leader_suid' => $_staffInfo['leader_suid'],
            );
        }
        
        return $performanceList;
    }

    public static function getPerformanceMoreinfos($suids, $fromDate, $endDate)
    {
        $af = new Aftersale_Func();
        $afConf = array(
            'exec_status' => Conf_Aftersale::STATUS_AFTER_CREATE . ',' . Conf_Aftersale::STATUS_NEW, 'duty_department' => Conf_Admin::ROLE_SALES,
        );
        $afRes = $af->getNumBySuids($suids, $afConf);

        // 销售日程
        $css = new Crm2_Sale_Schedule();
        $cssWhere = sprintf('status=0 and schedule_time>="%s" and suid in (%s) group by suid', date('Y-m-d H:i:s'), implode(',', $suids));
        $cssRes = $css->getListByWhere($cssWhere, 0, 0, array('suid', 'count(1)'), FALSE);
        $cssRes = Tool_Array::list2Map($cssRes, 'suid', 'count(1)');

        // 销售拜访
        $ccv = new Crm2_Customer_Visit();
        $ccvWhere = sprintf('status=0 and visit_time>="%s 00:00:00" and visit_time<="%s 23:59:59"', $fromDate, $endDate);
        $ccvGroup = ' group by suid, visit_type';
        $ccvFields = array('suid', 'visit_type', 'count(1)');
        $_ccvRes = $ccv->getListByWhere($ccvWhere . $ccvGroup, 0, 0, $ccvFields, FALSE);
        $ccvRes = array();
        foreach ($_ccvRes as $one)
        {
            if (!isset($ccvRes[$one['suid']]))
            {
                $ccvRes[$one['suid']] = array('scene' => 0, 'un_scene' => 0);
            }

            if ($one['visit_type'] == Conf_Crm::VISIT_TYPE_SCENE)
            {
                $ccvRes[$one['suid']]['scene'] += $one['count(1)'];
            }
            else
            {
                $ccvRes[$one['suid']]['un_scene'] += $one['count(1)'];
            }
        }

        $res = array();
        foreach ($suids as $_suid)
        {
            $res[$_suid]['after_sales'] = isset($afRes[$_suid]) ? $afRes[$_suid] : 0;
            $res[$_suid]['schedules'] = isset($cssRes[$_suid]) ? $cssRes[$_suid] : 0;
            $res[$_suid]['visits'] = isset($ccvRes[$_suid]) ? $ccvRes[$_suid] : array('scene' => 0, 'un_scene' => 0);
        }

        return $res;
    }

    /**
     * 补充客户消费属性字段
     *
     * @param $customerList
     */
    public static function appendConsumeAttr(&$customerList)
    {
        foreach ($customerList as &$customer)
        {
            $_consumeStat = array();
            if ($customer['order_num'] > 0)
            {
                //消费频率
                $days = (strtotime($customer['last_order_date']) - strtotime($customer['first_order_date'])) / 86400;
                $days = $days / $customer['order_num'];
                if ($days > 0 && $customer['order_num'] > 1)
                {
                    $_consumeStat['order_inter'] = sprintf("%.1f", $days);
                }

                //客单价
                $price = $customer['total_amount'] / 100 / $customer['order_num'];
                if ($price > 0)
                {
                    $_consumeStat['price_per_order'] = intval($price);
                }
            }
            $customer['_consume'] = $_consumeStat;
        }
    }

    public static function getCustomerForCrm($suid, $admin = array(), $start = 0, $num = 20)
    {
        $result = array();

        if (empty($user))
        {
            $as = new Admin_Staff();
            $admin = $as->get($suid);
        }
        $searchConf = array();
        $searchConf['sales_suid'] = $suid;
        $searchConf['status'] = Conf_Base::STATUS_NORMAL;

        $cc = new Crm2_Customer();
        $customerData = $cc->search($searchConf, array('*'), $start, $num);
        $result['total'] = $customerData['total'];
        $result['list'] = array();
        $customerVisitMapping = array();
        if (!empty($customerData['data']))
        {
            $cu = new Crm2_User();
            $cids = Tool_Array::getFields($customerData['data'], 'cid');
            $users = $cu->getUsersOfCustomer($cids);
            $customerUsers = array();
            if (!empty($users))
            {
                foreach ($users as $user)
                {
                    if ($user['status'] != Conf_Base::STATUS_NORMAL || strpos($user['mobile'], 'H') === 0)
                    {
                        continue;
                    }
                    $cid = $user['cid'];
                    $customerUsers[$cid][] = array(
                        'cid' => $user['cid'], 'uid' => $user['uid'], 'name' => $user['name'], 'mobile' => $user['mobile'],
                    );
                }
            }

            $days15Ago = date('Y-m-d 00:00', strtotime('-15 days'));
            foreach ($customerData['data'] as $customer)
            {
                $arr = array(
                    'cid' => $customer['cid'], 'name' => $customer['name'], 'sub_list' => $customerUsers[$customer['cid']] ? $customerUsers[$customer['cid']] : array(), 'identity' => $customer['identity'], 'visit_info' => '最近无拜访记录',
                );

                $where = sprintf(' cid=%d', $customer['cid']);
                $start = $num = 0;
                $visitList = Crm2_Customer_Visit_Api::getListByWhereString($where, $start, $num);
                if (!empty($visitList))
                {
                    $hasVisit15Days = 0;
                    foreach ($visitList as $item)
                    {
                        if ($item['visit_time'] > $days15Ago)
                        {
                            $hasVisit15Days++;
                        }
                    }

                    if ($hasVisit15Days > 0)
                    {
                        $arr['visit_info'] = sprintf('最近15天拜访%d次', $hasVisit15Days);
                    }
                    else
                    {
                        $arr['visit_info'] = '最近15天无拜访记录';
                    }
                }

                $result['list'][] = $arr;
            }
        }

        return $result;
    }

    /*
     * 获取客户选中的推荐品牌信息
     */
    public static function getCustomerRecommendBrandByUid($uid, $city_id)
    {
        $kv = new Data_Kvdb();
        $customer_brand = $kv->get(sprintf(Conf_User::KEYTMPL_CUSTOMER_BRAND, $uid, $city_id));
        if (empty($customer_brand))
        {
            return array();
        }
        else
        {
            return $customer_brand;
        }
    }

    /*
     * 设置客户选中的推荐品牌信息
     */
    public static function saveCustomerRecommendBrandByUid($uid, $city_id, $brandList)
    {
        $kv = new Data_Kvdb();
        $customer_brand = $kv->get(sprintf(Conf_User::KEYTMPL_CUSTOMER_BRAND, $uid, $city_id));
        if (!empty($customer_brand))
        {
            return TRUE;
        }
        if (empty($brandList))
        {
            $brandList = 1;
        }

        return $kv->set(sprintf(Conf_User::KEYTMPL_CUSTOMER_BRAND, $uid, $city_id), $brandList);
    }

    /*
     * 删除客户的推荐材料信息
     */
    public static function delCustomerProductRelationByUid($uid, $pid)
    {
        $pr = new Crm2_User_Product_Relation();
        $product = Shop_Api::getProductInfo($pid);
        $res = $pr->set($uid, $pid, array('uid' => $uid, 'sid' => $product['sku']['sid'], 'pid' => $pid,'city_id' => $product['product']['city_id'], 'status' => Conf_Base::STATUS_DELETED));
        $cc = new Cart_Cart();
        $cc->delete($uid, $pid);

        return $res;
    }

    /*
     * 获取客户购买商品历史
     *
     */
    public static function getCustomerProductRelationByUid($uid, $city_id, $start = 0, $num = 20, $conf = array())
    {
        $pr = new Crm2_User_Product_Relation();
        $conf['city_id'] = $city_id;

        return $pr->getListByUid($uid, $start, $num, $conf);
    }

    /*
     * 获取客户不显示商品列表
     */
    public static function getDeleteCustomerProductRelationByUid($uid, $start = 0, $num = 20)
    {
        $pr = new Crm2_User_Product_Relation();

        return $pr->getDeleteListByUid($uid, $start, $num);
    }

    public static function getCustomerProductRelationTotalByUid($uid, $conf = array())
    {
        $pr = new Crm2_User_Product_Relation();

        return $pr->getTotalByUid($uid, $conf);
    }

    public static function getDefaultCate1ByUid($uid, $city_id)
    {
        $pr = new Crm2_User_Product_Relation();

        return $pr->getDefaultCate1ByUid($uid, $city_id);
    }

    /*
     * 获取客户默认推荐的品牌
     */
    public static function getCustomerDefaultRecommendBrandByUid($uid, $cate2, $city_id, $brandList, $recommend = '')
    {
        $bid = 0;
        //显示购买最多的品牌
        if ($uid > 0)
        {
            $pr = new Crm2_User_Product_Relation();
            $bid = $pr->getCateFirstBrandByUid($uid, $cate2, $city_id);
            if (!$bid)
            {
                $recommend = self::getCustomerRecommendBrandByUid($uid, $city_id);
                if (!empty($recommend) && $recommend['value'] != '1')
                {
                    $recommend = json_decode($recommend['value'], TRUE);
                    $cate1 = 0;
                    foreach (Conf_Sku::$CATE2 as $key => $cate2_list)
                    {
                        if (in_array($cate2, array_keys($cate2_list)))
                        {
                            $cate1 = $key;
                            break;
                        }
                    }
                    if ($cate1 > 0)
                    {
                        $bid_arr = Tool_Array::list2Map($recommend, 'categoryId');
                        foreach ($brandList as $brand)
                        {
                            if (in_array($brand['bid'], $bid_arr[$cate1]['brand']))
                            {
                                $bid = $brand['bid'];
                                break;
                            }
                        }
                    }
                }
            }
        }
        elseif (!empty($recommend) && $recommend != '1')
        {
            $recommend = json_decode($recommend, TRUE);
            $cate1 = 0;
            foreach (Conf_Sku::$CATE2 as $key => $cate2_list)
            {
                if (in_array($cate2, array_keys($cate2_list)))
                {
                    $cate1 = $key;
                    break;
                }
            }
            if ($cate1 > 0)
            {
                $bid_arr = Tool_Array::list2Map($recommend, 'categoryId');
                foreach ($brandList as $brand)
                {
                    if (in_array($brand['bid'], $bid_arr[$cate1]['brand']))
                    {
                        $bid = $brand['bid'];
                        break;
                    }
                }
            }
        }
        //推荐的品牌
        if (!empty($bid))
        {
            $top_data = Shop_Api::getTopCategoryBrandProduct($city_id);
            if (!isset($top_data[$cate2][$bid]) || empty($top_data[$cate2][$bid]))
            {
                $bid = 0;
            }
        }
        if(!empty($bid) && !in_array($bid, Tool_Array::getFields($brandList, 'bid')))
        {
            $bid = 0;
        }
        if (empty($bid))
        {
            $top_data = Shop_Api::getTopCategroyProduct($city_id);
            if (!empty($top_data[$cate2]))
            {
                $pids = Tool_Array::getFields($top_data[$cate2], 'pid');
                $product_list = Shop_Api::getProductInfos($pids);
                foreach ($top_data[$cate2] as $value)
                {
                    $product = $product_list[$value['pid']];
                    if ($product['sku']['bid'] > 0)
                    {
                        $bid = $product['sku']['bid'];
                        break;
                    }
                }
            }
            else
            {
                $bid = $brandList[0]['bid'];
            }
        }

        return $bid;
    }
    
    /**
     * 获取分配销售的客户列表.
     * 
     * @param $fromSuid
     * @param $toSuids
     * 
     * @return array('staff', 'list')
     */
    public static function distributionSalesCustomerList($fromSuid, $toSuids)
    {
        echo $fromSuid, "\n";
        if (empty($fromSuid) || empty($toSuids))
        {
            throw new Exception('参数错误');
        }
        
        if (!is_array($toSuids)) $toSuids = array($toSuids);
        
        $cc = new Crm2_Customer();
        $cWhere = 'sales_suid='. $fromSuid;
        $cField = array('cid', 'name');
        $toDealCustomerList = $cc->getListByWhere($cWhere, 'order by cid', 0, 0, $cField);
        
        if (empty($toDealCustomerList)) throw new Exception('该员工无客户');
        
        $distributions = array();
        $toSuidNum = count($toSuids);
        
        foreach($toDealCustomerList as $item)
        {
            $modVal = $item['cid'] % $toSuidNum;
            $distributions[$toSuids[$modVal]][] = $item;
        }
        
        return $distributions;
    }
    
    public static function execDistributionSalesCustomer($fromSuid, $toSuids)
    {
        $distributions = self::distributionSalesCustomerList($fromSuid, $toSuids);
        
        $cc = new Crm2_Customer();
        foreach ($distributions as $toSuid => $customerList)
        {
            $upData = array(
                'sale_status' => Conf_User::CRM_SALE_ST_PRIVATE,
                'chg_sstatus_time' => date('Y-m-d H:i:s'),
                'sales_suid' => $toSuid,
            );
            $upWhere = sprintf('cid in (%s)', getfiles($customerList, 'cid'));
            $cc->updateByWhere($upData, array(), $upWhere);
        }
    }
    
    /**
     * 通过cid获取用户信息
     * @author wangxuemin
     * @param int $cid
     * @param array $field
     * @return array|array
     */
    public static function getUsersOfCustomer($cid, $field = array('*'))
    {
        if (empty($cid)){
            return array();
        }
        $user = new Crm2_User();
        return $user->getUsersOfCustomer($cid, $field);
    }
}
