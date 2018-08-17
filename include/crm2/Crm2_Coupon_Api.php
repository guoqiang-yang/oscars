<?php
/**
 * CRM相关接口
 */
class Crm2_Coupon_Api extends Base_Api
{
	const TMPL_CASHBACK_SMS = '您的累计消费已满%s元(除砂石类)，为了感谢您的支持，好材送给您%d元代金券，已发到您的账户。详情请致电400-058-5788',
		TMPL_NEW_SMS = '您的订单已配送，感谢支持。好材送您%d元代金券，已发到您的账户。详情请致电 400-058-5788';

	/**
	 * 取用户的券列表
	 */
	public static function getCouponList($cid, $all = false, $audit = true, $used = false)
	{
		$cc = new Crm2_Coupon();
		$list = $cc->getOfCustomer($cid, $all, $audit, $used);

		$summary = array();
		foreach ($list as $key => $item)
		{
			list($date, $time) = explode(' ', $item['deadline']);
			$summary[$item['amount']]++;
			$list[$key]['_deadline'] = $date;
		}
		return array('list' => $list, 'summary' => $summary);
	}

	public static function createCouponsOnce($customer, $type, $couponList, $words="", $sms=true)
	{
		$ccp = new Crm2_Coupon();
		$csq = new Crm2_Sms_Queue();
		$cid = $customer['cid'];
		$couponAmount = 0;
		$now = date('Y-m-d H:i:s');

		//每种券，加到券列表
		foreach ($couponList as $amount => $num)
		{
			//每个券
			for ($i = 0; $i < $num; $i++)
			{
				$deadline = date('Y-m-d H:i:s', strtotime('+6 month'));
				$coupon = array(
					'cid' => $cid,
					'code' => self::_getCodeOfCustomer($cid),
					'amount' => $amount,
					'type' => $type,
					'deadline' => $deadline,
				);

				$couponAmount += $amount;
				$ccp->add($coupon);
				//$this->_trace('%d:%s, 优惠券:￥%d, 截止日期:%s',
				//	$cid, $customer['name'], $amount, $deadline);
			}
		}

		//加入短信队列
		if ($sms)
		{
			$phoneArray = array_unique(array_filter(explode(',', $customer['phone'])));
			foreach ($phoneArray as $phone)
			{
				if (!Str_Check::checkMobile($phone))
					continue;

				$smsInfo = array(
					'cid' => $cid, 'mobile' => $phone, 'words' => $words, 'ctime' => $now, 'mtime' => $now,
				);
				$csq->add($smsInfo);
			}
		}
	}

	public static function createCashbackCoupons($customer, $threshold, $couponList)
	{
		$ccp = new Crm2_Coupon();
		$csq = new Crm2_Sms_Queue();
		$cid = $customer['cid'];
		$couponAmount = 0;
		$now = date('Y-m-d H:i:s');

		//每种券，加到券列表
		foreach ($couponList as $amount => $num)
		{
			//每个券
			for ($i = 0; $i < $num; $i++)
			{
				$deadline = date('Y-m-d H:i:s', strtotime('+1 month'));
				$coupon = array(
					'cid' => $cid, 'code' => self::_getCodeOfCustomer($cid), 'amount' => $amount, 'type' => Conf_Coupon::TYPE_CASHBACK, 'deadline' => $deadline,
				);

				$couponAmount += $amount;
				$ccp->add($coupon);
				//$this->_trace('%d:%s, 优惠券:￥%d, 截止日期:%s',
				//	$cid, $customer['name'], $amount, $deadline);
			}
		}

		//加入短信队列
		$phoneArray = array_unique(array_filter(explode(',', $customer['phone'])));
		$threasholdStr = self::_getCashbackThreshold($threshold);
		$words = sprintf(self::TMPL_CASHBACK_SMS, $threasholdStr, $couponAmount);
		foreach ($phoneArray as $phone)
		{
			if (!Str_Check::checkMobile($phone))
				continue;

			$smsInfo = array(
				'cid' => $cid, 'mobile' => $phone, 'words' => $words, 'ctime' => $now, 'mtime' => $now,
			);
			$csq->add($smsInfo);
		}
	}

