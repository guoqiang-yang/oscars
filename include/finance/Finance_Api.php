<?php
/**
 * 财务相关接口.
 */

class Finance_Api extends Base_Api
{
	
	/////////////////////  收款单记录  //////////////////////////////////
	
	public static function addMoneyInHistory($cid, $type, $price, $suid, $objid=0, $wid=0, $note='', $payType=0, $uid=0, $oid=0, $ctime='')
	{
		assert(!empty($cid));
		//assert(!empty($price));
		//assert(!empty($suid));

		//价格正负处理
		switch($type)
		{
			case Conf_Money_In::ORDER_PAIED:
            case Conf_Money_In::FINANCE_REFUND:
            case Conf_Money_In::ORDER_REFUND:
            case Conf_Money_In::CUSTOMER_AMOUNT_TRANSFER:
				$price = abs($price);
				break;
			case Conf_Money_In::REFUND_PAIED:
			case Conf_Money_In::FINANCE_INCOME:
            case Conf_Money_In::CUSTOMER_PRE_PAY:
            case Conf_Money_In::CUSTOMER_CASHBACK:
            case Conf_Money_In::CUSTOMER_DISCOUNT:
            case Conf_Money_In::AMOUNT_CUSTOMER_PAID:
            case Conf_Money_In::PLATFORM_SERVICE_FEE:
				$price = 0-abs($price);
				break;
			case Conf_Money_In::FINANCE_ADJUST;
			default:
				break;
		}

		$mi = new Finance_Money_In();
        
        // 如果是 客户的销售单记录，判断是否已经插入，如果插入了，不在执行插入
        if ($type == Conf_Money_In::ORDER_PAIED)
        {
            $_history = $mi->getByObjid($objid, Conf_Money_In::FINANCE_INCOME);
            if (!empty($_history))
            {
                //更新客户流失的仓库信息
                if($_history['wid'] == 0 && $wid!=0)
                {
                    $types = array(
                        //Conf_Money_In::ORDER_PAIED,
                        Conf_Money_In::FINANCE_INCOME,
                        Conf_Money_In::FINANCE_ADJUST,
                    );
                    $_where = sprintf('status=0 and type in (%s) and wid=0 and objid=%d',
                                    implode(',', $types), $objid);
                    $_info = array('wid' => $wid);
                    $mi->update($_where, $_info);
                }
                
                //return ;
            }
        }
        
		//写入数据
		$data = array(
			'cid' => $cid,
            'uid' => $uid,
			'objid' => $objid,
            'oid' => $oid,
			'wid' => $wid,
			'price' => $price,
			'type' => $type,
			'suid' => $suid,
			'note' => $note,
			'payment_type' => $payType,
            'ctime' => !empty($ctime)? $ctime: date('Y-m-d H:i:s'),
		);

		$insertRet = $mi->add($data);

		//写表成功
		if ($insertRet['id'])
		{
            //更新客户表的account_balance字段
            $cc =new Crm2_Customer();
            $update = array('account_balance' => $insertRet['insert_data']['amount']);
		    $customer = Crm2_Api::getCustomerInfo($cid);
            $customer = $customer['customer'];
            if($customer['contract_btime']<=date('Y-m-d') && $customer['contract_etime']>=date('Y-m-d'))
            {
                //还款日
                $or = new Order_Order();
                $where = sprintf('cid=%d AND status=%d AND step>=%d AND paid!=%d', $cid, Conf_Base::STATUS_NORMAL, Conf_Order::ORDER_STEP_PICKED, Conf_Order::HAD_PAID);
                $order = $or->getOrderByWhere($where,0,1, array('*'), array('ship_time', 'asc'));
                if(!empty($order))
                {
                    $info = current($order);
                    //结算日期之后的，说明已经结清了，是新的一轮逻辑了
                    if ($customer['payment_due_date'] > 0)
                    {
                            if($customer['contract_btime'] > 0 && $customer['payment_days'] > 0){
                                if($info['ship_time'] < $customer['contract_btime'] && date('Y-m-d',strtotime($info['ship_time']) + $customer['payment_days'] * 86400) > $customer['contract_btime'])
                                {
                                    $update['payment_due_date'] = $customer['contract_btime'];
                                }elseif($info['ship_time'] < $customer['contract_btime']){
                                    $update['payment_due_date'] = date('Y-m-d', strtotime($info['ship_time']) + $customer['payment_days'] * 86400);
                                }else{
                                    $_num = max(ceil(floor((strtotime($info['ship_time'])-strtotime($customer['contract_btime']))/86400)/$customer['payment_days']),1);
                                    $update['payment_due_date'] = date('Y-m-d', strtotime($customer['contract_btime'])+$_num*$customer['payment_days']*86400);
                                }
                            }else{
                                $update['payment_due_date'] = date('Y-m-d', strtotime($info['ship_time']) + $customer['payment_days'] * 86400);
                            }
                    }
                    else
                    {
                        if ($customer['account_balance'] > 0)
                        {
                            if($customer['contract_btime'] > 0 && $customer['payment_days'] > 0)
                            {
                                if($info['ship_time'] < $customer['contract_btime'] && date('Y-m-d',strtotime($info['ship_time']) + $customer['payment_days'] * 86400) > $customer['contract_btime'])
                                {
                                    $update['payment_due_date'] = $customer['contract_btime'];
                                }elseif($info['ship_time'] < $customer['contract_btime']){
                                    $update['payment_due_date'] = date('Y-m-d', strtotime($info['ship_time']) + $customer['payment_days'] * 86400);
                                }else{
                                    $_num = max(ceil(floor((strtotime($info['ship_time'])-strtotime($customer['contract_btime']))/86400)/$customer['payment_days']),1);
                                    $update['payment_due_date'] = date('Y-m-d', strtotime($customer['contract_btime'])+$_num*$customer['payment_days']*86400);
                                }
                            }else{
                                $update['payment_due_date'] = date('Y-m-d', strtotime($info['ship_time']) + $customer['payment_days'] * 86400);
                            }
                        }
                    }
                }else{
                    //已结清的用户要重置
                    if ($customer['payment_days'] > 0){
                        if($customer['contract_btime'] > 0 && date('Y-m-d')>=$customer['contract_btime']){
                            $_num = max(ceil(floor((time()-strtotime($customer['contract_btime']))/86400)/$customer['payment_days']),1);
                            $update['payment_due_date'] = date('Y-m-d', strtotime($customer['contract_btime'])+$_num*$customer['payment_days']*86400);
                        }elseif($customer['contract_btime'] > 0 && date('Y-m-d',time() + $customer['payment_days'] * 86400)>$customer['contract_btime']){
                            $update['payment_due_date'] = $customer['contract_btime'];
                        }else{
                            $update['payment_due_date'] = date('Y-m-d', time() + $customer['payment_days'] * 86400);
                        }
                    }elseif($customer['payment_due_date'] > 0)
                    {
                        $update['payment_due_date'] = 0;
                    }
                }
                if(isset($update['payment_due_date']) && $update['payment_due_date']!= $customer['payment_due_date'])
                {
                    $update['remind_count'] = 0;
                }
            }

			$cc->update($cid, $update);
		}
	}
	
