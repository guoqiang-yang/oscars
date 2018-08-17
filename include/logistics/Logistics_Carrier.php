<?php

/**
 * Class Logistics_Carrier
 *
 * 搬运工
 */
class Logistics_Carrier extends Base_Func
{
	public static $FIELDS = array('name', 'mobile', 'wid', 'status', 'real_name', 'card_num','bank_info', 'password', 'city_id');

	public function get($cid)
	{
		$cid = intval($cid);
		assert($cid > 0);

		$where = array('cid' => $cid);
		$data = $this->one->select('t_carrier', array('*'), $where);

		return $data['data'][0];
	}

	public function getByCids($cids)
	{
		assert($cids);

		$where = 'cid in (' . implode(',', $cids) . ')';
		$data = $this->one->select('t_carrier', array('*'), $where);

		return $data['data'];
	}

	/**
	 * @param array $info
	 * @return mixed
	 *
	 * 添加搬运工
	 */
	public function add(array $info)
	{
		assert(!empty($info));

		//信息处理
		$carrier = Tool_Array::checkCopyFields($info, self::$FIELDS);
		$carrier['ctime'] = date('Y-m-d H:i:s');

		//初始化密码
		$carrier['salt'] = 2510;
		$rawPasswd = substr($carrier['mobile'], -6);
		$carrier['password'] = Logistics_Auth_Api::createPasswdMd5($rawPasswd, $carrier['salt']);

		// 插入数据
		$res = $this->one->insert('t_carrier', $carrier);

		return $res['insertid'];
	}

	/**
	 * @param $cid
	 * @return mixed
	 *
	 * 删除搬运工，做的假删除
	 */
	public function delete($cid)
	{
		$cid = intval($cid);
		assert($cid > 0);

		$where = array('cid' => $cid);
		$update = array('status' => Conf_Base::STATUS_DELETED);
		$ret = $this->one->update('t_carrier', $update, array(), $where);

		return $ret['affectedrows'];
	}

	/**
	 * @param $cid
	 * @param $info
	 * @param array $change
	 * @return mixed
	 *
	 * 更新搬运工信息
	 */
	public function update($cid, $info, $change = array())
	{
		$cid = intval($cid);
		assert($cid > 0);

		$carrier = Tool_Array::checkCopyFields($info, self::$FIELDS);

		$where = array('cid' => $cid);
		$ret = $this->one->update('t_carrier', $carrier, $change, $where);

		return $ret['affectedrows'];
	}

	/**
	 * @param $mobile
	 * @return array
	 * @throws Exception
	 *
	 * 根据手机号查询
	 */
	public function getByMobile($mobile)
	{
		// 检查条件
		if (!Str_Check::checkMobile($mobile))
		{
			throw new Exception('common:mobile format error');
		}

		$mobile = strval($mobile);
		assert(!empty($mobile));

		$where = sprintf('mobile like "%%%s%%"', $mobile);
		$where .= ' and status=0';

		$data = $this->one->select('t_carrier', array('*'), $where);
		if (empty($data['data']))
		{
			return array();
		}

		return $data['data'][0];
	}

	public function getList(&$total, $searchConf, $start, $num, $getAll = 0)
	{
		$where = !$getAll ? ' status = 0 ' : ' 1=1 ';

		if (!empty($searchConf['wid']))
		{
            if (is_array($searchConf['wid']))
            {
                $where .= sprintf(' AND wid in(%s) ', join(',', $searchConf['wid']));
            } else {
                $where .= sprintf(' AND wid=%d ', $searchConf['wid']);
            }
		}
		if (!empty($searchConf['name']))
		{
			$where .= sprintf(' and (name like "%%%s%%" ) ', mysql_escape_string($searchConf['name']));
		}
		if (!empty($searchConf['mobile']))
		{
			$where .= sprintf(' and mobile like "%%%s%%" ', mysql_escape_string($searchConf['mobile']));
		}
		if (!empty($searchConf['cid']))
		{
			$where .= sprintf(' and cid="%d" ', $searchConf['cid']);
		}
		//多城市
//		$city = new City_City();
//		$searchConf['city_id'] = $city->getCity();
//		if (!empty($searchConf['city_id']))
//		{
//			$where .= sprintf(' and city_id=%d', $searchConf['city_id']);
//		}

		// 查询数量
		$data = $this->one->select('t_carrier', array('count(1)'), $where);
		$total = intval($data['data'][0]['count(1)']);
		if (empty($total))
		{
			return array();
		}

		$order = 'order by order_num desc';
		$data = $this->one->select('t_carrier', array('*'), $where, $order, $start, $num);

		return $data['data'];
	}
}
