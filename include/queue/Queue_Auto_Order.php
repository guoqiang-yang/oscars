<?php

class Queue_Auto_Order extends Queue_Base
{
    
    public function doJob($info)
    {
        if (empty($info))
        {
            return array(
                'st' => Data_Queue::QUEUE_ITEM_FAILED,
                'msg' => '[Job Info is Empty]',
            );
        }
        
        $oid = $info['oid'];

        $st = Data_Queue::QUEUE_ITEM_FINISHED;
        $msg = '';
        try{
            $result =Order_Api::autoSeparateOrder($oid);
            if($result['errno'] > 0)
            {
                $orderInfo = Order_Api::getOrderInfo($oid);
                if($orderInfo['saler_suid'] > 0)
                {
                    $saleInfo = Admin_Api::getStaff($orderInfo['saler_suid']);
                }
                if(empty($saleInfo['ding_id']))
                {
                    $saleInfo = Admin_Api::getStaff(1008);
                }
                $st = Data_Queue::QUEUE_ITEM_FAILED;
                $msg .= sprintf("\tFailed_Record# oid:%d\tnote:%s\n", $oid, $result['errmsg']);
                if(!empty($saleInfo['ding_id']))
                {
                    Tool_DingTalk::sendAutoOrderMessage($saleInfo['ding_id'], $oid, $result['errmsg']);
                }
            }
        } catch (Exception $e) {
            $st = Data_Queue::QUEUE_ITEM_FAILED;
            $msg .= sprintf("\tFailed_Record# oid:%d\tnote:%s\n", $oid, $e->getMessage());
            $adminInfos = Admin_Api::getStaffs(array(1004,1029,1036,1289));
            $userIds = array();
            foreach ($adminInfos as $item)
            {
                if(!empty($item['ding_id']))
                {
                    $userIds[] = $item['ding_id'];
                }
            }
            if(!empty($userIds))
            {
                Tool_DingTalk::sendAutoOrderMessage($userIds, $oid, $msg);
            }
        }
        
        if ($st == Data_Queue::QUEUE_ITEM_FINISHED)
        {
            $msg .= '[Job Execute Success]';
        }
        else
        {
            $msg .= '[Job Execute Failed]';
        }
        
        return array(
            'st' => $st,
            'msg' => $msg,
        );
    }
    
}