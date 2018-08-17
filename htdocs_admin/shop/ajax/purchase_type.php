<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $buyType;
    private $pid;
    private $changed;

    protected function getPara()
    {
        $this->buyType = Tool_Input::clean('r', 'buy_type',TYPE_UINT);
        $this->pid = Tool_Input::clean('r', 'pid',TYPE_UINT);
    }

    protected function main()
    {
        $tp = new Shop_Product();
        $productInfo = $tp->get($this->pid);
        
        if ($productInfo['buy_type'] == $this->buyType)
        {
            throw new Exception('没有变更采购状态，无需保存');
        }
        
        $tp->update($this->pid, array('buy_type' => $this->buyType));
        
        //日志
        $buyTypes = Conf_Product::getBuyTypeDesc();
        $this->changed .= '采购类型:' . $productInfo['buy_type'] . '=>' . $buyTypes[$this->buyType] . ',';
        $logInfo = array(
            'admin_id' => $this->_uid,
            'obj_id' => $this->pid,
            'obj_type' => Conf_Admin_Log::OBJTYPE_PRODUCT,
            'action_type' => 2,
            'params' => json_encode(array('pid' => $this->pid, 'changed' => $this->changed)),
        );
        Admin_Common_Api::addAminLog($logInfo);

    }
    
    protected function outputPage()
    {
        $result = array('pid' => $this->pid);
        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }

}

$app = new App('pri');
$app->run();

