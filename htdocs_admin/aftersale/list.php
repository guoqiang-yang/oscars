<?php

include_once ('../../global.php');

class App extends App_Admin_Page
{
	private $searchConf;
	private $total;
	private $list;
	private $start;
    private $action;
	private $num = 20;
    private $isAftersale;
    private $isAdmin;

	protected function getPara()
	{
		$this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->action = Tool_Input::clean('r', 'action', TYPE_STR);
		$this->searchConf = array(
			'id' => Tool_Input::clean('r', 'id', TYPE_UINT),
			'contact_id' => Tool_Input::clean('r', 'contact_id', TYPE_UINT),
			'contact_mobile' => Tool_Input::clean('r', 'contact_mobile', TYPE_STR),
			'objid' => Tool_Input::clean('r', 'objid', TYPE_UINT),
			'duty_department' => Tool_Input::clean('r', 'duty_department', TYPE_UINT),
			'fb_type' => Tool_Input::clean('r', 'fb_type', TYPE_UINT),
			'start_from_date' => Tool_Input::clean('r', 'start_from_date', TYPE_STR),
			'start_to_date' => Tool_Input::clean('r', 'start_to_date', TYPE_STR),
            'end_from_date' => Tool_Input::clean('r', 'end_from_date', TYPE_STR),
            'end_to_date' => Tool_Input::clean('r', 'end_to_date', TYPE_STR),
			'type' => Tool_Input::clean('r', 'type', TYPE_UINT),
            'typeid' => Tool_Input::clean('r', 'typeid', TYPE_UINT),
			'exec_status' => Tool_Input::clean('r', 'exec_status', TYPE_STR),
            'order' => Tool_Input::clean('r', 'order', TYPE_STR),
            'order_name' => Tool_Input::clean('r', 'order_name', TYPE_STR)
		);
	}

	protected function main()
	{
		//$this->isAftersale = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_AFTER_SALE_NEW);
		$this->isAdmin = Admin_Role_Api::isAdmin($this->_uid);
        $this->exRole = Admin_Role_Api::hasRoles($this->_user, array(Conf_Admin::ROLE_AFTER_SALE_NEW, Conf_Admin::ROLE_FINANCE_NEW));
		if (!Admin_Role_Api::isAdmin($this->_uid) && !$this->exRole)
		{
            if(!Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_CS_NEW))
            {
                $this->searchConf['suid'] = $this->_uid;
            }else{
                $this->searchConf['type'] = Conf_Aftersale::OBJTYPE_CUSTOMER;
                $this->searchConf['typeid'] = 1;
            }
		}

        //下载
        if ($this->action == 'download') {
            Aftersale_Api::exportListByWhere($this->searchConf);
            exit;
        }

        if(empty($this->searchConf['order']))
        {
            $this->searchConf['order'] = 'desc';
        }

        if(empty($this->searchConf['order_name']))
        {
            $this->searchConf['order_name'] = 'id';
        }

		$data = Aftersale_Api::getList($this->searchConf, $this->start, $this->num, $this->searchConf['order_name'], $this->searchConf['order']);
		$this->total = $data['total'];
		$this->list = $data['list'];
    }

	protected function outputBody()
	{
		$app = '/aftersale/list.php?' . http_build_query($this->searchConf);
		$pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

		$this->smarty->assign('pageHtml', $pageHtml);
		$this->smarty->assign('fb_type', Conf_Aftersale::$FB_TYPE);
		$this->smarty->assign('type', Conf_Aftersale::$Objtype_Desc);
		$this->smarty->assign('isAftersale', $this->isAftersale);
		$this->smarty->assign('isAdmin', $this->isAdmin);
		$this->smarty->assign('status_list', Conf_Aftersale::$STATUS);
		$this->smarty->assign('department_list', Conf_Aftersale::$DEPARTMENT);
		$this->smarty->assign('total', $this->total);
		$this->smarty->assign('list', $this->list);
        $this->smarty->assign('all_short_descs', json_encode(Conf_Aftersale::getShortDescOfObjtype()));
		$this->smarty->assign('searchConf', $this->searchConf);
        unset($this->searchConf['order']);
        unset($this->searchConf['order_name']);
        $order_url = '/aftersale/list.php?' . http_build_query($this->searchConf);
        $this->smarty->assign('order_url', $order_url);
        unset($this->searchConf['exec_status']);
        $page_url = '/aftersale/list.php?' . http_build_query($this->searchConf);
        $this->smarty->assign('page_url',$page_url);

		$this->smarty->display('aftersale/list.html');
	}
}

$app = new App('pri');
$app->run();