	public static function _getCashbackThreshold($threshold)
	{
		$wan = floor($threshold / 10000);
		$qian = ($threshold % 10000 >= 5000) ? 5 : 0;

		$str = '';
		if ($wan)
			$str .= sprintf('%d万', $wan);
		if ($qian)
			$str .= $qian ? ($wan ? $qian : $qian . '千') : '';
		return $str;
	}

	public static function _getCodeOfCustomer($cid)
	{
		$cc = new Crm2_Coupon();
		$list = $cc->getOfCustomer($cid, true);
		$codes = Tool_Array::list2Map($list, 'code');

		while (true)
		{
			$code = mt_rand(1000, 9999);
			if (empty($codes[$code]))
				return strval($code);
		}
		return '';
	}

	public static function createNewCustomerCoupons($customer, $orderAmount)
	{
		$ccp = new Crm2_Coupon();
		$csq = new Crm2_Sms_Queue();
		$cid = $customer['cid'];
		$couponAmount = 0;
		$now = date('Y-m-d H:i:s');

		//是否已经发过
		$coupons = $ccp->getOfCustomerType($cid, Conf_Coupon::TYPE_NEW);
		if (!empty($coupons)) return;

		//每种券，加到券列表
		if ($orderAmount > 3000) $orderAmount=3000;
		$couponList = Conf_Coupon::createNewCustomerCouponList($orderAmount);
		foreach ($couponList as $amount => $num)
		{
			//每个券
			for($i=0; $i<$num; $i++)
			{
				$deadline = date('Y-m-d H:i:s', strtotime('+1 month'));
				$coupon = array(
					'cid' => $cid,
					'code' => self::_getCodeOfCustomer($cid),
					'amount' => $amount,
					'type' => Conf_Coupon::TYPE_NEW,
					'deadline' => $deadline,
				);
				$couponAmount += $amount;
				$ccp->add($coupon);
			}
		}

		//加入短信队列
		if ($couponAmount>0)
		{
			$phoneArray = array_unique(array_filter(explode(',', $customer['phone'])));
			$words = sprintf(self::TMPL_NEW_SMS, $couponAmount);
			foreach($phoneArray as $phone)
			{
				if (!Str_Check::checkMobile($phone)) continue;

				$smsInfo = array(
					'cid' => $cid,
					'mobile' => $phone,
					'words' => $words,
					'ctime' => $now,
					'mtime' => $now,
				);
				$csq->add($smsInfo);
			}
		}

		return $couponList;
	}
	
	/**
	 * 取券列表页列表.
	 */
	public static function getCouponPageList($search, $sort, $adminInfo, $start=0, $num=20)
	{
		/* 角色判断
		if ($adminInfo['role']!=Conf_Admin::ROLE_SALES && $adminInfo['role']!=Conf_Admin::ROLE_ADMIN)
		{
			return array('total'=>0, 'data'=>array());
		}
		*/
		
		$where = '1=1';
		
		$customersOfSaler = array();
        $cc = new Crm2_Customer();
        
		if (Admin_Role_Api::hasRole($adminInfo, Conf_Admin::ROLE_SALES_NEW))
		{ 
            //销售
            $ccConf = array(
                'sales_suid'  => $adminInfo['suid'],
                'sale_status' => Conf_User::CRM_SALE_ST_PRIVATE,
            );
            $customerList = $cc->search($ccConf, array('cid'), 0, 0);
            $customersOfSaler = array_unique(array_keys($customerList['data']));
            
			if (empty($search['cid']) )
			{
				$where .= ' and cid in ('. implode(',', $customersOfSaler). ')';
			}
			else 
			{
				$where .= ' and cid='. (in_array($search['cid'], $customersOfSaler)? $search['cid']: 0);
			}
		} 
		else 
		{
			if (!empty($search['cid']))
			{
				$where .= ' and cid='. $search['cid'];
			}
		}
	
		if (!empty($search['type']) && array_key_exists($search['type'], Conf_Coupon::$couponTypes))
		{
			$where .= ' and type='. $search['type'];
		}
		if ($search['used'] != -1)
		{
			$where .= ' and used='. $search['used'];
		}
		
		$order = !empty($sort)? "order by $sort desc": '';
		
		$cCoupon = new Crm2_Coupon();
		$couponList = $cCoupon->getList($where, array('*'), $order, $start, $num);
        
        // 补充用户信息
		if ($couponList['total'])
		{
            $cc->appendInfos($couponList['data']);
		}
        
		return $couponList;
	}
	
