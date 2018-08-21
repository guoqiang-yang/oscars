<?php
/**
 * 客户 - 客户信息相关
 */
class Crm2_Customer extends Base_Func
{
    /**
     * 地址分隔.
     */
    const ADDR_SEPARATE = '###';
    
    /**
     * 冗余数据分隔.
     */
    const REDUNDANT_SEPARATE = ',';
    
    private static $_instance = null;
    
    private $_dao = null;
    function __construct()
    {
        parent::__construct();
        
        $this->_dao = new Data_Dao('t_customer');
    }
    
    /**
     * 添加/注册 客户Customer.
     * 
     * @param array $regInfo
     * @return int 客户id
     */
    public function add($regInfo)
    {
        assert( !empty($regInfo) && is_array($regInfo) );
        
        // 补齐字段
        if (empty($regInfo['identity']))
        {
            $regInfo['identity'] = Conf_User::CRM_IDENTITY_PERSONAL;
        }
       
        if (empty($regInfo['note']))
		{
			$regInfo['note'] = '';
		}
        
        //如果没有city_id,默认使用北京的city_id
        if (!isset($regInfo['city_id']) || !Conf_City::isCityExist($regInfo['city_id']))
        {
            $regInfo['city_id'] = Conf_City::BEIJING;
        }

        if (empty($regInfo['chg_sstatus_time']))
        {
            $regInfo['chg_sstatus_time'] = date('Y-m-d H:i:s');
        }
        
        if (empty($regInfo['level_for_sys']) 
            && !array_key_exists($regInfo['level_for_sys'], Conf_User::$Customer_Sys_Level_Descs))
        {
            $regInfo['level_for_sys'] = Conf_User::CRM_SYS_LEVEL_COMMON;
        }
        
		$regInfo['ctime'] = $regInfo['mtime'] = date('Y-m-d H:i:s');
  
        $cid = $this->_dao->add($regInfo);
        
        return $cid;
    }
    
    /**
     * 更新客户信息.
     * 
     * @param int $cid
     * @param array $upData
     * @param array $chgData
     */
    public function update($cid, $upData, $chgData=array())
    {
        $_cid = intval($cid);
        assert( $_cid > 0);
        
        
        // 如果更新内容包括 销售人员，这将该客户放到该销售的私海
        if (!empty($upData['sales_suid']))
        {
            $upData['sale_status'] = Conf_User::CRM_SALE_ST_PRIVATE;
        }
        
        $affectedRows = $this->_dao->update($_cid, $upData, $chgData);
        
		return $affectedRows;
    }
    
    public function updateByWhere(array $upData, array $chgData, $where)
	{
        assert(!empty($upData)||!empty($chgData));
        assert(!empty($where));
        
        // 如果更新内容包括 销售人员，这将该客户放到该销售的私海
        if (!empty($upData['sales_suid']))
        {
            $upData['sale_status'] = Conf_User::CRM_SALE_ST_PRIVATE;
        }
        
        $affectedRow = $this->_dao->updateWhere($where, $_upData, $_chgData);
        
        return $affectedRow;
	}
    
    public function get($cid)
    {
        $_cid = intval($cid);
        assert( $_cid>0 );
        
        $customerInfo = $this->_dao->get($_cid);
        
        return $customerInfo;
    }
    
    public function getBulk($cids, $field=array('*'))
    {
        assert( !empty($cids) && is_array($cids) );
        
        $customerInfos = $this->_dao->setFields($field)->getList($cids);
        
        foreach($customerInfos as &$_customer)
        {
            $this->_parseCustomerAddress($_customer['address']);
        }
        
        return $customerInfos;
    }
    
    public function searchCustomerWithMobiles($mobiles)
    {
        assert(!empty($mobiles) && is_array($mobiles));
        
        foreach($mobiles as $mobile)
        {
            $_where[] = sprintf('all_user_mobiles like "%%%s%%"', $mobile);
        }
        
        $where = implode(' or ', $_where);
        
        $customerInfos = $this->_dao->getListWhere($where);
        
        foreach($customerInfos as &$_customer)
        {
            $this->_parseCustomerAddress($_customer['address']);
        }
        
        return $customerInfos;
    }
    
    // 对where条件做统计
    public function getTotalByWhere($where)
    {
        assert( !empty($where) );
        
        return $this->_dao->getTotal($where);
    }
    
    public function appendInfo(array &$item, $field='cid')
	{
        assert(!empty($item[$field]));
        
		$cid = $item[$field];
		$c = $this->get($cid);
        
		$item['_customer'] = $c;
	}

