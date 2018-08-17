<?php

class Queue_Inventory extends Queue_Base
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
        
        $suid = $info['suid'];
        unset($info['suid']);
        
        $isDealProducts = Warehouse_Api::getDiffProductList($info);
        
        $st = Data_Queue::QUEUE_ITEM_FINISHED;
        $msg = '';
        foreach ($isDealProducts as $product)
        {
            try{
                Warehouse_Location_Api::saveCheckLocation($product['sid'], $product['location'], $product['wid'],
                    $product['last_num'], $product['note'], $suid, 5, -1, true);
            } catch (Exception $e) {
                $st = Data_Queue::QUEUE_ITEM_FAILED;
                $msg .= sprintf("\tFailed_Record# plan_id:%d\tsid:%d\tlocation:%s\twid:%d\tlast_num:%d\tnote:%s\n",
                            $info['pid'], $product['sid'], $product['location'], $product['wid'], $product['last_num'], $e->getMessage());
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