	/**
	 * 客户清单列表.
	 */
	public static function getCustomBillList($searchConf, $start, $num)
	{
		$fmi = new Finance_Money_In();
		
		$fRet = $fmi->getCustomerBillList($searchConf, '', $start, $num);
		$suids = Tool_Array::getFields($fRet['list'], 'suid');
		$cids = Tool_Array::getFields($fRet['list'], 'cid');


		if (!empty($fRet['list']))
		{
			$cc = new Crm2_Customer();
			$customers = $cc->getBulk($cids);
			$customers = Tool_Array::list2Map($customers, 'cid');

			$as = new Admin_Staff();
			$AdminInfos = Tool_Array::list2Map($as->getUsers(array_unique($suids)), 'suid');
			$paymentTypes = Conf_Base::$PAYMENT_TYPES;
			
			foreach ($fRet['list'] as &$one)
			{
				$cid = $one['cid'];
				if (isset($customers[$cid]))
				{
					$one['_customer'] = $customers[$cid];
				}
				$one['payment_name'] = array_key_exists($one['payment_type'], $paymentTypes)? $paymentTypes[$one['payment_type']]: '';
				$one['suinfo'] = $AdminInfos[$one['suid']];
				$one['objUrl'] = ($one['type']==Conf_Money_In::ORDER_PAIED)? '/order/order_detail.php?oid='.$one['objid'] :
								($one['type']==Conf_Money_In::REFUND_PAIED? '/order/edit_refund_new.php?rid='.$one['objid'] : '');
                $one['city_name'] = !empty($one['city_id'])? Conf_City::$CITY[$one['city_id']]: '-';
			}
		}
		
		return $fRet;
	}	
    
    /**
     * 财务修改用户的单条 支付记录
     */
    public static function modifyCustomerSinglePaidRecord($id, $realAmount, $paymentType, $type)
    {
        $fmi = new Finance_Money_In();
        $history = $fmi->getById($id);
        
        if(empty($history))
        {
            throw new Exception('修改的对象为空');
        }
        
        if($history['type']!=$type || $type!=Conf_Money_In::FINANCE_INCOME)
        {
            throw new Exception('该类型的对象不能修改！！');
        }
        
        // @todo 修改余额支付的问题，想明白在处理
        
        if (!empty($history))
        {
            $accountBalanceDiff = 0;
            $amountDiff = $realAmount - abs($history['price']);
            if ($type == Conf_Money_In::FINANCE_REFUND)
            {
                $amountDiff = abs($history['price'])-$realAmount;
                $price = abs($realAmount);
            }
            else 
            {
                $amountDiff = $realAmount - abs($history['price']);
                $price = 0 - abs($realAmount);
            }
            
            // 更新money_in
            $upPriceWhere = 'id=' . $history['id'];
            $upDatas = array(
                'price' => $price,
                'payment_type' => $paymentType,
            );
            
            $fmi->update($upPriceWhere, $upDatas, array());

            $upAmountWhere = 'cid=' . $history['cid'] . ' and id>=' . $history['id'];
            $fmi->update($upAmountWhere, array(), array('amount' => 0-$amountDiff));
            $accountBalanceDiff = 0-$amountDiff;
            
            // 更新订单表的实付
            if ($type == Conf_Money_In::FINANCE_INCOME)
            {
                $oo = new Order_Order();
                $ochange = array(
                    'real_amount' => $amountDiff,
                );
                $upData = array(
                    'payment_type' => $paymentType,
                );
                $oid = $history['objid'];
                $oo->update($oid, $upData, $ochange);
            }
            
            if ($accountBalanceDiff != 0)
            {
                // 更新客户表账目数据
                $cc = new Crm2_Customer();
                $cid = $history['cid'];
                $change = array('account_balance' => $accountBalanceDiff);
                $cc->update($cid, array(), $change);
            }
        }
        
    }
    
    /**
     * 重新计算客户的财务列表, 并更新t_customer表中的字段 account_balance
     * 
     * @param int $cid
     */
    public static function rebuildCustomerFinancialHistory($cid)
    {
        $fmi = new Finance_Money_In();
        $searchConf = array(
            'cid' => $cid,
            'status' => Conf_Base::STATUS_NORMAL,
        );
        $order = 'order by id';
        $billList = $fmi->getCustomerBillList($searchConf, $order, 0, 0);
        
        if ($billList['total'] == 0 || empty($billList['list'])) return;
        
        $finalAmount = 0;
        foreach($billList['list'] as $bill)
		{
			$finalAmount += $bill['price'];
			$where = 'id='. $bill['id'];
			
			$info = array(
				'amount' => $finalAmount
			);
		
            $fmi->update($where, $info);
		}
        
        // 更新t_customer::account_balance
        $cc = new Crm2_Customer();
		$cinfo = array(
			'account_balance' => $finalAmount,
		);
        $cc->update($cid, $cinfo);
    }
    
	
	/////////////////////  付款单记录  //////////////////////////////////
	
	public static function addMoneyOutHistory($stockInInfo, $price, $type, $note, $suid, $payType=0, $paidSource=0)
	{
		// 获取供应商账户余额
		$ws = new Warehouse_Supplier();
		$supplierInfo = $ws->get($stockInInfo['sid']);
		
		switch ($type)
		{
			case Conf_Money_Out::PURCHASE_IN_STORE:	//采购入库 - 应付增加
				$price = abs($price); break;
			case Conf_Money_Out::FINANCE_PAID: //财务付款 - 应付减少 
            case Conf_Money_Out::FINANCE_PRE_PAY: //预付
				$price = 0 - abs($price); break;
            case Conf_Money_Out::STOCKIN_REFUND:    // 入库单退货出库
                $price = 0 - abs($price); break;
            
			case Conf_Money_Out::FINANCE_ADJUST: //财务调账 - 安装实际情况记录
			default :
				break;
		}
		
		$inserData = array(
			'sid' => $stockInInfo['sid'],
			'objid' => $stockInInfo['id'],
			'wid' => $stockInInfo['wid'],
			'price' => $price,
			'type' => $type,
			'payment_type' => $payType,
            'paid_source' => $paidSource,
			'suid' => $suid,
			'note' => $note,
			'amount' => $supplierInfo['account_balance']+$price,
		);
        
        if (!empty($stockInInfo['spec_xxx_ctime']))
        {
            $inserData['ctime'] = $stockInInfo['spec_xxx_ctime'];
        }
        
        // 入库退款单
        if ($type==Conf_Money_Out::STOCKIN_REFUND && isset($stockInInfo['srid']))
        {
            $inserData['objid'] = $stockInInfo['srid'];
        }
        
        // 预付采购单
        if ($type==Conf_Money_Out::FINANCE_PRE_PAY && isset($stockInInfo['oid']))
        {
            $inserData['objid'] = $stockInInfo['oid'];
        }
		
		$fmo = new Finance_Money_Out();
		$insertRet = $fmo->add($inserData);
		
		//写表成功, 更新客户表的account_balance字段
		if ($insertRet['id'])
		{
			$update = array('account_balance' => $inserData['amount']);
			$ws->update($inserData['sid'], $update);
		}
		
		return $insertRet['id']? true: false;
	}
	
