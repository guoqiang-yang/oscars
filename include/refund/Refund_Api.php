<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 16/7/22
 * Time: 09:26
 */

class Refund_Api extends Base_Api
{
    public static function getCustomerRefundList($cid, $uid, $start = 0, $num = 20)
    {
        $or = new Order_Refund();
        $conf = array();
        $conf['cid'] = $cid;
        $conf['uid'] = $uid;

        $list = $or->getList($conf, $total, $start, $num, 'all');
        $hasMore = $total > $start + $num;

        //补充退款运费搬运费
        $rids = Tool_Array::getFields($list, 'rid');
        $refundFeeList = self::getRefundFeeByRids($rids);

        foreach ($list as &$item)
        {
            $rid = $item['rid'];
            $item['freight'] = $refundFeeList[$rid]['freight'];
            $item['carry_fee'] = $refundFeeList[$rid]['carry_fee'];

            if ($item['paid'] == Conf_Order::HAD_PAID)
            {
                $item['refund_step_show'] = Conf_Refund::$REFUND_STEP_TITLE[Conf_Refund::REFUND_STEP_PAID];
                $item['refund_desc'] = Conf_Refund::$REFUND_STEP_DESC[Conf_Refund::REFUND_STEP_PAID];
            }
            else
            {
                if ($item['status'] == Conf_Base::STATUS_UN_AUDIT)
                {
                    $item['refund_step_show'] = Conf_Refund::$REFUND_STEP_TITLE[Conf_Refund::REFUND_REJECTED];
                    $item['refund_desc'] = Conf_Refund::$REFUND_STEP_DESC[Conf_Refund::REFUND_REJECTED];
                }
                else
                {
                    $item['refund_step_show'] = Conf_Refund::$REFUND_STEP_TITLE[$item['step']];
                    $item['refund_desc'] = Conf_Refund::$REFUND_STEP_DESC[$item['step']];
                }
            }
        }

        return array('list' => $list, 'total' => $total, 'has_more' => $hasMore);
    }
    
    /**
     * 附加商品退货信息.
     * 
     * @param array $orderProducts 订单商品表
     * @param array $refundProducts 退单商品表 key=rid#sid
     * 
     * @return append_field {can_refund_vnum, can_refund_vnum}
     */
    public static function appendRefundInfo4OrderProducts(&$orderProducts, $refundProducts)
    {
        foreach($orderProducts as $pid => &$pinfo)
        {
            $pinfo['has_refund_num'] = 0;
            foreach($refundProducts as $rkey => $rpinfo)
            {
                list($_rid, $_pid) = explode('#', $rkey);
                
                if ($_pid!=$pid || $rpinfo['num']==0) continue;
                
                $hasRefundNum = $rpinfo['picked'] + $rpinfo['damaged_num'];
                $pinfo['has_refund_num'] += $hasRefundNum>0? $hasRefundNum: $rpinfo['apply_rnum'];
            }
            // 可空退数量
            $pinfo['can_refund_vnum'] = max($pinfo['vnum']-$pinfo['refund_vnum']-$pinfo['tmp_inorder_num'], 0);
            $pinfo['can_refund_vnum'] = min($pinfo['can_refund_vnum'], $pinfo['num']-$pinfo['has_refund_num']);
        }
    }

