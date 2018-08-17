<?php

class Queue_Base 
{
    
    const Queue_Type_Inventory = 1;         //盘点：将盘点结果导入到库存
    const Queue_Type_Auto_Order = 2;        //自动分单
    const Queue_Type_OutSourcer = 3;        //外包采购单更新订单商品外包商ID
    
   public static function getInstance($type)
   {
        $className = 'Queue_'. ucfirst(self::_getTypeDesc($type));

        if (class_exists($className))
        {
            $handle = new $className;
        }
        else
        {
            $handle = null;
        }
        
        return $handle;
   }
   
   public function doJob($info)
   {
       return array(
           'st' => Data_Queue::QUEUE_ITEM_FAILED,
           'msg' => '[Job Instance Execute Base Job]',
       );
   }
   
   private static function _getTypeDesc($type)
   {
       $allTypeDescs = array(
           self::Queue_Type_Inventory => 'inventory',
           self::Queue_Type_Auto_Order => 'auto_Order',
           self::Queue_Type_OutSourcer => 'out_Source',
       );
       
       return array_key_exists($type, $allTypeDescs)? $allTypeDescs[$type]: '';
   }
}