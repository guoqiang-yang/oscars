<?php
/**
 * 供应商列表
 */
class Warehouse_Supplier extends Base_Func
{
	private $supplierDao;

	public function __construct()
	{
		$this->supplierDao = new Data_Dao('t_supplier');
		parent::__construct();
	}

	public function add(array $supplier)
	{
		assert( !empty($supplier) );

		// 检测手机号是否注册
		$sinfo = $this->getByMobile($supplier['phone']);
		
		if (empty($sinfo))
		{
			// 插入数据
            $supplier['status'] = Conf_Base::STATUS_WAIT_AUDIT;
			$supplier['ctime'] = $supplier['mtime'] = date('Y-m-d H:i:s');
            $supplier['book_note'] = isset($supplier['book_note'])? $supplier['book_note']: '';
			$res = $this->one->insert('t_supplier', $supplier);
			$sid = $res['insertid'];
		}
		else 
		{
			$sid = -1;
		}
		
		return $sid;
	}

	public function delete($sid)
	{
		$sid = intval($sid);
		assert($sid > 0);

		$where = array('sid' => $sid);
		$update = array('status' => Conf_Base::STATUS_DELETED);
		$ret = $this->one->update('t_supplier', $update, array(), $where);
		return $ret['affectedrows'];
	}

	public function update($sid, array $info, array $change=array())
	{
		$sid = intval($sid);
		assert( $sid > 0 );
		assert( !empty($info) || !empty($change));
        
        if (isset($info['phone']))
        {
            // 检测手机号是否注册
            $sinfo = $this->getByMobile($info['phone']);
            
            // 手机号应该不能可以改，但是业务需求，只加不减
            if (!empty($sinfo))
            {
                $phone = explode(',', $sinfo[0]['phone']);
                $phoneNew = explode(',', $info['phone']);
                $info['phone'] = implode(',', array_unique(array_merge($phone, $phoneNew)));
            }
        }

		$where = array('sid' => $sid);
		$ret = $this->one->update('t_supplier', $info, $change, $where);
		return $ret['affectedrows'];
	}

	public function get($sid)
	{
		$sid = intval($sid);
		assert($sid > 0);

		$where = array('sid' => $sid);
		$data = $this->one->select('t_supplier', array('*'), $where);
		if (empty($data['data']))
		{
			return array();
		}

		return $data['data'][0];
	}

	public function getByMobile($mobile)
	{
		assert(!empty($mobile));
		
		$where = "phone like '%%$mobile%%'";
		$data = $this->one->select('t_supplier', array('*'), $where);
		
		return $data['data'];
	}
	
	public function getBulk(array $sids)
	{
		assert(!empty($sids));

		$where = array('sid' => $sids);
		$data = $this->one->select('t_supplier', array('*'), $where);
		if (empty($data['data']))
		{
			return array();
		}

		$list = Tool_Array::list2Map($data['data'], 'sid');
		return $list;
	}

	public function getList(array $conf, &$total, $order='', $start=0, $num=20, &$sum = 0)
	{
		if (empty($order))
		{
			$order = 'order by cid desc';
		}

		// 解析 conf 到 条件 $where
		$where = '1=1';
        if (!empty($conf['sid']))
        {
            $where .= sprintf(' and sid=%d ', mysql_escape_string($conf['sid']));
        }
		if (!empty($conf['cate1']))
		{
			$where .= sprintf(' and (cate1 like "%%%s%%") ', mysql_escape_string($conf['cate1']));
		}
		if (isset($conf['wid']) && !empty($conf['wid']))
		{
            if (is_array($conf['wid']))
            {
                $where .= ' and wid in ('. implode(',', $conf['wid']). ')';
            }
            else
            {
                $where .= ' and wid='. $conf['wid'];
            }
		}
		if (isset($conf['status']) && $conf['status'] != Conf_Base::STATUS_ALL)
        {
            $where .= sprintf(' and status = %d', $conf['status']);
        }
        if (isset($conf['managing_mode']) && $conf['managing_mode'] != Conf_Base::STATUS_ALL)
        {
            $where .= sprintf(' and managing_mode = %d', $conf['managing_mode']);
        }
        if (!empty($conf['city']) && empty($conf['sid']))
        {
            $where .= sprintf(' and find_in_set("%d", city)', $conf['city']);
        }

		if (!empty($conf['keyword']))
		{
			$where .= sprintf(' and (name like "%%%s%%" ', mysql_escape_string($conf['keyword']));
			$where .= sprintf(' or contact_name like "%%%s%%" ', mysql_escape_string($conf['keyword']));
			$where .= sprintf(' or address like "%%%s%%" ', mysql_escape_string($conf['keyword']));
			$where .= sprintf(' or products like "%%%s%%" ', mysql_escape_string($conf['keyword']));
			$where .= sprintf(' or phone like "%%%s%%" ', mysql_escape_string($conf['keyword']));
            $where .= sprintf(' or sid like "%%%s%%" ', mysql_escape_string($conf['keyword']));
            $where .= sprintf(' or note like "%%%s%%" ', mysql_escape_string($conf['keyword']));
            $where .= sprintf(' or alias_name like "%%%s%%")', mysql_escape_string($conf['keyword']));
		}
		
		if (!empty($conf['phone']))
		{
			$where .= sprintf(' and (phone like "%%%s%%") ', mysql_escape_string($conf['phone']));
		}

		// 查询数量
		$data = $this->one->select('t_supplier', array('count(1)'), $where);
		$total = intval($data['data'][0]['count(1)']);
		if (empty($total))
		{
			return array();
		}

		// 查询总数
		$data = $this->one->select('t_supplier', array('sum(account_balance)'), $where);
		$sum = intval($data['data'][0]['sum(account_balance)']);
		$sum /= 100;

		// 查询结果
		$data = $this->one->select('t_supplier', array('*'), $where, $order, $start, $num);
		if (empty($data['data']))
		{
			return array();
		}

		return $data['data'];
	}