	/**
	 * 供应商账单列表.
	 */
	public static function getSupplierBillList($search, $start, $num)
	{
		$fmo = new Finance_Money_Out();
		
		$fRet = $fmo->getSupplierBillList($search, '', $start, $num);
		$suids = Tool_Array::getFields($fRet['list'], 'suid');	//执行人
		$sids = Tool_Array::getFields($fRet['list'], 'sid');		//供应商


		if (!empty($fRet['list']))
		{
			$ws = new Warehouse_Supplier();
			$suppliers = $ws->getBulk($sids);
			$suppliers = Tool_Array::list2Map($suppliers, 'sid');

			$as = new Admin_Staff();
			$AdminInfos = Tool_Array::list2Map($as->getUsers(array_unique($suids)), 'suid');
			$paymentTypes = Conf_Stock::$PAYMENT_TYPES;
			
			foreach ($fRet['list'] as &$one)
			{
				$sid = $one['sid'];
				if (isset($suppliers[$sid]))
				{
					$one['_supplier'] = $suppliers[$sid];
				}
				$one['payment_name'] = array_key_exists($one['payment_type'], $paymentTypes)? $paymentTypes[$one['payment_type']]: '';
				$one['_operator'] = $AdminInfos[$one['suid']];
			}
		}
		
		return $fRet;
	}
    
    /**
     * 修改财务支出记录的金额.
     * 
     * @param int $supplierId   供应商id
     * @param int $objId        修改对象id，如入库单id
     * @param int $type         业务类型
     * @param int $finalPrice   需要修改的金额最终金额
     * @param int $priceDiff    需要修改的金额差值
     * @param int $isDel        是否需要删除该条记录
     */
    public static function modifyFinanceOutPrice($supplierId, $objId, $type, $finalPrice=0, $priceDiff=0, $isDel=false)
    {
        if (($finalPrice==0 && $priceDiff==0) || empty($objId) || empty($supplierId))
        {
            return;
        }
        
        $fmo = new Finance_Money_Out();
        $gwhere = 'sid='. $supplierId. ' and objid='. $objId. ' and type='. $type;
        $moneyOutInfo = $fmo->openGet($gwhere);
        
        if (count($moneyOutInfo)!=1)
        {
            return;
        }
        
        $_diffPrice = !empty($priceDiff)? $priceDiff: ($finalPrice-$moneyOutInfo[0]['price']);
        
        // 更新目标记录的price，amount
        $where = 'id='. $moneyOutInfo[0]['id'];
        
        if (!$isDel)
        {
            $change = array(
                'price' => $_diffPrice,
                'amount' => $_diffPrice,
            );
            $fmo->update($where, array(), $change);
        }
        else
        {
            $upData = array('status'=>Conf_Base::STATUS_DELETED);
            $fmo->update($where, $upData, array());
        }
        
        // 更新目标记录之后记录的所有amount值
        $_where = 'sid='. $supplierId. ' and id>'. $moneyOutInfo[0]['id'];
        $change = array(
            'amount' => $_diffPrice,
        );
        $fmo->update($_where, array(), $change);
        
        // 更新供应商的账户余额
        $ws = new Warehouse_Supplier();
        $change = array(
            'account_balance' => $_diffPrice,
        );
        $ws->update($supplierId, array(), $change);
    }
    
    
    public static function modifySupplierPaidSource($id, $newSource, $oldSource)
    {
        $fmo = new Finance_Money_Out();
        $where = 'status=0 and id='. $id;
        $moneyOutContent = $fmo->openGet($where);
        
        if (empty($moneyOutContent[0]) || $moneyOutContent[0]['paid_source']!=$oldSource)
        {
            return -1;
        }
        
        $upData = array('paid_source' => $newSource);
        
        // 更新money_out 表
        $fmo->update($where, $upData);
        
        // 更新入库单表 t_stock_in
        if ($moneyOutContent[0]['type']==Conf_Money_Out::FINANCE_PAID 
            && !empty($moneyOutContent[0]['objid']))
        {
            $ws = new Warehouse_Stock_In();
            $ws->update($moneyOutContent[0]['objid'], $upData);
        }
        
        return 1;
    }
    
    ////////////////////////// 第三方合作者 ////////////////////////////////////
    
    public static function getCoopworkerBillList($search, $start=0, $num=20, $order='')
    {
        $fcmo = new Finance_Coopworker_Money_Out();
        
        $coopworkerBillList = $fcmo->getList($search, $start, $num, $order);
        
        if (!empty($coopworkerBillList['data']))
        {
            self::_supplyCoopworkInfo($coopworkerBillList['data']);
        }
        
        return $coopworkerBillList;
    }
	
    // 支付第三方工人费用 （新逻辑，依据账号体系）
    public static function paidCoopworker($oid, $cuid, $type, $userType, $adminInfo, $payment_type=2)
    {
        // 取订单的对应工人信息
        $oc = new Logistics_Coopworker();
        $coopworkerOfOrder = $oc->getByOid($oid, $cuid, $type);
        
        $insertData = array(
            'cuid' => $cuid,
            'oid' => $oid,
            'wid' => $coopworkerOfOrder[0]['wid'],
            'price' => $coopworkerOfOrder[0]['price'],
            'type' => $type,
            'suid' => $adminInfo['suid'],
            'payment_type' => $payment_type,
            'user_type' => $userType,
            'note' => '',
        );
        
        $fcmo = new Finance_Coopworker_Money_Out();
        $fcmo->insert($insertData);
        
        return;
    }
    
    // 更新第三方工人费用
    public static function updateCoopworker($objId, $cuid, $type, $data, $objId=Conf_Coopworker::OBJ_TYPE_ORDER)
    {
        $where = "obj_id=$objId and cuid=$cuid and type=$type and obj_type = $objId";
        
        $fcmo = new Finance_Coopworker_Money_Out();
        $fcmo->update($where, $data);
    }   
    
    /**
     * 第三方工人的待支付列表.
     */
    public static function coopworkerWillpayList($cuid, $searchConf, $start=0, $num=20, $order='')
    {
        $oc = new Logistics_Coopworker();
        
        $ret = $oc->getByWorker($cuid, $searchConf, $start, $num, $order);
        
        self::_supplyCoopworkInfo($ret['data']);
        
        return $ret;
    }
    
    private static function _supplyCoopworkInfo(&$coopworkerList)
    {
        // 取司机搬运工的个人信息
        $driverIds = $carrierIds = array();
        foreach($coopworkerList as $bill)
        {
            if ($bill['user_type'] == Conf_Base::COOPWORKER_DRIVER)
            {
                $driverIds[] = $bill['cuid'];
            }
            else if ($bill['user_type'] == Conf_Base::COOPWORKER_CARRIER)
            {
                $carrierIds[] = $bill['cuid'];
            }
        }
        
        $driverInfos = $carrierInfos = array();
        if (!empty($driverIds))
        {
            $ld = new Logistics_Driver();
            $driverInfos = Tool_Array::list2Map($ld->getByDids($driverIds), 'did');
        }
        // 搬运工
        if (!empty($carrierIds))
        {
            $lc = new Logistics_Carrier();

            $carrierInfos = Tool_Array::list2Map($lc->getByCids($carrierIds), 'cid');
        }

        if (!empty($coopworkerList))
        {
            $as = new Admin_Staff();
            $suids = Tool_Array::getFields($coopworkerList, 'suid');	//执行人
            $AdminInfos = Tool_Array::list2Map($as->getUsers(array_unique($suids)), 'suid');

            foreach($coopworkerList as &$one)
            {
                $one['suinfo'] = $AdminInfos[$one['suid']];
                $one['worker'] = $one['user_type']==Conf_Base::COOPWORKER_DRIVER?
                        $driverInfos[$one['cuid']]: $carrierInfos[$one['cuid']];
                $one['worker']['link'] = $one['user_type']==Conf_Base::COOPWORKER_DRIVER?
                        '/logistics/add_driver.php?did='.$one['cuid']: '/logistics/add_carrier.php?cid='.$one['cuid'];
            }
        }
    }
    