	public static function updateRefundStepNew($staff, $rid, $nextStep, $productStr, $optype = '')
	{
		$or = new Order_Refund();
		$refund = $or->get($rid);
		$step = $refund['step'];
		$suid = $staff['suid'];

		//检查用户是否能操作这个修改
		$stepRes = Order_Helper::getRefundNextStep($staff, $step);
		if ($stepRes['next_step'] != $nextStep)
		{
			throw new Exception('common:permission denied');
		}

        if (!empty($nextStep))
        {
            $info = array('step' => $nextStep, 'oid' => $refund['oid']);
        }
        else
        {
            $info = array('oid' => $refund['oid']);
        }

        if ($nextStep == Conf_Refund::REFUND_STEP_SURE)
        {
            $info['audit_time'] = date('Y-m-d H:i:s');
        }

		// 如果入库, 则直接加库存 - 退货入库
		if ($nextStep == Conf_Refund::REFUND_STEP_IN_STOCK && $step < Conf_Refund::REFUND_STEP_IN_STOCK)
		{
            //锁库判断
            $lockedRet = Conf_Warehouse::isLockedWarehouse($refund['wid']);
            if ($lockedRet['st'])
            {
                throw new Exception($lockedRet['msg']);
            }
            
			if (empty($refund['wid']))
			{
				throw new Exception('order:empty wid');
			}
			//判断是否换货单生成的退货单
			$exchangedList = Exchanged_Api::getExchangedList(array('refund_id'=>$rid));
            if($exchangedList['total']>0)
            {
                $exchangedInfo = reset($exchangedList['list']);
                //判断是否需要入库，不入库就直接变上架
                if($exchangedInfo['need_storage']==0)
                {
                    $info['step'] = Conf_Refund::REFUND_STEP_SHELVED;
                    $info['adjust'] = $refund['price'];
                    $info['paid'] = Conf_Refund::HAD_PAID;
                    Order_Api::updateRefund($rid, $info);
                    $refundProducts = $or->getProductsOfRefund($rid);
                    foreach($refundProducts as $product)
                    {
                        $or->updateRefundProductWithInsert($refund['oid'], $product['pid'], $rid, array('picked' => $product['num'], 'damaged_num'=>0));
                    }
                    //判断对应的补单是否已完成，如果完成换货单状态自动变为已完成
                    $orderInfo = Order_Api::getOrderInfo($exchangedInfo['aftersale_oid']);
                    if($orderInfo['step']== Conf_Order::ORDER_STEP_FINISHED)
                    {
                        Exchanged_Api::updateExchanged($exchangedInfo['eid'],array('step'=>Conf_Exchanged::EXCHANGED_STEP_FINISHED));
                        Admin_Api::addOrderActionLog($suid, $exchangedInfo['oid'], Conf_Order_Action_Log::ACTION_FINISHED_EXCHANGED_ORDER, array('eid'=>$exchangedInfo['eid']));
                    }
                    return true;
                }else{
                    //判断对应的补单是否已完成，如果完成换货单状态自动变为已完成
                    $orderInfo = Order_Api::getOrderInfo($exchangedInfo['aftersale_oid']);
                    if($orderInfo['step']== Conf_Order::ORDER_STEP_FINISHED)
                    {
                        Exchanged_Api::updateExchanged($exchangedInfo['eid'],array('step'=>Conf_Exchanged::EXCHANGED_STEP_FINISHED));
                        Admin_Api::addOrderActionLog($suid, $exchangedInfo['oid'], Conf_Order_Action_Log::ACTION_FINISHED_EXCHANGED_ORDER, array('eid'=>$exchangedInfo['eid']));
                    }
                }
            }

			$ws = new Warehouse_Stock();
			$wsh = new Warehouse_Stock_History();
			$sp = new Shop_Product();
			$wl = new Warehouse_Location();
			$or = new Order_Refund();
			$wid = $refund['wid'];
            
            $products = $or->getProductsOfRefund($rid);
			if (empty($products))
			{
				return array();
			}
            
			//获取原库存
			$sids = array();
			foreach ($products as &$p)
			{
				if (empty($p['sid'])) //防止t_order_product sid 为0
				{
					$pinfo = $sp->get($p['pid']);
					$sids[] = $pinfo['sid'];
					$p['sid'] = $pinfo['sid'];
				}
				else
				{
					$sids[] = $p['sid'];
				}
			}

			$oldStocks = Tool_Array::list2Map($ws->getBulk($wid, $sids), 'sid');
            $_refundProductByPid = Tool_Array::list2Map($products, 'pid');  //@todo 需要重构
            
			//保存入库数量
			$items = array_filter(explode(',', $productStr));
			foreach ($items as $item)
			{
				list($pid, $stockinNum, $damageNum) = explode(":", $item);
				$stockinNum = intval($stockinNum);  //入库数量
                $damageNum = intval($damageNum);    //损坏数量
                
				if (empty($pid) || ($stockinNum+$damageNum)==0)
				{
					continue;
				}

                $_upRefundProduct = array(
                    'picked' => $stockinNum, 
                    'damaged_num' => $damageNum,
                    'outsourcer_id' => $oldStocks[$_refundProductByPid[$pid]['sid']]['outsourcer_id'],  //入库时实时计算，考虑异库退货情况
                );
                
                $or->updateRefundProductWithInsert($refund['oid'], $pid, $rid, $_upRefundProduct);
			}
            
            //重新获取更新后的退单商品
            $products = $or->getProductsOfRefund($rid);
			
			$location = Conf_Warehouse::$Virtual_Flags[Conf_Warehouse::VFLAG_ORDER_REFUND]['flag'];

            $needShelved = false; //是否需要上架
            $data4CostFIFO = array();
			foreach ($products as $product)
			{
                $willStockNum = $product['picked']+$product['damaged_num'];
                $sid = $product['sid'];
                
                // 外包商退货不变更库存
                if ($willStockNum <= 0 || $oldStocks[$sid]['outsourcer_id']>0)
                {
                    continue;
                }

                //入库
                $change = array('num'=>$willStockNum, 'damaged_num'=>$product['damaged_num']);
                $ws->save($wid, $sid, array(), $change);
                
                // Cost-FIFO数据
                $data4CostFIFO[] = array('sid'=>$product['sid'], 'num'=>$willStockNum, 'cost'=>$product['cost']);

                // 保存历史记录
                $history = array(
                    'old_num' => isset($oldStocks[$sid]) ? $oldStocks[$sid]['num'] : 0,
                    'old_occupied' => isset($oldStocks[$sid]) ? $oldStocks[$sid]['occupied'] : 0,
                    'num' => $willStockNum,
                    'occupied' => 0,
                    'iid' => $rid,
                    'suid' => $suid,
                    'type' => Conf_Warehouse::STOCK_HISTORY_REFUND_IN,
                );
                $wsh->add($wid, $sid, $history);

                // 损坏数量 上的残品储位
                if ($product['damaged_num'] > 0)
                {
                    $wl = new Warehouse_Location();
                    $wl->add(Conf_Warehouse::$Virtual_Flags[Conf_Warehouse::VFLAG_DAMAGED]['flag'], $wid, $sid, $product['damaged_num']);
                }

                // 入库商品放到虚拟货位，等待上架
                if ($product['picked'] > 0)
                {
                    $wl->add($location, $wid, $product['sid'], $product['picked']);
                    $needShelved = true;
                }

            }
            
            // 写入Cost-FIFO队列
            if (!empty($data4CostFIFO))
            {
                $billInfo = array('in_id'=>$rid, 'in_type'=>Conf_Warehouse::STOCK_HISTORY_REFUND_IN);
                Shop_Cost_Api::enqueueSids4FifoCost($wid, $billInfo, $data4CostFIFO);
            }

            // 退货单不需要上架
            if (!$needShelved)
            {
                $info['step'] = Conf_Refund::REFUND_STEP_SHELVED;
            }
		}

        // 售后确认订单，并提交财务付款
        if($optype == 'final_audit')
        {
            if ($refund['paid'] != Conf_Refund::UN_PAID)
            {
                throw new Exception('已经提交财务，请刷新!');
            }

            $info['to_finance_time'] = date('Y-m-d H:i:s');
            $oo = new Order_Order();
            $or = new Order_Refund();
            $refundProducts = Tool_Array::list2Map($or->getProductsOfRefund($rid), 'pid');
            $auditProducts = array_filter(explode(',', $productStr));

            if (count($auditProducts) != count($refundProducts))
            {
                throw new Exception('审核商品的数量与入库数量不等，请核对！！');
            }

            $refundPrice = 0;
            $damagedPrice = 0; //报损价格
            foreach ($auditProducts as $aproduct)
            {
                list($_pid, $_num) = explode(':', $aproduct);
                $_num = intval($_num);

                if (empty($_pid))
                {
                    throw new Exception('入库商品异常，请刷新重试！！');
                }
                
                if ( !Shop_Api::isSandCementBrickForWid3Product($_pid, $refund['wid']))
                {
                    if ($_num>($refundProducts[$_pid]['picked']+$refundProducts[$_pid]['damaged_num']) || $_num<$refundProducts[$_pid]['picked'])
                    {
                        throw new Exception('商品ID:'.$_pid.' 审核异常；不能少于入库数量，不能多于入库+损坏数量！');
                    }
                }
                else
                {
                    if ($_num > $refundProducts[$_pid]['num'])
                    {
                        throw new Exception('商品ID:'.$_pid.' 审核异常；不能多于入库数量！');
                    }
                }

                $oo->updateOrderProductInfo($refund['oid'], $_pid, $rid, array('num' => $_num));
                $refundPrice += $refundProducts[$_pid]['price']*$_num;
                $damagedPrice += $refundProducts[$_pid]['price']*($_num-$refundProducts[$_pid]['picked']);
                $refundProducts[$_pid]['num'] = $_num;
            }

            $orderInfo = Order_Api::getOrderInfo($refund['oid']);
            // 退优惠
            if(Conf_Privilege::PROMOTION_NEW_STATUS && $orderInfo['ctime'] >= Conf_Privilege::PROMOTION_NEW_UPDATE_TIME)
            {
                $refundPrivilege = Privilege_2_Api::recalPromotionPrivilege($refund['oid'], $rid, $refundProducts);
            }else{
                $refundPrivilege = Privilege_Api::recalPromotionPrivilege($refund['oid'], $rid, $refundProducts);
            }

            $info['price'] = $refundPrice;
            $info['damaged_price'] = $damagedPrice;
			$info['refund_privilege'] = $refundPrivilege;
            $info['verify_suid'] = $suid;
            $info['paid'] = Conf_Refund::HAD_AUDIT;

            $refund['price'] = $refundPrice;
            $refundPrice4Order = self::calRefundPrice($refund, $refund['adjust'], $refundPrivilege);

			//Order_Api::updateOrderInfo($refund['oid'], array(), array('refund' => $refundPrice4Order));

			//退的优惠券
			if (!empty($newPrivilegeData['refund_coupon_ids']))
			{
				$info['refund_coupon'] = json_encode($newPrivilegeData['refund_coupon_ids']);
			}
        
            //订单操作日志
            $auditLogParam = array(
                'rid' => $rid,
                'damaged_price' => $damagedPrice/100,
            );
			Admin_Api::addOrderActionLog($staff['suid'], $refund['oid'], Conf_Order_Action_Log::ACTION_AUDIT_REFUND_ORDER, $auditLogParam);
        }

		//执行更新
		if ($nextStep == Conf_Refund::REFUND_STEP_IN_STOCK && $step < Conf_Refund::REFUND_STEP_IN_STOCK)
		{
			$info['received_suid'] = $suid;
            $info['stockin_time'] = date('Y-m-d H:i:s', time());
		}

        // 征用verify_suid 记录最终审核并提交财务的人员id
//		if ($nextStep == Conf_Refund::REFUND_STEP_SURE && $step < Conf_Refund::REFUND_STEP_SURE)
//		{
//			$info['verify_suid'] = $suid;
//		}

		if ($optype == 'finance')
		{
			//退款，只更新paid字段
			$info = array('paid' => Conf_Refund::HAD_PAID, 'oid' => $refund['oid'], 'paid_time' => date('Y-m-d H:i:s', time()));
        }

		//退款
		if ($optype == 'finance')
		{
			if ($step < Conf_Refund::REFUND_STEP_IN_STOCK)
			{
				throw new Exception('退货单未入库，不能退款！');
			}
			if ($refund['paid'] == Conf_Refund::HAD_PAID)
			{
				throw new Exception('退货单已退款，请不用重复操作！');
			}
            if ($refund['paid'] != Conf_Refund::HAD_AUDIT)
            {
                throw new Exception('退货单为审核，请不能操作！');
            }
            //判断是否要少退
            $upOrderInfo = array();
            $orderInfo = Order_Api::getOrderInfo($refund['oid']);
            $refundData = self::calRefund2Amount($refund);
            if($refundData['adjust'] > 0)
            {
                $info['adjust'] = $refundData['adjust'];
            }

			self::_checkRefund($refund, $refund['adjust']);
            // 财务: 应收减少
			self::_addMoneyInRefundHistory($refund, $staff);

			// 退款金额写入余额 （非加盟商订单）
			if ($refundData['real_pay'] > 0 && !Order_Helper::isFranchiseeOrder($orderInfo['wid'], $orderInfo['city_id']))
			{
				self::_refundToAmount($refund, $refundData['real_pay'], $staff);

                WeiXin_Message_Api::sendRefundMessage($refund['uid'], $refund['oid'], $rid);
			}

			$info['refund_to_amount'] = $refundData['real_pay'];

			//订单操作日志
			$param = array(
				'balance' => $refundData['real_pay'] > 0 ? 'Y' : 'N',
				'adjust' => $refundData['real_pay'] / 100,
			);
			Admin_Api::addOrderActionLog($staff['suid'], $refund['oid'], Conf_Order_Action_Log::ACTION_PAY_REFUND_ORDER, $param);
            
            //更新订单付款状态
            $_totalAmount = Order_Helper::calOrderTotalPrice($orderInfo);

            $chgOrderInfo['refund'] = $refundData['need_pay'];
            if($orderInfo['real_amount'] >= $_totalAmount-$chgOrderInfo['refund'])
            {
                $upOrderInfo['paid'] = Conf_Order::HAD_PAID;
            }
            
            Order_Api::updateOrderInfo($refund['oid'],  $upOrderInfo, $chgOrderInfo);
		}

		Order_Api::updateRefund($rid, $info);

        
        //更新客户消费数据
        if($optype == 'final_audit')
        {
            $cc = new Crm2_Customer();
            $consumeFromRefund = Crm2_Stat_Api::statRefundDatas4Customer($refund['cid']);
            $cc->update($refund['cid'], $consumeFromRefund);
        }

        if($optype == 'finance' && !Order_Helper::isFranchiseeOrder($refund['wid']))
        {
            //积分减少
            Cpoint_Api::updatePointWhenCustomerRefund($rid);
        }
        
		if ($nextStep == Conf_Refund::REFUND_STEP_SURE)
		{
			//订单操作日志
			Admin_Api::addOrderActionLog($staff['suid'], $refund['oid'], Conf_Order_Action_Log::ACTION_AGREE_REFUND_ORDER);
		}
		else if ($nextStep == Conf_Refund::REFUND_STEP_IN_STOCK)
		{
			//订单操作日志
			Admin_Api::addOrderActionLog($staff['suid'], $refund['oid'], Conf_Order_Action_Log::ACTION_PUT_IN_REFUND_ORDER);
		}
	}

