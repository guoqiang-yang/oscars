<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	private $wid;
	private $sid;
	private $refer;
	private $cityId;
	//private $cost;

	private $stock;

	protected function getPara()
	{
		$this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
		$this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
		//$this->cost = Tool_Input::clean('r', 'cost', TYPE_UINT)*100;
		$this->refer = Tool_Input::clean('r', 'refer', TYPE_STR);
		$this->stock = array(
			//'num' => Tool_Input::clean('r', 'num', TYPE_UINT),
			//'occupied' => Tool_Input::clean('r', 'occupied', TYPE_UINT),
			//'cost' => Tool_Input::clean('r', 'cost', TYPE_NUM)*100,
            'purchase_price' => Tool_Input::clean('r', 'purchase_price', TYPE_NUM)*100,
			'alert_threshold' => Tool_Input::clean('r', 'alert_threshold', TYPE_UINT),
		);
		
		$this->wid = $this->getWarehouseId();
		$this->cityId = Tool_Input::clean('r', 'city_id', TYPE_UINT);
	}

	protected function checkPara()
	{
		if (empty($this->sid))
		{
			throw new Exception('warehouse:empty sku id');
		}
		if (empty($this->wid) && empty($this->cityId))
		{
			throw new Exception('warehouse:empty warehouse id');
		}
	}

	protected function checkAuth()
	{
		parent::checkAuth('/warehouse/edit_stock');
	}

	protected function main()
	{
	    if (!empty($this->wid))
        {
            $productInfo = Warehouse_Api::getStockDetail($this->wid, $this->sid);
            Warehouse_Api::saveStock($this->wid, $this->sid, $this->stock);
            $info = array(
                'admin_id' => $this->_uid,
                'obj_id' => $this->sid,
                'obj_type' => Conf_Admin_Log::OBJTYPE_SKU,
                'action_type' => 1,
                'params' => json_encode(array('sid' => $this->sid, 'from_price' => $productInfo['stock'][$this->wid]['purchase_price']/100 , 'to_price' => $this->stock['purchase_price']/100)),
                'wid' => $this->wid,
            );
            Admin_Common_Api::addAminLog($info);
        }
        elseif (!empty($this->cityId) && empty($this->wid))
        {
            $wids = Conf_Warehouse::$WAREHOUSE_CITY[$this->cityId];
            foreach ($wids as $wid)
            {
                $productInfo = Warehouse_Api::getStockDetail($wid, $this->sid);
                Warehouse_Api::saveStock($wid, $this->sid, $this->stock);
                $info = array(
                    'admin_id' => $this->_uid,
                    'obj_id' => $this->sid,
                    'obj_type' => Conf_Admin_Log::OBJTYPE_SKU,
                    'action_type' => 1,
                    'params' => json_encode(array('sid' => $this->sid, 'from_price' => $productInfo['stock'][$wid]['purchase_price']/100 , 'to_price' => $this->stock['purchase_price']/100)),
                    'wid' => $wid,
                );
                Admin_Common_Api::addAminLog($info);
            }
        }

		// 成本价钱写入到库存表中。
		//$info = array('cost' => $this->cost);
		//Shop_Api::updateProduct($this->sid, $info);
	}

	protected function outputPage()
	{
		$result = array('sid' => $this->stock['sid'], 'url' => $this->refer);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App('pri');
$app->run();

