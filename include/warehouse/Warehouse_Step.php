<?php

/**
 * 订单显示相关接口
 */
class Warehouse_Step extends Base_Api
{
	public static function getOrderNextStep($staff, $step)
	{
		$buttonText = '';
		$nextStep = 0;

		$roles = explode(',', $staff['role']);
		switch($step)
		{
			case Conf_In_Order::ORDER_STEP_NEW :// 1,      //客户已确认
				if (in_array(Conf_Admin::ROLE_BUYER, $roles) ||
					in_array(Conf_Admin::ROLE_ADMIN, $roles))
				{
					$buttonText = '确认采购';
					$nextStep = Conf_In_Order::ORDER_STEP_SURE;
				}
				break;

			case Conf_In_Order::ORDER_STEP_SURE :
			case Conf_In_Order::ORDER_STEP_RECEIVED :
			default:
				break;
		}

		return array('text' => $buttonText, 'next_step' => $nextStep);
	}

	public static function getOrderButtonHtml($staff, $order)
	{
		$step = $order['step'];
		$ret = self::getOrderNextStep($staff, $step);
		$buttonText = $ret['text'];
		$nextStep = $ret['next_step'];

		if ($buttonText)
		{
			$buttonHtml = sprintf('<a href="javascript:void(0);" class="btn btn-primary _j_chg_order_step" data-next_step="%d" style="margin-left:20px;">%s</a>',
				$nextStep, $buttonText);
		}

		return $buttonHtml;
	}
}