	/**
	 * 计算订单应退金额
	 * @param $refund
	 * @return array
	 */
	public static function calRefundAmount($refund)
	{
		//退款信息空，返回空
		if (empty($refund))
		{
			return array('amount' => 0, 'desc' => array('退款单为空' => '0'));
		}

		//订单信息空，返回空
		$oid = $refund['oid'];
		$oo = new Order_Order();
		$order = $oo->get($oid);
		if (empty($order))
		{
			return array('amount' => 0, 'desc' => array('订单为空' => 0));
		}

		//订单未付款，返回空
		if ($order['real_amount'] <= 0)
		{
			return array('amount' => 0, 'desc' => array('订单未付款' => 0));
		}

		//订单已付款
        $amount = self::calRefundPrice($refund);

		return array('amount' => $amount, 'desc' => array());
	}

	public static function calRefund2Amount($refund)
    {
        $refundData = array();
        if($refund['paid'] == Conf_Refund::HAD_PAID)
        {
            $refundData['real_pay'] = self::calRefundPrice($refund);
            $refundData['need_pay'] = $refundData['real_pay'] + $refund['adjust'];
            $refundData['adjust'] = $refund['adjust'];
        }else{
            //退款信息空，返回空
            if (empty($refund))
            {
                return array('real_pay' => 0, 'need_pay' => 0, 'adjust' => 0);
            }

            //订单信息空，返回空
            $oid = $refund['oid'];
            $oo = new Order_Order();
            $order = $oo->get($oid);
            if (empty($order))
            {
                return array('real_pay' => 0, 'need_pay' => 0, 'adjust' => 0);
            }

            $refundData['adjust'] = 0;
            //少退前提： 实付金额大于0，订单总金额大于实付金额
            $order_need_pay = Order_Helper::calOrderTotalPrice($order);//应付金额
            $refundPrice4Order = self::calRefundPrice($refund);
            if($order['real_amount'] > 0 && $order_need_pay > $order['real_amount']){
                if($refundPrice4Order > ($order_need_pay-$order['real_amount'])) {
                    //如果 退款金额大于订单金额减实付 ,计算少退=订单金额减实付
                    $refundData['adjust'] = $refund['adjust'] = $order_need_pay - $order['real_amount'];
                }else{
                    //否则少退金额=退款金额
                    $refundData['adjust'] = $refund['adjust'] = $refundPrice4Order;
                }
            }elseif($order['real_amount'] <= 0)
            {
                $refundData['adjust'] = $refund['adjust'] = $refundPrice4Order;
            }
            $refundData['real_pay'] = self::calRefundPrice($refund);
            $refundData['need_pay'] = $refundData['real_pay'] + $refund['adjust'];
        }
        return $refundData;
    }

