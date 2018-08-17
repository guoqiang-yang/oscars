<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $ssid;
	private $widOut;
	private $widIn;
	private $note;
	private $nextStep;
	
	private $saveRet;
	private $upRet = false;
	
	protected function getPara()
	{
		$this->ssid = Tool_Input::clean('r', 'ssid', TYPE_UINT);
		$this->widOut = Tool_Input::clean('r', 'wid_out', TYPE_UINT);
		$this->widIn = Tool_Input::clean('r', 'wid_in', TYPE_UINT);
		$this->note = Tool_Input::clean('r', 'note', TYPE_STR);
		$this->nextStep = Tool_Input::clean('r', 'next_step', TYPE_STR);
	}
	
    protected function checkAuth()
    {
        $ssid = Tool_Input::clean('r', 'ssid', TYPE_UINT);
        if (empty($ssid))
        {
            parent::checkAuth('/warehouse/stock_shift');   
        }
        else
        {
            parent::checkAuth('/warehouse/ajax/save_stock_shift');
        }
    }


    protected function checkPara()
	{
        //锁库判断
        $lockedOut = Conf_Warehouse::isLockedWarehouse($this->widOut);
        if ($this->nextStep==Conf_Stock_Shift::STEP_STOCK_OUT && $lockedOut['st']) //调拨出库
        {
            throw new Exception($lockedOut['msg']);
        }
        
        $lockedIn = Conf_Warehouse::isLockedWarehouse($this->widIn);
        if ($this->nextStep==Conf_Stock_Shift::STEP_STOCK_IN && $lockedIn['st'])    //调拨入库
        {
            throw new Exception($lockedIn['msg']);
        }
        
		if ($this->ssid==0 && (empty($this->widIn) || empty($this->widOut)))
		{
			throw new Exception('wid Out/In is must');
		}
		if ($this->widIn == $this->widOut)
		{
			throw new Exception('wid Out/In is equal');
		}
		
		if (!empty($this->nextStep))
		{
            if (!$this->_hasPermissionForShift())
            {
                throw new Exception('common:permission denied - is not you\'re warehouse');
            }
		}
        
        if (Conf_Warehouse::isCoopWid($this->widIn) || Conf_Warehouse::isCoopWid($this->widOut))
        {
            throw new Exception('第三方的仓库，不能调拨！');
        }

        if (!empty($this->ssid) && !self::_isSameCity($this->ssid))
        {
            throw new Exception('暂不支持不同城市间调拨！');
        }
	}
	
	protected function main()
	{
		if (empty($this->ssid))		//创建移库单
		{
			$data = array(
				'src_wid' => $this->widOut,
				'des_wid' => $this->widIn,
				'step' => Conf_Stock_Shift::STEP_CREATE,
				'create_suid' => $this->_uid,
				'note' => $this->note,
                'status' => Conf_Base::STATUS_WAIT_AUDIT
			);
			
			$this->saveRet = Warehouse_Api::createStockShift($data);
            //创建调拔单日志
            $info = array(
                'admin_id' => $this->_uid,
                'obj_id' => $this->saveRet,
                'obj_type' => Conf_Admin_Log::OBJTYPE_SATOCK_SHIFT,
                'action_type' => 1,
                'wid' => $this->widOut,
                'params' => json_encode(array('id' => $this->saveRet, 'json' => json_encode(array('src_wid' => $this->widOut, 'des_wid' => $this->widIn, 'note' => $this->note)))),
            );
            Admin_Common_Api::addAminLog($info);
		}
		else	//更新移库单
		{
			if (!empty($this->widOut))
			{
				$data['src_wid'] = $this->widOut;
			}
			if (!empty($this->widIn))
			{
				$data['des_wid'] = $this->widIn;
			}
			if (!empty($this->note))
			{
				$data['note'] = $this->note;
			}
			if (!empty($this->nextStep))
			{
				$data['step'] = $this->nextStep;
			}
			
            if (isset($data['step']))
            {
                switch($data['step'])
                {
                    case Conf_Stock_Shift::STEP_STOCK_OUT:
                        $data['stockout_suid'] = $this->_uid; break;
                    case Conf_Stock_Shift::STEP_STOCK_IN:
                        $data['stockin_suid'] = $this->_uid; break;
                    default:
                        break;
                }
            }
            $shiftInfos = Warehouse_Api::getStockShiftInfo($this->ssid);
            if($shiftInfos['status'] == Conf_Base::STATUS_NORMAL && !isset($data['step']))
            {
                throw new Exception('调拔单发起申请后不能再修改！');
            }
			$this->upRet = Warehouse_Api::updateStockShift($this->ssid, $data, $this->_user);
            //添加调拔单日志
            $info = array(
                'admin_id' => $this->_uid,
                'obj_id' => $this->ssid,
                'obj_type' => Conf_Admin_Log::OBJTYPE_SATOCK_SHIFT,
            );
            if (isset($data['step']))
            {
                switch($data['step'])
                {
                    case Conf_Stock_Shift::STEP_STOCK_OUT:
                        $info['wid'] = $this->widOut;
                        $info['action_type'] = 5;
                        $info['params'] = json_encode(array('id' => $this->ssid));
                        break;
                    case Conf_Stock_Shift::STEP_STOCK_IN:
                        $info['wid'] = $this->widIn;
                        $info['action_type'] = 6;
                        $info['params'] = json_encode(array('id' => $this->ssid));
                        break;
                    default:
                        break;
                }
            }else{
                $info['action_type'] = 2;
                $info['params'] = json_encode(array('id' => $this->ssid, 'json' => json_encode($data)));
            }
            Admin_Common_Api::addAminLog($info);
			$this->saveRet = $this->ssid;
		}
	}
	
	protected function outputBody()
	{
		$result = array('ssid' => $this->saveRet, 'st'=>  $this->upRet);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
    
    /**
     * 是否有权限操作出库，入库
     */
    private function _hasPermissionForShift()
    {
        if (Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_ADMIN_NEW))
        {
            return true;
        }
        
        $allowWarehouse = $this->getAllowedWarehouses();
        $widOutPremission = $this->nextStep==Conf_Stock_Shift::STEP_STOCK_OUT && array_key_exists($this->widOut, $allowWarehouse);
        $widInPremission = $this->nextStep==Conf_Stock_Shift::STEP_STOCK_IN && array_key_exists($this->widIn, $allowWarehouse);
        
        return $widInPremission || $widOutPremission;
    }

    /**
     * 判断是否是同城市间移库
     *
     * @param $ssid
     * @return bool
     */
    private function _isSameCity($ssid)
    {
        $sfDao = new Data_Dao('t_stock_shift');
        $widCity = Conf_Warehouse::$WAREHOUSE_CITY_MAPPING;

        $stockShiftOrd = $sfDao->get($ssid);
        if ($widCity[$stockShiftOrd['src_wid']] != $widCity[$stockShiftOrd['des_wid']])
        {
            return false;
        }

        return true;
    }
}

$app = new App('pri');
$app->run();