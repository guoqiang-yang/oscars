<?php

include_once ('../../../global.php');;

class App extends App_Admin_Ajax
{
    private $lineId;
    private $opType;
    private $dids;
    private $reason;
    private $oid;
    private $carModelInfo = array();
    
    protected function getPara()
    {
        $this->lineId = Tool_Input::clean('r', 'line_id', TYPE_UINT);
        $this->opType = Tool_Input::clean('r', 'op_type', TYPE_STR);
        $this->dids = json_decode(Tool_Input::clean('r', 'dids', TYPE_STR), true);
        $this->reason = Tool_Input::clean('r', 'reason', TYPE_STR);
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        
        $this->carModelInfo = array(
            'car_model' => Tool_Input::clean('r', 'car_model', TYPE_UINT),
            'did' => Tool_Input::clean('r', 'did', TYPE_UINT),
            'step' => Tool_Input::clean('r', 'step', TYPE_UINT),
            'price' => Tool_Input::clean('r', 'price', TYPE_UINT)*100,
        );
    }
    
    protected function checkPara()
    {
        if (empty($this->lineId) || empty($this->opType))
        {
            throw new Exception('参数错误！');
        }
        if (!in_array($this->opType, array('del_modify_order', 'add_modify_order', 
                                'reject', 'cancel', 'add_chg_carmodel', 'del_chg_carmodel')))
        {
            throw new Exception('操作类型非法！');
        }
        if(($this->opType=='reject'||$this->opType=='cancel')&& empty($this->reason))
        {
            throw new Exception('请填写拒单原因！');
        }

    }

    protected function main()
    {
        Logistics_Order_Api::chgOrderLineAndDriver($this->lineId, $this->dids, $this->oid, $this->carModelInfo, $this->opType, $this->reason, $this->_uid);
    }
    
    protected function outputBody()
    {
        $response = new Response_Ajax();
		$response->setContent(array('ret'=>1));
		$response->send();
        
		exit;
    }
    
}

$app = new App();
$app->run();