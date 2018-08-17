<?php

/**
 * 临时采购 - 已采购.
 */

class Warehouse_Temporary_Had_Purchase extends Base_Func
{
    
    const TABLE = 't_temporary_had_purchased';
 
    public function save($datas)
    {
        assert(!empty($datas));
        assert(!empty($datas['sid']));
        
        if(!isset($datas['buy_date']) ||empty($datas['buy_date']))
        {
            $datas['buy_date'] = date('Y-m-d');
        }
        $datas['ctime'] = date('Y-m-d H:i:s');
        
        $changeData = array(
            'temp_num' => $datas['num'],
        );
        
        $this->one->insert(self::TABLE, $datas, array(), $changeData);
        
    }
    
    public function get($buyDate, $wid=0, $start=0, $num=0, $order='')
    {
        $response = array('total'=>0, 'data'=>array());
        
        $where = 'date(buy_date)=date("'. $buyDate. '")';
        if (!empty($wid))
        {
            $where .= ' and wid='.$wid;
        }
        
        $where .= ' and status=0';
        
        $cRet = $this->one->select(self::TABLE, array('count(1)'), $where);
        
        $response['total'] = $total = intval($cRet['data'][0]['count(1)']);
        if (empty($response['total']))
        {
            return $response;
        }
        
        $ret = $this->one->select(self::TABLE, array('*'), $where, $order, $start, $num);
        $response['data'] = $ret['data'];
        
        return $response;
    }
    
    public function update($buyData, $wid, $sid, $upData=array(), $chgData=array())
    {
        assert(!empty($upData)&&!empty($chgData));
        
        $where = 'date(buy_date)=date("'. $buyData. '")'
                .' and wid='. $wid. ' and sid='. $sid;
        
        $ret = $this->one->update(self::TABLE, $upData, $chgData, $where);
        
        return $ret['affectedrows'];
    }
}