    //****************************************** 客户余额 **********************************************//
    
    /**
     * 获取客户的账号财务余额列表.
     */
    public static function getCustomerAmountList($search, $start=0, $num=20, $order='')
    {
        $fca = new Finance_Customer_Amount();
        $amountList = $fca->getList($search, $start, $num, $order);
        
        if (!empty($amountList['data']))
        {
            $cids = $suids = array();
            foreach ($amountList['data'] as $_amountBill)
            {
                $cids[] = $_amountBill['cid'];
                $suids[] = $_amountBill['suid'];
                if (!empty($_amountBill['saler_suid']))
                {
                    $suids[] = $_amountBill['saler_suid'];
                }
            }

            $cc = new Crm2_Customer();
            $customers = Tool_Array::list2Map($cc->getBulk(array_unique($cids)), 'cid');

            $as = new Admin_Staff();
            $staffList = Tool_Array::list2Map($as->getUsers(array_unique($suids)), 'suid');

            $paymentTypes = Conf_Base::getPaymentTypes();
            foreach($amountList['data'] as &$_amountBill)
            {
                $_amountBill['objUrl'] = '';
                if ($_amountBill['type']==Conf_Finance::CRM_AMOUNT_TYPE_PAID)
                {
                    $_amountBill['objUrl'] = '/order/order_detail.php?oid='. $_amountBill['objid'];
                }
                else if ($_amountBill['type']==Conf_Finance::CRM_AMOUNT_TYPE_REFUND)
                {
                    $_amountBill['objUrl'] = '/order/edit_refund.php?rid='.$_amountBill['objid'];
                }

                $_amountBill['payment_name'] = $paymentTypes[$_amountBill['payment_type']];
                $_amountBill['_customer'] = array(
                    'name' => $customers[$_amountBill['cid']]['name'],
                    'contact_name' => $customers[$_amountBill['cid']]['contact_name'],
                    'phone' => $customers[$_amountBill['cid']]['phone'],
                );
                $_amountBill['_staff'] = array(
                    'name' => $staffList[$_amountBill['suid']]['name'],
                );
                
                if (!empty($_amountBill['saler_suid']) && array_key_exists($_amountBill['saler_suid'], $staffList))
                {
                    $_amountBill['_saler'] = array(
                        'name' => $staffList[$_amountBill['saler_suid']]['name'],
                    );
                }
                $_amountBill['city_name'] = !empty($_amountBill['city_id'])? Conf_City::$CITY[$_amountBill['city_id']]: '';
            }

        }
        
        return $amountList;
    }
    
    /**
     * 统计预付用户.
     */
    public static function statPrebuyCustomer($start=0, $num=50)
    {
        $minAmount = 500000;
        
        // 预存客户统计 && 客户列表
        $fca = new Finance_Customer_Amount();
        $where = sprintf('status=%d and type=%d and price>%d',
                    Conf_Base::STATUS_NORMAL, Conf_Finance::CRM_AMOUNT_TYPE_PREPAY, $minAmount);
        $group = ' group by cid';
        $sumField = array('sum(price)');
        $totalField = array('count(distinct cid)');
        $listField = array('*', 'sum(price) as sum_prices', 'count(1) as times');
        
        $sum = $fca->openGet($where, $sumField);
        $total = $fca->openGet($where, $totalField);
        $stat['total_price'] = $sum['data'][0]['sum(price)'];
        $stat['total_customers'] = $total['data'][0]['count(distinct cid)'];
        
        $list = $fca->openGet($where.$group, $listField, 'order by times desc', $start, $num);
        $customerList = $list['data'];
        
        $cc = new Crm2_Customer();
        $cc->appendInfos($customerList, 'cid');
        
        // 补充销售信息
		$as = new Admin_Staff();
        $salesSuids = array();
        foreach($customerList as &$oner)
        {
            if (!empty($oner['_customer']['sales_suid']))
            {
                $salesSuids[] = $oner['_customer']['sales_suid'];
            }
            $oner['_saler'] = array();
        }
        
        if(!empty($salesSuids))
        {
            $salesInfos = Tool_Array::list2Map($as->getUsers($salesSuids), 'suid');
            foreach($customerList as &$oner)
            {
                $oner['_saler'] = $salesInfos[$oner['_customer']['sales_suid']];
            }
        }
        // 预存客户的总剩余余额
        $crm2dao = new Data_Dao('t_customer');
        $_where = 'status=0 and cid in (select cid from t_customer_amount_history where '.$where. ')';
        $stat['available_balance'] = $crm2dao->getSum('account_amount', $_where);
      
        return array('stat'=>$stat, 'customer'=>$customerList);
    }


