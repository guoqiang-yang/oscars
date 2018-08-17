<?php
/**
 *  功能: 计算销售业绩相关
 *
 * @param
 *      total_customer: 客户数
 *      total_order_customer:
 *          all:  所有下单客户数
 *          paid: 支付订单客户数
 *      new_order_customer:
 *          all: 所有首单客户数
 *          paid: 首单支付客户数,
 *      second_order_customer: 新复购客户数
 *      sales_amount:
 *          all: 销售额
 *          pre: 预存额
 *          spending: 订单额
 *          withdraw: 余额提现
 *      spending_amount:
 *          all: 消费额
 *          paid: 已支付
 *          debt: 已回单未支付
 *          self: 自助下单
 *          haocai: 自有商品(OEM)
 *          not_haocai: 非自有商品
 *          new_customer: 新客
 *          vip_coupon_amount: vip
 *      call: 拜访数
 *      input: 录入线索数
 *      order: 确认订单数
 *      refund: 确认退款额
 *      refund_stat:
 *          price: 订单退款额
 *          num: 退款数
 *          refund: 退款额
 *
 */
class Crm2_Performance extends Base_Func
{
	private $customerDao;
	private $customerAmountDao;
	private $moneyInDao;
	private $orderDao;
	private $orderProductDao;
	private $refundDao;
	private $trackingDao;
	private $suserDao;

	function __construct()
	{
		parent::__construct();
		$this->customerDao = new Data_Dao('t_customer');
		$this->customerAmountDao = new Data_Dao('t_customer_amount_history');
		$this->moneyInDao = new Data_Dao('t_money_in_history');
		$this->orderDao = new Data_Dao('t_order');
		$this->orderProductDao = new Data_Dao('t_order_product');
		$this->refundDao = new Data_Dao('t_refund');
		$this->trackingDao = new Data_Dao('t_customer_tracking');
		$this->suserDao = new Data_Dao('t_staff_user');
        $this->orderPrivilegeDao = new Data_Dao('t_order_privilege');
	}

	/**
	 * 新下单客户数。 下单以客户确认为准。
	 * @param $suid
	 * @param $date
	 * @return int
	 * @todo 有种情况没处理:刚下首单的客户，分给某个销售
	 * @todo (1)当天分会算业绩
	 * @todo (2)第二天分,不算业绩,但是没有转移记录的话,可能造成误解
	 */
	public function getNewOrderCustomerNum($suid, $date)
	{
		$newOrderArr = array();

		//销售条件
		//$staff = Admin_Api::getStaff($suid);
        //$salersConf = ($staff['kind']==2) ?
        //	sprintf('cid in (select cid from t_customer where status=0 and sales_suid=%d or record_suid=%d)', $suid, $suid):
        //	sprintf('saler_suid=%d', $suid);
        $salersConf = sprintf('saler_suid=%d', $suid);

		//取该销售当天的订单
		$where = sprintf('status=0 and step>=2 and %s and ctime>="%s 00:00:00" and ctime<="%s 23:59:59"',
			$salersConf, $date, $date);
		$orders = $this->orderDao->setSlave()->getListWhere($where);
		foreach ($orders as $order)
		{
			$cid = $order['cid'];
			$oid = $order['oid'];

			//如果是首单,计数
			$w = sprintf('status=0 and step>=2 and cid=%d', $cid);
			$ordersOfCustomer = $this->orderDao->setSlave()->order('oid','asc')->limit(0,1)->getListWhere($w);
			$ordersOfCustomer = array_values($ordersOfCustomer);
			$firstOrder = $ordersOfCustomer[0];
			if ($firstOrder['oid'] == $oid)
			{
				$newOrderArr['all'][$cid] = 1;
				if ($order['paid']=1)
				{
					$newOrderArr['paid'][$cid] = 1;
				}
			}
		}

		$allNum = count($newOrderArr['all']);
		$paidNum = count($newOrderArr['paid']);
		$ret = array('all'=> $allNum, 'paid'=> $paidNum);
		return $ret;
	}

