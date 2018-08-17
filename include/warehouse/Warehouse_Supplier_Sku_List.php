<?php

/**
 * 供货商的商品列表.
 */

class Warehouse_Supplier_Sku_List extends Base_Func
{
    
    private $dao;

	public function __construct()
	{
		parent::__construct();
        
		$this->dao = new Data_Dao('t_supplier_sku_list');
	}
    
    public function getSupplierSkuList($supplierId, $start=0, $num=0)
    {
        assert(!empty($supplierId));
        
        $fields = array('id', 'supplier_id', 'sku_id', 'purchase_price', 'ctime');
        $where = 'status=0 and supplier_id='. $supplierId;
        
        return $this->dao->setFields($fields)->limit($start, $num)->getListWhere($where, false);
    }
    
    public function getSupplierListBySku($skuid)
    {
        assert($skuid);

        $where = 'status = 0 and sku_id = ' .  $skuid;

        return $this->dao->getListWhere($where, false);
    }
    
    public function addSku($supplierId, $skuId, $addInfo=array())
    {
        assert(!empty($supplierId));
        assert(!empty($skuId));
        
        $addInfo['supplier_id'] = $supplierId;
        $addInfo['sku_id'] = $skuId;
        $addInfo['status'] = Conf_Base::STATUS_NORMAL;
        $addInfo['ctime'] = date('Y-m-d H:i:s');
        
        $updateFields = array('status');
        
        $insertId = $this->dao->add($addInfo, $updateFields);
        
        return $insertId;
    }
    
    /**
     * 当不存在该sku时，添加sku.
     */
    public function addSkuWhenUnExist($supplierId, $skuInfos)
    {
        assert(!empty($supplierId));
        assert(!empty($skuInfos));
        
        $ownSkuList = $this->getSupplierSkuList($supplierId);
        $ownSkuIds = Tool_Array::getFields($ownSkuList, 'sku_id');
        
        foreach($skuInfos as $one)
        {
            if (!isset($one['sku_id'])) continue;
            if (in_array($one['sku_id'], $ownSkuIds)) continue;
            
            $addInfo = isset($one['purchase_price'])? array('purchase_price'=>$one['purchase_price']): array();
            $this->addSku($supplierId, $one['sku_id'], $addInfo);
        }
        
    }
    
    /**
     * sku是否存在.
     * 
     * @param int $supplierId
     * @param int $skuId
     */
    public function isSkuExist($supplierId, $skuId)
    {
        $where = sprintf('status=0 and supplier_id=%d and sku_id=%d',
                $supplierId, $skuId);
        
        return $this->dao->getTotal($where)? true: false;
    }
    
    public function modifyPurchasePrice($supplierId, $skuId, $price)
    {
        assert(!empty($supplierId));
        assert(!empty($skuId));
        assert($price > 0);

        if (in_array($skuId, array(15467, 12904)) && $price != 100)
        {
            throw new Exception('虚拟运费单价1元，不能修改，可以通过修改虚拟运费商品数量来调整运费！');
        }

        $where = 'supplier_id='. $supplierId.' and sku_id='. $skuId;
        $upInfo = array('purchase_price' => $price);
        
        $affectedrows = $this->dao->updateWhere($where, $upInfo);
        
        return $affectedrows;
    }
    
    public function delete($supplierId, $skuId)
    {
        assert(!empty($supplierId));
        assert(!empty($skuId));
        
        $where = 'supplier_id='. $supplierId.' and sku_id='. $skuId;
        $upInfo = array('status' => Conf_Base::STATUS_DELETED);
        
        $affectedrows = $this->dao->updateWhere($where, $upInfo);
        
        return $affectedrows>0? true: false;
    }
    
}