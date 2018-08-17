<?php
/**
 * 客户回访
 *
 */
class Crm2_Customer_Tracking extends Base_Func
{
    
    private $_dao = null;
    
    function __construct()
    {
        $this->_dao = new Data_Dao('t_customer_tracking');
    }
    
	public function add(array $info)
	{
		assert(!empty($info));

		// 信息处理
		$fields = array('cid', 'edit_suid', 'content', 'type');
		$tracking = Tool_Array::checkCopyFields($info, $fields);

		// 插入数据
		$tracking['ctime'] = $tracking['mtime'] = date('Y-m-d H:i:s');
        $tid = $this->_dao->add($tracking);
        
		return $tid;
	}

	public function update($tid, array $info)
	{
		$tid = intval($tid);
		assert($tid > 0);

		// 更新
		$fields = array('edit_suid', 'content');
		$tracking = Tool_Array::checkCopyFields($info, $fields);
		
        $affectedrows = $this->_dao->update($tid, $tracking);
        
		return $affectedrows;
	}
    
    public function get($tid)
	{
		$tid = intval($tid);
		assert($tid > 0);

        return $this->_dao->get($tid);
	}

    public function getList($searchConf, $start, $num)
    {
        $where = '1=1';
        if ($this->is($searchConf['cid']))
        {
            $where .= ' and cid='. $searchConf['cid'];
        }
        if ($this->is($searchConf['type']))
        {
            $where .= ' and type='. $searchConf['type'];
        }
        if ($this->is($searchConf['sales_suid']))
        {
            $cc = new Crm2_Customer();
            $ccConf = array(
                'sales_suid'  => $searchConf['sales_suid'],
                'sale_status' => Conf_User::CRM_SALE_ST_PRIVATE,
            );
            $customerList = $cc->search($ccConf, array('cid'), 0, 0);
            $cids = array_unique(array_keys($customerList['data']));
            
            $where .= sprintf(' and (edit_suid=%d or cid in(%s))', $searchConf['sales_suid'], implode(',', $cids));
        }
	    if ($this->is($searchConf['from_date']))
	    {
		    $where .= sprintf(' and ctime>="%s"', date("Y-m-d 00:00:00", strtotime($searchConf['from_date'])));
	    }
	    if ($this->is($searchConf['end_date']))
	    {
		    $where .= sprintf(' and ctime<="%s"', date("Y-m-d 23:59:59", strtotime($searchConf['end_date'])));
	    }
        if ($this->is($searchConf['edit_suid']))
        {
            $where .= sprintf(' and edit_suid=%d', $searchConf['edit_suid']);
        }

        $total = $this->_dao->getTotal($where);
        
        $datas = array();
        if ($total > 0)
        {
            $datas = $this->_dao
                    ->order('tid', 'desc')
                    ->limit($start, $num)
                    ->getListWhere($where);
        }
        
        return array('total'=>$total, 'data'=>$datas);
    }
    
    
    //////////////////////////// Old  ////////////////////////
    
	public function getCustomerLatestTracking($cids, $type = 0, $orderBy='')
	{
		if(empty($cids))
		{
			return array();
		}

		$where = array('cid' => $cids);
		if ($type > 0)
		{
			$where['type'] = $type;
		}
        
        $data = $this->_dao
                ->order($orderBy)
                ->getListWhere($where);
		if (empty($data))
		{
			return array();
		}

		$result = array();
		foreach ($data as $item)
		{
			$cid = $item['cid'];
			if (!isset($data[$cid]))
			{
				$result[$cid] = $item;
			}
			else
			{
				if ($data[$cid]['tid'] > $item['cid'])
				{
					$result[$cid] = $item;
				}
			}
		}

		return $result;
	}


	
}
