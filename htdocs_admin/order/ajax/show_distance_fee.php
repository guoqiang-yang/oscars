<?php


include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $oid;
	private $distance;
	private $modelFeeList;
	private $status;
	private $res;
	private $communityId;
	private $wid;
	private $note;

	protected function getPara()
	{
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
	}

	protected function main()
	{
		$order = Order_Api::getOrderInfo($this->oid);

        $lc = new Logistics_Coopworker();
        $coopworkerOrderList = $lc->getByOids(array($this->oid), Conf_Base::COOPWORKER_DRIVER);
        $dids = Tool_Array::getFields($coopworkerOrderList, 'cuid');
        $drivers = array();
        if (!empty($dids))
        {
            $drivers = Tool_Array::list2Map(Logistics_Api::getDriverByDids($dids), 'did');
        }

        $carModels = array();
        foreach ($coopworkerOrderList as $_order)
        {
            $carModels[] = empty($_order['car_model'])? $drivers[$_order['cuid']]['car_model']: $_order['car_model'];
        }

        if ($order['delivery_type'] != Conf_Order::DELIVERY_BY_YOURSELF && $order['community_id'] > 0)
		{
			$this->res = true;

            $data = Order_Community_Api::getDistanceAndFeeListNew($order['community_id'], $order['wid']);

			$this->distance = $data['distance'];
            foreach ($data['fee_list'] as $key => $value)
            {
                if (!in_array($value['car_model'], $carModels))
                {
                    unset($data['fee_list'][$key]);
                }
            }
            $this->modelFeeList = $data['fee_list'];
			$this->status = $data['status'];
			$this->communityId = $order['community_id'];
			$this->wid = $order['wid'];
			$this->note = $data['note'];

		}
		else
		{
			$this->res = false;
		}

	}

	protected function outputPage()
	{
		$this->smarty->assign('res', $this->res);
		$this->smarty->assign('distance', $this->distance);
		$this->smarty->assign('fee_list', $this->modelFeeList);
		$this->smarty->assign('status', $this->status);
		$this->smarty->assign('community_id', $this->communityId);
		$this->smarty->assign('wid', $this->wid);
		$this->smarty->assign('note', $this->note);

		$html = $this->smarty->fetch('order/block_distance_fee.html');
		$result = array('html' => $html);
		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
	}
}

$app = new App('pub');
$app->run();
