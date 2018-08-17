<?php

class Shop_Processed_Order  extends Base_Func
{
    private $_dao = null;
    
    function __construct()
    {
        $this->_dao = new Data_Dao('t_processed_order');
    }


    /**
     * 创建加工单.
     * 
     * @param type $info
     * @param type $products
     */
    public function createProcessedOrder($wid, $type, $info, $products)
    {
        assert(!empty($wid));
        assert(!empty($type));
        
        // 生产加工单
        $info['wid'] = $wid;
        $info['type'] = $type;
        $id = $this->_dao->add($info);
        
        // 插入商品
        $spop = new Shop_Processed_Order_Products();
        $addRet = $spop->addProducts($id, $products);
        
        if ($addRet)
        {
            $ret = array('errno'=>0, 'errmsg'=>'ok', 'data'=>array('id'=>$id));
        }
        else
        {
            $ret = array('errno'=>31, 'errmsg'=>'生产加工单商品失败');
        }
        
        return $ret;
    }
    
    
    public function get($id)
    {
        assert(!empty($id));
        
        return $this->_dao->get($id);
        
    }
    
    public function getList($search, $start=0, $num=20, $field=array('*'))
    {
        $where = $this->_genWhere($search);
        
        return $this->_dao->setFields($field)->limit($start, $num)->getListWhere($where);
    }
    
    public function getTotal($search)
    {
        $where = $this->_genWhere($search);
        
        return $this->_dao->getTotal($where);
    }


    private function _genWhere($search)
    {
        $where = '1=1';
        if ($this->is($search['sid']))
        {
            $where .= ' and id in (select id from t_processed_order_products where sid='.$search['sid'].')';
        }
        else if ($this->is($search['wid']))
        {
            $where .= ' and wid='. $search['wid'];
        }
        else if ($this->is($search['type']))
        {
            $where .= ' and type='. $search['type'];
        }
        
        return $where;
    }
}