	public static function calRefundPrice($refund, $adjust = 0, $refundPrivilege = 0)
	{
		$adjust == 0 && $adjust = $refund['adjust'];
		$refundPrivilege == 0 && $refundPrivilege = $refund['refund_privilege'];
		return $refund['price'] + $refund['refund_freight'] + $refund['refund_carry_fee'] - $refund['freight'] - $refund['carry_fee'] - $adjust - $refundPrivilege;
	}

	/**
	 * 计算订单最多可退的运费和搬运费
	 * 计算方法：订单的运费 - 订单下所有退单要退的运费总和
	 *         订单的搬运费 - 订单下所有退单要退的搬运费总和
	 * @param $oid
	 * @return array
	 */
	public static function getMaxRefundInfo($oid, $rid = 0)
	{
		$data = array(
			'freight' => 0,
			'carry_fee' => 0,
		);

		if (empty($oid))
		{
			return $data;
		}

		$oo = new Order_Order();
		$order = $oo->get($oid);
		if (empty($order) || ($order['freight'] <= 0 && $order['customer_carriage'] <= 0) )
		{
			return $data;
		}
        $pp = new Privilege_Privilege();
        $freightPrivilege = $pp->getPrivilegeByType($oid, Conf_Privilege::$TYPE_FREIGHT);
		$canRefundMaxFreight = $order['freight'];
        if(!empty($freightPrivilege))
        {
            $canRefundMaxFreight -= $freightPrivilege['amount'];
        }
		$canRefundMaxCarryFee = $order['customer_carriage'];
		$or = new Order_Refund();
		$list = $or->getListOfOrder($oid);
		if (!empty($list))
		{
			foreach ($list as $item)
			{
				if ($item['status'] != Conf_Base::STATUS_NORMAL)
				{
					continue;
				}
				if ($rid > 0 && $item['rid'] == $rid)
				{
					continue;
				}

				$canRefundMaxFreight -= $item['refund_freight'];
				$canRefundMaxCarryFee -= $item['refund_carry_fee'];
			}
		}

		$canRefundMaxCarryFee < 0 && $canRefundMaxCarryFee = 0;
		$canRefundMaxFreight < 0 && $canRefundMaxFreight = 0;

		$data = array(
			'freight' => $canRefundMaxFreight,
			'carry_fee' => $canRefundMaxCarryFee,
		);

		return $data;
	}