	public function appendInfos(array &$list, $field='cid')
	{
		if (empty($list))
        {
            return;
        }
        
		$cids = Tool_Array::getFields($list, $field);
		if (empty($cids)) 
        {
            return;
        }
        
		$customers = $this->getBulk($cids);

		foreach ($list as $idx => $item)
		{
			$cid = $item[$field];
			if (empty($customers[$cid])) 
            {
                continue;
            }
            
			$c = $customers[$cid];
			$list[$idx]['_customer'] = $c;
		}
        
	}
    
    /**
     * 统计销售的总欠款.
     */
    public function statBalanceDueBySales()
    {
        $field = array('sales_suid', 'sum(account_balance)/100 as bd');
        $where = 'status=0 and account_balance>0 group by sales_suid';
        
        $ret = $this->_dao->setFields($field)->getListWhere($where, false);
        
        return Tool_Array::list2Map($ret, 'sales_suid', 'bd');
    }
    
    /**
     * 取公司工长.
     */
    public function getBusinessCustomers($bid, $field=array('*'), $start=0, $num=0)
    {
        assert(!empty($bid));
        
        $where = array(
            'bid' => $bid,
            'status' => Conf_Base::STATUS_NORMAL,
        );
        
        $total = $this->_dao->getTotal($where);
        
        $list = array();
        if ($total != 0)
        {
            $list = $this->_dao->setFields($field)
                           ->limit($start, $num)
                           ->getListWhere($where);
        }
        
        return array('total'=>$total, 'data'=>$list);
    }
    
    
    /**
     * 按照进入公海的尺度搜索客户.
     * 
     * @param string $type
     * @param int $salerSuid
     */
    public function searchWithPublicScale($type, $salerSuid=0, $start=0, $num=20)
    {
        $field = array('*');
        $where = 'status=0';
        $order = 'cid';
        
        switch($type)
        {
            case 'no_order': //未下单客户维度 
                $diffTime = 'TIMESTAMPDIFF(DAY, chg_sstatus_time, now()) as diff_time';
                $where .= ' and date(last_order_date)<date(chg_sstatus_time)';
                $field = array('*', $diffTime);
                $order = 'diff_time';
                break;
            case 'no_rebuy': //未复购客户维度
                $where .= ' and date(last_order_date)>=date(chg_sstatus_time)';
                $diffTime = 'TIMESTAMPDIFF(DAY, last_order_date, now()) as diff_time';
                $field = array('*', $diffTime);
                $order = 'diff_time';
                break;
            default:
                break;
        }
        
        if (!empty($salerSuid))
        {
            $where .= ' and sales_suid='. $salerSuid;
        }
        
        $total = $this->_dao->getTotal($where);
        
        $list = array();
        if ($total != 0)
        {
            $list = $this->_dao
                        ->setFields($field)
                        ->order($order, 'desc')
                        ->limit($start, $num)
                        ->getListWhere($where);
        }
        
        return array('total'=>$total, 'data'=>$list);
    }

    public function getList($searchConf, $field = array('*'), $start = 0, $num = 20, $orderBy = 'cid', $sort = 'desc')
    {
        return $this->_dao
            ->setFields($field)
            ->order($orderBy, $sort)
            ->limit($start, $num)
            ->getListWhere($searchConf, false);
    }
    
    /**
     * 筛选.
     * 
     * @param array $searchConf
     * @param array $field
     * @param int $start
     * @param int $num     
     * @param string $orderBy
     * @param string $sort
     * 
     * @rule
     *  - 全职销售：默认查看自己的私海客户            staff_kind=1
     *  - 兼职销售：默认查看自己录入和自己经营的客户    staff_kind=2
     *  - 电话销售：
     *  - 网络销售：
     * 
     * @return
     *      array('total'=>'总条数', 'data'=>'数据')
     */
    public function search($searchConf, $field=array('*'), $start=0, $num=20, $orderBy='cid', $sort = 'desc')
    {
        $rWhere = $this->_getRestrictSearchCondition($searchConf);
        $unrWhere = $this->_getUnrestrictSearchCondition($searchConf);
        
        $where = $rWhere.' and '. $unrWhere;
        $total = $this->_dao->getTotal($where);
        
        $datas = array();
        if ($total > 0)
        {
            $orderBy = !empty($orderBy)? $orderBy: 'cid';
            $datas = $this->_dao
                    ->setFields($field)
                    ->order($orderBy, $sort)
                    ->limit($start, $num)
                    ->getListWhere($where);
        }
        
        // 数据重组
        if (count($field)==1 && $field[0]=='*')
        {
            foreach ($datas as &$_oner)
            {
                $this->_parseCustomerAddress($_oner['address']);

                $_oner['mobiles'] = array_slice(explode(self::REDUNDANT_SEPARATE, $_oner['all_user_mobiles']), 0, 2);
                $_oner['names'] = array_slice(explode(self::REDUNDANT_SEPARATE, $_oner['all_user_names']), 0, 2);
            }
        }
        
        return array('total'=>$total, 'data'=>$datas);
    }
    