	/**
	 * 新复购客户数。 下单已实际付款为准。 复购是说第二次下单
	 * @param $suid
	 * @param $date
	 * @return int
	 * @todo 修改逻辑,从订单里查
	 */
	public function getSecondOrderCustomerNum($suid, $date)
	{
		$suidSql = sprintf("(sales_suid=%d)", $suid);
		$where = sprintf('status=0 and %s and second_order_date="%s"',
			$suidSql, $date);
		$num = $this->customerDao->setSlave()->getTotal($where);
		return $num;
	}

	/**
	 * 销售额。 预存款 + 订单额(非预存款, 实际收款额) - 余额账户提现
	 * @todo 如果销售专员变动,会将预存款计入到新的销售专员
	 * @todo 没有扣除运费&搬运费：一方面计算麻烦,一方面也让销售多少鼓励用户付运费搬运费
	 * @param $suid
	 * @param $date
	 * @return int
	 */
	public function getSalesAmount($suid, $date)
	{
		$suidSql = sprintf("(sales_suid=%d)", $suid);

		//计算预存额
		$field = 'price';
		$customerSql = sprintf('select cid from t_customer where %s and status=0',
			$suidSql);
		$where = sprintf('ctime>="%s 00:00:00" and ctime<="%s 23:59:59" and status=0 and type=%d and cid in (%s) and price>500000',
			$date, $date, Conf_Finance::CRM_AMOUNT_TYPE_PREPAY, $customerSql);
		$pre = $this->customerAmountDao->setSlave()->getSum($field, $where);
		$pre = abs($pre);

		//减去余额账户提现
		$field = 'price';
		$customerSql = sprintf('select cid from t_customer where %s and status=0',
			$suidSql);
		$where = sprintf('ctime>="%s 00:00:00" and ctime<="%s 23:59:59" and status=0 and type=%d and cid in (%s)',
			$date, $date, Conf_Finance::CRM_AMOUNT_TYPE_CASH, $customerSql);
		$withdraw = $this->customerAmountDao->setSlave()->getSum($field, $where);
		$withdraw = abs($withdraw);

		//计算订单额,不包括预存款
		//$cidSql = $this->_isParttime($suid) ? ("or cid in (select cid from t_customer where status=0 and record_suid=".$suid.")") : "";
		$cidSql = '';
		$orderSql = sprintf('select oid from t_order where (saler_suid=%d %s) and status=0 and delivery_date>"%s"',
			$suid, $cidSql, date('Y-m-d 00:00:00', strtotime($date)-86400*90));
		$where = sprintf('ctime>="%s 00:00:00" and ctime<="%s 23:59:59" and status=0 and type=%d and payment_type<>%d and objid in (%s)',
			$date, $date, Conf_Money_In::FINANCE_INCOME, Conf_Base::PT_BALANCE, $orderSql);
		$field = 'price';

		$spending = $this->moneyInDao->setSlave()->getSum($field, $where);
		$spending = abs($spending);

		$ret = array(
			'all' => ($pre+$spending-$withdraw) / 100,
			'pre' => $pre/100,
			'spending' => $spending/100,
			'withdraw' => $withdraw/100,
		);
		return $ret;
	}