	private static function _checkRefund($refundInfo, $adjust)
	{
		$price = $refundInfo['price'] - $adjust * 100;
		$price < 0 && $price = 0;
		$fca = new Finance_Customer_Amount();
		$where = sprintf(' status=0 and cid=%d AND type=%d AND objid=%d AND payment_type=%d AND price=%d', 
                $refundInfo['cid'], Conf_Finance::CRM_AMOUNT_TYPE_REFUND, $refundInfo['oid'], Conf_Base::PT_BALANCE, $price);
		$total = $fca->getTotalByWhere($where);
		if ($total > 0)
		{
			throw new Exception('order:has refund');
		}
	}

	private static function _addMoneyInRefundHistory($refund, $staff)
	{
		$price = self::calRefundPrice($refund, $refund['adjust'], $refund['refund_privilege']) + $refund['adjust'];
        
		if ($price < 0)
		{
			$oo = new Order_Order();
			$order = $oo->get($refund['oid']);
			$price = $order['real_amount'];
		}

		$price = 0 - $price;
		$wid = $refund['wid'] ? $refund['wid'] : Conf_Warehouse::WID_3;
		$type = Conf_Money_In::REFUND_PAIED;
		$cid = $refund['cid'];
		$suid = $staff['suid'];
		$rid = $refund['rid'];
        $oid = $refund['oid'];

		Finance_Api::addMoneyInHistory($cid, $type, $price, $suid, $rid, $wid, '', 0, $refund['uid'], $oid);
	}

