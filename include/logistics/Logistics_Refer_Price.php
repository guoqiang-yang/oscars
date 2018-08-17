<?php

Class Logistics_Refer_Price extends Base_Func
{
    private $times = 1;

    public function setTimes($times)
    {
        $this->times = $times;

        return $this;
    }

    public function calReferPriceByCoopworkerOrderId($id)
    {
        $lc = new Logistics_Coopworker();
        $coopworkerOrder = $lc->get($id);
        $orderInfo = Order_Api::getOrderInfo($coopworkerOrder['oid']);
        $result = array();
        if ($coopworkerOrder['type'] == Conf_Base::COOPWORKER_DRIVER)
        {
            // 获取订单排线信息
            if (!empty($orderInfo['line_id']))
            {
                $lineInfo = Logistics_Order_Api::getByLineId($orderInfo['line_id']);
                if (!empty($lineInfo))
                {
                    $oidsInLine = explode(',', $lineInfo['oids']);
                    $lineInfo['merge_order'] = array_diff($oidsInLine, array($orderInfo['oid']));
                    if (!empty($oidsInLine))
                    {
                        //获取基础运费
                        $result = Order_Community_Api::getBaseDriverFeesByOids($oidsInLine);
                    }
                }
            }
            else
            {
                $result = Order_Community_Api::getBaseDriverFeesByOids(array($orderInfo['oid']));
            }
        }

        $template = Conf_Coopworker::getEditCoopworkerFlagForWid($coopworkerOrder['wid'], $coopworkerOrder['type']);
        switch ($template)
        {
            case 'driver_1':
                $referPrice = $this->_getPriceForDrver_1($coopworkerOrder, $result);
                if (!empty($oidsInLine) && in_array($orderInfo['source_oid'], $oidsInLine))
                {
                    $referPrice = 0;
                }
                break;
            case 'driver_2':
                $referPrice = $this->_getPriceForDrver_2($coopworkerOrder, $result);
                break;
            case 'driver_3':
                $referPrice = $this->_getPriceForDrver_3($coopworkerOrder, $result);
                break;
            case 'driver_4':
                $referPrice = $this->_getPriceForDrver_4($coopworkerOrder, $result);
                break;
            case 'driver_5':
                $referPrice = $this->_getPriceForDrver_5($coopworkerOrder, $result);
                break;
            case 'carrier_1':
                $referPrice = $this->_getPriceForCarrier_1($coopworkerOrder, $orderInfo);
                break;
            case 'carrier_2':
                $referPrice = $this->_getPriceForCarrier_2($coopworkerOrder);
                break;
            default :
                $referPrice = 0;
                break;
        }
        
        return array('refer_price'=>$referPrice, 'distance'=>$result['fee']['distance'], 'base_price' => $result['fee']['fee'][$coopworkerOrder['car_model']]);
    }

    private function _getPriceForDrver_1(&$coopworkerOrder, $result)
    {
        $rule = Conf_Driver::$DRIVER_FEE_RULES[Conf_Driver::$WAREHOUSE_DRIVER_FEE_RULES[$coopworkerOrder['wid']]];

        if ($result['oid'] == $coopworkerOrder['oid'])
        {
            $referPrice = $result['fee']['fee'][$coopworkerOrder['car_model']];
            $referPrice += ($referPrice - $rule[$coopworkerOrder['car_model']]['decline_fee']) * ($this->times - 1);

        }
        else
        {
            $referPrice = $rule[$coopworkerOrder['car_model']]['second_order_fee'];
        }

        return $referPrice;
    }

    private function _getPriceForDrver_2(&$coopworkerOrder, $result)
    {

        if ($result['oid'] == $coopworkerOrder['oid'])
        {
            $referPrice = $result['fee']['fee'][$coopworkerOrder['car_model']];
        }
        else
        {
            $referPrice = 0;
        }

        return $referPrice;
    }

    private function _getPriceForDrver_3(&$coopworkerOrder, $result)
    {

        if ($result['oid'] == $coopworkerOrder['oid'])
        {
            $referPrice = $result['fee']['fee'][$coopworkerOrder['car_model']];
        }
        else
        {
            $referPrice = Conf_Driver::$TIANJIN_CAR_MODEL_FEE_RULES[$coopworkerOrder['car_model']]['second_order_fee'];
        }

        return $referPrice;
    }

    private function _getPriceForDrver_4(&$coopworkerOrder, $result)
    {

        if ($result['oid'] == $coopworkerOrder['oid'])
        {
            $referPrice = $result['fee']['fee'][$coopworkerOrder['car_model']];
        }
        else
        {
            $referPrice = Conf_Driver::$QINGDAO_CAR_MODEL_FEE_RULES[$coopworkerOrder['car_model']]['second_order_fee'];
        }

        return $referPrice;
    }

    private function _getPriceForDrver_5(&$coopworkerOrder, $result)
    {

        if ($result['oid'] == $coopworkerOrder['oid'])
        {
            $referPrice = $result['fee']['fee'][$coopworkerOrder['car_model']];
        }
        else
        {
            $referPrice = Conf_Driver::$CHONGQING_CAR_MODEL_FEE_RULES[$coopworkerOrder['car_model']]['second_order_fee'];
        }

        return $referPrice;
    }

    private function _getPriceForCarrier_1(&$coopworkerOrder, $orderInfo)
    {
        // $maxCarrierFee = Logistics_Api::calCarryFee4Carrier($coopworkerOrder['oid']);
        // switch ($orderInfo['service'])
        // {
        //     case 1:
        //         $referPrice = $maxCarrierFee['worker']['ele'];
        //         break;
        //     case 2:
        //         $referPrice = $maxCarrierFee['worker']['common'] * $orderInfo['floor_num'];
        //         break;
        //     default :
        //         $referPrice = 0;
        //         break;
        // }

        $client = new Yar_Client(MS . "/cmpt/order/fees");
        $result = $client->AdminCarryFee($coopworkerOrder['oid']);
        $referPrice = 0;
        if ( isset($result['worker']) ) {
            $referPrice = $result['worker'];
        }
        return $referPrice;
    }

    private function _getPriceForCarrier_2(&$coopworkerOrder)
    {
        // if ($coopworkerOrder['user_type'] == Conf_Base::COOPWORKER_DRIVER)
        // {
        //     $lc = new Logistics_Coopworker();
        //     $where = array(
        //         'oid' => $coopworkerOrder['oid'],
        //         'cuid' => $coopworkerOrder['cuid'],
        //         'status' => Conf_Base::STATUS_NORMAL,
        //         'type' => Conf_Base::COOPWORKER_DRIVER,
        //         'user_type' => Conf_Base::COOPWORKER_DRIVER,
        //     );
        //     $list = $lc->getListByWhere($where, array('car_model'));
        //     $referPrice = Conf_Coopworker::$LF_CARRIER_FEE_RULES[$list[0]['car_model']];
        // }
        // else
        // {
        //     $referPrice = 0;
        // }

        $client = new Yar_Client(MS . "/cmpt/order/fees");
        $result = $client->AdminCarryFee($coopworkerOrder['oid']);
        $referPrice = 0;
        if ( isset($result['worker']) ) {
            $referPrice = $result['worker'];
        }
        return $referPrice;
    }
}