	/**
	 * 消费额。 客户付款的订单
	 * @param $suid
	 * @param $date
	 * @return int
	 */
	public function getSpendingAmount($suid, $date)
	{
		//$cidSql = $this->_isParttime($suid) ? ("or cid in (select cid from t_customer where status=0 and record_suid=".$suid.")") : "";
        $cidSql = '';

		//已支付
		$where = sprintf('(saler_suid=%d %s) and ctime>="%s 00:00:00" and ctime<="%s 23:59:59" and status=0 and paid=1',
			$suid, $cidSql, $date, $date);
		$field = 'real_amount';
		$paid = $this->orderDao->setSlave()->getSum($field, $where);
		$paid = $paid / 100;

		//已确认未支付
		$where = sprintf('(saler_suid=%d %s) and ctime>="%s 00:00:00" and ctime<="%s 23:59:59" and status=0 and step>=%d and paid<>1',
			$suid, $cidSql, $date, $date, Conf_Order::ORDER_STEP_SURE);
		$field = 'price+freight+customer_carriage-privilege';
		$notPaid = $this->orderDao->setSlave()->getSum($field, $where);
		$notPaid = $notPaid / 100;
		$all = $paid + $notPaid;

		//已回单未支付
		$where = sprintf('(saler_suid=%d %s) and ctime>="%s 00:00:00" and ctime<="%s 23:59:59" and status=0 and step=%d and paid<>1',
			$suid, $cidSql, $date, $date, Conf_Order::ORDER_STEP_FINISHED);
		$field = 'price+freight+customer_carriage-privilege-refund-real_amount';
		$debt = $this->orderDao->setSlave()->getSum($field, $where);
		$debt = $debt / 100;

		//自助下单
		$where = sprintf('(saler_suid=%d %s) and ctime>="%s 00:00:00" and ctime<="%s 23:59:59" and status=0 and step>=%d and source>0',
			$suid, $cidSql, $date, $date, Conf_Order::ORDER_STEP_SURE);
		$field = 'price+freight+customer_carriage-privilege';
		$self = $this->orderDao->setSlave()->getSum($field, $where);
		$self = $self / 100;

		//自有商品
		$oidWhere = sprintf('(select oid from t_order where (saler_suid=%d %s) and ctime>="%s 00:00:00" and ctime<="%s 23:59:59" and step>=%d and status=0)',
			$suid, $cidSql, $date, $date, Conf_Order::ORDER_STEP_SURE);
		$sidWhere = sprintf('(select sid from t_sku where bid=%d)', Conf_Sku::HAOCAI_BRAND_ID);
		$where = sprintf('oid in %s and sid in %s and status=0 and rid=0', $oidWhere, $sidWhere);
		$field = 'price*num';
		$haocai = $this->orderProductDao->setSlave()->getSum($field, $where);
		$haocai = $haocai / 100;

		//非自有商品
		$sidWhere = sprintf('(select sid from t_sku where bid=%d)', Conf_Sku::HAOCAI_BRAND_ID);
		$where = sprintf('oid in %s and sid not in %s and status=0 and rid=0', $oidWhere, $sidWhere);
		$field = 'price*num';
		$notHaocai = $this->orderProductDao->setSlave()->getSum($field, $where);
		$notHaocai = $notHaocai / 100;

		//新客
		$thisMonth = date("Y-m", strtotime($date));
		$cidWhere = sprintf('select cid from t_customer where status=0 and first_order_date>="%s-01 00:00:00" and first_order_date<="%s-31 23:59:59"', $thisMonth, $thisMonth);
		$where = sprintf('(saler_suid=%d %s) and ctime>="%s 00:00:00" and ctime<="%s 23:59:59" and status=0 and step>=%d and cid in (%s)',
			$suid, $cidSql, $date, $date, Conf_Order::ORDER_STEP_SURE, $cidWhere);
		$field = 'price+freight+customer_carriage-privilege';
		$new = $this->orderDao->setSlave()->getSum($field, $where);
		$new = $new / 100;

        //vip现金券
        $where = sprintf('type=%d and oid IN (select oid from t_order where (saler_suid=%d %s) and ctime>="%s 00:00:00" and ctime<="%s 23:59:59" and status=0 and (paid=1 or (step>=%d and paid<>1)))',
            Conf_Privilege::$TYPE_COUPON_VIP,$suid, $cidSql, $date, $date, Conf_Order::ORDER_STEP_SURE);
        $field = 'amount';
        $vip = $this->orderPrivilegeDao->setSlave()->getSum($field, $where);
        $vip = $vip / 100;
        $all += $vip;
		//返回
		$ret = array('all'=> $all, 'paid'=> $paid, 'debt'=> $debt, 'self' => $self,
			'haocai'=>$haocai, 'not_haocai'=>$notHaocai, 'new_customer' => $new, 'vip_coupon_amount' => $vip);
		return $ret;
	}

	/**
	 * 拜访数
	 * @param $suid
	 * @param $date
	 * @return int
	 */
	public function getCallNum($suid, $date)
	{
		$where = sprintf('type=%d and edit_suid=%d and ctime>="%s 00:00:00" and ctime<="%s 23:59:59"',
			1, $suid, $date, $date);
		$num = $this->trackingDao->setSlave()->getTotal($where);
		return $num;
	}

	/**
	 * 录入线索数
	 * @param $suid
	 * @param $date
	 * @return num
	 */
	public function getInputNum($suid, $date)
	{
		$where = sprintf('status=0 and record_suid=%d and ctime>="%s 00:00:00" and ctime<="%s 23:59:59"',
			$suid, $date, $date);
		$num = $this->customerDao->setSlave()->getTotal($where);
		return $num;
	}