	private static function _refundToAmount($refund, $price, $staff)
	{
		// 财务退款，写入 应收明细
		$cid = $refund['cid'];
		$objid = $refund['rid'];
        $oid = $refund['oid'];
		$note = '退货退款（oid:' . $refund['oid'] . ', rid:' . $objid . ')';
        
        Finance_Api::addMoneyInHistory($cid, Conf_Money_In::ORDER_REFUND, $price, $staff['suid'], $objid, $refund['wid'], $note, Conf_Base::PT_BALANCE, $refund['uid'], $oid);
        $saveData = array(
            'uid' => $refund['uid'],
            'type' => Conf_Finance::CRM_AMOUNT_TYPE_REFUND,
            'price' => abs($price),
            'payment_type' => Conf_Base::PT_BALANCE,
            'note' => $note,
            'objid' => $objid,
            'suid' => $staff['suid'],
            'oid' => $oid,
        );

        Finance_Api::addCustomerAmountHistory($cid, $saveData);
	}


    public static function getRefundForSameCustomer($cid, $communityId)
    {
        $where = sprintf('cid = %d and type = %d and step = %d', $cid, Conf_Refund::REFUND_TYPE_NEXT_ORDER, Conf_Refund::REFUND_STEP_SURE);
        $or = new Order_Refund();
        $refundInfos = $or->getListByWhere($where);

        if (empty($refundInfos)) {
            return array();
        }

        $oids = Tool_Array::getFields($refundInfos, 'oid');

        $oo = new Order_Order();
        $orderInfos = $oo->getBulk($oids);
        foreach ($orderInfos as $order) {
            if ($order['community_id'] == $communityId) {
                foreach ($refundInfos as $refund) {
                    if ($refund['oid'] == $order['oid']) {
                        $rids[] = $refund['rid'];
                    }
                }
            }
        }
        return $rids;
    }

    public static function getRefundByRid($rid)
    {
        $or = new Order_Refund();
        return $or->get($rid);
    }

    public static function getRefundByRids($rids)
    {
        $or = new Order_Refund();
        return $or->getBulk($rids);
    }

    public static function getRefundProductByRid($rid)
    {
        $res = array(
            'summary' => array(),
            'products' => array(),
            'refund_products' => array(),
        );

        $or = new Order_Refund();
        $products = $or->getProductsOfRefund($rid);

        if (!empty($products))
        {
            $ss = new Shop_Sku();
            $sids = Tool_Array::getFields($products, 'sid');
            $skuInfos = $ss->getBulk($sids);

            $allCate2 = Conf_Sku::$CATE2;
            foreach ($products as &$product)
            {
                if ($product['num'] <= 0)
                {
                    continue;
                }

                $product['sku_info'] = array_key_exists($product['sid'], $skuInfos)?
                    $skuInfos[$product['sid']]: array();

                Shop_Helper::formatPic($product['sku_info']);
                // 商品摘要
                $cate2 = isset($product['sku_info']['cate2'])?$product['sku_info']['cate2']:0;
                $cate2 = in_array($cate2, Conf_Sku::$Summary_Cate2_Ids)? $cate2: 10000;

                if (!array_key_exists($cate2, $res['summary']))
                {
                    $cate1 = $product['sku_info']['cate1'];
                    $name = $cate2!=10000? $allCate2[$cate1][$cate2]['name']: '其他';

                    $res['summary'][$cate2]['name'] = $name;
                    $res['summary'][$cate2]['num'] = 0;
                }
                $res['summary'][$cate2]['num'] += $product['num'];

                // 订单商品详情
                if ($product['rid'] == 0)   // 订单商品
                {
                    $res['products'][] = $product;
                }
                else    //退款单商品
                {
                    $res['refund_products'][] = $product;
                }
            }
        }

        return $res;
    }