    // 获取有限制的搜索条件
    private function _getRestrictSearchCondition($searchConf)
    {
        $where = ' 1=1 ';
  
        // 对于：sale_status - 搜索 自己/下属的私海 或 公海
        if (!empty($searchConf['sales_suid']))
        {
            if ($searchConf['sale_status'] == Conf_User::CRM_SALE_ST_PUBLIC)
            {
                $where .= sprintf(' and sale_status=%d', Conf_User::CRM_SALE_ST_PUBLIC);
            }
            else
            {
                $where .= sprintf(' and sale_status=%d', Conf_User::CRM_SALE_ST_PRIVATE);
            }
        }
        else if (!empty($searchConf['sale_status']))
        {
            $where .= sprintf(' and sale_status=%d', $searchConf['sale_status']);
        }
        
        if ($searchConf['sale_status'] !== Conf_User::CRM_SALE_ST_PUBLIC){
            if (!empty($searchConf['sales_suid']))
            {
                $where .= sprintf(' and sales_suid=%d', $searchConf['sales_suid']);
            }
        }

        //按城市
        if (!empty($searchConf['city_id']))
        {
            $where .= sprintf(' and city_id = %d', $searchConf['city_id']);
        }

        return $where;
    }
    
    // 获取无限制的搜索条件
    private function _getUnrestrictSearchCondition($searchConf)
    {
        $where = ' 1=1 ';
        $searchCommonKeys = array(
            'identity',
        );
        $searchKeywordKeys = array(
            'name', 'all_user_names', 'all_user_mobiles', 'address', 'note',
        );
        
        // 按cid
        if (!empty($searchConf['cid']))
        {
            $where .= ' and cid='. $searchConf['cid'];
            return $where;
        }
        
        // 按客户基本信息模糊匹配
        if (!empty($searchConf['keyword']))
        {
            $_where = '';
            foreach($searchKeywordKeys as $_key)
            {
                if (empty($_where))
                {
                    $_where = sprintf(' %s like "%%%s%%"', $_key, $searchConf['keyword']);
                }
                else
                {
                    $_where .= sprintf(' or %s like "%%%s%%"', $_key, $searchConf['keyword']);
                }
            }
            $where .= ' and ('. $_where. ')';
        } 
        else 
        {
            if (!empty($searchConf['mobile']))
            {
                $where .= sprintf(' and all_user_mobiles like "%%%s%%" ', $searchConf['mobile']);
            }
            if (!empty($searchConf['name']))
            {
                $where .= sprintf(' and (name like "%%%s%%" or all_user_names like "%%%s%%") ', 
                            $searchConf['name'], $searchConf['name']);
            }
        }
        
        // 客户的销售级别
        if (isset($searchConf['level_for_saler']) && $searchConf['level_for_saler']!=Conf_Base::STATUS_ALL)
        {
            if (is_array($searchConf['level_for_saler']))
            {
                // 私海查销售的全部客户，公海只是对于的销售级别的客户
                
                if (!empty($searchConf['sales_suid']) && $searchConf['sale_status'] == Conf_User::CRM_SALE_ST_PUBLIC)
                {
                    $where .= ' and level_for_saler in ('. implode(',', $searchConf['level_for_saler']).')';
                }
            }
            else
            {
                $where .= ' and level_for_saler='.$searchConf['level_for_saler'];
            }
        }
        
        // 常规搜索条件
        foreach($searchCommonKeys as $_key)
        {
            if (!empty($searchConf[$_key]))
            {
                $where .= " and $_key=". $searchConf[$_key];
            }
        }
        
        return $where;
    }
    
    
    /**
     * 更新customer中的冗余信息, 将user的信息冗余到customer表中.
     * 
     * @param int $cid  客户id
     *  通过cid查询当前绑定的所有的user.name, user.mobile 并更新customer中的冗余字段
     *      name    -> 合并到 all_user_names
     *      mobile  -> 合并到 all_user_mobiles
     */
    public function updateRedundantCustomerInfo($cid)
    {
        assert( !empty($cid) );
        
        $cu = new Crm2_User();
        
        $userInfos = $cu->getUsersOfCustomer($cid, array('uid', 'name', 'mobile'));
        
        $upDatas = array('name'=>array(), 'mobile'=>array());
        foreach($userInfos as $_uinfo)
        {
            if (!empty($_uinfo['name']))
            {
                $upDatas['name'][] = $_uinfo['name'];
            }
            $upDatas['mobile'][] = $_uinfo['mobile'];
        }
        
        $upData['all_user_names'] = implode(self::REDUNDANT_SEPARATE, $upDatas['name']);
        $upData['all_user_mobiles'] = implode(self::REDUNDANT_SEPARATE, $upDatas['mobile']);
        
        $affectedRows = $this->update($cid, $upData);
        
        return $affectedRows;
    }
    
    
    /**
     * 解析地址 - 显示使用， 去掉添加信息.
     * 
     * @param type $address
     */
    private function _parseCustomerAddress(&$address)
    {
        if (strpos($address, self::ADDR_SEPARATE) !== false)
        {
            $addrList = explode(self::ADDR_SEPARATE, $address);
            
            $address = $addrList[1];
        }
    }
    