	public function appendInfo(array &$item)
	{
		assert(!empty($item['sid']));
		$sid = $item['sid'];
		$c = $this->get($sid);
		$item['supplier'] = $c;
	}

	public function appendInfos(array &$list)
	{
		if (empty($list)) return;

		$sids = Tool_Array::getFields($list, 'sid');
		if (empty($sids)) return;

		$suppliers = $this->getBulk($sids);
		foreach ($list as $idx => $item)
		{
			$sid = $item['sid'];
			if (empty($suppliers[$sid])) continue;

			$c = $suppliers[$sid];
			$list[$idx]['_supplier'] = $c;
		}
	}

	/**
	 * 获取全部
	 * @return mixed
	 */
	public function getAll()
	{
		return $this->supplierDao->getAll();
	}

	/**
	 * 获取所有sku的最后一次入库的供货商
	 *
	 * @param string $deadline
	 * @return array sid=>suppliers
	 */
	public function getLastSupplierOfAllSku($deadline='')
	{
		$allSuppliers = $this->getAll();
		$deadline = (empty($deadline)) ? date('Y-m-d'):date('Y-m-d',strtotime($deadline));

		//一个月的最后供应商
		static $lastSuppliersMap;
		if (empty($lastSuppliersMap))
		{
			$lastSuppliersMap = array();
			$from = date('Y-m-d', strtotime($deadline)-86400*30);
			$table = 't_in_order_product as A inner join t_in_order as B on A.oid = B.oid ';
			$where = sprintf('A.ctime>"%s 00:00:00" and A.ctime<"%s 23:59:59" and A.status=0', $from, $deadline);
			$fields = array('A.sid, B.wid, B.sid as supplier_id, A.ctime');
			$res = $this->one->setDBMode()->select($table, $fields, $where);
			$products = $res['data'];
			foreach ($products as $item)
			{
				$sid = $item['sid'];
				$wid = $item['wid'];
				if (empty($lastSuppliersMap[$sid][$wid])
					|| $lastSuppliersMap[$sid][$wid]['ctime']<$item['ctime'])
				{
					$supplier = $allSuppliers[$item['supplier_id']];
					$lastSuppliersMap[$sid][$wid] = $supplier;
				}
			}
		}

		//所有sid
		$ss = new Shop_Sku();
		$skus = $ss->getAll(array('sid'));
		foreach ($skus as $sku)
		{
			$sid = $sku['sid'];
			foreach (Conf_Warehouse::$WAREHOUSES as $wid=>$warehouse)
			{
				if (empty($lastSuppliersMap[$sid][$wid]))
				{
					$table = 't_in_order_product as A inner join t_in_order as B on A.oid = B.oid ';
					$where = sprintf('A.sid=%d and B.wid=%d and A.ctime<"%s 23:59:59" and A.status=0', $sid, $wid, $deadline);
					$fields = array('A.sid, B.wid, B.sid as supplier_id, A.ctime');
					$order = 'order by ctime desc';
					$res = $this->one->setDBMode()->select($table, $fields, $where, $order, 0, 1);
					if (!empty($res['data']))
					{
						$supplier = $allSuppliers[$res['data'][0]['supplier_id']];
						$lastSuppliersMap[$sid][$wid] = $supplier;
					}
				}
			}
		}

		return $lastSuppliersMap;
	}

    /**
     * 获取供应商余额列表
     * @author libaolong
     * @param $conf
     * @param int $start
     * @param int $num
     * @return array
     */
    public function getSupplierAmountList($conf, $start = 0, $num = 20)
    {
        $sah = new Data_Dao('t_supplier_amount_history');

        $where = sprintf(' 1=1 and status=%d ', Conf_Base::STATUS_NORMAL);

        if (!empty($conf['sid']))
        {
            $where .= sprintf(' and sid=%d ', $conf['sid']);
        }
        if (!empty($conf['type']))
        {
            $where .= sprintf(' and type=%d ', $conf['type']);
        }
        if (!empty($conf['btime']))
        {
            $where .= sprintf(' and ctime>="%s" ', $conf['btime'].' 00:00:00');
        }
        if (!empty($conf['etime']))
        {
            $where .= sprintf(' and ctime<="%s" ', $conf['etime'].' 23:59:59');
        }

        $total = $sah->getTotal($where);
        $list = $sah->limit($start, $num)->order(' order by id desc ')->getListWhere($where);
        $sids = Tool_Array::getFields($list, 'sid');
        if (!empty($sids))
        {
            $supplierList = $this->getBulk($sids);
            foreach ($list as &$item)
            {
                $item['_supplier'] = $supplierList[$item['sid']];
            }
        }

        if (!empty($conf['sid']))
        {
            $supplier = $this->get($conf['sid']);
        }

        return array('list' => $list, 'total' => $total, 'supplier' => $supplier);
    }

    /**
     * 增加供应商流水及余额
     * @author libaolong
     * @param $info
     */
    public function addSupplierAmountRecord($info)
    {
        $supplier = $this->get($info['sid']);
        $salDao = new Data_Dao('t_supplier_amount_history');

        $info['amount'] = $supplier['amount'] + $info['price'];
        $salDao->add($info);
        $this->update($info['sid'], array('amount' => $info['amount']));
    }
}