    public static function getRefundFeeByOid($oid, $onlyPaid = false)
    {
        $freight = $carryFee = 0;

        $rids = array();
        $refundList = Order_Api::getRefundList(array('oid' => $oid), 0, 0);
        if (!empty($refundList['list']))
        {
            foreach ($refundList['list'] as $refund)
            {
                if ($onlyPaid && $refund['paid'] != Conf_Order::HAD_PAID)
                {
                    continue;
                }
                //单独退货，取补单里头的运费搬运费商品价格
                if ($refund['type'] == Conf_Refund::REFUND_TYPE_ALONE)
                {
                    $rids[] = $refund['rid'];
                }
                //预约退货，从t_refund表里头取
                else if ($refund['type'] == Conf_Refund::REFUND_TYPE_NEXT_ORDER)
                {
                    $freight += $refund['freight'];
                    $carryFee += $refund['carry_fee'];
                }
            }

            if (!empty($rids))
            {
                $addonOrderList = Order_Api::getAfterSaleOrder($rids, Conf_Order::AFTERSALE_TYPE_REFUND);
                $oids = Tool_Array::getFields($addonOrderList, 'oid');
                if (!empty($oids))
                {
                    $products = Order_Api::getProductByOids($oids);
                    if (!empty($products))
                    {
                        foreach ($products as $product)
                        {
                            if ($product['rid'] > 0)
                            {
                                continue;
                            }

                            $product['sid'] == Conf_Refund::REFUND_VIRTUAL_FREIGHT && $freight += $product['price'] * $product['num'];
                            $product['sid'] == Conf_Refund::REFUND_VIRTUAL_CARRY_FEE && $carryFee += $product['price'] * $product['num'];
                        }
                    }
                }
            }
        }

        return array('freight' => $freight, 'carry_fee' => $carryFee);
    }

    public static function getRefundFeeByRid($rid, $onlyPaid = false)
    {
        $freight = $carryFee = 0;

        $refund = Refund_Api::getRefundByRid($rid);
        if ($onlyPaid && $refund['paid'] != Conf_Order::HAD_PAID)
        {
            return array('freight' => $freight, 'carry_fee' => $carryFee);
        }

        if ($refund['type'] == Conf_Refund::REFUND_TYPE_NEXT_ORDER)
        {
            $freight = $refund['freight'];
            $carryFee = $refund['carry_fee'];
        }
        else if ($refund['type'] == Conf_Refund::REFUND_TYPE_ALONE)
        {
            $addonOrderList = Order_Api::getAfterSaleOrder($rid, Conf_Order::AFTERSALE_TYPE_REFUND);
            $oids = Tool_Array::getFields($addonOrderList, 'oid');
            if (!empty($oids))
            {
                $products = Order_Api::getProductByOids($oids);
                if (!empty($products))
                {
                    foreach ($products as $product)
                    {
                        if ($product['rid'] > 0)
                        {
                            continue;
                        }

                        $product['sid'] == Conf_Refund::REFUND_VIRTUAL_FREIGHT && $freight += $product['price'] * $product['num'];
                        $product['sid'] == Conf_Refund::REFUND_VIRTUAL_CARRY_FEE && $carryFee += $product['price'] * $product['num'];
                    }
                }
            }
        }

        return array('freight' => $freight, 'carry_fee' => $carryFee);
    }

    /**
     * 根据订单号获取订单的运费，搬运费
     * @param $oids
     *
     * @return array
     */
    public static function getRefundFeeByOids($oids, $onlyPaid = false)
    {
        $data = array();

        $refundList = Order_Api::getRefundList(array('oid' => $oids), 0, 0);
        if (!empty($refundList['list']))
        {
            $rids = array();
            foreach ($refundList['list'] as $refund)
            {
                if ($onlyPaid && $refund['paid'] != Conf_Order::HAD_PAID)
                {
                    continue;
                }
                //单独退货，取补单里头的运费搬运费商品价格
                if ($refund['type'] == Conf_Refund::REFUND_TYPE_ALONE)
                {
                    $rids[] = $refund['rid'];
                }
                //预约退货，从t_refund表里头取
                else if ($refund['type'] == Conf_Refund::REFUND_TYPE_NEXT_ORDER)
                {
                    $oid =  $refund['oid'];
                    $data[$oid]['freight'] += $refund['freight'];
                    $data[$oid]['carry_fee'] += $refund['carry_fee'];
                }
            }

            if (!empty($rids))
            {
                $addonOrderList = Order_Api::getAfterSaleOrder($rids, Conf_Order::AFTERSALE_TYPE_REFUND);

                $afterOids = Tool_Array::getFields($addonOrderList, 'oid');
                if (!empty($afterOids))
                {
                    $products = Order_Api::getProductByOids($afterOids);
                    if (!empty($products))
                    {
                        foreach ($products as $product)
                        {
                            if ($product['rid'] > 0)
                            {
                                continue;
                            }

                            $oid = $product['oid'];
                            $aftersaleId = $addonOrderList[$oid]['aftersale_id'];
                            $rid = $aftersaleId;
                            $oriOid = $addonOrderList[$rid]['oid'];
                            $product['sid'] == Conf_Refund::REFUND_VIRTUAL_FREIGHT && $data[$oriOid]['freight'] += $product['price'] * $product['num'];
                            $product['sid'] == Conf_Refund::REFUND_VIRTUAL_CARRY_FEE && $data[$oriOid]['carry_fee'] += $product['price'] * $product['num'];
                        }
                    }
                }
            }
        }

        return $data;
    }

