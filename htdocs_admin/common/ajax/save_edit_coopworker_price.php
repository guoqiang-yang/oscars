<?php

include_once('../../../global.php');

Class App extends App_Admin_Ajax
{
    private $info;

    protected function getPara()
    {
        $this->info = json_decode(Tool_Input::clean('r', 'info', TYPE_STR), true);
    }

    protected function checkPara()
    {
        if (empty($this->info['id'])) {
            throw new Exception('参数错误！');
        }
    }

    protected function checkAuth()
    {
        parent::checkAuth('hc_order_edit_coopworker');
    }

    protected function main()
    {
        $lc = new Logistics_Coopworker();
        $coopworkerOrder = $lc->get($this->info['id']);

        $oo = new Order_Order();
        $order = $oo->get($coopworkerOrder['oid']);

        $flag = Conf_Coopworker::getEditCoopworkerFlagForWid($coopworkerOrder['wid'], $coopworkerOrder['type']);
        switch ($flag) {
            case 'driver_1':
                $this->_saveEditCoopworkerPriceForDriver_1($coopworkerOrder, $order);
                break;
            case 'driver_2':
                $this->_saveEditCoopworkerPriceForDriver_2($coopworkerOrder, $order);
                break;
            case 'driver_3':
                $this->_saveEditCoopworkerPriceForDriver_2($coopworkerOrder, $order);
                break;
            case 'driver_4':
                $this->_saveEditCoopworkerPriceForDriver_2($coopworkerOrder, $order);
                break;
            case 'driver_5':
                $this->_saveEditCoopworkerPriceForDriver_2($coopworkerOrder, $order);
                break;
            case 'carrier_1':
                $this->_saveEditCoopworkerPriceForCarrier_1($coopworkerOrder, $order);
                break;
            case 'carrier_2':
                $this->_saveEditCoopworkerPriceForCarrier_2($coopworkerOrder, $order);
                break;
            case 'carrier_3':
                $this->_saveEditCoopworkerPriceForCarrier_2($coopworkerOrder, $order);
                break;
            case 'carrier_4':
                $this->_saveEditCoopworkerPriceForCarrier_4($coopworkerOrder);
                break;
            default :
                break;
        }
    }

    protected function outputBody()
    {
        $result = array('id' => $this->info['id']);
        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }

    private function _saveEditCoopworkerPriceForDriver_1($coopworkerOrder, $order)
    {
        if (empty($this->info['money_note']) && (!empty($this->info['reward_price']) || !empty($this->info['fine_price'])
            || !empty($this->info['other_price'])))
        {
            throw new Exception('奖励、处罚、其他车型必须填写备注！');
        }

        $otherPriceData = array(
            Conf_Driver::DRIVER_FEE_TYPE_TRASH => $this->info['trash_price'],
            Conf_Driver::DRIVER_FEE_TYPE_SECOND_RING => $this->info['second_ring_road_price'],
            Conf_Driver::DRIVER_FEE_TYPE_REWARD => $this->info['reward_price'],
            Conf_Driver::DRIVER_FEE_TYPE_FINE => $this->info['fine_price'],
            Conf_Driver::DRIVER_FEE_TYPE_OTHER => $this->info['other_price'],
        );
        $data = array(
            'price' => ($this->info['refer_price'] + $this->info['trash_price'] + $this->info['second_ring_road_price']
                + $this->info['reward_price'] - $this->info['fine_price'] + $this->info['other_price']) * 100,
            'base_price' => $this->info['base_price'] * 100,
            'refer_price' => $this->info['refer_price'] * 100,
            'times' => $this->info['times'],
            'other_price' => Logistics_Api::generateDriverFee($otherPriceData),
            'money_note' => $this->info['money_note'],
        );

        if (!empty($order['line_id']))
        {
            $lineInfo = Logistics_Order_Api::getByLineId($order['line_id']);
            $oids = explode(',', $lineInfo['oids']);
            if (!empty($order['source_oid']) && is_array($oids) && in_array($order['source_oid'], $oids))
            {
                throw new Exception('该订单是补单，且与主单在一条排线上，所以该订单不能添加运费！');
            }
        }

        $this->_checkStatement($coopworkerOrder['statement_id']);
        Logistics_Coopworker_Api::updateOrderCoopworkerById($this->info['id'], $data);
        Logistics_Coopworker_Api::updateStatementPrice($coopworkerOrder['statement_id']);

        // 已经支付，修改 财务支出
        $this->_updateFinanceCoopworkerMoneyOut($coopworkerOrder, $data);

        $param['role'] = '司机';
        $driver = Logistics_Api::getDriver($coopworkerOrder['cuid']);
        $param['name'] = $driver['name'];
        $param['price'] = $data['price']/100;
        $param['reason'] = $data['money_note'];
        $param['action'] = '修改司机费用';
        $param['id'] = $coopworkerOrder['cuid'];
        Admin_Api::addOrderActionLog($this->_uid, $coopworkerOrder['oid'], Conf_Order_Action_Log::ACTION_EDIT_COOPWORKER, $param);
    }

    private function _saveEditCoopworkerPriceForDriver_2($coopworkerOrder, $orderInfo)
    {
        $ld = new Logistics_Driver();
        $coopworkerOrder['_driver'] = $ld->get($coopworkerOrder['cuid']);

        $result = array();
        if (!empty($orderInfo['line_id']))
        {
            $lineInfo = Logistics_Order_Api::getByLineId($orderInfo['line_id']);
            if (!empty($lineInfo))
            {
                $oidsInLine = explode(',', $lineInfo['oids']);
                $lineInfo['merge_order'] = array_diff($oidsInLine, array($orderInfo['oid']));
                if (!empty($oidsInLine))
                {
                    $result = Order_Community_Api::getBaseDriverFeesByOids($oidsInLine);
                }
            }
        }
        else
        {
            $result = Order_Community_Api::getBaseDriverFeesByOids(array($orderInfo['oid']));
        }

        if ($result['oid'] == $coopworkerOrder['oid'])
        {
            $referPrice = $result['fee']['fee'][$coopworkerOrder['car_model']];
        }
        else
        {
            $referPrice = 0;
        }

        if (empty($this->info['money_note']) && ($this->info['price'] * 100) != $referPrice)
        {
            throw new Exception('录入费用与推荐费用不一致时，必须填写备注！');
        }
        $data = array(
            'price' => $this->info['price'] * 100,
            'base_price' => $this->info['price'] * 100,
            'refer_price' => $this->info['refer_price'] * 100,
            'money_note' => $this->info['money_note'],
        );

        $this->_checkStatement($coopworkerOrder['statement_id']);
        Logistics_Coopworker_Api::updateOrderCoopworkerById($this->info['id'], $data);
        Logistics_Coopworker_Api::updateStatementPrice($coopworkerOrder['statement_id']);

        // 已经支付，修改 财务支出
        $this->_updateFinanceCoopworkerMoneyOut($coopworkerOrder, $data);

        $param['role'] = '司机';
        $driver = Logistics_Api::getDriver($coopworkerOrder['cuid']);
        $param['name'] = $driver['name'];
        $param['price'] = $data['price']/100;
        $param['reason'] = $data['money_note'];
        $param['action'] = '修改司机费用';
        $param['id'] = $coopworkerOrder['cuid'];
        Admin_Api::addOrderActionLog($this->_uid, $coopworkerOrder['oid'], Conf_Order_Action_Log::ACTION_EDIT_COOPWORKER, $param);
    }

    private function _saveEditCoopworkerPriceForCarrier_1($coopworkerOrder, $order)
    {
        if (empty($this->info['money_note']) && (!empty($this->info['reward_price']) || !empty($this->info['fine_price'])
                || !empty($this->info['other_price'])))
        {
            throw new Exception('奖励、处罚、其他费用必须填写备注！');
        }

        // $carrierFee = Logistics_Api::calCarryFee4Carrier($coopworkerOrder['oid']);
        // if ($order['service'] == 1)
        // {
        //     $referPrice = $carrierFee['worker']['ele'];
        // }
        // else
        // {
        //     $referPrice = $carrierFee['worker']['common'] * $order['floor_num'];
        // }
        $client = new Yar_Client(MS . "/cmpt/order/fees");
        $result = $client->AdminCarryFee($coopworkerOrder['oid'], $order['floor_num'], $order['service']);
        $referPrice = 0;
        if ( isset($result['worker']) ) {
            $referPrice = $result['worker'];
        }

        $otherPriceData = array(
            Conf_Driver::DRIVER_FEE_TYPE_REWARD => $this->info['reward_price'],
            Conf_Driver::DRIVER_FEE_TYPE_FINE => $this->info['fine_price'],
            Conf_Driver::DRIVER_FEE_TYPE_OTHER => $this->info['other_price'],
        );
        $otherPrice = Logistics_Api::generateDriverFee($otherPriceData);
        $data = array(
            'price' => ($this->info['base_price'] + $this->info['reward_price'] - $this->info['fine_price']
                + $this->info['other_price']) * 100,
            'base_price' => $this->info['base_price'] * 100,
            'refer_price' => $this->info['refer_price'] * 100,
            'other_price' => $otherPrice,
            'money_note' => $this->info['money_note'],
        );

        $lc = new Logistics_Coopworker();
        $where = array('oid' => $coopworkerOrder['oid'], 'status' => Conf_Base::STATUS_NORMAL, 'type' => Conf_Base::COOPWORKER_CARRIER);
        $carrierTotalFees = array_sum(Tool_Array::getFields($lc->getListByWhere($where), 'base_price'));

        if (($carrierTotalFees + $data['base_price'] - $coopworkerOrder['base_price']) > $referPrice)
        {
            throw new Exception('订单中所有搬运工的应得费用总和不能大于推荐费用！');
        }

        $this->_checkStatement($coopworkerOrder['statement_id']);
        Logistics_Coopworker_Api::updateOrderCoopworkerById($this->info['id'], $data);
        Logistics_Coopworker_Api::updateStatementPrice($coopworkerOrder['statement_id']);

        // 已经支付，修改 财务支出
        $this->_updateFinanceCoopworkerMoneyOut($coopworkerOrder, $data);

        $param['role'] = '搬运工';
        $carrier = Logistics_Api::getCarrier($coopworkerOrder['cuid']);
        $param['name'] = $carrier['name'];
        $param['price'] = $data['price']/100;
        $param['reason'] = $data['money_note'];
        $param['action'] = '修改搬运工费用';
        $param['id'] = $coopworkerOrder['cuid'];
        Admin_Api::addOrderActionLog($this->_uid, $coopworkerOrder['oid'], Conf_Order_Action_Log::ACTION_EDIT_COOPWORKER, $param);
    }

    private function _saveEditCoopworkerPriceForCarrier_2($coopworkerOrder, $order)
    {
        $lc = new Logistics_Coopworker();
        $referPrice = 0;
        if ($coopworkerOrder['user_type'] == Conf_Base::COOPWORKER_DRIVER)
        {
            $where = array(
                'oid' => $coopworkerOrder['oid'],
                'cuid' => $coopworkerOrder['cuid'],
                'status' => Conf_Base::STATUS_NORMAL,
                'type' => Conf_Base::COOPWORKER_DRIVER,
                'user_type' => Conf_Base::COOPWORKER_DRIVER,
            );
            $list = $lc->getListByWhere($where, array('car_model'));
            $referPrice = Conf_Coopworker::$LF_CARRIER_FEE_RULES[$list[0]['car_model']];
        }

        if (empty($this->info['money_note']) && ($this->info['price'] * 100) != $referPrice)
        {
            throw new Exception('录入费用与推荐费用不一致时，必须填写备注！');
        }

        $data = array(
            'price' => $this->info['price'] * 100,
            'base_price' => $this->info['price'] * 100,
            'refer_price' => $this->info['refer_price'] * 100,
            'money_note' => $this->info['money_note'],
        );

        if (!empty($order['line_id']))
        {
            $lineInfo = Logistics_Order_Api::getByLineId($order['line_id']);
            $oids = explode(',', $lineInfo['oids']);
            $where = array('oid' => $oids, 'status' => Conf_Base::STATUS_NORMAL, 'cuid' => $coopworkerOrder['cuid'], 'type' => Conf_Base::COOPWORKER_CARRIER);
            $otherCarrierTotalFees = array_sum(Tool_Array::getFields($lc->getListByWhere($where), 'price'));
            if ($otherCarrierTotalFees - $coopworkerOrder['price'] > 0)
            {
                throw new Exception('该搬运工在排线中其他的订单中已经录入费用，请勿重复录入！');
            }
        }

        $this->_checkStatement($coopworkerOrder['statement_id']);
        Logistics_Coopworker_Api::updateOrderCoopworkerById($this->info['id'], $data);
        Logistics_Coopworker_Api::updateStatementPrice($coopworkerOrder['statement_id']);

        // 已经支付，修改 财务支出
        $this->_updateFinanceCoopworkerMoneyOut($coopworkerOrder, $data);

        $param['role'] = '搬运工';
        $carrier = Logistics_Api::getCarrier($coopworkerOrder['cuid']);
        $param['name'] = $carrier['name'];
        $param['price'] = $data['price']/100;
        $param['reason'] = $data['money_note'];
        $param['action'] = '修改搬运工费用';
        $param['id'] = $coopworkerOrder['cuid'];
        Admin_Api::addOrderActionLog($this->_uid, $coopworkerOrder['oid'], Conf_Order_Action_Log::ACTION_EDIT_COOPWORKER, $param);
    }

    private function _saveEditCoopworkerPriceForCarrier_4($coopworkerOrder)
    {
        $lc = new Logistics_Coopworker();
        $referPrice = 0;
        if ($coopworkerOrder['user_type'] == Conf_Base::COOPWORKER_DRIVER)
        {
            $where = array(
                'oid' => $coopworkerOrder['oid'],
                'cuid' => $coopworkerOrder['cuid'],
                'status' => Conf_Base::STATUS_NORMAL,
                'type' => Conf_Base::COOPWORKER_DRIVER,
                'user_type' => Conf_Base::COOPWORKER_DRIVER,
            );
            $list = $lc->getListByWhere($where, array('car_model'));
            $referPrice = Conf_Coopworker::$LF_CARRIER_FEE_RULES[$list[0]['car_model']];
        }

        if (empty($this->info['money_note']) && ($this->info['price'] * 100) != $referPrice)
        {
            throw new Exception('录入费用与推荐费用不一致时，必须填写备注！');
        }

        $data = array(
            'price' => $this->info['price'] * 100,
            'base_price' => $this->info['price'] * 100,
            'refer_price' => $this->info['refer_price'] * 100,
            'money_note' => $this->info['money_note'],
        );

        $this->_checkStatement($coopworkerOrder['statement_id']);
        Logistics_Coopworker_Api::updateOrderCoopworkerById($this->info['id'], $data);
        Logistics_Coopworker_Api::updateStatementPrice($coopworkerOrder['statement_id']);

        // 已经支付，修改 财务支出
        $this->_updateFinanceCoopworkerMoneyOut($coopworkerOrder, $data);

        $param['role'] = '搬运工';
        $carrier = Logistics_Api::getCarrier($coopworkerOrder['cuid']);
        $param['name'] = $carrier['name'];
        $param['price'] = $data['price']/100;
        $param['reason'] = $data['money_note'];
        $param['action'] = '修改搬运工费用';
        $param['id'] = $coopworkerOrder['cuid'];
        Admin_Api::addOrderActionLog($this->_uid, $coopworkerOrder['oid'], Conf_Order_Action_Log::ACTION_EDIT_COOPWORKER, $param);
    }

    private function _checkStatement($id)
    {
        if ($id) {
            $lcs = new Logistics_Coopworker_Statement();
            $statementInfo = $lcs->getById($id);
            if ($statementInfo['step'] >= Conf_Coopworker::STATEMENT_STEP_CHECK) {
                throw new Exception('结算单' . Conf_Coopworker::$Statement_Step[$statementInfo['step']] . '不能修改');
            }
        }
    }

    private function _updateFinanceCoopworkerMoneyOut($coopworkerOrder, $data)
    {
        if ($coopworkerOrder['paid'] == 1)
        {
            Finance_Api::updateCoopworker($coopworkerOrder['oid'], $coopworkerOrder['cuid'], $coopworkerOrder['type'], $data, Conf_Coopworker::OBJ_TYPE_ORDER);
        }
    }
}

$app = new App();
$app->run();