	/**
	 * 申请优惠券 列表.
	 * 
	 * @param array $search
	 * @param int $start
	 * @param int $num
	 * @return array
	 */
	public static function getApplyCouponList($search, $start=0, $num=20)
	{
		// 取申请优惠券列表
		$cca = new Crm2_Coupon_Apply();
		$applyList = $cca->getList($search['sales_suid'], $search['status'], $total, $start, $num);
        
		if ($total != 0)
		{
			// 补充信息
			$cids = array();
			$adminIds = array();
			
			foreach($applyList as $one)
			{
				array_push($cids, $one['cid']);
				array_push($adminIds, $one['sales_suid']);
				
				if (!empty($one['admin_suid']))
				{
					array_push($adminIds, $one['admin_suid']);
				}
			}
			
			$cc = new Crm2_Customer;
			$cinfos = Tool_Array::list2Map($cc->getBulk(array_unique($cids)), 'cid');
			
			$as = new Admin_Staff();
			$adminInfos = Tool_Array::list2Map($as->getUsers(array_unique($adminIds)), 'suid');
			
			foreach($applyList as &$one)
			{
				$one['couponInfo'] = json_decode($one['coupons'], true);
				$one['customer_info'] = $cinfos[$one['cid']];
				$one['sales_info'] = $adminInfos[$one['sales_suid']];
				$one['admin_info'] = isset($adminInfos[$one['admin_suid']])? $adminInfos[$one['admin_suid']]: array();
			}
			
		}
        
		return array('total'=>$total, 'datas'=>$applyList);
	}


	/**
	 * 申请优惠券.
	 * 
	 * @param array $info
	 * @param array $adminInfo	管理员信息
	 */
	public static function applyCoupon($info, $adminInfo)
	{
		//$canApply = self::_canApplyCoupon($info, $adminInfo);
		
		$ret = 0;
		//if ($canApply)
		//{
			$cca = new Crm2_Coupon_Apply();
			$ret = $cca->add($info);
		//	}
		
		return $ret;
	}
	
	/**
	 * 是否有权申请优惠券.
	 * 
	 * @rules 申请优惠券的规则：
	 *	1. 管理员可以申请
	 *	2. 销售人员可以为自己的客户申请
	 * 
	 * @param array $info
	 * @param array $adminInfo	管理员信息
	 */
	private static function _canApplyCoupon($info, $adminInfo)
	{
		assert(isset($info['sales_suid']));
		assert(isset($info['cid']));
		
		$canApply = false;
        if (Admin_Role_Api::isAdmin($adminInfo['suid'], $adminInfo))
        { // 系统管理员
            $canApply = true;
        }
        else if (Admin_Role_Api::hasRole($adminInfo, Conf_Admin::ROLE_SALES_NEW))
        { // 销售人员
            $cc = new Crm2_Customer();
            $cinfo = $cc->get($info['cid']);

            $canApply = $cinfo['sales_suid']==$info['sales_suid']? true: false;
        }
		
		return $canApply;
	}
	
	/**
	 * 审批 申请的优惠券.
	 * 
	 * @param int $id 申请id
	 * @param int $status 审批状态
	 * @param array $adminInfo 操作员信息
	 */
	public static function auditApplyCoupon($id, $status, $adminInfo)
	{	
		// 更新优惠券申请表状态
		$cca = new Crm2_Coupon_Apply();
		$info = array(
			'status' => $status,
			'admin_suid' => $adminInfo['suid'],
		);
		$upRet = $cca->update($id, $info);
		
		// 注券逻辑
		if ($upRet && $status==Conf_Coupon::ST_PASS)
		{
			$applyInfo = $cca->get($id);
			
			$cc = new Crm2_Customer();
			$customer = $cc->get($applyInfo['cid']);
			
//			$as = new Admin_Staff();
//			$salerInfo = $as->get($applyInfo['sales_suid']);
			
			$coupons = json_decode($applyInfo['coupons'], true);
			$couponList = array($coupons['amount'] => $coupons['num']);
			$smsContent = sprintf(Conf_Coupon::$smsTemplate['apply_coupon'],
					$coupons['num']*$coupons['amount']);
			
			self::createCouponsOnce($customer, Conf_Coupon::TYPE_SALES, $couponList, $smsContent, true);
		}
		
	}
    
	
}
