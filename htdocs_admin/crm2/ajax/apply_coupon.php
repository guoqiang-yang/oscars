<?php

/**
 * 优惠券申请 - 销售人员使用.
 */

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $cid;
	private $num;
	private $note;
	private $cate;
	private $retSt;

	protected function getPara()
	{
		$this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
		$this->num = Tool_Input::clean('r', 'num', TYPE_UINT);
		$this->note = Tool_Input::clean('r', 'note', TYPE_STR);
		$this->cate = Tool_Input::clean('r', 'cate', TYPE_UINT);
	}

	protected function checkPara()
	{
		if (empty($this->cid))
		{
			throw new Exception('apply_coupon: customer id is empty');
		}
		if (empty($this->cate))
		{
			throw new Exception('apply coupon: cate is empty');
		}
	}

	protected function checkAuth()
	{
		parent::checkAuth();

		/*
		$roleLevels = Admin_Role_Api::getRoleLevels($this->_uid, $this->_user);
		if (!isset($roleLevels[Conf_Admin::ROLE_ADMIN]) && !isset($roleLevels[Conf_Admin::ROLE_SALES]))
		{
			throw new Exception('common:permission denied');
		}
		*/
	}

	protected function main()
	{
		$couponInfo = array(
			'amount' => Conf_Coupon::COMMON_COUPON_PRICE,
			'num' => $this->num,
			'cate' => $this->cate,
		);

//		if ($this->cate == Conf_Coupon::CATE_COUPON_SAND)
//		{
//			$couponInfo['amount'] = Conf_Coupon::SAND_COUPON_PRICE;
//		}

		$info = array(
			'cid' => $this->cid,
			'sales_suid' => $this->_uid,
			'note' => $this->note,
			'coupons' => json_encode($couponInfo),
		);
		$this->retSt = Coupon_Api::apply($info);
	}

	protected function outputBody()
	{
		$result = array('errno' => $this->retSt > 0 ? 1 : 0, 'cid' => $this->cid);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App();
$app->run();