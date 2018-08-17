<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $list;
    private $searchConf;
    private $total;
    private $num = 20;
    private $start;
    private $searchType;
    private $permissionList;
    private $download = 0;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->searchType = Tool_Input::clean('r', 'search_type', TYPE_STR);
        $this->download = Tool_Input::clean('r', 'download', TYPE_UINT);
        if ($this->searchType != 'role')
        {
            $this->searchConf = array(
                'role' => Tool_Input::clean('r', 'role', TYPE_STR),
                'department' => Tool_Input::clean('r', 'department', TYPE_UINT),
            );
        }
        else
        {
            $this->searchConf = array(
                'module' => Tool_Input::clean('r', 'module', TYPE_STR),
                'list_page' => Tool_Input::clean('r', 'list_page', TYPE_STR),
                'permission' => Tool_Input::clean('r', 'permission', TYPE_STR),
            );
        }
    }

    protected function checkPara()
    {
        $this->permissionList = Conf_Admin_Page::getModulesForRoleManage();
        if ($this->searchType == 'role')
        {
            if (empty($this->searchConf['module']))
            {
                $this->searchConf['module'] = key($this->permissionList);
            }
            if (empty($this->searchConf['list_page']))
            {
                $this->searchConf['list_page'] = key($this->permissionList[$this->searchConf['module']]['pages']);
            }
        }
    }

    protected function main()
    {
        if (!empty($this->download))
        {
            //Admin_Role_Api::exportAllRoles();
            $this->_exportRoles();
            
            exit;
        }
        if ($this->searchType == 'role')
        {
            $where = '';
            if (empty($this->searchConf['permission']))
            {
                $where .= sprintf("permission like '%%%s%%'", $this->searchConf['list_page'] . '":');
            }
            else
            {
                $where .= sprintf("permission regexp '%s[^:]'", '"' . $this->searchConf['permission'] . '"');
                $where .= sprintf(" or permission regexp '%s[^:]'", '"' . str_replace('/', '\\\\\\\\/', $this->searchConf['permission']). '"');
            }
            $data = Permission_Api::getListByWhere($where, array('*'), $this->start, $this->num);
        }
        else
        {
            $data = Permission_Api::getList($this->searchConf, $this->start, $this->num);
        }

        $this->list = $data['list'];
        $this->total = $data['total'];
        $this->addFootJs('js/apps/role.js');
    }

    protected function outputBody()
    {
        $app = '/admin/role_list.php?' . http_build_query(array_merge($this->searchConf, array('search_type' => $this->searchType)));
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        if ($this->searchType == 'role')
        {
            $this->smarty->assign('modules', $this->permissionList);
        }
        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('search_type', $this->searchType);
        $this->smarty->assign('list', $this->list);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('departments', Conf_Permission::$DEPAREMENT);

        $this->smarty->display('admin/role_list.html');
    }
    
    
    private function _exportRoles()
    {
        $roleMapping = $this->_getRoleMapping();

        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . '后台角色.csv');
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        
        $pr = new Permission_Role();
        $roleList = $pr->getListWhere('status=0', array('*'), 0, 0);
        
        $head = array('id', '角色名称');
        $modules = Conf_Admin_Page::$MODULES;
        foreach ($modules as $item)
        {
            $head[] = $item['name'];
        }
        
        iconv('utf-8','gb2312',$head);
        Data_Csv::send($head);
        
        foreach ($roleList['list'] as $role)
        {
            if ($role['id'] < 132) continue;
            
            $roleInfo = array($role['id'], $role['role']);
            
            $myRoleIds = array();
            if (!empty($role['rel_role']))
            {
                $myRoleIds = explode(',', $role['rel_role']);
                $myRoleIds[] = $role['id'];
            }
            else
            {
                $myRoleIds[] = $role['id'];
            }
            
            $_roleInfo = array();
            foreach($myRoleIds as $roleId)
            {
                $permission = json_decode($roleList['list'][$roleId]['permission'], true);
                
                foreach($permission as $pItems)
                {
                    foreach($pItems as $item)
                    {
                        if(!in_array($roleMapping[$item]['button'], $_roleInfo[$roleMapping[$item]['module']][$roleMapping[$item]['page']]))
                        {
                            $_roleInfo[$roleMapping[$item]['module']][$roleMapping[$item]['page']][] = $roleMapping[$item]['button'];
                        }
                    }
                }
            }
            foreach($head as $one)
            {
                if (in_array($one, array('id', '角色名称'))) continue;
                
                if (!array_key_exists($one, $_roleInfo))
                {
                    $roleInfo[] = '-';
                }
                else
                {
                    $con = '';
                    foreach($_roleInfo[$one] as $page => $buttons)
                    {
                        $con .= "【{$page}】：". implode('、', $buttons). "\r";
                    }
                    $roleInfo[] = $con;
                }
            }
            
            iconv('utf-8', 'gb2312', $roleInfo);
            Data_Csv::send($roleInfo);
        }
        
    }
    
    private function _getRoleMapping()
    {
        $roles = array();
        
        foreach(Conf_Admin_Page::$MODULES as $moduleItem)
        {
            foreach($moduleItem['pages'] as $pageItem)
            {
                foreach ($pageItem['buttons'] as $button)
                {
                    $roles[$button['key']] = array(
                        'button' => $button['name'],
                        'page' => $pageItem['name'],
                        'module' => $moduleItem['name'],
                    );
                }
            }
        }
        
        return $roles;
    }
}

$app = new App('pri');
$app->run();

