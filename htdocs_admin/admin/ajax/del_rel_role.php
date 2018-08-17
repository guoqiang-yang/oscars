<?php

/**
 * 关联权限删除.
 * 
 */

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $id;
    private $relId;
    
    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->relId = Tool_Input::clean('r', 'rel_id', TYPE_UINT);
    }
    
    protected function checkPara()
    {
        if (empty($this->id) || empty($this->relId))
        {
            throw new Exception('数据异常！');
        }
    }
        
    protected function main()
    {
        $_roleInfos = Permission_Api::getBulk(array($this->id, $this->relId));
        
        $roleInfo = $_roleInfos[$this->id];
        $relRoleInfo = $_roleInfos[$this->relId];
        
        
        $willDelRoleIds = empty($relRoleInfo['rel_role'])? array($this->relId):
                            array_merge(array($this->relId), explode(',', $relRoleInfo['rel_role']));
        
        $newRelIds = array_diff(explode(',', $roleInfo['rel_role']), $willDelRoleIds);
        
        Permission_Api::update($this->id, array('rel_role'=>implode(',', $newRelIds)));
                            
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