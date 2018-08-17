<?php

/**
 * Class Logistics_Driver
 *
 * 司机
 */
class Logistics_Driver extends Base_Func
{
	public static $FIELDS = array(
		'name',
		'mobile',
		'car_model',
		'source',
		'wid',
		'status',
		'car_code',
		'can_carry',
		'score',
		'note',
        'real_name',
        'card_num',
		'bank_info',
		'password',
		'city_id',
		'refuse_num',
		'regid',
        'trans_scope',
        'can_trash',
        'can_escort',
        'car_province',
        'car_number',
	);

	public function get($did)
	{
		$did = intval($did);
		assert($did > 0);

		$where = array('did' => $did);
		$data = $this->one->select('t_driver', array('*'), $where);

		return $data['data'][0];
	}

	public function getByDids($dids, $field=array('*'))
	{
		assert(!empty($dids));

		$where = 'did in (' . implode(',', $dids) . ')';
		$data = $this->one->select('t_driver', $field, $where);

		return $data['data'];
	}

	/**
	 * @param array $info
	 * @return mixed
	 *
	 * 添加司机
	 */
	public function add(array $info)
	{
		assert(!empty($info));

		//信息处理
		$driver = Tool_Array::checkCopyFields($info, self::$FIELDS);
		$driver['ctime'] = date('Y-m-d H:i:s');

		//初始化密码
		$driver['salt'] = 2510;
		$rawPasswd = substr($driver['mobile'], -6);
		$driver['password'] = Logistics_Auth_Api::createPasswdMd5($rawPasswd, $driver['salt']);

		//插入数据
		$res = $this->one->insert('t_driver', $driver);

		return $res['insertid'];
	}

	/**
	 * @param $did
	 * @return mixed
	 *
	 * 删除司机，做的假删除
	 */
	public function delete($did)
	{
		$did = intval($did);
		assert($did > 0);

		$where = array('did' => $did);
		$update = array('status' => Conf_Base::STATUS_DELETED);
		$ret = $this->one->update('t_driver', $update, array(), $where);

		return $ret['affectedrows'];
	}

	/**
	 * @param $did
	 * @param $info
	 * @param array $change
	 * @return mixed
	 *
	 * 更新司机信息
	 */
	public function update($did, $info, $change = array())
	{
		$did = intval($did);
		assert($did > 0);

		$driver = Tool_Array::checkCopyFields($info, self::$FIELDS);

		$where = array('did' => $did);
		$ret = $this->one->update('t_driver', $driver, $change, $where);

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
		$where .= ' ';
		$data = $this->one->select('t_driver', array('*'), $where);
		if (empty($data['data']))
		{
			return array();
		}
		return $data['data'][0];
	}

	public function checkModel($mid)
	{
		$where = array('car_model' => $mid);
		$chkData = $this->one->select('t_driver', array('count(did)'), $where);

		if ($chkData['data'][0]['count(did)'] >= 1)
		{
			return TRUE;
		}

		return FALSE;
	}

	public function checkSource($sid)
	{
		$where = array('source' => $sid);
		$chkData = $this->one->select('t_driver', array('count(did)'), $where);

		if ($chkData['data'][0]['count(did)'] >= 1)
		{
			return TRUE;
		}

		return FALSE;
	}
    
    public function getByRawWhere($where, $start=0, $num=20, $field=array('*'), $order='')
    {
        $ret = $this->one->select('t_driver', $field, $where, $order, $start, $num);
        
        return $ret['data'];
    }

	public function  getList(&$total, $searchConf, $start, $num, $getAll = 0)
	{
		$where = $this->_getWhereByConf($searchConf, $getAll);

		// 查询数量
		$data = $this->one->select('t_driver', array('count(1)'), $where);
		$total = intval($data['data'][0]['count(1)']);
		if (empty($total))
		{
			return array();
		}

		$order = 'order by score desc, order_num desc';
		$data = $this->one->select('t_driver', array('*'), $where, $order, $start, $num);

		return $data['data'];
	}

