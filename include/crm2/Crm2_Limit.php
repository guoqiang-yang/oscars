<?php

/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 16/1/13
 * Time: 09:17
 */
class Crm2_Limit extends Base_Func
{
    //2016-01发优惠券活动
    public static $KEY_SEND_COUPHON_201601 = 1;
    public static $MAX_SEND_COUPON_201601 = 1;

    //2016-01薅羊毛（收集手机号）活动
    public static $KEY_COLLECT_MOBILE_201601 = 2;
    public static $MAX_COLLECT_MOBILE_201601 = 50000;       //单位分，最大可以领500的优惠券

	public static $KEY_SEND_AMOUNT_MSG = 5;         //是否发过余额提醒短信
	public static $VAL_HAS_SEND_AMOUNT_MSG = 1;     //发过，发送完之后设置成这个值，避免重发
	public static $VAL_NOT_SEND_AMOUNT_MSG = 0;     //充值的时候设置成没发过的状态，这样下次低于指定值还会发

    //2017-02-24分享抽奖活动
    public static $MAX_LOTTERY_NUM_PER_DAY_170224 = 3;             //每个微信号每天最多抽3次
    public static $KEY_LOTTERY_NUM_170224 = 7;              //每日已抽次数的key

	private $_dao;

	public function __construct()
	{
		$this->_dao = new Data_Dao('t_customer_limit');

		parent::__construct();
	}

	public function add($info)
	{
		if (!isset($info['ext']))
		{
			$info['ext'] = '';
		}
		return $this->_dao->add($info);
	}

	public function update($id, $update, $change = array())
	{
		return $this->_dao->update($id, $update, $change);
	}

	public function getByWhere($where)
	{
		return $this->_dao->getListWhere($where);
	}

	public function deleteByWhere($where)
	{
		return $this->_dao->deleteWhere($where);
	}
}