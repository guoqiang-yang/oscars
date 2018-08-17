<?php
/**
 * 订单关联的商品
 */
class Warehouse_In_Order_Product extends Base_Func
{
	private $orderProductDao;
	public function __construct()
	{
		$this->orderProductDao = new Data_Dao('t_in_order_product');
		parent::__construct();
	}

	public function update($oid, array $products)
	{
		$oid = intval($oid);
		assert( $oid > 0 );
		assert( !empty($products) );

        //检测更新商品的采购来源
        foreach ($products as $product)
		{
            if (!isset($product['source'])||empty($product['source']))
            {
                throw new Exception('采购单类型异常，请联系技术人员！');
            }
        }
		
		//添加或更新
		foreach ($products as $product)
		{
			$product['oid'] = $oid;
			$this->insert($product);
		}

        //查询原有products
//		$oldProducts = $this->getProductsOfOrder($oid);

//		//原来有,现在没有的,删除；有做采购单的人主动删除不要的商品
//		$oldPids = Tool_Array::getFields($oldProducts, 'pid');
//		$newPids = Tool_Array::getFields($products, 'pid');
//		$delPids = array_diff($oldPids, $newPids);
//		foreach ($delPids as $pid)
//		{
//			$this->delete($oid, $pid);
//		}

		return true;
	}

	public function insert(array $info)
	{
		assert(!empty($info));
        if (!isset($info['source'])||empty($info['source']))
        {
            throw new Exception('采购单类型异常，请联系技术人员！');
        }

		$info['oid'] = intval($info['oid']);
		$info['sid'] = intval($info['sid']);
		$info['price'] = intval(strval($info['price']));
		$info['sale_price'] = intval(strval($info['sale_price']));
		$info['num'] = intval($info['num']);

		assert(!empty($info['oid']));
		assert(!empty($info['sid']));

		if (0 == $info['num'] )
		{
			$ret = $this->delete($info['oid'], $info['sid'], $info['source']);
		}
		else
		{
			$info['status'] = Conf_Base::STATUS_NORMAL;
            
            if (empty($info['ctime']))
            {
                $info['ctime'] = date('Y-m-d H:i:s');
            }
			$update = array('price', 'num', 'status', 'sale_price');
			$res = $this->one->insert('t_in_order_product', $info, $update);
			$ret = $res['affectedrows'];
		}

		return $ret;
	}

    public function updateProduct($oid, $sid, $info, $source=Conf_In_Order::SRC_COMMON)
    {
        assert( !empty($info) );

		$info['price'] = intval(strval($info['price']));
		$info['num'] = intval($info['num']);
        $info['status'] = Conf_Base::STATUS_NORMAL;

		assert( !empty($oid) );
		assert( !empty($sid) );

		if (0 == $info['num'] )
		{
			$ret = $this->delete($oid, $sid, $source);
		}
		else
        {
            $where = 'oid='. $oid. ' and sid='. $sid.' and source='.$source;
            $res = $this->one->update('t_in_order_product', $info, array(), $where);
            
			$ret = $res['affectedrows'];
        }
        
        return $ret;
    }   
    
	public function delete($oid, $sid, $source=Conf_In_Order::SRC_COMMON)
	{
		$oid = intval($oid);
		$sid = intval($sid);
		assert($oid > 0);
		assert($sid > 0);
        assert(intval($source)>0);

		$where = array('oid' => $oid, 'sid' => $sid, 'source'=>$source);
		$update = array('status' => Conf_Base::STATUS_DELETED);
		$ret = $this->one->update('t_in_order_product', $update, array(), $where);
		return $ret['affectedrows'];
	}

	public function getProductsOfOrder($oid, $field=array('*'))
	{
		$oid = intval($oid);
		assert($oid > 0);

        $where = sprintf(' oid=%d and status=%d and num>0', $oid, Conf_Base::STATUS_NORMAL);
		$data = $this->one->select('t_in_order_product', $field, $where);
		if (empty($data['data']))
		{
			return array();
		}

		return $data['data'];
	}
    
    public function getSumByOid($field, $oid)
    {
        $where = 'oid='. $oid. ' and status=0';
        $field[0] = $field[0]. ' as sum';
        $ret = $this->one->select('t_in_order_product', $field, $where);
        
        return !empty($ret['data'])? intval($ret['data'][0]['sum']): 0;
    }
    
    public function getProductsByWhere($where, $field=array('*'), $start=0, $num=20, $order='', $forceIndex='')
    {
        assert(!empty($where));
        
        $kind = !empty($forceIndex)? 't_in_order_product force index('.$forceIndex.')': 't_in_order_product';
        
        $data = $this->one->select($kind, $field, $where, $order, $start, $num);
        
        return $data['data'];
    }

	public function getLatestRecordBysidAndDate($sid, $wid, $date)
	{
		$date = $date . ' 23:59:59';
		$where = sprintf(' sid=%d and ctime<="%s" and status=%d and oid in (select oid from t_in_order where wid=%d)', $sid, $date, Conf_Base::STATUS_NORMAL, $wid);
		$data = $this->one->select('t_in_order_product', array('*'), $where, 'order by oid desc', 0, 1);
		if (empty($data['data']))
		{
			return array();
		}

		$products = array_shift($data['data']);

		return $products;
	}
    
    public function deleteProductsByOrder($oid)
    {
        $where = array(
            'oid' => $oid,
        );
        
        $upData = array('status'=>Conf_Base::STATUS_DELETED);
        
        $ret = $this->one->update('t_in_order_product', $upData, array(), $where);
        
        return $ret['affectedrows'];
    }

    public function getOrdersBySid($sid)
    {
        $where = array('sid' => $sid);
        $list = $this->orderProductDao->getListWhere($where);

        return $list;
    }

    public function getListRawWhere($where, &$total, $order, $start = 0, $num = 20, $fields = array('*'))
    {
        $total = $this->orderProductDao->setSlave()->getTotal($where);
        if ($total <= 0)
        {
            return array();
        }

        if (empty($order))
        {
            $order = array('oid', 'asc');
        }

        return $this->orderProductDao->setSlave()->order($order[0], $order[1])->limit($start, $num)->setFields($fields)->getListWhere($where);
    }
}