	private function _getWhereByConf($searchConf, $getAll = 0)
	{
        if ($getAll)
        {
            $where = '1=1';
        }
        else
        {
            $where = (isset($searchConf['status'])&&$searchConf['status']!=Conf_Base::STATUS_ALL) ? 
                    'status='.$searchConf['status']: '1=1';
        }
        
		if (!empty($searchConf['wid']))
		{
            if (is_array($searchConf['wid']))
            {
                $where .= sprintf(' AND wid in(%s) ', join(',', $searchConf['wid']));
            } else {
                $where .= sprintf(' AND wid=%d ', $searchConf['wid']);
            }
		}
		if (!empty($searchConf['car_model']))
		{
			$where .= sprintf(' and car_model="%d" ', $searchConf['car_model']);
		}
		if (!empty($searchConf['source']))
		{
			$where .= sprintf(' and source="%d" ', $searchConf['source']);
		}
		if (!empty($searchConf['name']))
		{
			$where .= sprintf(' and (name like "%%%s%%") ', mysql_escape_string($searchConf['name']));
		}
		if (!empty($searchConf['mobile']))
		{
			$where .= sprintf(' and mobile like "%%%s%%" ', mysql_escape_string($searchConf['mobile']));
		}
		if (!empty($searchConf['car_code']))
		{
			$where .= sprintf(' and car_code="%d" ', $searchConf['car_code']);
		}
		if (!empty($searchConf['can_carry']))
		{
			$where .= sprintf(' and can_carry="%d" ', $searchConf['can_carry']);
		}
		if (!empty($searchConf['did']))
		{
			$where .= sprintf(' and did="%d" ', $searchConf['did']);
		}
		//多城市
		if (empty($searchConf['city_id']))
		{
			$city = new City_City();
			$searchConf['city_id'] = $city->getCity();
		}
		if (!empty($searchConf['city_id']))
		{
			$where .= sprintf(' and city_id=%d', $searchConf['city_id']);
		}
        
//		if (!empty($searchConf['type']))
//		{
//			switch ($searchConf['type'])
//			{
//				case 'not_check':
//					$where .= sprintf(' AND did not in (select did from t_driver_queue)');
//					break;
//				case 'check':
//					$where .= sprintf(' AND did in (select did from t_driver_queue)');
//					break;
//				case 'not_accept_0':
//					$where .= sprintf(' AND did in (select did from t_driver_queue where line_id>0 AND type=%d AND alloc_time<"%s")', Conf_Driver::QUEUE_TYPE_ALLOC, date('Y-m-d H:i:s', time() - 600));
//					break;
//				case 'not_accept_10':
//					$where .= sprintf(' AND did in (select did from t_driver_queue where line_id>0 AND type=%d AND alloc_time>="%s" AND alloc_time<"%s")', Conf_Driver::QUEUE_TYPE_ALLOC, date('Y-m-d H:i:s', time() - 600), date('Y-m-d H:i:s', time() - 900));
//					break;
//				case 'not_accept_15':
//					$where .= sprintf(' AND did in (select did from t_driver_queue where line_id>0 AND type=%d AND alloc_time>="%s")', Conf_Driver::QUEUE_TYPE_ALLOC, date('Y-m-d H:i:s', time() - 900));
//					break;
//				case 'accept':
//					$where .= sprintf(' AND did in (select did from t_driver_queue where line_id>0 AND type=%d)', Conf_Driver::QUEUE_TYPE_ACCEPT);
//					break;
//				case 'send':
//					$where .= sprintf(' AND did in (select did from t_driver_queue where line_id>0 AND type=%d)', Conf_Driver::QUEUE_TYPE_SEND);
//					break;
//				case 'has_send':
//					$where .= sprintf(' AND did in (select did from t_driver_queue where line_id>0 AND type=%d)', Conf_Driver::QUEUE_TYPE_ARRIVE);
//					break;
//				case 'refuse':
//					$where .= sprintf(' AND refuse_num>=%d', Conf_Driver::MAX_REFUSE_NUM);
//					break;
//				default:
//					//nothing
//			}
//
//			return $where;
//		}

		return $where;
	}
}
