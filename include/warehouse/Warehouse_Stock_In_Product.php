<?php
/**
 * 订单关联的商品
 */
class Warehouse_Stock_In_Product extends Base_Func
{
	private $stockInProductDao;
	public function __construct()
	{
		$this->stockInProductDao = new Data_Dao('t_stock_in_product');
		parent::__construct();
	}

	public function update($id, array $products)
	{
		$id = intval($id);
		assert( $id > 0 );
		assert( !empty($products) );

		//查询原有products
		$oldProducts = $this->getProductsOfStockIn($id);

		//添加或更新
		foreach ($products as $product)
		{
			$product['id'] = $id;
			$this->insert($product);
		}

		//原来有,现在没有的,删除
		$oldPids = Tool_Array::getFields($oldProducts, 'sid');
		$newPids = Tool_Array::getFields($products, 'sid');
		$delPids = array_diff($oldPids, $newPids);
		foreach ($delPids as $sid)
		{
			$this->delete($id, $sid);
		}

		return true;
	}

	public function insert(array $info)
	{
		assert(!empty($info));

		$info['id'] = intval($info['id']);
		$info['sid'] = intval($info['sid']);
		$info['price'] = intval(strval($info['price']));
		$info['num'] = intval($info['num']);
        $info['srid'] = 0;

		assert(!empty($info['id']));
		assert(!empty($info['sid']));

		if (0 == $info['num'] )
		{
			$ret = $this->delete($info['id'], $info['sid']);
		}
		else
		{
			$info['status'] = Conf_Base::STATUS_NORMAL;
            
            if (empty($info['ctime']))
            {
                $info['ctime'] = date('Y-m-d H:i:s');
            }
            
			$update = array('price', 'num', 'status');
			$res = $this->one->insert('t_stock_in_product', $info, $update);
			$ret = $res['affectedrows'];
		}

		return $ret;
	}
    
    /**
     * 插入退货商品.
     */
    public function insertRefund($info)
    {
        assert(!empty($info));
        assert(!empty($info['srid']));
        
        $info['status'] = Conf_Base::STATUS_NORMAL;
        
        if (empty($info['ctime']))
        {
            $info['ctime'] = date('Y-m-d H:i:s');
        }
        
        $res = $this->one->insert('t_stock_in_product', $info);
			
        return $res['affectedrows'];
    }
    
    public function updateProduct($id, $sid, $info, $change=array())
    {
        assert( !empty($info)||!empty($change) );
		assert( !empty($id) );
		assert( !empty($sid) );

		$where = 'id='. $id. ' and sid='. $sid;
        $res = $this->one->update('t_stock_in_product', $info, $change, $where);

        $ret = $res['affectedrows'];
        
        return $ret;
    }

    /**
     * 删除入库单商品
     * @param int $id 入库单id
     * @param int $pid 商品id，如果商品id为0，则删除入库单全部商品
     */
	public function delete($id, $sid=0)
	{
		$id = intval($id);
		$sid = intval($sid);
		assert( $id > 0 );
		
        $where['id'] = $id;
        
        if (!empty($sid))
        {
            $where['sid'] = $sid;
        }
        
		$update = array('status' => Conf_Base::STATUS_DELETED);
		$ret = $this->one->update('t_stock_in_product', $update, array(), $where);
		
        return $ret['affectedrows'];
	}

    /**
     * 删除入库退货单商品
     * @param int $srid 入库退货单srid
     * @param int $sid sku_id，如果sku_id为0，则删除入库退货单全部商品
     */
    public function deleteBySrid($srid, $sid=0)
    {
        $srid = intval($srid);
        $sid = intval($sid);
        assert( $srid > 0 );

        $where['srid'] = $srid;

        if (!empty($sid))
        {
            $where['sid'] = $sid;
        }

        $update = array('status' => Conf_Base::STATUS_DELETED);
        $ret = $this->one->update('t_stock_in_product', $update, array(), $where);

        return $ret['affectedrows'];
    }

	public function getProductsOfStockIn($id, $srid=0, $status = Conf_Base::STATUS_NORMAL, $field=array('*'))
	{
		if(empty($id) && empty($srid))
		{
			return array();
		}
		$where = array(
            'id' => $id, 
            'status'=> $status,
        );
        
        if (!empty($srid))
        {
            $where['srid'] = $srid;
        }
        
		$data = $this->one->select('t_stock_in_product', $field, $where);
		if (empty($data['data']))
		{
			return array();
		}

		$products = $data['data'];
		return $products;
	}
    
        
    public function getProductsByIds($ids, $field=array('*'))
    {
        assert(is_array($ids));
        
        $where = array(
            'id' => $ids,
            'status' => Conf_Base::STATUS_NORMAL,
        );
        
        $data = $this->one->select('t_stock_in_product', $field, $where);
		if (empty($data['data']))
		{
			return array();
		}

		return $data['data'];
        
    }

	public function getLastItems($sid, $num=10)
	{
		$sid = intval($sid);
		assert($sid > 0);

		$where = array('sid'=> $sid, 'status'=>0);
		$list = $this->stockInProductDao->order('ctime', 'desc')->limit(0, $num)->getListWhere($where, false);
		return $list;
	}
    
    public function getByRawWhere($where, $kind='', $field=array('*'), $order='', $start=0, $num=0)
    {
        $kind = !empty($kind)? $kind: 't_stock_in_product';
        
        $ret = $this->one->select($kind, $field, $where, $order, $start, $num);
        
        return $ret['data'];
    }
    
    /**
     * 查询采购单的商品是否入库.
     * 
     * @param int $oid 入库单id
     * @param int $sid skuid
     * @param int $source 入库单【商品来源】（临采 or 普采）
     */
    public function checkProductStockin($oid, $sid, $source)
    {
        assert(!empty($oid));
        assert(!empty($sid));
        assert($source==Conf_In_Order::SRC_COMMON || $source==Conf_In_Order::SRC_TEMPORARY);
        
        $where = sprintf('status=0 and srid=0 and sid=%d and id in '
                . '(select id from t_stock_in where status=0 and oid=%d and source=%d)',
                $sid, $oid, $source);
        
        $ret = $this->one->select('t_stock_in_product', array('*'), $where);
        
        return !empty($ret['data'])? true: false;
    }

    public function updateRefundProduct($srid, $sid, $info)
    {
        $where = array(
            'srid' => $srid,
            'sid' => $sid,
        );
        $ret = $this->stockInProductDao->updateWhere($where, $info);

        return $ret;
    }

    public function getSumByWhere($field, $where=false)
    {
        $ret = $this->stockInProductDao->getSum($field, $where);
        return $ret;
    }
}
