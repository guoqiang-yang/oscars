<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 16/7/27
 * Time: 09:34
 */
class Conf_Invite
{
	//邀请用户奖励的优惠券
	public static $INVITE_REWARD_COUPON = array(
		60 => 1,
		100 => 1,
		200 => 1,
		300 => 1,
		500 => 1,
	);

	//被邀请用户奖励的优惠券
	public static $REGISTER_REWARD_COUPON = array(30 => 1);

	//被邀请用户下单后，多少天邀请人可以获取奖励
	public static $REWARD_INTERVAL = 15;

	public static $START_DATE = '2016-08-02 00:00:00';
	public static $END_DATE = '2016-08-22 00:00:00';

	public static function isOnLine()
	{
		$date = date('Y-m-d H:i:s');

		return $date >= self::$START_DATE && $date <= self::$END_DATE;
	}
}