<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	private $ssid;
    private $type;
    private $reason;
    private $data = array();

    private $retSt;
	
	protected function getPara()
	{
	}

	protected function checkPara()
    {
        if(empty($this->ssid))
        {
            throw new Exception('调拔单id非法');
        }
    }

    protected function checkAuth()
    {
        $this->ssid = Tool_Input::clean('r', 'ssid', TYPE_UINT);
        $this->type = Tool_Input::clean('r', 'type', TYPE_STR);
        $this->reason = Tool_Input::clean('r', 'reason', TYPE_STR);
        
        if($this->type == 'apply')
        {
            $this->data['status'] = Conf_Base::STATUS_NORMAL;
            parent::checkAuth('/warehouse/ajax/stock_shift_apply');
        }
        elseif($this->type == 'rebut')
        {
            if(empty($this->reason))
            {
                throw new Exception('驳回原因不能为空');
            }
            
            $this->data['status'] = Conf_Base::STATUS_UN_AUDIT;
            parent::checkAuth('/warehouse/ajax/stock_shift_rebut');
            
        }
        else
        {
            throw new Exception('参数错误');
        }
    }
	
	protected function main()		
	{

        $stockShiftInfo = Warehouse_Api::getStockShiftInfo($this->ssid);
        if($this->type == 'apply')
        {
            if ($stockShiftInfo['status']!=5 && $stockShiftInfo['status']!=6)
            {
                throw new Exception('该调拨单已申请！');
            }
            //检测货位库存
            Warehouse_Stock_Helper::chkHasEnoughLocStock($stockShiftInfo['products'], $stockShiftInfo['src_wid'], $distributionLocs);
                 
            //检测商品上下架状态 
            $sids = Tool_Array::getFields($stockShiftInfo['products'], 'sid');
            $cityId = Conf_Warehouse::$WAREHOUSE_CITY_MAPPING[$stockShiftInfo['src_wid']];
            Shop_Product_Helper::chkHasOfflineProductsBySids($sids, $cityId);
            
            
            $wssp = new Warehouse_Stock_Shift_Product();
            foreach ($stockShiftInfo['products'] as $p)
            {
                $wssp->update($this->ssid, $p['sid'], array('from_location' => $distributionLocs[$p['sid']]['loc']));
            }
            
            //调拨单发起申请后加占用
            $ws = new Warehouse_Stock();
            $wl = new Warehouse_Location();
            foreach($distributionLocs as $_sid => $_locInfo)
            {
                $locNum = 0;
                foreach($_locInfo['raw_loc'] as $item)
                {
                    $locNum += $item['num'];
                    $wl->update($_sid, $item['loc'], $stockShiftInfo['src_wid'], array(), array('occupied' => $item['num']));
                }
                
                $ws->update($stockShiftInfo['src_wid'], $_sid, array(), array('occupied'=>$locNum));
            }
        }
        elseif($this->type == 'rebut')
        {
            
            Warehouse_Location_Api::parseLocationAndNum($stockShiftInfo['products']);
            
            //调拨单驳回后加减占用
            $ws = new Warehouse_Stock();
            $wl = new Warehouse_Location();
            foreach ($stockShiftInfo['products'] as $_product)
            {
                $locNum = 0;
                foreach($_product['_from_location'] as $item)
                {
                    $locNum += $item['num'];
                    $wl->update($_product['sid'], $item['loc'], $stockShiftInfo['src_wid'], array(), array('occupied'=>0-$item['num']));
                }
                
                $ws->update($stockShiftInfo['src_wid'], $_product['sid'], array(), array('occupied'=>0-$locNum));
            }
            
            //清空调拨商品的货位信息
            $wssp = new Warehouse_Stock_Shift_Product();
            foreach ($stockShiftInfo['products'] as $p)
            {
                $wssp->update($this->ssid,$p['sid'], array('from_location' => ''));
            }
        }
        
        $wss = new Warehouse_Stock_Shift();
        $wss->update($this->ssid, $this->data);
        
        //添加调拔单日志
        $info = array(
            'admin_id' => $this->_uid,
            'obj_id' => $this->ssid,
            'obj_type' => Conf_Admin_Log::OBJTYPE_SATOCK_SHIFT,
            'wid' => $stockShiftInfo['src_wid'],
        );
        if($this->type == 'apply')
        {
            $info['action_type'] = 3;
            $info['params'] = json_encode(array('id' => $this->ssid));
        }else{
            $info['action_type'] = 4;
            $info['params'] = json_encode(array('id' => $this->ssid, 'reason' => $this->reason));
        }
        Admin_Common_Api::addAminLog($info);
	}
	
	protected function outputBody()
	{
		$result = array('st' => $this->retSt);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App('pri');
$app->run();