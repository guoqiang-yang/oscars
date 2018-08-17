<?php

/**
 * 创建新任务的对话框.
 * 
 * @uses 
 *  1 在使用的页面加入js：js/apps/admin_task.js
 *  2 在使用的页面加入html：
 *      <a href="javascript:;" class="btn btn-primary _j_show_admin_task" data-objid="0" data-objtype="0">添加新任务</a>
 *      no-button:
 *      <a href="javascript:;" class="_j_show_admin_task" data-objid="0" data-objtype="0">添加新任务</a>
 *      其中：
 *          data-objid=""   定义见 Conf_Admin_Task::$Objtype_Desc
 *          data-objtype="" 定义见 Conf_Admin_Task::getShortDescOfObjtype()
 * 
 * @notice 不支持跨越
 * 
 * @author yangguoqiang
 * @date    2016-03-15
 */

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $objId;
    private $objType;
    private $devType;   // 设备类型
    
    private $shortDescs;
    
    private $dialog;
    
    private $_all_dev_types = array('pc', 'h5');
    
    protected function getPara()
    {
        $this->objId = Tool_Input::clean('r', 'objid', TYPE_UINT);
        $this->objType = Tool_Input::clean('r', 'objtype', TYPE_UINT);
        $this->devType = Tool_Input::clean('r', 'dev_type', TYPE_STR);
        
        $this->devType = in_array($this->devType, $this->_all_dev_types)? $this->devType: 'pc';
        $this->objType = (empty($this->objType)&&$this->devType=='h5')? Conf_Admin_Task::OBJTYPE_ORDER: $this->objType;
        
    }
    
    protected function main()
    {
        
        $this->shortDescs = Conf_Admin_Task::getShortDescOfObjtype($this->objType);
        
        $this->dialog = $this->_getDialog();
    }
    
    protected function outputBody()
    {
        
        $result = array('st'=>0, 'html'=>  $this->dialog);
        
        $response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
    }
    
    private function _getDialog()
    {
        $this->smarty->assign('objid', $this->objId);
        $this->smarty->assign('objtype', $this->objType);
        $this->smarty->assign('dev_type', $this->devType);
        
        $this->smarty->assign('short_descs', $this->shortDescs[$this->objType]);
        $this->smarty->assign('objtypes', Conf_Admin_Task::$Objtype_Desc);
        $this->smarty->assign('show_desc_of_objtype', json_encode(Conf_Admin_Task::getShortDescOfObjtype()));
        $this->smarty->assign('staff_roles', Conf_Permission::$DEPAREMENT);
        $this->smarty->assign('staff_grouped', json_encode(Admin_Role_Api::getDepartmentOfStaff()));
        
        return $this->smarty->fetch('user/aj_show_task.html');
    }
}

$app = new App();
$app->run();