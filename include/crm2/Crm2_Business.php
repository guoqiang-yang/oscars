<?php
/**
 * 公司
 */
class Crm2_Business extends Base_ORM
{
	protected $table = 't_business';
	protected $idField = 'bid';
	protected $fields = array(
		'bid',
		'name',
		'contract_name',
		'contract_phone',
		'contract_phone2',
		'address',
		'payment_days',
		'staff_count',
		'join_date',
		'sales_suid',
		'sales_suid2',
		'note',
		'status',
		'ctime',
		'mtime',
	);


	public function getList(array $conf, $order='', $start = 0, $num = 20)
	{
		// 解析 conf 到 条件 $where
		$where = '1=1';


		if (!empty($conf['bid']))
		{
			$where .= sprintf(' and bid="%d" ', $conf['bid']);
		}
		if (!empty($conf['sales_suid']))
		{
			$where .= sprintf(' and (sales_suid="%d" OR sales_suid2="%d") ', $conf['sales_suid'], $conf['sales_suid']);
		}
		if (!empty($conf['name']))
		{
			$where .= sprintf(' and (name like "%%%s%%") ', mysql_escape_string($conf['name']));
		}
		if (!empty($conf['contract_name']))
		{
			$where .= sprintf(' and contract_name like "%%%s%%" ', mysql_escape_string($conf['contract_name']));
		}
		if (!empty($conf['contract_phone']))
		{
			$where .= sprintf(' and (contract_phone like "%%%s%%" or contract_phone2 like "%%%s%%") ', mysql_escape_string($conf['contract_phone']), mysql_escape_string($conf['contract_phone']));
		}

		// 查询数量
		$data = $this->one->select($this->table, array('count(1)'), $where);
		$total = intval($data['data'][0]['count(1)']);
		if (empty($total))
		{
			return array('total' => $total, 'list' => array());
		}

		// 查询结果
		$data = $this->one->select($this->table, array('*'), $where, $order, $start, $num);
		if (empty($data['data']))
		{
			return array('total' => $total, 'list' => array());
		}

		return array('total' => $total, 'list' => $data['data']);
	}

	public function appendBusiness(&$list, $field = 'bid')
	{
		$bids = Tool_Array::getFields($list, $field);
		$bids = array_filter(array_unique($bids));
		if(empty($bids))
		{
			return;
		}

		$businessList = $this->getByIds($bids);
		foreach ($list as &$item)
		{
			$bid = $item[$field];
			if ($bid > 0)
			{
				$item['business'] = $businessList[$bid];
			}
			else
			{
				$item['business'] = array();
			}
		}
	}
}
