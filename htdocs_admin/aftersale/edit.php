<?php
include_once ('../../global.php');

class App extends App_Admin_Page
{
	private $id;
    private $oid;
	private $info;

	protected function getPara()
	{
		$this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
	}

	protected function main()
	{
		//检查权限
		$uid = $this->_uid;
//		$isAftersale = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_AFTER_SALE);
//        $isAdmin = Admin_Role_Api::isAdmin($this->_uid, $this->_user);
//		if (!in_array($uid, Conf_Aftersale::$SALE_EXEC) && !$isAftersale && !$isAdmin)
//		{
//			throw new Exception('aftersale: without authority');
//		}
		if($this->id){
			$this->info = Aftersale_Api::getDetail($this->id);
            $this->oid = $this->info['objid'];
            unset($this->info['id']);
		}
		$this->addFootJs(array(
		    'js/apps/aftersale.js',
            'js/core/FileUploader.js',
            'js/core/imgareaselect.min.js',
            'js/apps/uploadpic.js')
        );
		$this->addCss(array());
	}

	protected function outputBody()
	{
		$this->smarty->assign('info', $this->info);
		$this->smarty->assign('department', Conf_Aftersale::$DEPARTMENT_DEFAULT);
		$this->smarty->assign('department_list', Conf_Aftersale::$COPY_DEPARTMENT);
		$this->smarty->assign('fb_type', Conf_Aftersale::$FB_TYPE);
		$this->smarty->assign('types', Conf_Aftersale::$TYPE);
        $this->smarty->assign('staff_roles', Conf_Permission::$DEPAREMENT);
        $this->smarty->assign('staff_grouped', json_encode(Admin_Role_Api::getDepartmentOfStaff()));
        $this->smarty->assign('show_desc_of_objtype', json_encode(Conf_Aftersale::getShortDescOfObjtype()));
        $this->smarty->assign('objtypes', Conf_Aftersale::$Objtype_Desc);
        $this->smarty->assign('oid', $this->oid);
        $this->smarty->assign('a_id', $this->id);
        $this->smarty->display('aftersale/edit.html');
	}
}

$app = new App('pri');
$app->run();
