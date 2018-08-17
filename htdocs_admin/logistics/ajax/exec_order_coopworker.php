<?php

/**
 * 操作订单中得合作工人.
 */

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $oid;
    private $cuid;
    private $type;
    private $execType;
    private $userType;
    private $modifyType;
    private $price;
    private $paymentType;
    private $statementIds;
    private $blukDatas;
    private $driverOtherFee;
    private $basePrice;
    private $driverTimes;
    private $driverMoneyNote;
    private $objType = Conf_Coopworker::OBJ_TYPE_ORDER;
    private $lineInfo;
    private $sourceOid;
    private $lineId;
    private $id;

    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->cuid = Tool_Input::clean('r', 'cuid', TYPE_UINT);
        $this->type = Tool_Input::clean('r', 'type', TYPE_UINT);
        $this->execType = Tool_Input::clean('r', 'exec_type', TYPE_STR);
        $this->price = Tool_Input::clean('r', 'price', TYPE_NUM)*100;
        $this->userType = Tool_Input::clean('r', 'user_type', TYPE_UINT);
        $this->modifyType = Tool_Input::clean('r', 'modify_type', TYPE_STR);
        $this->paymentType = Tool_Input::clean('r', 'payment_type', TYPE_UINT);
        $this->statementIds = Tool_Input::clean('r', 'statement_ids', TYPE_ARRAY);
        $this->blukDatas = Tool_Input::clean('r', 'bluk_datas', TYPE_STR);

        if ($this->execType == 'modify_driver_price' || $this->execType == 'modify_driver_price_lf' || $this->execType == 'modify_carrier_price_lf')
        {
            $this->basePrice = Tool_Input::clean('r', 'base_price', TYPE_NUM);
            $this->driverTimes = Tool_Input::clean('r', 'times', TYPE_UINT);
            $this->driverMoneyNote = Tool_Input::clean('r', 'money_note', TYPE_STR);
            $this->lineInfo = Tool_Input::clean('r', 'line_info', TYPE_STR);
            $this->sourceOid = Tool_Input::clean('r', 'source_oid', TYPE_UINT);
            $this->driverOtherFee = array(
                Conf_Driver::DRIVER_FEE_TYPE_TRASH => Tool_Input::clean('r', 'trash_price', TYPE_NUM),
                Conf_Driver::DRIVER_FEE_TYPE_SECOND_RING => Tool_Input::clean('r', 'second_ring_road_price', TYPE_NUM),
                Conf_Driver::DRIVER_FEE_TYPE_REWARD => Tool_Input::clean('r', 'reward_price', TYPE_NUM),
                Conf_Driver::DRIVER_FEE_TYPE_FINE => Tool_Input::clean('r', 'fine_price', TYPE_NUM),
                Conf_Driver::DRIVER_FEE_TYPE_OTHER => Tool_Input::clean('r', 'other_price', TYPE_NUM),
            );
        }
        else if ($this->execType == 'del' && $this->type == Conf_Base::COOPWORKER_DRIVER)
        {
            $this->lineId = Tool_Input::clean('r', 'line_id', TYPE_UINT);
        }

        if ($this->execType == 'del')
        {
            $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        }
    }
    
    protected function checkPara()
    {
        if (($_REQUEST['exec_type']=='paid'||$_REQUEST['exec_type']=='bluk_paid'||$_REQUEST['exec_type']=='statement_bluk_paid')
            && empty($this->paymentType))
        {
            throw new Exception('请选择支付类型');
        }
    }
    
    protected function checkAuth()
    {
        $execType = isset($_REQUEST['exec_type'])? $_REQUEST['exec_type']: '';
        $type = isset($_REQUEST['type'])? $_REQUEST['type']: '';
        
        switch ($execType)
        {
            case 'del':
                if ($type == Conf_Base::COOPWORKER_DRIVER)
                {
                    parent::checkAuth('hc_order_del_driver');
                }
                else 
                {
                    parent::checkAuth('hc_order_add_del_carrier');
                }
                break;
            case 'paid':
                parent::checkAuth('hc_order_paid_coopworker'); break;
            
            case 'modify_driver_price':
                parent::checkAuth('hc_order_edit_coopworker'); break;
            case 'modify_driver_price_lf':
                parent::checkAuth('hc_order_edit_coopworker'); break;
            case 'modify_carrier_price_lf':
                parent::checkAuth('hc_order_edit_coopworker'); break;
            case 'statement_bluk_paid':
                parent::checkAuth('hc_order_paid_coopworker'); break;
            case 'statement_paid':
                parent::checkAuth('hc_order_paid_coopworker_franchisee'); break;
            case 'bluk_paid':
                parent::checkAuth('hc_order_paid_coopworker'); break;
            default:
                throw new Exception('common:permission denied');
                
        }
        
    }
    
    protected function main()
    {
        if ($this->execType == 'del')   //删除
        {
            if ($this->id) {
                $lc = new Logistics_Coopworker();
                $coopworkerOrder = $lc->get($this->id, array('statement_id'));
                if (!empty($coopworkerOrder['statement_id']))
                {
                    $lcs = new Logistics_Coopworker_Statement();
                    $statementInfo = $lcs->getById($coopworkerOrder['statement_id']);
                    if ($statementInfo['step'] >= Conf_Coopworker::STATEMENT_STEP_CHECK) {
                        throw new Exception('结算单' . Conf_Coopworker::$Statement_Step[$statementInfo['step']] . '不能删除');
                    }
                }
            }
            $data['status'] = Conf_Base::STATUS_DELETED;
            Logistics_Coopworker_Api::updateOrderCoopworkerById($this->id, $data);
            Order_Api::updateCoopworkerOrderCarrierNum($this->oid);
            if (!empty($statementInfo['id']))
            {
                Logistics_Coopworker_Api::updateStatementPrice($statementInfo['id']);
            }
            $where = array(
                'cuid' => $this->cuid,
                'oid' => $this->oid,
                'type' => Conf_Base::COOPWORKER_DRIVER,
                'user_type' => Conf_Base::COOPWORKER_DRIVER,
                'status' => Conf_Base::STATUS_NORMAL,
            );
            $info = Logistics_Coopworker_Api::getDriverOrdersByWhere($where);

            if (!empty($this->lineId) && $this->type == Conf_Base::COOPWORKER_DRIVER && count($info) < 1)
            {
                $queue = Logistics_Api::getDriverQueue($this->cuid);
                if ($queue['line_id'] == $this->lineId)
                {
                    $update = array(
                        'line_id' => 0,
                        'step' => Conf_Driver::STEP_EMPTY,
                    );
                    Logistics_Api::updateDriverInQueue($this->cuid, $update);
                }

            }
            
            // 订单状态退回，代码需要重新组织，有点乱；先加到这里，后面重构 addby guoqiang/20170913
            $oo = new Order_Order();
            $orderInfo = $oo->get($this->oid);
            $coopworkerInOrder = $lc->getByOid($this->oid, 0, Conf_Base::COOPWORKER_DRIVER);
            
            if (empty($coopworkerInOrder) && $orderInfo['step']<Conf_Order::ORDER_STEP_PICKED && $orderInfo['step']>=Conf_Order::ORDER_STEP_SURE)
            {
                $oo->update($this->oid, array('step'=>Conf_Order::ORDER_STEP_BOUGHT));
            }

            $param = array('action' => '删除');
            if ($this->userType == Conf_Base::COOPWORKER_DRIVER) {
                $param['role'] = '司机';
                $driver = Logistics_Api::getDriver($this->cuid);
                $param['name'] = $driver['name'];
                $param['id'] = $this->cuid;

            } else if ($this->userType == Conf_Base::COOPWORKER_CARRIER) {
                $param['role'] = '搬运工';
                $carrier = Logistics_Api::getCarrier($this->cuid);
                $param['name'] = $carrier['name'];
                $param['id'] = $this->cuid;
            }

            Admin_Api::addOrderActionLog($this->_uid, $this->oid, Conf_Order_Action_Log::ACTION_EDIT_COOPWORKER, $param);
        }
        else if ($this->execType == 'paid') //支付
        {
            $canPay = $this->_checkCanPay();
            
            if (!$canPay)
            {
                throw new Exception('不能支付该订单，请联系管理员！');
            }
            
            $data['paid'] = 1;
            Logistics_Coopworker_Api::updateOrderCoopworker($this->oid, $this->cuid, $this->type, $data, $this->objType);
            
            // 写支出记录 t_coopworker_money_out_history 表
            Finance_Api::paidCoopworker($this->oid, $this->cuid, $this->type, $this->userType, $this->_user, $this->paymentType);
            $param = array('action' => '支付费用');
            if ($this->userType == Conf_Base::COOPWORKER_DRIVER) {
                $param['role'] = '司机';
                $driver = Logistics_Api::getDriver($this->cuid);
                $param['name'] = $driver['name'];
                $param['price'] = $this->price/100;
                $param['id'] = $this->cuid;

            } else if ($this->userType == Conf_Base::COOPWORKER_CARRIER) {
                $param['role'] = '搬运工';
                $carrier = Logistics_Api::getCarrier($this->cuid);
                $param['name'] = $carrier['name'];
                $param['price'] = $this->price/100;
                $param['id'] = $this->cuid;
            }

            Admin_Api::addOrderActionLog($this->_uid, $this->oid, Conf_Order_Action_Log::ACTION_EDIT_COOPWORKER, $param);
            
        } 
        else if ($this->execType == 'modify')   //调整费用
        {
            throw new Exception('订单详情页调整工人费用已经下线');
            
            $lc = new Logistics_Coopworker();
            $coopworkerOrder = $lc->getByOid($this->oid, $this->cuid, $this->type, $this->objType);
            if ($coopworkerOrder[0]['statement_id'])
            {
                $lcs = new Logistics_Coopworker_Statement();
                $statementInfo = $lcs->getById($coopworkerOrder[0]['statement_id']);
                if ($statementInfo['step'] >= Conf_Coopworker::STATEMENT_STEP_CHECK)
                {
                    throw new Exception('订单' . Conf_Coopworker::$Statement_Step[$statementInfo['step']] . '不能修改');
                }
                $totalPrice = $statementInfo['price'] - $coopworkerOrder[0]['price'] + $this->price;
                $where = array('id'=> $statementInfo['id']);
                $updata = array('price' => $totalPrice);
                $lcs->updateByWhere($where, $updata);
            }
            $data['price'] = $this->price;
            Logistics_Coopworker_Api::updateOrderCoopworker($this->oid, $this->cuid, $this->type, $data, $this->objType);
            
            // 已经支付，修改 财务支出
            if ($this->modifyType == 'paid')
            {
                Finance_Api::updateCoopworker($this->oid, $this->cuid, $this->type, $data, $this->objType);
            }
        }
        else if ($this->execType == 'modify_driver_price' || $this->execType == 'modify_driver_price_lf' || $this->execType == 'modify_carrier_price_lf') //修改司机费用
        {
            $lineInfo = json_decode($this->lineInfo,true);
            if (!empty($this->sourceOid) && is_array($lineInfo) && in_array($this->sourceOid, $lineInfo))
            {
                throw new Exception('该订单是补单，且与主单在一条排线上，所以该订单不能添加运费！');
            }
            $lc = new Logistics_Coopworker();
            $coopworkerOrder = $lc->getByOid($this->oid, $this->cuid, $this->type, $this->userType, $this->objType);
            
            if ($coopworkerOrder[0]['statement_id'])
            {
                $lcs = new Logistics_Coopworker_Statement();
                $statementInfo = $lcs->getById($coopworkerOrder[0]['statement_id']);
                if ($statementInfo['step'] >= Conf_Coopworker::STATEMENT_STEP_CHECK)
                {
                    throw new Exception('订单' . Conf_Coopworker::$Statement_Step[$statementInfo['step']] . '不能修改');
                }
                $totalPrice = $statementInfo['price'] - $coopworkerOrder[0]['price'] + $this->price;
                $where = array('id'=> $statementInfo['id']);
                $updata = array('price' => $totalPrice);
                $lcs->updateByWhere($where, $updata);
            }

            $data['price'] = $this->price;
            $data['times'] = $this->driverTimes;
            $data['base_price'] = $this->basePrice*100;
            $data['other_price'] = Logistics_Api::generateDriverFee($this->driverOtherFee);
            $data['money_note'] = $this->driverMoneyNote;

            //如果是拼单的情况
            $order = Order_Api::getOrderInfo($this->oid);
            if ($order['city_id'] == Conf_City::LANGFANG && $this->type == Conf_Base::COOPWORKER_CARRIER)
            {
                $where = array('oid' => $lineInfo, 'status' => Conf_Base::STATUS_NORMAL, 'cuid' => $this->cuid, 'type' => Conf_Base::COOPWORKER_CARRIER);
                $otherCarrierTotalFees = array_sum(Tool_Array::getFields($lc->getListByWhere($where), 'price'));

                if ($otherCarrierTotalFees > 0)
                {
                    throw new Exception('该搬运工在排线中其他的订单中已经录入费用，请勿重复录入！');
                }
            }

            Logistics_Coopworker_Api::updateOrderCoopworker($this->oid, $this->cuid, $this->type, $data, $this->objType);

            // 已经支付，修改 财务支出
            if ($this->modifyType == 'paid')
            {
                Finance_Api::updateCoopworker($this->oid, $this->cuid, $this->type, $data, $this->objType);
            }
            if ($this->type == Conf_Base::COOPWORKER_DRIVER) {
                $param = array('action' => '修改司机费用');
            }
            elseif ($this->type == Conf_Base::COOPWORKER_CARRIER)
            {
                $param = array('action' => '修改搬运工费用');
            }
            if ($this->userType == Conf_Base::COOPWORKER_DRIVER) {
                $param['role'] = '司机';
                $driver = Logistics_Api::getDriver($this->cuid);
                $param['name'] = $driver['name'];
                $param['price'] = $this->price/100;
                $param['id'] = $this->cuid;

            } else if ($this->userType == Conf_Base::COOPWORKER_CARRIER) {
                $param['role'] = '搬运工';
                $carrier = Logistics_Api::getCarrier($this->cuid);
                $param['name'] = $carrier['name'];
                $param['price'] = $this->price/100;
                $param['id'] = $this->cuid;
            }

            $param['reason'] = $this->driverMoneyNote;
            Admin_Api::addOrderActionLog($this->_uid, $this->oid, Conf_Order_Action_Log::ACTION_EDIT_COOPWORKER, $param);
        }
        else if ($this->execType == 'bluk_paid') //批量支付
        {
            Logistics_Coopworker_Api::blukPayCoopworker($this->blukDatas, $this->_user, $this->paymentType);
        }
        else if ($this->execType == 'statement_bluk_paid' || $this->execType == 'statement_paid') //结算单批量结算
        {
            Logistics_Coopworker_Api::blukPayStatements($this->statementIds, $this->paymentType, $this->_uid);
        }
    }
    
    protected function outputBody()
    {
        $result = array('st'=>1);
		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
    }
    
    private function _checkCanPay()
    {
        $workers = Logistics_Coopworker_Api::getOrderOfWorkers($this->oid, $this->type, false, $this->objType);
        
        $canPay = false;
        
        foreach($workers as $worker)
        {
            if ($this->cuid==$worker['cuid'] && $this->price==$worker['price']&&$this->userType==$worker['user_type'])
            {
                $canPay = true;
                break;
            }
        }
        
        return $canPay;
    }
    
}

$app = new App();
$app->run();