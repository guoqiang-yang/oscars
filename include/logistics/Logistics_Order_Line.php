<?php

/**
 * 排线类.
 */

class Logistics_Order_Line extends Base_Func
{
    
    private $orderLineDao = null;

    public function __construct()
    {
        parent::__construct();
        
        $this->orderLineDao = new Data_Dao('t_order_line');
    }

    public function add($oids, $wid, $carModels, $moreInfo)
    {
        assert(!empty($oids));
        assert(!empty($wid));
        assert(!empty($carModels));
        
        $_oids = is_array($oids)? implode(Conf_Coopworker::Orderline_CarModel_Sp2, $oids): $oids;
        $_models = is_array($carModels)? implode(Conf_Coopworker::Orderline_CarModel_Sp2, $carModels): $carModels;
        $addInfo = $moreInfo;
        $addInfo['oids'] = $_oids;
        $addInfo['wid'] = $wid;
        $addInfo['car_models'] = $_models;
        $addInfo['locked'] = 1;
        $addInfo['ctime'] = date('Y-m-d H:i:s');
        
        $lineId = $this->orderLineDao->add($addInfo);
        
        return $lineId;
    }
    
    public function update($lineId, $upData)
    {
        assert(!empty($lineId));
        assert(!empty($upData));
        
        $affectRow = $this->orderLineDao->update($lineId, $upData);
        
        return $affectRow;
    }
    
    public function getByLineId($lineId)
    {
        if (empty($lineId)) return array();
        $where = array(
            'status' => Conf_Base::STATUS_NORMAL,
            'id' => $lineId,
        );
        
        $ret = $this->orderLineDao->getListWhere($where);
        
        return current($ret);
    }
    
    public function getByOid($oid)
    {
        assert(!empty($oid));
        
        $where = 'status='.Conf_Base::STATUS_NORMAL.' and oids like "%'.$oid.'%"';
        
        $ret = $this->orderLineDao->getListWhere($where);
        
        return $ret;
    }
    
    /**
     * 解析线中的car_model字段
     * 
     * @param type $carModels
     */
    public function parseCarModelInLine($carModels)
    {
        $models = explode(Conf_Coopworker::Orderline_CarModel_Sp2, $carModels);
        
        $parseModels = array();
        foreach($models as $modelInfo)
        {
            $_models = explode(Conf_Coopworker::Orderline_CarModel_Sp1, $modelInfo);
            $_m = substr($_models[0], 1);
            $allCarModels = Conf_Driver::$CAR_MODEL;
            $parseModels[] = array(
                'model' => $_m,
                'desc' => $allCarModels[$_m],
                'is_alloc' => $_models[1],
                'price' => $_models[2],
            );
        }
        
        return $parseModels;
    }
    
    /**
     * 生成db中使用的格式.
     * 
     * @param array $carModels
     */
    public function genCarModelForLine($carModels)
    {
        $models = array();
        foreach($carModels as $_model)
        {
            $_m = array(
                Conf_Coopworker::Orderline_CarModel_Flag.$_model['model'],
                $_model['is_alloc'],
                $_model['price']
            );
            $models[] = implode(Conf_Coopworker::Orderline_CarModel_Sp1, $_m);
        }
        
        return implode(Conf_Coopworker::Orderline_CarModel_Sp2, $models);
    }
    
    /**
     * 获取未分配的线.
     * 
     * @param type $wid
     * @param type $carModel
     * @param bool $getAll
     * @param int $exceptLineId 不包含的线路id
     * @return type
     */
    public function getUnAllocLine($wid, $carModel, $getAll=false, $exceptLineId=0)
    {
        assert(!empty($wid));
        assert(!empty($carModel));
        
        $model = Conf_Coopworker::Orderline_CarModel_Flag.$carModel
                .Conf_Coopworker::Orderline_CarModel_Sp1.'0';
        $where = sprintf('status=%d and wid=%d and car_models like "%%%s%%"',
                Conf_Base::STATUS_NORMAL, $wid, $model);
        if (!empty($exceptLineId))
        {
            $where .= ' and id != '.$exceptLineId;
        }
        
        $order = 'order by delivery_date asc, priority desc';
        
        if (!$getAll)
        {
            $ret = $this->orderLineDao->order($order)->limit(0,1)
                    ->getListWhere($where);
            
            return !empty($ret)? current($ret): array();
        }
        else
        {
            $ret = $this->orderLineDao->order($order)
                    ->getListWhere($where);
            
            return $ret;
        }
    }
    
    public function search($search, $start=0, $num=0, $order='')
    {
        $where = 'status = '. Conf_Base::STATUS_NORMAL;
        
        if ($this->is($search['id']))
        {
            $where .= ' and id='. $search['id'];
        }
        else
        {
            if ($this->is($search['oid']))
            {
                $where .= ' and oids like "%'.$search['oid'].'%"';
            }
            if ($this->is($search['btime']))
            {
                $where .= ' and delivery_date>="'.$search['btime'].' 00:00:00"';
            }
            if ($this->is($search['etime']))
            {
                $where .= ' and delivery_date<="'.$search['etime'].' 23:59:59"';
            }
            if ($this->is($search['address']))
            {
                $where .= ' and address like "%'. $search['address'].'%"';
            }
            if ($this->is($search['wid']))
            {
                if (is_array($search['wid']))
                {
                    $where .= sprintf(' AND wid in(%s) ', join(',', $search['wid']));
                } else {
                    $where .= sprintf(' AND wid=%d ', $search['wid']);
                }
            }

            $searchCarModel = '';
            if ($this->is($search['car_model']))
            {
                $searchCarModel .= Conf_Coopworker::Orderline_CarModel_Flag.$search['car_model'];
            }
            if (isset($search['car_model_type']) && $search['car_model_type']!=Conf_Base::STATUS_ALL)
            {
                $searchCarModel .= Conf_Coopworker::Orderline_CarModel_Sp1.$search['car_model_type']
                                   .Conf_Coopworker::Orderline_CarModel_Sp1;
            }
            if (!empty($searchCarModel))
            {
                $where .= ' and car_models like "%'. $searchCarModel. '%"';
            }
        }
        $total = $this->orderLineDao->getTotal($where);
        
        $ret = array();
        if ($total)
        {
            $order = !empty($order)? $order: 'order by id desc';
            $ret = $this->orderLineDao->order($order)->limit($start, $num)
                            ->getListWhere($where);
        }
        
        return array('total'=>$total, 'data'=>$ret);
    }
}