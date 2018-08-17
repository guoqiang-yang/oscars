<?php

/**
 * 商品相关业务
 */
class Warehouse_Inventory_Products extends Base_Func
{
	private $_dao;

	public function __construct()
	{
		$this->_dao = new Data_Dao('t_inventory_products');

		parent::__construct();
	}

	public function add($pid, $wid, array $info)
	{
		assert(!empty($pid));
		assert(!empty($wid));
		assert(!empty($info));
		$info['plan_id'] = $pid;
		$info['wid'] = $wid;

		return $this->_dao->add($info);
	}

	public function batchAdd($pid, $wid, array $products)
    {

        foreach ($products as &$product)
        {
            $product['plan_id'] = $pid;
            $product['wid'] = $wid;
            $product['ctime'] = date('Y-m-d H:i:s');
            $product['note'] = '';
        }

        return $this->_dao->batchAdd($products);
    }

    public function getList($searchConf, $start = 0, $num = 20, $order='')
    {
        $where = $this->_getWhereByConf($searchConf);
        $total = $this->_dao->getTotal($where);
        if ($total <= 0)
        {
            return array('total' => 0, 'list' => array());
        }

        if (empty($order))
        {
            $order = array('sid', 'desc');
        }

        $list = $this->_dao->order($order[0], $order[1])->limit($start, $num)->getListWhere($where);

        return array('total' => $total, 'list' => $list);
    }

    public function getListRawWhere($where, &$total, $order, $start = 0, $num = 20, $fields = array('*'))
    {
        $total = $this->_dao->setSlave()->getTotal($where);
        if ($total <= 0)
        {
            return array();
        }

        if (empty($order))
        {
            $order = array('sid', 'asc');
        }

        return $this->_dao->setSlave()->order($order[0], $order[1])->limit($start, $num)->setFields($fields)->getListWhere($where);
    }

    public function updateProduct($pid, $times, $tid, $products)
    {
        assert( !empty($times) );
        assert( !empty($pid) );
        assert( !empty($products) );

        $field = '';
        switch ($times)
        {
            case 1:
                $field = 'task_id1';
                break;
            case 2:
                $field = 'task_id2';
                break;
            case 3:
                $field = 'task_id3';
                break;
        }
        $info = array(
            $field => $tid,
        );

        $where = '1 = 0';
        foreach ($products as $product)
        {
            $where .= sprintf(' or (plan_id = %d and sid = %d and location = "%s" and %s = %d)' , $pid, $product['sid'], $product['location'], $field, 0);
        }

        $res = $this->one->update('t_inventory_products', $info, array(), $where);

        $ret = $res['affectedrows'];

        return $ret;
    }


    public function updateTaskProductPickedNum($tid, $sid, $location, $times, $num, $note='')
    {
        $tid = intval($tid);
        $sid = intval($sid);
        $times = intval($times);
        assert($tid > 0);
        assert($sid > 0);
        assert($times > 0);
        $task_id = '';
        $is_picked = '';
        $changNum = '';
        switch ($times)
        {
            case 1:
                $task_id = 'task_id1';
                $is_picked = 'is_picked1';
                $changNum = 'first_num';
                break;
            case 2:
                $task_id = 'task_id2';
                $is_picked = 'is_picked2';
                $changNum = 'second_num';
                break;
            case 3:
                $task_id = 'task_id3';
                $is_picked = 'is_picked3';
                $changNum = 'third_num';
                break;
        }

        $where = array(
            $task_id => $tid,
            'location' => $location,
            'sid' => $sid,
        );
        $update = array(
            $changNum => $num,
        );

        if (empty($note))
        {
            $update[$is_picked] = 1;
        }

        if (!empty($note))
        {
            $update['note'] = $note;
            $update['is_deal'] = 1;
        }

        return $this->_dao->updateWhere($where, $update);
    }

    private function _getWhereByConf($searchConf)
    {
        $where = '1=1';

        if (!empty($searchConf['pid']))
        {
            $where .= sprintf(' AND plan_id = %d', $searchConf['pid']);
        }
        if (isset($searchConf['task_id1']))
        {
            $where .= sprintf(' AND task_id1 = %d', $searchConf['task_id1']);
        }
        if (isset($searchConf['task_id2']))
        {
            $where .= sprintf(' AND task_id2 = %d', $searchConf['task_id2']);
        }
        if (isset($searchConf['task_id3']))
        {
            $where .= sprintf(' AND task_id3 = %d', $searchConf['task_id3']);
        }
        if (isset($searchConf['is_picked1']))
        {
            $where .= sprintf(' AND is_picked1 = %d', $searchConf['is_picked1']);
        }
        if (isset($searchConf['is_picked2']))
        {
            $where .= sprintf(' AND is_picked2 = %d', $searchConf['is_picked2']);
        }
        if (isset($searchConf['is_picked3']))
        {
            $where .= sprintf(' AND is_picked3 = %d', $searchConf['is_picked3']);
        }
        if (!empty($searchConf['location']))
        {
            $where .= sprintf(' AND location = "%s"', $searchConf['location']);
        }
        if (!empty($searchConf['sid']))
        {
            $where .= sprintf(' AND sid = %d', $searchConf['sid']);
        }

        return $where;
    }
}