    public static function getRefundFeeByRids($rids, $onlyPaid = false)
    {
        $data = array();

        $refundList = Refund_Api::getRefundByRids($rids);
        if (!empty($refundList))
        {
            $rids = array();
            foreach ($refundList as $refund)
            {
                if ($onlyPaid && $refund['paid'] != Conf_Order::HAD_PAID)
                {
                    continue;
                }

                $rid = $refund['rid'];
                //单独退货，取补单里头的运费搬运费商品价格
                if ($refund['type'] == Conf_Refund::REFUND_TYPE_ALONE)
                {
                    $rids[] = $refund['rid'];
                }
                //预约退货，从t_refund表里头取
                else if ($refund['type'] == Conf_Refund::REFUND_TYPE_NEXT_ORDER)
                {
                    $oid =  $refund['oid'];
                    $data[$rid]['freight'] += $refund['freight'];
                    $data[$rid]['carry_fee'] += $refund['carry_fee'];
                }
            }
        }

        if (!empty($rids))
        {
            $addonOrderList = Order_Api::getAfterSaleOrder($rids, Conf_Order::AFTERSALE_TYPE_REFUND);
            $afterOids = Tool_Array::getFields($addonOrderList, 'oid');
            if (!empty($afterOids))
            {
                $products = Order_Api::getProductByOids($afterOids);
                if (!empty($products))
                {
                    foreach ($products as $product)
                    {
                        if ($product['rid'] > 0)
                        {
                            continue;
                        }

                        $oid = $product['oid'];
                        $aftersaleId = $addonOrderList[$oid]['aftersale_id'];
                        $rid = $aftersaleId;
                        $product['sid'] == Conf_Refund::REFUND_VIRTUAL_FREIGHT && $data[$rid]['freight'] += $product['price'] * $product['num'];
                        $product['sid'] == Conf_Refund::REFUND_VIRTUAL_CARRY_FEE && $data[$rid]['carry_fee'] += $product['price'] * $product['num'];
                    }
                }
            }
        }

        return $data;
    }

    public static function updateOrderRefundPrivilege($oid)
    {
        $or = new Order_Refund();
        $refundList = $or->getListOfOrder($oid);
        if(!empty($refundList))
        {
            $orderProducts = Order_Api::getOrderProducts($oid);
            $refundProducts = $orderProducts['refund_products'];
            $orderProducts = $orderProducts['products'];
            $updateRefundList = array();
            $refundPrivilegeProducts = array();
            foreach ($refundProducts as $key => $product)
            {
                list($_rid,$_pid) = explode('#', $key);
                if($product['status'] == Conf_Base::STATUS_NORMAL && $refundList[$_rid]['paid'] != Conf_Refund::HAD_PAID)
                {
                    if($product['num']==($orderProducts[$_pid]['num']-$refundPrivilegeProducts[$_pid]))
                    {
                        $updateRefundList[$_rid] += $orderProducts[$_pid]['privilege'] - round($refundPrivilegeProducts[$_pid]*$orderProducts[$_pid]['privilege']/$orderProducts[$_pid]['num']);
                    }else{
                        $updateRefundList[$_rid] += round($product['num']*$orderProducts[$_pid]['privilege']/$orderProducts[$_pid]['num']);
                    }
                    $refundPrivilegeProducts[$_pid] += $product['num'];
                }
            }
            $orderInfo = Order_Api::getOrderInfo($oid);
            $refundAmount = 0;
            foreach ($refundList as $rid => $refund)
            {
                $info = array('oid' => $refund['oid']);
                $info['refund_privilege'] = $updateRefundList[$rid];
                $refundPrice4Order = self::calRefundPrice($refund, $refund['adjust'], $updateRefundList[$rid]);
                $refundAmount += $refundPrice4Order;
                //判断是否要少退
                $order_need_pay = Order_Helper::calOrderTotalPrice($orderInfo);//应付金额
                //少退前提： 实付金额大于0，订单总金额大于实付金额
                if($orderInfo['real_amount'] && $order_need_pay > $orderInfo['real_amount']){
                    if($refundPrice4Order > ($order_need_pay-$orderInfo['real_amount'])) {
                        //如果 退款金额大于订单金额减实付 ,计算少退=订单金额减实付
                        $info['adjust'] = $order_need_pay - $orderInfo['real_amount'];
                    }else{
                        //否则少退金额=退款金额
                        $info['adjust'] = $refundPrice4Order;
                    }
                }
                Order_Api::updateRefund($rid, $info);
            }
            Order_Api::updateOrderInfo($oid, array('refund' => $refundAmount));
        }
    }
}