	/**
	 * 订单数。注意：这个是以客服确认为准，不是以付款为准
	 *
	 * @param $suid
	 * @param $date
	 * @return num
	 */
	public function getOrderNum($suid, $date)
	{
		$where = sprintf('saler_suid=%d and ctime>="%s 00:00:00" and ctime<="%s 23:59:59" and status=0 and step>=%d',
			$suid, $date, $date, Conf_Order::ORDER_STEP_SURE);
		$num = $this->orderDao->setSlave()->getTotal($where);
		return $num;
	}

	/**
	 * 退款额。注意：这个是以客服确认为准，不是以付款为准
	 *
	 * @param $suid
	 * @param $date
     * @param $fileds {price: 退货额；num: 退货量；all: 全部}
     * 
	 * @return int
	 */
	public function getRefund($suid, $date, $field='price')
	{
		$oidSql = sprintf('select oid from t_order where status=0 and saler_suid=%d', $suid);
		$where = sprintf('status=0 and ctime>="%s 00:00:00" and ctime<="%s 23:59:59" and oid in (%s) and paid in(%d, %d)',
			$date, $date, $oidSql, Conf_Refund::HAD_AUDIT, Conf_Refund::HAD_PAID);
		$fields = array('sum(price)', 'count(1)');
		//$num = $this->refundDao->setSlave()->getSum($fields, $where);
        $res = $this->refundDao->setSlave()->setFields($fields)->getListWhere($where, false);

        $where = sprintf('status=0 and step>=1 and saler_suid=%d and ctime>="%s 00:00:00" and ctime<="%s 23:59:59" and refund>0',
            $suid, $date, $date);
        $fields = array('sum(refund)');
        $refundRet = $this->orderDao->setFields($fields)->getListWhere($where, false);

        if ($field == 'price')
        {
            return $res[0]['sum(price)']/100;
        }
        else if ($field == 'num')
        {
            return $res[0]['count(1)'];
        }
        else
        {
            return array(
                'price' => $res[0]['sum(price)']/100,
                'num' => $res[0]['count(1)'],
                'refund' =>$refundRet[0]['sum(refund)'] / 100,
            );
        }
	}


	/**
	 * 查询某些销售人员,某段时间的绩效相关数据
	 */
	public function getPerformanceList(array $suids, $fromDate, $endDate)
	{
		if (empty($suids)) return array();
        
		//获取数据
		$values = $this->getPerformancesFromKVDB($fromDate, $endDate);

		//过滤
		$performanceList = array();
		foreach($values as $value)
		{
			$key = $value['name'];
			list($pre, $date, $suid) = explode('_', $key);
			if (!in_array($suid, $suids)) continue;
			if ($date>$endDate || $date<$fromDate) continue;

			$performance = json_decode($value['value'], true);
			$performance['suid'] = $suid;
			if (!isset($performanceList[$suid]))
			{
				$performanceList[$suid] = $performance;
			}
			else
			{
                $performanceList[$suid]['total_customer'] = $performance['total_customer'];
                $performanceList[$suid]['total_order_customer']['all'] += $performance['total_order_customer']['all'];
                $performanceList[$suid]['total_order_customer']['paid'] += $performance['total_order_customer']['paid'];
				$performanceList[$suid]['new_order_customer']['all'] += $performance['new_order_customer']['all'];
				$performanceList[$suid]['new_order_customer']['paid'] += $performance['new_order_customer']['paid'];
				$performanceList[$suid]['second_order_customer'] += $performance['second_order_customer'];
				$performanceList[$suid]['sales_amount']['all'] += $performance['sales_amount']['all'];
				$performanceList[$suid]['sales_amount']['pre'] += $performance['sales_amount']['pre'];
				$performanceList[$suid]['sales_amount']['spending'] += $performance['sales_amount']['spending'];
				$performanceList[$suid]['sales_amount']['withdraw'] += $performance['sales_amount']['withdraw'];
				$performanceList[$suid]['spending_amount']['all'] += $performance['spending_amount']['all'];
				$performanceList[$suid]['spending_amount']['paid'] += $performance['spending_amount']['paid'];
				$performanceList[$suid]['spending_amount']['debt'] += $performance['spending_amount']['debt'];
				$performanceList[$suid]['spending_amount']['self'] += $performance['spending_amount']['self'];
				$performanceList[$suid]['spending_amount']['new_customer'] += $performance['spending_amount']['new_customer'];
				$performanceList[$suid]['spending_amount']['haocai'] += $performance['spending_amount']['haocai'];
				$performanceList[$suid]['spending_amount']['not_haocai'] += $performance['spending_amount']['not_haocai'];
                $performanceList[$suid]['spending_amount']['vip_coupon_amount'] += $performance['spending_amount']['vip_coupon_amount'];
				$performanceList[$suid]['call'] += $performance['call'];
				$performanceList[$suid]['input'] += $performance['input'];
				$performanceList[$suid]['order'] += $performance['order'];
				$performanceList[$suid]['refund'] += $performance['refund'];
                $performanceList[$suid]['refund_stat']['price'] += $performance['refund_stat']['price'];
                $performanceList[$suid]['refund_stat']['num'] += $performance['refund_stat']['num'];
                $performanceList[$suid]['refund_stat']['refund'] += $performance['refund_stat']['refund'];
			}
		}

		//补充信息
		$as = new Admin_Staff();
		$as->appendSuers($performanceList);
		return $performanceList;
	}

