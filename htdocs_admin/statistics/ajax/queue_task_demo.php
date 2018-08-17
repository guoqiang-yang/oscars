<?php
include_once('../../../global.php');
/**
 * 添加任务demo
 * @author wangxuemin
 */
class App extends App_Admin_Ajax
{
    private $result;
    private $stime;
    private $mtime;
    private $city;
    private $warehouse;
    private $type;

    /**
     * (non-PHPdoc)
     * @see Base_App::getPara()
     */
    protected function getPara()
    {
        $this->stime = Tool_Input::clean('r', 'stime', TYPE_STR);
        $this->mtime = Tool_Input::clean('r', 'mtime', TYPE_STR);
        $this->type = Tool_Input::clean('r', 'type', TYPE_UINT);
        $this->city = Tool_Input::clean('r', 'city', TYPE_UINT);
        $this->warehouse = Tool_Input::clean('r', 'warehouse', TYPE_UINT);
    }
    
    /**
     * (non-PHPdoc)
     * @see Base_App::main()
     */
    protected function main()
    {  
        //除uid、ding_id、mobile、title为必传，其它参数根据业务自行选传
        $params = array(
            'title' => '商品数据',  
            'stime' => $this->stime,
            'mtime' => $this->mtime,
            'city' => $this->city,
            'warehouse' => $this->warehouse
        );
        Queue_Task_Base::enqueue(
            $this->type, 
            $params, $this->_uid, 
            $this->_user['mobile'], 
            $this->_user['ding_id']
        );
    }
    
    /**
     * (non-PHPdoc)
     * @see Base_App::outputBody()
     */
    protected function outputBody()
    {
        $response = new Response_Ajax();
        $response->setContent($this->result);
        $response->send();
        exit;
    }
}
$app = new App('pub');
$app->run();