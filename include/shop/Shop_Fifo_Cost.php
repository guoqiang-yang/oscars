<?php
/**
 * 先进先出:成本 - 批次成本队列.
 * 
 */

class Shop_Fifo_Cost extends Base_Func
{
    
    private $dao;
    private $historyDao;
    
    function __construct()
    {
        parent::__construct();
        
        $this->dao = new Data_Dao('t_fifo_cost_queue');
        $this->historyDao = new Data_Dao('t_fifo_cost_history');
        
    }
    
    public function getBulkVaildCostsOfFifoQueue($wid, $sids, $fields=array('*'), $formatBySid=false)
    {
        if (empty($wid) || empty($sids)) return array();
        
        $fields = $fields!=array('*')? array_unique(array_merge($fields, array('id', 'sid'))): $fields;
        
        $where = sprintf('status=0 and num>0 and wid=%d and sid in (%s)', $wid, implode(',', $sids));
        
        $ret = $this->dao->setFields($fields)->order('id', 'asc')->getListWhere($where, false);
        
        $response = $ret;
        if ($formatBySid)   //format: sid=>{id=>{num, cost}, ... }
        {
            $response = array();
            
            foreach($ret as $qItem)
            {
                $response[$qItem['sid']][$qItem['id']] = $qItem;
            }
        }
        
        return $response;
    }
    
    
    public function getVaildCostOfFifoQueue($sid, $wid)
    {
        $where = sprintf('status=0 and num>0 and sid=%d and wid=%d', $sid, $wid);
        
        $ret = $this->dao->order('id', 'asc')->getListWhere($where);
        
        foreach ($ret as &$item)
        {
            $inDesc = $this->_appendHistoryTypeDesc($item['in_type'], $item['in_id']);
            $item['_in_desc'] = $inDesc['desc'];
            $item['_in_href'] = $inDesc['href'];
        }
        
        return $ret;
    }

    /**
     * 获取库均成本.
     */
    public function getAveCost($wid, $sids)
    {
        assert(!empty($wid) && !empty($sids));
        
        $_sids = is_array($sids)? implode(',', $sids): $sids;
        
        $field = array('sid', 'round(sum(num*cost)/sum(num)) as ave_cost');
        $where = sprintf('status=0 and num>0 and wid=%d and sid in (%s)', $wid, $_sids);
        $group = ' group by sid, wid';
        
        $ret = $this->dao->setFields($field)
                         ->getListWhere($where.$group, false);
        
        return Tool_Array::list2Map($ret, 'sid', 'ave_cost');
    }

    public function insert($sid, $wid, $data)
    {
        $assertVal = !empty($sid) && !empty($wid) && !empty($data['num']) && isset($data['cost'])
                     && isset($data['in_id']) && isset($data['in_type'])? true: false;
        assert($assertVal);
        
        $data['sid'] = $sid;
        $data['wid'] = $wid;
        
        $insertId = $this->dao->add($data);
        
        return $insertId;
    }
    
    /**
     * 单据出库操作时，获取成本，并更新对应数量.
     * 
     * @param int $id
     * @param int $num
     * @param array $outData     out_id:out_type:num
     */
    public function update4StockOut($id, $num)
    {
        assert(!empty($id)&&!empty($num));
        
        $affectRow = $this->dao->update($id, array(), array('num'=>0-$num));
        
        return $affectRow;
    }
    
    /**
     * 通过批次删除成本队列数据.
     * 
     * 盘盈不能通过改接口删除：
     *      原因：盘盈的in_id全部等于0，这样会导致全部删除（误删）
     * 
     * @param type $inId
     * @param type $inType
     * @param int $sid  需删除某批次的skuid，为0 批次全部删除
     */
    public function deleteFifoCostByBatch($inId, $inType, $sid=0)
    {
        assert(!empty($inId));
        
        $where = 'status=0 and in_id='. $inId. ' and in_type='. $inType.
                 (!empty($sid)? ' and sid='.$sid: '');
            
        $affectRow = $this->dao->deleteWhere($where);
        
        return $affectRow;
    }
    
    //////////////////////////////  t_fifo_cost_history  ////////////////////////////////
    
    public function insertHistory($sid, $wid, $data)
    {
        $assertVal = !empty($sid) && !empty($wid) && !empty($data['num']) && isset($data['cost'])
                     && isset($data['in_id']) && isset($data['in_type'])
                     && isset($data['out_id']) && isset($data['out_type']) ? true: false;
        assert($assertVal);
        
        $data['sid'] = $sid;
        $data['wid'] = $wid;
        
        $insertId = $this->historyDao->add($data);
        
        return $insertId;
    }
    
    public function getCostOfFifoHistory($sid, $wid, $start=0, $num=20)
    {
        assert(!empty($sid) && !empty($wid));
        
        $where = sprintf('sid=%d and wid=%d', $sid, $wid);
        
        $ret = $this->historyDao->order('id', 'desc')
                    ->limit($start, $num)
                    ->getListWhere($where);
        
        foreach ($ret as &$item)
        {
            $inDesc = $this->_appendHistoryTypeDesc($item['in_type'], $item['in_id']);
            $outDesc = $this->_appendHistoryTypeDesc($item['out_type'], $item['out_id']);
            $item['_in_desc'] = $inDesc['desc'];
            $item['_in_href'] = $inDesc['href'];
            $item['_out_desc'] = $outDesc['desc'];
            $item['_out_href'] = $outDesc['href'];
        }
        
        return $ret;
    }
    
    public function getTotalOfFifoHistory($sid, $wid)
    {
        assert(!empty($sid) && !empty($wid));
        
        $where = sprintf('sid=%d and wid=%d', $sid, $wid);
        
        return $this->historyDao->getTotal($where);
    }
    
    /**
     * 通过批次删除成本历史数据.
     * 
     * 盘盈不能通过改接口删除：
     *      原因：盘盈的in_id全部等于0，这样会导致全部删除（误删）
     * 
     * @param int $inId
     * @param int $inType
     * @param int $sid  需删除某批次的skuid，为0 批次全部删除
     */
    public function deleteFifoHistoryByBatch($inId, $inType, $sid=0)
    {
        assert(!empty($inId));
        
        $where = 'in_id='. $inId. ' and in_type='. $inType.
                (!empty($sid)? ' and sid='.$sid: '');
        
        $affectRow = $this->historyDao->deleteWhere($where);
        
        return $affectRow;
    }
    
    /////////////////////////// PRIVATE //////////////////////////
    
    private function _appendHistoryTypeDesc($type, $type2Id)
    {
        $typesDescs = Conf_Warehouse::stockHistoryTypeDetails();
        
        $desc = 'Sorry';
        $href = '';
        
        if (array_key_exists($type, $typesDescs))
        {
            $desc = $typesDescs[$type]['name'];
            $href = !empty($typesDescs[$type]['href'])? sprintf($typesDescs[$type]['href'], $type2Id): '';
        }
        
        return array('desc'=>$desc, 'href'=>$href);
    }
}