    /**
     * 记录客户的账务余额历史数据.
     * 
     * @param int $cid  客户id
     * @param array $data 
     *      type            [必选]  支付金额类型    见：Conf_Finance::$Crm_AMOUNT_TYPE_DESCS
     *      payment_type    [可选]  支付类型        见：Conf_Base::$PAYMENT_TYPES   
     *      suid            [必选]  操作人id
     *      price           [必选]  金额
     *      objid           [可选]  单据id（退款/支付销售单必须）
     *      oid             [可选]  销售单id
     *      note            [可选]  备注
     *      uid             [可选]  如果有明确的uid，必须填写
     * @return int
     */
    public static function addCustomerAmountHistory($cid, $data)
    {
        if (empty($cid) || empty($data) || empty($data['type'])
            || empty($data['suid']) || !isset($data['price']))
        {
            return -1;
        }

        if (!array_key_exists($data['type'], Conf_Finance::$Crm_AMOUNT_TYPE_DESCS))
        {
            return -2;
        }

        if ( (!isset($data['objid'])||empty($data['objid']))
             && ($data['type']==Conf_Finance::CRM_AMOUNT_TYPE_PAID || $data['type']==Conf_Finance::CRM_AMOUNT_TYPE_REFUND))
        {
            return -3;
        }

        $cc = new Crm2_Customer();
        $customer = $cc->get($cid);
        
        if (empty($customer) || $customer['status']!=Conf_Base::STATUS_NORMAL)
        {
            return -10;
        }
        
        $data['city_id'] = !empty($data['city_id'])? $data['city_id']: $customer['city_id'];
        
        if ($data['type'] == Conf_Finance::CRM_AMOUNT_TYPE_PAID)
        {
            $oo = new Order_Order();
            $order = $oo->get($data['objid']);
            $data['city_id'] = $order['city_id'];
        }
        else if ($data['type'] == Conf_Finance::CRM_AMOUNT_TYPE_REFUND)
        {
            $or = new Order_Refund();
            $refund = $or->get($data['objid']);
            $data['city_id'] = $refund['city_id'];
        }
        
        $data['cid'] = $cid;
        
        $fca = new Finance_Customer_Amount();
        $res = $fca->save($data);

        // 更新客户的余额
        if ($res['id'])
        {
            //更新客户表的account_amount字段
			$cc =new Crm2_Customer();
			//$update = array('account_amount' => $res['data']['amount']);
            
            $consumeFields = array('account_amount');
            if ($data['type']==Conf_Finance::CRM_AMOUNT_TYPE_PREPAY || $data['type']==Conf_Finance::CRM_AMOUNT_TYPE_PREPAY_ORDER)
            {
                $consumeFields[] = 'perpay_amount';
            }
            $consumeRes = Crm2_Stat_Api::statCustomerAmount4Customer($cid, $consumeFields);
            
            if (!empty($consumeRes))
            {
                $cc->update($cid, $consumeRes);
            }
        }
        
		if ($data['price'] > 0 && $data['type'] == Conf_Finance::CRM_AMOUNT_TYPE_PREPAY)
		{
			$customerInfo = Crm2_Api::getCustomerInfo($cid, true, false);
			$users = $customerInfo['users'];
			if (!empty($users))
			{
				foreach ($users as $user)
				{
					if ($user['is_admin'])
					{
//						$msg = sprintf('亲爱的工长，您已充值成功，请登录好材APP查询，如有问题请致电好材客服热线400-058-5788，感谢您对好材的支持，祝您生活愉快！');
//						Data_Sms::send($user['mobile'], $msg);
                        Data_Sms::sendNew($user['mobile'], Conf_Sms::RECHARGE_SUCC_KEY, array());
					}
				}

				//预充值客户，余额小于1000的时候短信提醒一下
//				if ($res['data']['amount'] < Conf_Finance::REMIND_MIN_AMOUNT)
//				{
//					$val = Crm2_Api::getLimit($cid, Crm2_Limit::$KEY_SEND_AMOUNT_MSG);
//					if ($val == Crm2_Limit::$VAL_NOT_SEND_AMOUNT_MSG)
//					{
//						foreach ($users as $user)
//						{
//							if ($user['is_admin'])
//							{
//								$amount = round(Conf_Finance::REMIND_MIN_AMOUNT / 100, 2);
//								$msg = sprintf('尊敬的VIP客户，您的余额不足%d元，请及时充值。现在充值享受多重优惠，详情咨询4001582400退订回t', $amount);
//
////								Data_Sms::send($user['mobile'], $msg);
//								//echo "TO {$user['mobile']}：", $msg;
//							}
//						}
//
//						Crm2_Api::setLimit($cid, Crm2_Limit::$KEY_SEND_AMOUNT_MSG, Crm2_Limit::$VAL_HAS_SEND_AMOUNT_MSG);
//					}
//				}
			}
		}


		//预充值客户，发余额通知短信
		if ($data['price'] < 0 && $data['type'] == Conf_Finance::CRM_AMOUNT_TYPE_PAID)
		{
			$where = sprintf(' cid=%d AND type=%d AND status=%d', $cid, Conf_Finance::CRM_AMOUNT_TYPE_PREPAY, Conf_Base::STATUS_NORMAL);
			$isPrePay = $fca->getTotalByWhere($where);
			if ($isPrePay > 0)
			{
				$customerInfo = Crm2_Api::getCustomerInfo($cid, true, false);
				$users = $customerInfo['users'];
				if (!empty($users))
				{
					foreach ($users as $user)
					{
						if ($user['is_admin'])
						{
							$price = round(abs($data['price']) / 100, 2);
							$amount = round($res['data']['amount'] / 100, 2);
//							$msg = sprintf('亲爱的工长，您已消费成功，余额%.2f元，请登录好材APP查询，如有问题请致电好材客服热线400-058-5788，感谢您对好材的支持，祝您生活愉快！', $amount);
//							Data_Sms::send($user['mobile'], $msg);
							Data_Sms::sendNew($user['mobile'], Conf_Sms::CONSUME_SUCC_KEY, array('balance' => $amount));

                            WeiXin_Message_Api::sendConsumeMessage($user['uid'], $data['objid'], $price);
						}
					}

					//预充值客户，余额小于1000的时候短信提醒一下
//					if ($res['data']['amount'] < Conf_Finance::REMIND_MIN_AMOUNT)
//					{
//						$val = Crm2_Api::getLimit($cid, Crm2_Limit::$KEY_SEND_AMOUNT_MSG);
//						if ($val == Crm2_Limit::$VAL_NOT_SEND_AMOUNT_MSG)
//						{
//							foreach ($users as $user)
//							{
//								if ($user['is_admin'])
//								{
//									$amount = round(Conf_Finance::REMIND_MIN_AMOUNT / 100, 2);
//									$msg = sprintf('尊敬的VIP客户，您的余额不足%d元，请及时充值。现在充值享受多重优惠，详情咨询4001582400退订回t', $amount);
//
////									Data_Sms::send($user['mobile'], $msg);
//									//echo "TO {$user['mobile']}：", $msg;
//								}
//							}
//
//							Crm2_Api::setLimit($cid, Crm2_Limit::$KEY_SEND_AMOUNT_MSG, Crm2_Limit::$VAL_HAS_SEND_AMOUNT_MSG);
//						}
//					}
				}
			}
		}

		//预充值客户，退款到余额发短信提醒
//		if ($data['price'] > 0 && $data['type'] == Conf_Finance::CRM_AMOUNT_TYPE_REFUND)
//		{
//			$where = sprintf(' cid=%d AND type=%d AND status=%d', $cid, Conf_Finance::CRM_AMOUNT_TYPE_PREPAY, Conf_Base::STATUS_NORMAL);
//			$isPrePay = $fca->getTotalByWhere($where);
//			if ($isPrePay > 0)
//			{
//				$customerInfo = Crm2_Api::getCustomerInfo($cid, true, false);
//				$users = $customerInfo['users'];
//				if (!empty($users))
//				{
//					foreach ($users as $user)
//					{
//						if ($user['is_admin'])
//						{
////							$msg = sprintf('亲爱的工长，您的订单已经完成退款，请您留意查收好材账户!如有问题请致电好材客服热线400-058-5788，感谢您对好材的支持，祝您生活愉快！');
////							Data_Sms::send($user['mobile'], $msg);
//                            Data_Sms::sendNew($user['mobile'], Conf_Sms::REFUND_SUCC_KEY, array());
//						}
//					}
//				}
//			}
//		}

		return $res;
	}
    
    
    /**
     * 重新计算客户的余额列表, 并更新t_customer表中的字段 account_amount
     * 
     * @param int $cid
     */
    public static function rebuildCustomerAmountHistory($cid)
    {
        $fca = new Finance_Customer_Amount();
        $searchConf = array(
            'cid' => $cid,
            'status' => Conf_Base::STATUS_NORMAL,
        );
        $order = 'order by id';
        $billList = $fca->getList($searchConf, 0, 0, $order);
        
        if ($billList['total'] == 0 || empty($billList['data'])) return;
        
        $finalAmount = 0;
        foreach($billList['data'] as $bill)
		{
			$finalAmount += $bill['price'];
			$where = 'id='. $bill['id'];
			
			$info = array(
				'amount' => $finalAmount
			);
		
            $fca->updateByWhere($where, $info);
		}
        
        // 更新t_customer::account_balance
        $cc = new Crm2_Customer();
		$cinfo = array(
			'account_amount' => $finalAmount,
		);
        $cc->update($cid, $cinfo);
    }

