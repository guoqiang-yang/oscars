<?php

include_once ('../../../global.php');

Class App extends App_Admin_Ajax
{
    private $id;    //t_coopworker_order 表中的id字段
    private $html;

    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
    }

    protected function checkAuth()
    {
        parent::checkAuth('hc_order_edit_coopworker');
    }

    protected function main()
    {
        $lc = new Logistics_Coopworker();
        $coopworkerOrder = $lc->get($this->id);
        $lrp = new Logistics_Refer_Price();
        $ret = $lrp->setTimes($coopworkerOrder['times'])->calReferPriceByCoopworkerOrderId($this->id);
        $referPrice = $ret['refer_price'];


        if ($coopworkerOrder['user_type'] == Conf_Base::COOPWORKER_DRIVER)
        {
            $ld = new Logistics_Driver();
            $coopworkerOrder['_driver'] = $ld->get($coopworkerOrder['cuid']);
            if ($coopworkerOrder['type'] == Conf_Base::COOPWORKER_DRIVER) {
                $coopworkerOrder['_driver'] = $ld->get($coopworkerOrder['cuid']);
            } else {
                $coopworkerOrder['_carrier'] = $ld->get($coopworkerOrder['cuid']);
            }
        }
        else if ($coopworkerOrder['user_type'] == Conf_Base::COOPWORKER_CARRIER)
        {
            $lc = new Logistics_Carrier();
            $coopworkerOrder['_carrier'] = $lc->get($coopworkerOrder['cuid']);
        }

        if (!empty($coopworkerOrder['other_price']))
        {
            $coopworkerOrder['_other_price'] = Logistics_Api::parseDriverFee($coopworkerOrder['other_price']);
        }

        $template = Conf_Coopworker::getEditCoopworkerFlagForWid($coopworkerOrder['wid'], $coopworkerOrder['type']);
        switch ($template)
        {
            case 'driver_1':
                $coopworkerOrder['base_price'] = $ret['base_price'];
                $orderInfo = Order_Api::getOrderInfo($coopworkerOrder['oid']);
                if (!empty($oidsInLine) && in_array($orderInfo['source_oid'], $oidsInLine))
                {
                    $referPrice = 0;
                }
                $declineFee = Conf_Driver::$DRIVER_FEE_RULES[Conf_Driver::$WAREHOUSE_DRIVER_FEE_RULES[$coopworkerOrder['wid']]][$coopworkerOrder['car_model']]['decline_fee'];
                $this->smarty->assign('decline_fee', $declineFee);
                break;
            case 'driver_2':
                $this->smarty->assign('distance', round($ret['distance']/1000, 2));
                break;
            case 'driver_3':
                $this->smarty->assign('distance', round($ret['distance']/1000, 2));
                break;
            case 'driver_4':
                $this->smarty->assign('distance', round($ret['distance']/1000, 2));
                break;
            case 'driver_5':
                $this->smarty->assign('distance', round($ret['distance']/1000, 2));
                break;
            case 'carrier_1':
                break;
            case 'carrier_2':
                break;
            case 'carrier_3':
                break;
            default :
                break;
        }

        $coopworkerOrder['refer_price'] = $referPrice;

        $this->smarty->assign('driver_fee_types', Conf_Driver::$DRIVER_FEE_TYPES);
        $this->smarty->assign('coopworker_order', $coopworkerOrder);
        $this->smarty->assign('car_models', Conf_Driver::$CAR_MODEL);
        $this->smarty->assign('template', $template);
        $this->html = $this->smarty->fetch('common/block_edit_coopworker.html');
    }

    protected function outputBody()
    {
        $result = array('id' => $this->id, 'html' => $this->html);
        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}
$app = new App();
$app->run();