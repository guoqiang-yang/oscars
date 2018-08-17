<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $oid;
    private $wid;
    private $objid;
    private $type;
    private $products;
    
    private $response;
    
    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
        $this->objid = Tool_Input::clean('r','objid', TYPE_UINT);
        $this->type = Tool_Input::clean('r', 'type', TYPE_UINT);
        $products = json_decode(Tool_Input::clean('r', 'products', TYPE_STR), true);
        $this->products = Tool_Array::list2Map($products, 'sid');
        
        $this->response = array('errno'=>0, 'errmsg'=>'');
    }
    
    protected function checkPara()
    {
        if (empty($this->objid) || empty($this->type) || empty($this->products))
        {
            throw new Exception('common:params error');
        }
        
        if (!array_key_exists($this->type, Conf_Warehouse::$Virtual_Flags))
        {
            throw new Exception('改单据商品不能上架！');
        }
        
        foreach ($this->products as &$p)
        {
            $p['loc'] = trim($p['loc']);
            $chkRet = Warehouse_Location_Api::checkLocaton($p['loc'], false);
            
            if (!$chkRet)
            {
                $this->response['errno'] = 10;
                $this->response['errmsg'] = '货位号格式错误，请检查，正确格式: [如 C-01-10-99]';
            }
        }
        
        $locations = Tool_Array::getFields($this->products, 'loc');
        $sids = Tool_Array::getFields($this->products, 'sid');
        
        // sku->货位 试行多对多！原来是一对多【去掉检测】
//        if(count(array_unique($locations))!=count(array_unique($sids)))
//        {
//            $this->response['errno'] = 11;
//            $this->response['errmsg'] = '存在相同的货位号，请检查！';
//        }
    }
    
    protected function checkAuth()
    {
        parent::checkAuth('/warehouse/shelved_detail');
    }
    
    protected function main()
    {
        if ($this->response['errno'] != 0)
        {
            return;
        }
        
        Warehouse_Location_Api::billShelved($this->objid, $this->type, $this->products, $this->_uid);
        
        //入库单上架日志
        switch ($this->type)
        {
            case Conf_Warehouse::VFLAG_STOCK_IN:
                $info = array(
                    'admin_id' => $this->_uid,
                    'obj_id' => $this->oid,
                    'obj_type' => Conf_Admin_Log::OBJTYPE_IN_ORDER,
                    'action_type' => 5,
                    'params' => json_encode(array('id' => $this->objid)),
                    'wid' => $this->wid,
                );
                Admin_Common_Api::addAminLog($info);
                break;
            
            case Conf_Warehouse::VFLAG_ORDER_REFUND:
                $exchangedList = Exchanged_Api::getExchangedList(array('refund_id'=>$this->objid));
                if($exchangedList['total']>0)
                {
                    $exchangedInfo = reset($exchangedList['list']);
                    //判断对应的补单是否已完成，如果完成换货单状态自动变为已完成
                    $orderInfo = Order_Api::getOrderInfo($exchangedInfo['aftersale_oid']);
                    if($orderInfo['step']== Conf_Order::ORDER_STEP_FINISHED)
                    {
                        Exchanged_Api::updateExchanged($exchangedInfo['eid'],array('step'=>Conf_Exchanged::EXCHANGED_STEP_FINISHED));
                        Admin_Api::addOrderActionLog($this->_uid, $exchangedInfo['oid'], Conf_Order_Action_Log::ACTION_FINISHED_EXCHANGED_ORDER, array('eid'=>$exchangedInfo['eid']));
                    }
                }
                break;
                
            case Conf_Warehouse::VFLAG_SHIFT:
                $info = array(
                    'admin_id' => $this->_uid,
                    'obj_id' => $this->objid,
                    'obj_type' => Conf_Admin_Log::OBJTYPE_SATOCK_SHIFT,
                    'action_type' => 8,
                    'wid' => $this->wid,
                    'params' => '',
                );
                Admin_Common_Api::addAminLog($info);
                break;
            
            default:
                break;
        }
    }
    
    protected function outputBody()
    {
        $response = new Response_Ajax();
		$response->setContent($this->response);
        
		$response->send();
    }
    
}

$app = new App();
$app->run();