    /**
     * 合并用户 - 合并用户涉及的相关的数据表.
     * 
     * @param int $masterCid
     * @param int $slaveCid
     */
    public function mergeCustomersForRelationTables($masterCid, $slaveCid)
    {
        if (empty($masterCid) || empty($slaveCid)) return;
        
        // 更新用户相关的数据表 不更新的数据表：t_sms_queue
        $tables = array('t_coupon', 't_coupon_apply','t_construction_site', 't_order', 't_weixin_customer',
            't_refund', 't_money_in_history', 't_customer_amount_history', 't_customer_tracking','t_cpoint_history', 't_cpoint_order', 't_cpoint_order_product');
        
        $upData = array('cid' => $masterCid);
        $where = 'cid='.$slaveCid;
        foreach($tables as $_table)
        {
            $this->one->update($_table, $upData, array(), $where);
        }
        
        return;
    }

    /**
     * 分离用户 - 分离用户涉及的相关的数据表.
     * 
     * @param int $fromCid  需要分离数据的客户的cid
     * @param int $toCid    接受数据的客户的cid
     * @param int $spUid    分离该用户uid的数据
     */
    public function separateCustomerForRelationTables($fromCid, $toCid, $spUid)
    {
        if (empty($fromCid) || empty($toCid) || empty($spUid))
        {
            return;
        }
        
        // 更新用户相关的数据表 不更新的数据表：t_sms_queue
        $tables = array('t_construction_site', 't_order','t_refund', 
            't_money_in_history', 't_customer_amount_history',);
        
        $upData = array('cid' => $toCid);
        $where = 'cid='. $fromCid. ' and uid='. $spUid;
        foreach($tables as $_table)
        {
            $this->one->update($_table, $upData, array(), $where);
        }
        
        // 删除微信对应关系，用户下次登录时会自动记录
        $this->one->delete('t_weixin_customer', 'cid='.$fromCid);
        
        return;
    }

    public function getListByWhere($where, $order, $start = 0, $num = 20, $fields = array('*'))
    {
        return $this->_dao->limit($start, $num)->order($order)->setFields($fields)->getListWhere($where);
    }

    ////////////////////////////  Old  ///////////////////////////////////////
    
	public function delete($cid)
	{
        
	}


	

	public function ifMobileOccupied($mobile)
	{
		
	}
    

	public function getByWhere($where, $fields)
	{
		return $this->_dao->setSlave()->setFields($fields)->order('last_order_date', 'desc')->getListWhere($where);
	}

	/**
     * 判断是否老客户
     */
	public function isOldCustomerByCid($cid)
    {
        $customer_info = $this->_dao->get($cid);
        if($customer_info['order_num']>1)
        {
            return true;
        }elseif($customer_info['order_num']==1){
            $oo = new Order_Order();
            $order_num = $oo->getTotalByWhere(' cid='.$cid.' AND step>=2 AND status=0');
            if($order_num>0)
            {
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    
    /**
     * 更新客户绑定的企业信息
     * @author wangxuemin
     * @param int $bid
     * @param array $params
     * @param mixed $cid array | int
     */
    public function updateBusinessData($bid, $params = array(), $cid = '')
    {
        // 同步企业和客户信息暂时关闭，运营调整中
        return;
        if (!empty($cid)){
            if (is_array($cid)){
                $this->_dao->updateWhere(array('cid' => $cid), $params);
            } else {
                $this->_dao->updateWhere("cid = {$cid}", $params);
            }
        } else {
            $this->_dao->updateWhere("bid = {$bid}", $params);
        }
    }
}