    /**
     * 获取客户未支付的订单
     *
     * @param type $cid
     * @param type $etime
     * @param type $discount
     */
    public static function getCustomerUnpaidOrderList($cid, $etime, $discount=0)
    {
        $response = array('order'=>array(), 'customer'=>array(), 'prices'=>array(), 'msg'=>'');

        if (empty($cid)||empty($etime))
        {
            $response['msg'] = '参数错误！';
            return $response;
        }

        // 获取客户信息
        $customerInfo = Crm2_Api::getCustomerInfo($cid, false, false);
        $response['customer'] = $customerInfo['customer'];

        // 获取客户的未结账订单
        $oo = new Order_Order();
        $where = sprintf('cid=%d and step>=%d and date(delivery_date)<=date("%s") and paid!=1 and status=0',
            $cid, Conf_Order::ORDER_STEP_PICKED, $etime);
        $orderBy = array('oid', 'asc');
        $total = 0;
        $orderList = $oo->getListRawWhere($where, $total, $orderBy, 0, 0);

        if ($total == 0)
        {
            $response['msg'] = '截止到【'. $etime.'】账单已经结清！';
            return $response;
        }
        $or = new Order_Refund();
        $oids = Tool_Array::getFields($orderList, 'oid');
        $refundList = $or->getListOfOrder($oids);
        $refundSummary = array();
        foreach($refundList as $_refund)
        {
            if ($_refund['step'] < Conf_Refund::REFUND_STEP_PAID)
            {
                continue;
            }

            if (!isset($refundSummary[$_refund['oid']]))
            {
                $refundSummary[$_refund['oid']] = array();
            }

            array_push($refundSummary[$_refund['oid']], $_refund['rid']);
        }
        // 计算订单价钱
        $orderPrices = array('summary'=>0, 'real_pay'=>0, 'discount_price'=>0, 'had_paid'=>0);
        foreach($orderList as &$_oinfo)
        {
            $baseRealPay = Order_Helper::calOrderNeedToPay($_oinfo);

            $_oinfo['bills'] = array(
                'price_price' => $_oinfo['price'],
                'freight' => $_oinfo['freight'],
                'privilege' => $_oinfo['privilege'],
                'customer_carriage' => $_oinfo['customer_carriage'],
                'refund_price' => $_oinfo['refund'],
                'refund_ids' => implode(',',$refundSummary[$_oinfo['oid']]),
                'had_paid' => $_oinfo['real_amount'],
                'will_real_pay' => floor($baseRealPay*(100-$discount)/100),
                'discount_price' => floor($baseRealPay*$discount/100),
            );

            $orderPrices['summary'] += $baseRealPay+$_oinfo['customer_carriage'];
            $orderPrices['real_pay'] += floor($baseRealPay*(100-$discount)/100);
            $orderPrices['discount_price'] += floor($baseRealPay*$discount/100);
            $orderPrices['had_paid'] += $_oinfo['real_amount'];
        }

        $response['order'] = $orderList;
        $response['prices'] = $orderPrices;

        return $response;
    }
    
    
    //****************************************** 账期客户 **********************************************//
    
    /**
     * 获取账期客户未支付的订单
     * 
     * @param type $cid
     * @param type $etime
     * @param type $discount
     */
    public static function getAccountCustomerUnpaidOrderList($cid, $etime, $discount=0)
    {
        $response = array('order'=>array(), 'customer'=>array(), 'prices'=>array(), 'msg'=>'');
        
        if (empty($cid)||empty($etime))
        {
            $response['msg'] = '参数错误！';
            return $response;
        }
        
        // 获取客户信息
        $customerInfo = Crm2_Api::getCustomerInfo($cid, false, false);
        if (empty($customerInfo['customer']['payment_days']))
        {
            $response['msg'] = '客户不是账期客户！';
            return $response;
        }
        $response['customer'] = $customerInfo['customer'];
        
        // 获取客户的未结账订单
        $oo = new Order_Order();
        $where = sprintf('cid=%d and step>=%d and date(delivery_date)<=date("%s") and paid!=1 and status=0',
                    $cid, Conf_Order::ORDER_STEP_PICKED, $etime);
        $orderBy = array('oid', 'asc');
        $total = 0;
        $orderList = $oo->getListRawWhere($where, $total, $orderBy, 0, 0);
        
        if ($total == 0)
        {
            $response['msg'] = '截止到【'. $etime.'】账单已经结清！';
            return $response;
        }
        
        // 获取退单
        $or = new Order_Refund();
        $oids = Tool_Array::getFields($orderList, 'oid');
        $refundList = $or->getListOfOrder($oids);
   
        $refundSummary = array();
        foreach($refundList as $_refund)
        {
            if ($_refund['step'] < Conf_Refund::REFUND_STEP_IN_STOCK)
            {
                continue;
            }
            
            if (!isset($refundSummary[$_refund['oid']]))
            {
                $refundSummary[$_refund['oid']] = array(
                    'rids' => array(),
                    'price' => 0,
                );
            }
            
            array_push($refundSummary[$_refund['oid']]['rids'], $_refund['rid']);
            $refundSummary[$_refund['oid']]['price'] += $_refund['price'];
        }
        
        // 计算订单价钱
        $orderPrices = array('summary'=>0, 'real_pay'=>0, 'discount_price'=>0, 'had_paid'=>0);
        foreach($orderList as &$_oinfo)
        {
            $refundPrice = 0;
            $refundIds = array();
            if (isset($refundSummary[$_oinfo['oid']]))
            {
                $refundIds = implode(',', $refundSummary[$_oinfo['oid']]['rids']);
                $refundPrice = $refundSummary[$_oinfo['oid']]['price'];
            }
            
            $baseRealPay = ($_oinfo['price']+$_oinfo['freight']-$_oinfo['privilege']-$refundPrice-$_oinfo['real_amount']);
            
            $_oinfo['bills'] = array(
                'price_price' => $_oinfo['price'],
                'freight' => $_oinfo['freight'],
                'privilege' => $_oinfo['privilege'],
                'customer_carriage' => $_oinfo['customer_carriage'],
                'refund_price' => $refundPrice,
                'refund_ids' => $refundIds,
                'had_paid' => $_oinfo['real_amount'],
                'will_real_pay' => floor($baseRealPay*(100-$discount)/100+$_oinfo['customer_carriage']),
                'discount_price' => floor($baseRealPay*$discount/100),
            );
            
            $orderPrices['summary'] += $baseRealPay+$_oinfo['customer_carriage'];
            $orderPrices['real_pay'] += floor($baseRealPay*(100-$discount)/100+$_oinfo['customer_carriage']);
            $orderPrices['discount_price'] += floor($baseRealPay*$discount/100);
            $orderPrices['had_paid'] += $_oinfo['real_amount'];
        }
        
        $response['order'] = $orderList;
        $response['prices'] = $orderPrices;
        
        return $response;
    }
    
    //****************************************** 平台客户结款 **********************************************//
    
