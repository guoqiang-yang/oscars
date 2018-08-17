<?php

class Queue_Out_Source extends Queue_Base
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
            switch ($info['type'])
            {
                case 'add':
                    $one = Data_One::getInstance();
                    
                    $preOpWhere = sprintf('outsourcer_id=%d and wid=%d and status=0 and tmp_inorder_id=0 and sid in (%s)',
                                    $info['sid'], $info['wid'], implode(',', $info['sids']) );
                    
                    $oWhere = sprintf('status=0 and wid=%d and ship_time>="%s 00:00:00" and ship_time<="%s 23:59:59"', $info['wid'], $info['bdate'],$info['edate']);
                    $orderList = Order_Api::getOrderListByWhere($oWhere, array(), 0, 0, array('oid'));
                    $oids = Tool_Array::getFields($orderList, 'oid');
                    
                    $_step = 500;
                    $_beginPos = 0;
                    $dealOids = array_slice($oids, $_beginPos, $_step);
                    while (!empty($dealOids))
                    {
                        $opWhere = sprintf(' and vnum>refund_vnum+tmp_inorder_num and rid=0 and oid in (%s)', implode(',', $dealOids));
                        $_upData = array(
                           'tmp_inorder_id' => $oid, 
                           'tmp_inorder_num' => 'vnum-refund_vnum',
                        );
                        
                        $one->setDBMode()->update('t_order_product', $_upData, array(), $preOpWhere.$opWhere, array('tmp_inorder_num'));
                        
                        $_beginPos += $_step;
                        $dealOids = array_slice($oids, $_beginPos, $_step);
                    }
                    
                    //更新退单
                    $hRefund = new Data_Dao('t_refund');
                    $rWhere = sprintf('status=0 and wid=%d and stockin_time>="%s 00:00:00" and stockin_time<="%s 23:59:59"', 
                                    $info['wid'], $info['bdate'], $info['edate']);
                    $refundList = $hRefund->setFields(array('rid'))->getListWhere($rWhere);
                    $rids = Tool_Array::getFields($refundList, 'rid');
                    
                    if (!empty($rids))
                    {
                        $rpWhere = sprintf(' and  rid in(%s)', implode(',', $rids));
                        $_upRdata = array(
                            'tmp_inorder_id' => $oid, 
                            'tmp_inorder_num' => 'picked+damaged_num',
                        );
                        $one->setDBMode()->update('t_order_product force index (rid)', $_upRdata, array(), $preOpWhere.$rpWhere, array('tmp_inorder_num'));
                    }
                    
                    break;
                case 'delete':
                    $op = new Data_Dao('t_order_product');
                    $upRet = $op->updateWhere(array('tmp_inorder_id'=>$oid), array('tmp_inorder_id'=>0, 'tmp_inorder_num' => 0));
                    
                    if (empty($upRet))
                    {
                        $st = Data_Queue::QUEUE_ITEM_FAILED;
                    }
                    
                    break;
                default:
                    break;
            }

        } catch (Exception $e) {
            $st = Data_Queue::QUEUE_ITEM_FAILED;
            $msg .= sprintf("\tFailed_Record# oid:%d\ttype:%d\tnote:%s\n", $oid, $info['type'], $e->getMessage());
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