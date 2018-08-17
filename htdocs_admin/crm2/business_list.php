<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $num = 20;
    private $start;
    private $search;
    private $total;
    private $businessList;

    protected function getPara()
    {
        $this->search = array(
            'bid' => Tool_Input::clean('r', 'bid', TYPE_UINT),
            'name' => Tool_Input::clean('r', 'name', TYPE_STR),
            'mobile' => Tool_Input::clean('r', 'mobile', TYPE_STR),
            'address' => Tool_Input::clean('r', 'address', TYPE_STR),
        );
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
    }

    protected function main()
    {
//        $roles = explode(',', $this->_user['roles']);
//        $pr = new Permission_Role();
//        $roleInfos = $pr->getBulk($roles);
//        $rkeysArr = Tool_Array::getFields($roleInfos, 'rkey');
        
        $roleIds = explode(',', $this->_user['roles']);
        $rkeysArr = Permission_Api::getRolesRkey($roleIds);
        
        if (in_array(Conf_Admin::ROLE_SALES_NEW, $rkeysArr))
        {
            $this->search['sales_suid'] = $this->_user['team_member'];
        }
        
        $this->businessList = Business_Api::getList($this->search, $this->start, $this->num);
        $this->total = $this->businessList['total'];
        $this->addFootJs(array('js/apps/business.js'));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $app = '/crm2/business_list.php?' . http_build_query($this->search);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);
        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('search_conf', $this->search);
        $this->smarty->assign('business_list', $this->businessList['data']);
        $this->smarty->assign('status_list', Conf_Base::$STATUS);

        $this->smarty->display('crm2/business_list.html');
    }
}

$app = new App();
$app->run();