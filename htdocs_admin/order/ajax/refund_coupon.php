<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $rid;
	private $id;

	protected function getPara()
	{
		$this->rid = Tool_Input::clean('r', 'rid', TYPE_UINT);
		$this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
	}

	protected function main()
	{
		if (!empty($this->rid) && !empty($this->id))
		{
			$refund = Order_Api::getRefund($this->rid);
			if (!empty($refund))
			{
				if (!empty($refund['info']['refund_coupon']))
				{
					$refundCoupons = json_decode($refund['info']['refund_coupon'], true);
					if (!empty($refundCoupons))
					{
						if (in_array($this->id, $refundCoupons))
						{
							Coupon_Api::refundCoupon($this->id);
							$refundCoupons = array_diff($refundCoupons, array($this->id));
							Order_Api::updateRefund($this->rid, array('oid' => $refund['info']['oid'], 'refund_coupon' => json_encode($refundCoupons)));
						}
					}
				}
			}
		}
	}

	protected function outputPage()
	{
		$result = array('rid' => $this->rid, 'oid' => $this->refund['oid'], 'st' => $this->retSt);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
	}

}

$app = new App('pri');
$app->run();

