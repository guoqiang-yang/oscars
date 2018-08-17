<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 16/8/16
 * Time: 16:00
 */
class Logistics_Driver_Queue extends Base_Func
{
	private $_dao;


	public function __construct()
	{
		$this->_dao = new Data_Dao('t_driver_queue');

		parent::__construct();
	}

	public function get($id)
	{
		return $this->_dao->get($id);
	}

	public function geBulk($dids)
	{
		$where = array('did' => $dids);

		return $this->_dao->getListWhere($where);
	}

	public function getList($seachConf, $start = 0, $num = 20, $order='')
	{
		$where = $this->_getWhereByConf($seachConf);

		$total = $this->_dao->getTotal($where);
		if ($total <= 0)
		{
			return array('total' => 0, 'list' => array());
		}

        if (empty($order))
        {
            $order = array('id', 'desc');
        }
        
		$list = $this->_dao->order($order[0], $order[1])->limit($start, $num)->getListWhere($where);
        
		return array('total' => $total, 'list' => $list);
	}

	public function getByDid($did)
	{
		$where = array('did' => $did);
		$list = $this->_dao->getListWhere($where);
		if (empty($list))
		{
			return array();
		}

		return array_shift($list);
	}

	public function getByLineid($lineid)
	{
		$where = array('line_id' => $lineid);

		$list = $this->_dao->getListWhere($where);
        
		return $list;
	}
    
    public function getByLineids($lineIds)
    {
        assert(!empty($lineIds));
        $where = sprintf('status=0 and step>=%d and line_id in (%s)',
                    Conf_Driver::STEP_ALLOC, implode(',',$lineIds) );
        
        $list = $this->_dao->getListWhere($where);
        
        return $list;
    }

	public function getByDidAndWid($did, $wid)
	{
		$where = array('did' => $did, 'wid' => $wid);
		$list = $this->_dao->getListWhere($where);
		if (empty($list))
		{
			return array();
		}

		return array_shift($list);
	}

	public function isCheckIn($did)
	{
		$queue = $this->getByDid($did);
		if (empty($queue) || $queue['status'] != Conf_Base::STATUS_NORMAL || $queue['step'] == Conf_Driver::STEP_ARRIVE || $queue['step'] == Conf_Driver::STEP_EMPTY)
		{
			return false;
		}

		return true;
	}

	public function checkIn($info)
	{
		$res = $this->getByDid($info['did']);
		if (!empty($res))
		{
			return $this->_dao->update($res['id'], $info);
		}
		return $this->_dao->add($info);
	}

	public function delete($id)
	{
		return $this->_dao->delete($id);
	}

	public function deleteByWhere($where)
	{
		$update = array(
			'wid' => 0,
			'line_id' => 0,
			'fee' => 0,
			'check_time' => '0000-00-00 00:00:00',
			'alloc_time' => '0000-00-00 00:00:00',
			'refuse_time' => '0000-00-00 00:00:00',
			'refuse_num' => 0,
			'step' => Conf_Driver::STEP_EMPTY,
		);
		return $this->_dao->updateWhere($where, $update);
	}

	public function update($id, $update, $change = array())
	{
		return $this->_dao->update($id, $update, $change);
	}
    
    public function updateByDid($did, $update, $change=array())
    {
        assert(!empty($did));
        assert(!empty($update)||!empty($change));
        
        $where = 'did='.$did;
        return $this->_dao->updateWhere($where, $update, $change);
    }

	public function getListByWhere($where, $orderBy, $field=array('*'), $withPK=true)
	{
		return $this->_dao->setFields($field)->order($orderBy)->getListWhere($where, $withPK);
	}


	private function _getWhereByConf($seachConf)
	{
		$where = sprintf('status=%d AND step>%d', Conf_Base::STATUS_NORMAL, Conf_Driver::STEP_EMPTY);

        // 如果按照id查询，不在关联后续的状态
		if (!empty($seachConf['did']))
		{
			$where .= sprintf(' AND did=%d', $seachConf['did']);
            
            return $where;
		}
		if (!empty($seachConf['wid']))
		{
		    if (is_array($seachConf['wid']))
            {
                $where .= sprintf(' AND wid in(%s) ', join(',', $seachConf['wid']));
            } else {
                $where .= sprintf(' AND wid=%d ', $seachConf['wid']);
            }
		}
		if (!empty($seachConf['line_id']))
		{
			$where .= sprintf(' AND line_id=%d', $seachConf['line_id']);
		}
		if (!empty($seachConf['car_model']))
		{
			$where .= sprintf(' AND car_model=%d', $seachConf['car_model']);
		}
		if (!empty($seachConf['step']))
		{
			$where .= sprintf(' AND step=%d', $seachConf['step']);
		}
		if (!empty($seachConf['name']))
        {
            $where .= sprintf(' AND name like "%%%s%%"', $seachConf['name']);
        }

		return $where;
	}

}