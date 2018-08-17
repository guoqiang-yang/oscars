<?php

include_once ('../../global.php');

class App extends App_Admin_Page
{
	private $eid;
	private $exchanged;
    private $orderProducts;
    private $exchangedProducts;
    private $saler_name;
    private $driver_name;
	protected $headTmpl = 'head/head_none.html';
	protected $tailTmpl = 'tail/tail_none.html';

	protected function getPara()
	{
		$this->eid = Tool_Input::clean('r', 'eid', TYPE_UINT);
	}

	protected function main()
	{
		$this->exchanged = Exchanged_Api::getExchanged($this->eid);
        $orderProducts = Order_Api::getOrderProducts($this->exchanged['info']['oid']);
        $this->orderProducts = $orderProducts['products'];
        foreach ($this->orderProducts as $pid => &$productInfo)
        {
            $productInfo['exchanged_refund_num'] = 0;
            foreach ($this->exchanged['refund_products'] as $_pid => $rproduct)
            {
                if ($_pid == $pid)
                {
                    $productInfo['exchanged_refund_num'] += $rproduct['num'];
                }
            }
            if($productInfo['exchanged_refund_num'] == 0)
            {
                unset($this->orderProducts[$pid]);
            }
        }
        if (!empty($this->exchanged['order']['community_id']))
        {
            $communityInfo = Order_Community_Api::get($this->exchanged['order']['community_id']);
            $this->exchanged['order']['print_address'] = $communityInfo['district'] . ' ' . $communityInfo['name'] . ' ' . $this->exchanged['order']['_address'] . '（' . $communityInfo['address'] . '）';
        }
        else
        {
            $this->exchanged['order']['print_address'] = $this->exchanged['order']['_district'] . ' ' . $this->exchanged['order']['address'];
        }
        $exchangedProducts = Order_Api::getOrderProducts($this->exchanged['info']['aftersale_oid']);
        $this->exchangedProducts = $exchangedProducts['products'];
        if($this->exchanged['order']['saler_suid'] > 0)
        {
            $as = new Admin_Staff();
            $this->saler_name = $as->get($this->exchanged['order']['saler_suid']);
        }
        // 获取订单的第三方工人（司机，搬运工）
        $coopworders = Logistics_Coopworker_Api::getOrderOfWorkers($this->exchanged['info']['aftersale_oid'], 0, TRUE, Conf_Coopworker::OBJ_TYPE_ORDER);

        $driver_list = array();
        foreach ($coopworders as $oner)
        {
            if ($oner['type'] == Conf_Base::COOPWORKER_DRIVER)
            {
                $driver_list[] = $oner['name'];
            }
        }
        $this->driver_name = implode(',',$driver_list);
		$this->removeJs();
		$this->addFootJs(array());
		$this->addCss(array());
	}

	protected function outputBody()
	{
		$this->smarty->assign('exchanged', $this->exchanged);
		$this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
		$this->smarty->assign('cate2_list_all', Conf_Sku::$CATE2);
        $this->smarty->assign('warehouse_list', Conf_Warehouse::$WAREHOUSES);
        $this->smarty->assign('orderProducts', $this->orderProducts);
        $this->smarty->assign('exchangedProducts', $this->exchangedProducts);
        $this->smarty->assign('saler_name', $this->saler_name);
        $this->smarty->assign('driver_name', $this->driver_name);
		$this->smarty->display('order/exchanged_print.html');
	}
}

$app = new App('pri');
$app->run();