    /**
     * 获取待支付的订单信息.
     * 
     * @param int $cid
     * @param array $oids
     */
    public static function getPlatformDebitInfo($cid, $oids)
    {
        $ret = array(
            'cinfo' => array(),
            'orders' => array(),
            'abnormal_orders' => array(),
            'can_pay' => false,
            'reason' => '非法操作，请查看异常订单列表',
            'prices' => array('total'=>0, 'need_pay'=>0, 'case_back'=>0, 'paid'=>0),
        );
        if (!array_key_exists($cid, Conf_Finance::$Platform_Debits))
        {
            return $ret;
        }
        
        $cidConfig = Conf_Finance::$Platform_Debits[$cid];
        
        // 获取用户信息
        $cc = new Crm2_Customer();
        $oo = new Order_Order();
        
        $ret['cinfo'] = $cc->get($cid);
        $orderInfos = $oo->getBulk($oids);
        
        $orderPrice = 0;
        $ret['can_pay'] = true;
        foreach($orderInfos as &$oinfo)
        {
            if ($oinfo['step'] < Conf_Order::ORDER_STEP_PICKED)
            {
                $ret['can_pay'] = false;
                $oinfo['_unpay_reason'] = '订单未出库';
                $ret['abnormal_orders'][] = $oinfo;
            }
            else if ($oinfo['paid'] == Conf_Order::HAD_PAID)
            {
                $ret['can_pay'] = false;
                $oinfo['_unpay_reason'] = '订单已经完全支付';
                $ret['abnormal_orders'][] = $oinfo;
            }
            else if ($oinfo['source'] != $cidConfig['order_scode'])
            {
                $ret['can_pay'] = false;
                $oinfo['_unpay_reason'] = '非'.$cidConfig['name'].'订单';
                $ret['abnormal_orders'][] = $oinfo;
            }
            else
            {
                if ($cidConfig['pay_coop_fee']) //支付全部费用
                {
                    $orderNeedPay = Order_Helper::calOrderNeedToPay($oinfo);
                }
                else    //只支付订单商品费用
                {
                    $coopFee = $oinfo['freight']+$oinfo['customer_carriage'];
                    $hadPaidProduct = max(0, $oinfo['real_amount']-$coopFee);
                    $orderNeedPay = $oinfo['price'] - $oinfo['privilege'] - $oinfo['refund'] - $hadPaidProduct;
                }
                
                $oinfo['_server_fee'] = floor($orderNeedPay*$cidConfig['rebate']);
                $oinfo['_need_pay'] = $orderNeedPay-$oinfo['_server_fee'];
                $ret['orders'][] = $oinfo;
                $orderPrice += $oinfo['_need_pay'];
                
                $ret['prices']['total'] += $orderNeedPay;
                $ret['prices']['need_pay'] += $oinfo['_need_pay'];
                $ret['prices']['case_back'] += $oinfo['_server_fee'];
                $ret['prices']['paid'] += $oinfo['real_amount'];
            }
        }
        
//        if ($ret['cinfo']['account_amount'] < $orderPrice)
//        {
//            $ret['can_pay'] = false;
//            $ret['reason'] = '账户余额不能支付全部订单';
//        }
        if ($orderPrice <= 0)
        {
            $ret['can_pay'] = false;
            $ret['reason'] = '待支付订单总金额异常';
        }
        
        return $ret;
    }
    
