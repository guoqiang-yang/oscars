<?php

/**
 * 加盟商订单相关接口
 */
class Order_Franchisee_Api
{
    /**
     * 订单状态流转.
     * 
     * @param int $oid      订单id
     * @param int $nextStep 下一个状态
     * @param array $staff  操作人信息
     */
    public static function forwardOrderStep($oid, $nextStep, $staff)
    {
        $osf = new Order_Step_Flow($oid, $nextStep);
        $osf->setOperator($staff);
        
        switch ($nextStep)
        {
            case Conf_Order::ORDER_STEP_SURE:
            case Conf_Order::ORDER_STEP_BOUGHT:
            case Conf_Order::ORDER_STEP_HAS_DRIVER:    
                $osf->sure();   break;
            case Conf_Order::ORDER_STEP_PICKED:
                //$osf->checkPicked();
                $osf->dispatch();
                break;
            case Conf_Order::ORDER_STEP_FINISHED:
                $osf->finished();
                break;
            default :
                break;
        }
        
    }
}