<?php

/**
 * 调拨单的异常商品处理：破损，预盘亏.
 * 
 */

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
    private $ssid;
    private $sid;
    private $num;
    private $type;
    private $note;
    
    private $ssinfo;    //调拨单信息
    private $sspinfo;   //调拨单商品信息
    
    private $handler;
    
    protected function getPara()
    {
        $this->ssid = Tool_Input::clean('r', 'ssid', TYPE_UINT);
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
        $this->num = Tool_Input::clean('r', 'num', TYPE_UINT);
        $this->type = Tool_Input::clean('r', 'type', TYPE_UINT);
        $this->note = Tool_Input::clean('r', 'note', TYPE_STR);
    }
    
    protected function checkPara()
    {
        if (empty($this->ssid) || empty($this->sid))
        {
            throw new Exception('数据异常，请联系技术人员！');
        }
        if (empty($this->num) || empty($this->type))
        {
            throw new Exception('请选择处理类型，或填写数量！');
        }
        if ($this->type != Conf_Warehouse::VFLAG_DAMAGED && $this->type != Conf_Warehouse::VFLAG_LOSS)
        {
            throw new Exception('处理类型非法！');
        }
        if (empty($this->note))
        {
            throw new Exception('请填写备注');
        }
    }
    
    protected function main()
    {
        $this->handler['sshift'] = new Warehouse_Stock_Shift();
        $this->handler['ssproduct'] = new Warehouse_Stock_Shift_Product();
        
        $this->_getInfos();
        $this->_check();
        
        $this->_deal();
    }
    
    protected function outputBody()
    {
		$response = new Response_Ajax();
		$response->setContent(array('st'=>1));
		$response->send();
        
		exit;
    }


    // 获取调拨单，调拨单商品信息
    private function _getInfos()
    {
        $this->ssinfo = $this->handler['sshift']->getById($this->ssid);
        
        $opWhere = 'status=0 and ssid='.$this->ssid.' and sid='. $this->sid;
        $this->sspinfo = current($this->handler['ssproduct']->getByRawWhere($opWhere));
        
        $abnormalLocInfo = array($this->sid => array('num'=>$this->sspinfo['abnormal_num'], 'loc'=>  $this->sspinfo['abnormal_location']));
        Warehouse_Location_Api::parseLocationAndNum($abnormalLocInfo, 'loc');
        
        $this->sspinfo['_abnormal_location'] = $abnormalLocInfo[$this->sid];
        
    }
    
    private function _check()
    {
        if (empty($this->ssinfo) || empty($this->sspinfo))
        {
            throw new Exception('调拨单异常，请核实数据');
        }
        
        if ($this->ssinfo['step'] < Conf_Stock_Shift::STEP_STOCK_IN)
        {
            throw new Exception('操作失败: 调拨单未入库');
        }
        
        if (!empty($this->sspinfo['to_location']))
        {
            throw new Exception('操作失败：商品已上架');
        }
        
        if ($this->num > $this->sspinfo['num']-$this->sspinfo['abnormal_num'])
        {
            throw new Exception('数量不足：不能大于可处理数量');
        }
    }
    
    private function _deal()
    {
        $ws = new Warehouse_Stock();
        $wl = new Warehouse_Location();
        
        $desLoc = Conf_Warehouse::$Virtual_Flags[$this->type]['flag'];
        
        //1 报损货位库存
        $wl->add($desLoc, $this->ssinfo['des_wid'], $this->sid, $this->num);
        
        //2 待上架货位库存 减去损坏数量
        $vShiftLoc = Conf_Warehouse::$Virtual_Flags[Conf_Warehouse::VFLAG_SHIFT]['flag'];
        $wl->update($this->sid, $vShiftLoc, $this->ssinfo['des_wid'], array(), array('num'=>0-$this->num));
        
        //3 总库存：损坏数量
        $change = array('damaged_num'=>$this->num);
        $ws->update($this->ssinfo['des_wid'], $this->sid, array(), $change);
        
        //4 货位历史
        $locHistory = array(
            'old_num' => $this->sspinfo['num']-$this->sspinfo['abnormal_num'],  //调拨单待上架数量
            'chg_num' => $this->num,
            'des_loc' => $desLoc,
            'suid' => $this->_uid,
            'iid' => $this->ssid,
            'type' => Conf_Warehouse::STOCK_HISTORY_STOCK_SHIFT_IN,
            'note' => $this->note,
        );
        $wl->addHistory($this->sid, $this->ssinfo['des_wid'], $vShiftLoc, $locHistory);
        
        //5 更新调拨单商品信息
        $_abnormalLoc = array();
        $_isDealLoc = false;
        if (isset($this->sspinfo['_abnormal_location']['_loc']))
        {
            foreach($this->sspinfo['_abnormal_location']['_loc'] as $one)
            {
                if ($one['loc'] == $desLoc)
                {
                    $one['num'] += $this->num;
                    $_isDealLoc = true;
                }
                $_abnormalLoc[] = $one;
            }
        }
        if (!$_isDealLoc)
        {
            $_abnormalLoc[] = array('num'=>$this->num, 'loc'=>$desLoc);
        }
        $abnormalLoc = Warehouse_Location_Api::genLocationAndNum(array($this->sid=>$_abnormalLoc));
        $upData = array(
            'abnormal_num' => $this->sspinfo['abnormal_num']+$this->num,
            'abnormal_location' => $abnormalLoc[$this->sid],
        );
        
        $this->handler['ssproduct']->update($this->ssid, $this->sid, $upData);
        
        //6 写日志
        //添加调拔单日志
        $typeName = $this->type == Conf_Warehouse::VFLAG_DAMAGED? '残损移架': '预盘亏';
        $info = array(
            'admin_id' => $this->_uid,
            'obj_id' => $this->ssid,
            'obj_type' => Conf_Admin_Log::OBJTYPE_SATOCK_SHIFT,
            'action_type' => 7,
            'wid' => $this->ssinfo['des_wid'],
            'params' => json_encode(array('type'=>$typeName, 'num'=>$this->num, 'note'=>$this->note)),
        );
        Admin_Common_Api::addAminLog($info);
    }
}

$app = new App();
$app->run();