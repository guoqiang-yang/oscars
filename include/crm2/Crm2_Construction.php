<?php
/**
 * 树屋动态
 */
class Crm2_Construction extends Base_Func
{
	public function add(array $info)
	{
		unset($info['id']);
		assert( !empty($info) );
        
		$info['cid'] = intval($info['cid']);
		assert( !empty($info['cid']) );

        $info['uid'] = intval($info['uid']);
        assert( !empty($info['uid']) );
        
		$info['last_order_date'] = date('Y-m-d');
		$info['ctime'] = $info['mtime'] = date('Y-m-d H:i:s');

        if (empty($info['lat']))
        {
            $info['lat'] = 0.0;
        }
        
        if (empty($info['lng']))
        {
            $info['lng'] = 0.0;
        }
        
		$res = $this->one->insert('t_construction_site', $info);
		return $res['insertid'];
	}

	public function update($id, array $info, array $change=array())
	{
		$id = intval($id);
		assert( $id > 0 );
		assert( !empty($info) );

		$where = array('id' => $id);
		$ret = $this->one->update('t_construction_site', $info, $change, $where);
		return $ret['affectedrows'];
	}

	public function updateBulk(array $info, array $change=array(), $where)
	{
		assert( !empty($where) );
		$ret = $this->one->update('t_construction_site', $info, $change, $where);
		return $ret['affectedrows'];
	}

	public function delete($id)
	{
		$id = intval($id);

		$update = array('status' => Conf_Base::STATUS_DELETED);
		$where = array('id' => $id);
		$ret = $this->one->update('t_construction_site', $update, array(), $where);
		return $ret['affectedrows'];
	}

    public function getTotalByWhere($where)
    {
        $data = $this->one->select('t_construction_site', array('count(1)'), $where);
        
        return intval($data['data'][0]['count(1)']);
    }
    
    public function getListByWhere($where, $field=array('*'), $start=0, $num=20)
    {
        $order = 'order by id desc';
        
        $data = $this->one->select('t_construction_site', $field, $where, $order, $start, $num);
        
        return $data['data'];
    }
    
	public function getListOfCustomer($cid, &$total, $start=0, $num=20 ,$all=false)
	{
		$cid = intval($cid);
		assert( $cid > 0 );

		$where = sprintf('cid=%d AND status=%d', $cid, Conf_Base::STATUS_NORMAL);
		if (!$all)
		{
			//多城市
			$city = new City_City();
			$curCity = $city->getCity();
			$where .= sprintf(' AND (city=%d OR city=%d)', $curCity, Conf_City::OTHER);
		}

		//获取总数
		$data = $this->one->select('t_construction_site', array('count(1)'), $where);
		$total = intval($data['data'][0]['count(1)']);
		if (empty($total))
		{
			return array();
		}

		//查询结果
		$order = 'order by id desc';
		$data = $this->one->select('t_construction_site', array('*'), $where, $order, $start, $num);
		if (empty($data['data']))
		{
			return array();
		}

		return $data['data'];
	}

	public function get($id)
	{
		$id = intval($id);
		assert( $id > 0 );

		$where = array('id' => $id);

		//获取总数
		$res = $this->one->select('t_construction_site', array('*'), $where);
		if (empty($res['data']))
		{
			return array();
		}
		return $res['data'][0];
	}

	public function getBulk(array $ids)
	{
		if (empty($ids))
		{
			return array();
		}

		$where = array('id' => $ids);

		//获取总数
		$res = $this->one->select('t_construction_site', array('*'), $where);
		if (empty($res['data']))
		{
			return array();
		}
		return Tool_Array::list2Map($res['data'], 'id');
	}

	public function getByAddress($cid, $address)
	{
		$cid = intval($cid);
		assert( $cid > 0 );
		assert( !empty($address) );

		$where = array('cid' => $cid, 'address' => $address, 'status' => Conf_Base::STATUS_NORMAL);

		//获取总数
		$res = $this->one->select('t_construction_site', array('*'), $where);
		if (empty($res['data']))
		{
			return array();
		}
		return $res['data'][0];
	}

	public function getLastConstructionSitesOfCustomer($cid)
	{
		$cid = intval($cid);
		assert( $cid > 0 );

		$where = sprintf(' cid="%d" AND step!="%d" AND construction>0 ', $cid, Conf_Order::ORDER_STEP_EMPTY);
		$order = 'order by oid desc ';

		//获取总数
		$res = $this->one->select('t_order', array('*'), $where, $order, 0, 1);

		if (empty($res['data']))
		{
			return array();
		}

		if ($res['data'][0]['construction'] <= 0)
		{
			return array();
		}

		$construction = self::get($res['data'][0]['construction']);
		if (empty($construction) || $construction['status'] != Conf_Base::STATUS_NORMAL)
		{
			return array();
		}

		return $construction;
	}

	public function getListBySite($site)
	{
		if (!empty($site))
		{
			$where = ' address like "%' . $site . '%" ';
			$order = ' order by id desc ';
			$res = $this->one->select('t_construction_site', array('id'), $where, $order, 0, 0);

			return $res['data'];
		}

		return array();
	}

	public function getAll()
	{
		$data = $this->one->select('t_construction_site', array('*'), '');

		return $data['data'];
	}

	public function getCustomerSiteNum($cid)
	{
		$cid = intval($cid);
		assert( $cid > 0 );

		$where = array('cid' => $cid, 'status' => Conf_Base::STATUS_NORMAL);

		//获取总数
		$data = $this->one->select('t_construction_site', array('count(1)'), $where);
		$total = intval($data['data'][0]['count(1)']);
		if (empty($total))
		{
			return false;
		}

		return true;
	}
    
    public function search($search, $start=0, $num=20)
    {
        $where = 'status='. Conf_Base::STATUS_NORMAL;
        
        if ($this->is($search['cid']))
        {
            $where .= sprintf(' and cid=%d', $search['cid']);
        }
        if ($this->is($search['is_chk']))
        {
            $where .= $search['is_chk']==1? ' and community_id!=0': ' and community_id=0';
        }
        
        $tdata = $this->one->select('t_construction_site', array('count(1)'), $where);
		$total = intval($tdata['data'][0]['count(1)']);
        
        $data = array();
        if ($total > 0)
        {
            $order = 'order by id desc';
            $res = $this->one->select('t_construction_site', array('*'), $where, $order, $start, $num);
            $data = $res['data'];
            
            foreach($data as &$_data)
            {
                $addresses = explode(Conf_Area::Separator_Construction, $_data['address'], 2);
                if (count($addresses) == 2)
                {
                    $_data['community_name'] = $addresses[0];
                    $_data['_address'] = $addresses[1];
                }
                else
                {
                    $_data['community_name'] = '';
                    $_data['_address'] = $addresses[0];
                }
            }
        }
        
        return array('total'=>$total, 'data'=>$data);
    }
}