    public function getTotalCustomerNum($suid)
    {
        //销售条件
        //$staff = Admin_Api::getStaff($suid);
        //$where = ($staff['kind'] == 2) ?
        //    sprintf('status=0 and (sales_suid=%d or record_suid=%d)', $suid, $suid):
        //    sprintf('status=0 and sales_suid=%d', $suid);
        $where = sprintf('status=0 and sales_suid=%d', $suid);
        $total = $this->customerDao->setSlave()->getTotal($where);

        return intval($total);
    }

    public function getTotalOrderCustomerNum($suid, $date)
    {
        $newOrderArr = array();

        //销售条件
        //$staff = Admin_Api::getStaff($suid);
        //$salersConf = ($staff['kind']==2) ?
        //    sprintf('cid in (select cid from t_customer where status=0 and sales_suid=%d or record_suid=%d)', $suid, $suid):
        //    sprintf('saler_suid=%d', $suid);
        $salersConf = sprintf('saler_suid=%d', $suid);

        //取该销售当天的订单
        $where = sprintf('status=0 and step>=2 and %s and ctime>="%s 00:00:00" and ctime<="%s 23:59:59"',
                         $salersConf, $date, $date);
        $orders = $this->orderDao->setSlave()->getListWhere($where);
        foreach ($orders as $order)
        {
            $cid = $order['cid'];
            $newOrderArr['all'][$cid] = 1;
            if ($order['paid']=1)
            {
                $newOrderArr['paid'][$cid] = 1;
            }
        }

        $allNum = count($newOrderArr['all']);
        $paidNum = count($newOrderArr['paid']);
        $ret = array('all'=> $allNum, 'paid'=> $paidNum);

        return $ret;
    }

	public function getPerformancesFromKVDB($fromDate, $endDate)
	{
		$kvdb = new Data_Kvdb();
		$values = array();

		// 取数据
		$len = strlen(Conf_User::KEYTMPL_SALESMAN_PERFORMANCE)-3;
		$format = substr(Conf_User::KEYTMPL_SALESMAN_PERFORMANCE, 0, $len);
		if ($fromDate == $endDate)
		{
			$key = sprintf($format, $endDate);
			$values = $kvdb->getByPrefix($key);
		}
		else
		{
			$monthList = array();
			for ($date = $fromDate; $date<=$endDate; $date=date('Y-m-d', strtotime($date)+86400))
			{
				$month = substr($date, 0, 7);
				$monthList[$month] = 1;
			}
			foreach ($monthList as $month=> $tmp)
			{
				$key = sprintf($format, $month);
				$res = $kvdb->getByPrefix($key);
				$values = array_merge($values, $res);
			}
		}

		return $values;
	}

	private function _isParttime($suid)
	{
		$suser = $this->suserDao->get($suid);
		if (Admin_Role_Api::hasRole($suser, Conf_Admin::ROLE_SALES_NEW) &&
			$suser['kind']==Conf_Admin::JOB_KIND_PARTTIME)
		{
			return true;
		}
		return false;
	}
}
