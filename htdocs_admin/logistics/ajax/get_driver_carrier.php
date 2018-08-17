<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
    private $num = 10;
    private $oid;
    private $role;
    private $keyword;
    private $carModel;
    private $wid;
    private $start;
    private $search;
    private $searchList;
    private $objType = Conf_Coopworker::OBJ_TYPE_ORDER;
    
    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->role = Tool_Input::clean('r', 'role', TYPE_UINT);
        $this->keyword = Tool_Input::clean('r', 'keyword', TYPE_STR);
        $this->carModel = Tool_Input::clean('r', 'car_model', TYPE_UINT);
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
        $this->retType = Tool_Input::clean('r', 'ret_type', TYPE_STR);
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->objType = Tool_Input::clean('r', 'obj_type', TYPE_UINT);

        $this->objType = Conf_Coopworker::OBJ_TYPE_ORDER;
        
        $this->search['car_model'] = $this->carModel;
        
        if (!empty($this->wid))
        {
            $this->search['wid'] = $this->wid;
        }
        
        if (!empty($this->keyword))
        {
            if (is_numeric($this->keyword))
            {
                $this->search['mobile'] = $this->keyword;
            }
            else 
            {
                $this->search['name'] = $this->keyword;
            }
        }
    }
    
    protected function checkPara()
    {
        if (!array_key_exists($this->role, Conf_Base::getCoopworkerTypes()))
        {
            throw new Exception('Error: inner Error! ');
        }
    }

    protected function main()
    {
        // 该订单已经选择的工人
        $_hasSelectedInfos = Logistics_Coopworker_Api::getOrderOfWorkers($this->oid, 0, 1, $this->objType);
        
        // 已经选择的司机
        $hasSelectedDrivers = array();
        $hasSelectedInfos = array();
        foreach ($_hasSelectedInfos as $oner)
        {
            $_key = $this->_gKey($oner['cuid'], $oner['type'], $oner['user_type']);
            $hasSelectedInfos[$_key] = $oner;
            
            // 选择已经安排的司机
            if ($oner['user_type'] == Conf_Base::COOPWORKER_DRIVER && $oner['type']!=Conf_Base::COOPWORKER_CARRIER)
            {
                $hasSelectedDrivers[] = array(
                    'cuid' => $oner['cuid'],
                    'name' => $oner['info']['name'],
                    'mobile' => $oner['info']['mobile'],
                    'user_type' => $oner['user_type'],
                    'type' => $oner['type'],
                    'hasSelected' => 0,
                    //'price' => $oner['price']/100,
                );
            }
        }
        
        //搬运工
        if ($this->role == Conf_Base::COOPWORKER_CARRIER)
        {
            $searchList = Logistics_Api::getCarrierList($this->search, $this->start, $this->num);
            
            foreach($searchList['list'] as &$oner)
            {
                $_key = $this->_gKey($oner['cid'], Conf_Base::COOPWORKER_CARRIER, Conf_Base::COOPWORKER_CARRIER);
                $oner['hasSelected'] = 0;
                
                if (array_key_exists($_key, $hasSelectedInfos) 
                    && $hasSelectedInfos[$_key]['type']==Conf_Base::COOPWORKER_CARRIER)
                {
                    $oner['hasSelected'] = 1;
                    $oner['price'] = $hasSelectedInfos[$_key]['price']/100;
                }
            }
            
            // 司机搬运工
            foreach ($hasSelectedDrivers as &$_driver)
            {   
                $_key = $this->_gKey($_driver['cuid'], Conf_Base::COOPWORKER_CARRIER, Conf_Base::COOPWORKER_DRIVER);
                if (array_key_exists($_key, $hasSelectedInfos))
                {
                    $_driver['price'] = $hasSelectedInfos[$_key]['price']/100;
                    $_driver['hasSelected'] = 1;
                }
                else
                {
                    $_driver['price'] = '';
                }
            }
        }
        else    //司机
        {
            $searchList = Logistics_Api::getDriverList($this->search, $this->start, $this->num);
            foreach($searchList['list'] as &$oner)
            {
                $_key = $this->_gKey($oner['did'], Conf_Base::COOPWORKER_DRIVER, Conf_Base::COOPWORKER_DRIVER);
                $oner['hasSelected'] = 0;
                if (array_key_exists($_key, $hasSelectedInfos) 
                    && $hasSelectedInfos[$_key]['type']==Conf_Base::COOPWORKER_DRIVER)
                {
                    $oner['hasSelected'] = 1;
                    $oner['price'] = $hasSelectedInfos[$_key]['price']/100;
                }
            }
        }
        
        if ($this->retType == 'html')
        {
            $pageHtml = Str_Html::getJsPagehtml2($this->start, $this->num, $searchList['total'], '_j_search_driver_carrier');

            $this->smarty->assign('role', $this->role);
            $this->smarty->assign('pageHtml', $pageHtml);
            $this->smarty->assign('total', $searchList['total'] + count($hasSelectedDrivers));
            $this->smarty->assign('worker_list', $searchList['list']);
            $this->smarty->assign('has_selected_drivers', $hasSelectedDrivers);
            $this->smarty->assign('role', $this->role);
            $this->searchList['html'] = $this->smarty->fetch('logistics/aj_get_driver_carrier.html');
            $this->searchList['total'] = $searchList['total'];
            
            $this->searchList['list'] = $searchList['list'];
        }
        else 
        {
            $this->searchList = $searchList;
        }
    }
    
    protected function outputBody()
    {
		$response = new Response_Ajax();
		$response->setContent($this->searchList);
		$response->send();
		exit;
    }
    
    private function _gKey($cuid, $type, $userType)
    {
        return $cuid.'#'.$type. '#'. $userType;
    }
}

$app = new App();
$app->run();