    /**
     * 平台结款 - 支付.
     * 
     * @rule
     *  - 如果有抹零（$moling>0), 余额+$moling 支付全部$oids订单
     *  - 如果没有抹零，可以以部分支付
     * 
     * @param int $cid
     * @param array $payInfos
     * @param int $suid
     */
    public static function paidPlatformDebit($cid, $payInfos, $suid)
    {
        if (!array_key_exists($cid, Conf_Finance::$Platform_Debits))
        {
            throw new Exception('非平台结款客户，不能结款');
        }
        $cidConfig = Conf_Finance::$Platform_Debits[$cid];
        
        $oids = Tool_Array::getFields($payInfos, 'oid');
        $cc = new Crm2_Customer();
        $oo = new Order_Order();
        $customer = $cc->get($cid);
        $orderInfos = $oo->getBulk($oids);
        
        if ($customer['account_amount'] <= 1000)
        {
            throw new Exception('账户没有余额，请充值后在支付！');
        }
        
        // check：是否可以支付
        $orderWaitPay = 0;
        foreach($orderInfos as &$oinfo)
        {
            if ($oinfo['step'] < Conf_Order::ORDER_STEP_PICKED)
            {
                throw new Exception('订单未出库: '. $oinfo['oid']);
            }
            else if ($oinfo['paid'] == Conf_Order::HAD_PAID)
            {
                throw new Exception('订单已经完全支付: '. $oinfo['oid']);
            }
            else if ($oinfo['source'] != $cidConfig['order_scode'])
            {
                throw new Exception('非'.$cidConfig['name'].'订单: '. $oinfo['oid']);
            }
            else
            {
                if ($cidConfig['pay_coop_fee']) //支付全部费用
                {
                    $orderNeedPay = Order_Helper::calOrderNeedToPay($oinfo);
                }
                else    //只支付订单商品费用
                {
                    $coopFee = $oinfo['freight']+$oinfo['customer_carriage'];
                    $hadPaidProduct = max(0, $oinfo['real_amount']-$coopFee);
                    $orderNeedPay = $oinfo['price']- $oinfo['privilege'] - $oinfo['refund'] - $hadPaidProduct;
                }
                
                // 服务费
                if ($payInfos[$oinfo['oid']]['paid']<floor($orderNeedPay*(1-$cidConfig['rebate'])) ) //部分支付
                {
                    $oinfo['_server_fee'] = floor($payInfos[$oinfo['oid']]['paid']/(1-$cidConfig['rebate'])*$cidConfig['rebate']);
                }
                else
                {
                    $oinfo['_server_fee'] = floor($orderNeedPay*$cidConfig['rebate']); 
                }
                
                $oinfo['_need_pay_total'] = $orderNeedPay;
                $oinfo['_need_pay'] = $orderNeedPay-$oinfo['_server_fee'];
                $oinfo['_wait_pay'] = $payInfos[$oinfo['oid']]['paid']-$payInfos[$oinfo['oid']]['moling'];
                $oinfo['_wait_moling'] = $payInfos[$oinfo['oid']]['moling'];
                $orderWaitPay += $oinfo['_wait_pay'];
                
                if (intval($oinfo['_wait_pay']+$oinfo['_wait_moling']) > intval($oinfo['_need_pay']) )
                {
                    throw new Exception("支付金额异常：\n订单：".$oinfo['oid']." \n欠款：".($oinfo['_need_pay']/100)
                            ." \n支付：".($oinfo['_wait_pay']/100)." \n抹零：".($oinfo['_wait_moling']/100));
                }
            }
        }
        
        if ($customer['account_amount']<$orderWaitPay)
        {
            throw new Exception('账户余额不能支付全部订单');
        }
        if ($orderWaitPay <= 0)
        {
            throw new Exception('待支付订单总金额异常');
        }
        
        // pay: 支付
        $accountAmount = $customer['account_amount'];
        foreach($orderInfos as $order)
        {
            if ($accountAmount <= 0)
            {
                break;
            }
            
            //1 支付订单
            $real_amount = $order['_wait_pay'];
            $serviceFee = $order['_server_fee'];

            $orderTotalPrice = Order_Helper::calOrderTotalPrice($order);
            
            // 计算支付情况
            if ($accountAmount >= $real_amount)
            {
                $accountAmount -= $real_amount;
            }
            else
            {
                $real_amount = $accountAmount;
                $serviceFee = floor($real_amount/(1-$cidConfig['rebate'])*$cidConfig['rebate']);
                $accountAmount = 0;
            }
            
            // 如果金额差0.5元，即为完全收款
            $paid = ($real_amount+$order['real_amount']+$serviceFee+$order['_wait_moling']+50)>=$orderTotalPrice?
                        Conf_Order::HAD_PAID: Conf_Order::PART_PAID;
            
            $upData = array('paid' => $paid);
            $change = array('real_amount' => $real_amount+$serviceFee+$order['_wait_moling']);
            $oo->update($order['oid'], $upData, $change);
            
            //2 财务流水
            //addMoneyInHistory($cid, $type, $price, $suid, $objid=0, $wid=0, $note='', $payType=0, $uid=0)
            $note1 = $cidConfig['name'].'余额支付订单';
            $note2 = $cidConfig['name'].'服务费';
            self::addMoneyInHistory($order['cid'], Conf_Money_In::FINANCE_INCOME, 
                $real_amount, $suid, $order['oid'], $order['wid'], $note1, Conf_Base::PT_BALANCE, $order['uid'], $order['oid']);
            self::addMoneyInHistory($order['cid'], Conf_Money_In::PLATFORM_SERVICE_FEE,
                $serviceFee, $suid, $order['oid'], $order['wid'], $note2, 0, $order['uid'], $order['oid']);
            
            // 抹零
            if ($order['_wait_moling']!=0)
            {
                $note3 = '抹零（单号：'.$order['oid'].')';
                self::addMoneyInHistory($order['cid'], Conf_Money_In::FINANCE_ADJUST, 0-abs($order['_wait_moling']), $suid, 
                        $order['oid'], $order['wid'], $note3, 0, $order['uid'], $order['oid']);
            }
            
            //3 从平台账号扣除余额
            $saveData = array(
                'type' => Conf_Finance::CRM_AMOUNT_TYPE_PAID,
                'price' => 0 - abs($real_amount),
                'payment_type' => Conf_Base::PT_BALANCE,
                'note' => '余额支付销售单：' . $order['oid'].' 工长CID/UID：'.$order['cid'].'/'.$order['uid'],
                'objid' => $order['oid'],
                'suid' => $suid,
                'uid' => 0,
                'oid' => $order['oid'],
            );

            self::addCustomerAmountHistory($cid, $saveData);
        }
    }
  
    
    /**
     * 通过订单id计算订单的费用明细.
     * 
     * @detail
     *      订单应付：货款+运费+搬运费-优惠 (t_order)
     *      退货应退：货款-少退+退搬运费+退运费-少腿优化    (t_refund)
     * 
     *      财务收款：(t_money_in_history)
     *      财务调账：
     *      账期客户支付：
     *      平台服务费：
     *      客户坏账：
     *      客户返点：
     * 
     *      客户余额转移：   (t_customer_amount_history)
     *      订单退款进余额：
     * 
     * @param type $oid
     */
    public static function calFinanceDetailByOrder($oid)
    {
        $detail = array();
        $billStep = array();
        
        // 订单退货单数据
        $oo = new Order_Order();
        $or = new Order_Refund();
        $orderInfo = $oo->get($oid);
        $refundInfos = $or->getListOfOrder($oid);
        
        $detail['订单应付'] = ($orderInfo['price']+$orderInfo['freight']+$orderInfo['customer_carriage']-$orderInfo['privilege']);
        $billStep['order'] = array(
            'cid' => $orderInfo['cid'],
            'uid' => $orderInfo['uid'],
            'wid' => $orderInfo['wid'],
            'city_id' => $orderInfo['city_id'],
            'paid' => $orderInfo['paid'],
            'step' => $orderInfo['step'],
            'payment_type' => $orderInfo['payment_type'],
            'delivery_date' => $orderInfo['delivery_date'],
        );
        
        $rids = array();
        $billStep['refund'] = array();
        foreach($refundInfos as $r)
        {
            $detail['退货应退-'.$r['rid']] = 0-($r['price']+$r['refund_carry_fee']+$r['refund_freight']-$r['refund_privilege']-$r['freight']-$r['carry_fee']);
            $rids[] = $r['rid'];
            
            $billStep['refund'][$r['rid']] = $r['paid'];
        }
        
        // 财务流水
        $fmi = new Finance_Money_In();
        $fmiTypes = array(
            Conf_Money_In::FINANCE_INCOME, 
            Conf_Money_In::FINANCE_ADJUST, 
            Conf_Money_In::CUSTOMER_DISCOUNT,
            Conf_Money_In::AMOUNT_CUSTOMER_PAID, 
            Conf_Money_In::PLATFORM_SERVICE_FEE, 
            Conf_Money_In::CUSTOMER_BAD_DEBT,
        );
        $fmiWhere = array(
            'status' => Conf_Base::STATUS_NORMAL,
            'objid' => $oid,                        //@todo 使用订单id筛选
        );
        $MoneyIns = $fmi->openGet($fmiWhere, array('price', 'type'));
        
        $fmiTypeDescs = Conf_Money_In::$STATUS_DESC;
        foreach($MoneyIns['data'] as $mi)
        {
            if (in_array($mi['type'], $fmiTypes))
            {
                $k = $fmiTypeDescs[$mi['type']];
                Tool_Func::setIntVal($detail, $k, 0-abs($mi['price']));
            }
        }
        
        // 客户余额
        $fca = new Finance_Customer_Amount();
        $fcaTypes = array(
            Conf_Finance::CRM_AMOUNT_TYPE_REFUND,
            Conf_Finance::CRM_AMOUNT_TRANSFER,
        );

        // @todo 使用oid替换
        $refundWhere = '';
        if (!empty($rids))
        {
            $refundWhere = sprintf(' or (objid in (%s) and type=%d)', implode(',', $rids), Conf_Finance::CRM_AMOUNT_TYPE_REFUND);
        }
        $fcaWhere = sprintf('status=0 and ((objid=%s and type=%d)  %s)',
            $oid, Conf_Finance::CRM_AMOUNT_TRANSFER, $refundWhere);
        $cAmounts = $fca->openGet($fcaWhere, array('type', 'price'));

        $fcaTypesDescs = Conf_Finance::$Crm_AMOUNT_TYPE_DESCS;
        foreach($cAmounts['data'] as $ca)
        {
            if (in_array($ca['type'], $fcaTypes))
            {
                $k = $fcaTypesDescs[$ca['type']];
                Tool_Func::setIntVal($detail, $k, abs($ca['price']));
            }
        }

        // 可转余额
        //$detail['can_trans_fee'] = $detail['财务收款'] + $detail['订单应付'] + $detail['财务转余额'];
        
        return array('detail'=>$detail, 'bill_step'=>$billStep);
    }
    
    public static function trans2AmountFromOrder($oid, $balance, $note, $suid)
    {
        $oo = new Order_Order();
        $orderInfo = $oo->get($oid);
        
        //余额从客户账务流水 转移到 客户余额流水中
        self::addMoneyInHistory(
            $orderInfo['cid'], 
            Conf_Money_In::CUSTOMER_AMOUNT_TRANSFER, 
            abs($balance), 
            $suid, 
            $oid, 
            $orderInfo['wid'], 
            '客户余额转移(备注：' . $note . ')', 
            Conf_Base::PT_BALANCE,
            $orderInfo['uid'],
            $oid
        );

        //插入客户账务余额流水
        $saveData = array(
            'type' => Conf_Finance::CRM_AMOUNT_TRANSFER,
            'price' => abs($balance),
            'payment_type' => Conf_Base::PT_BALANCE,
            'note' => '客户余额转移(备注：' . $note . ')',
            'objid' => $oid,
            'oid' => $oid,
            'uid' => $orderInfo['uid'],
            'suid' => $suid,
        );

        self::addCustomerAmountHistory($orderInfo['cid'], $saveData);
        $oo->update($oid,array('real_amount'=>$orderInfo['real_amount']-abs($balance)));
    }
}
