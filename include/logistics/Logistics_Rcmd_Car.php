<?php

class Logistics_Rcmd_Car
{
    /**
     * 定义车型代码，与Conf_Driver中一致.
     */
    const XMB = 2;  //小面
    const JB = 4;   //金杯
    
    /**
     * 车型数据 - 根据装置率从小到大排序.
     * 
     *  l:长(厘米) w:宽(厘米) h:高(厘米) wgt:(克)
     */
    private static $Car_Datas = array(
        self::XMB => array('l'=>180, 'w'=>110, 'h'=>120, 'vol'=>2376000, 'wgt'=>1200000),   //小面
        self::JB => array('l'=>310, 'w'=>130, 'h'=>125, 'vol'=>5037500, 'wgt'=>3000000),   //金杯
    );
    
    
    /**
     * 通过实际购买确定商品数据.
     */
    private static $Datas_From_RealBuy = array(
        12126, 11719, 13828, 12314, 11772, 12259, 12003, 10935, 11901, 11401
    );
    
    /**
     * 不需要计算的sku. 
     * 
     *  - 冷热水管
     */
    private static $Ignore_Skus = array(
        10835, 10836, 13912, 11736, 11813, 10003, 10002, 11613, 
        11222, 12452, 10879, 11003, 10001, 10000, 11582,            //冷热水管
    );
    
    private $response;
    
    function __construct()
    {
        
    }
    
    private function _init()
    {
        $this->response = array(
            'errno' => 0,
            'errmsg' => 'OK',
            'data' => array(),
        );
    }

    /**
     * 为销售单推荐车型.
     * 
     * @param type $oid
     */
    public function getRcmdBySaleOrder($oid)
    {
        $this->_init();
     
        //$mem1 = round(memory_get_usage()/1024/1024, 2); 
        
        $oo = new Order_Order();
        $orderInfo = $oo->get($oid, false, false);
        
        $owhere = 'status=0 and rid=0 and num>0 and oid='. $oid;
        $ofield = array('oid', 'num', 'note', 'sid');
        $orderProducts = $oo->getOrderProductsByRawWhere($owhere, 0, 0, $ofield);
        
        try{
            $this->_checkSaleOrder($orderInfo, $orderProducts['data']);
            
            // 计算商品的基本数据
            $statBDatas = $this->_statBaseDatasFromProducts($orderProducts['data']);
            
            $rcmdCars = $this->_rcmdCars($statBDatas);
            
            $this->response['data'] = $this->_formatCars($rcmdCars);
            

            //$mem2 = round(memory_get_usage()/1024/1024, 2);  
            //echo "end:mem1：$mem1 MB\t mem2：$mem2 MB\tDiff：".($mem2-$mem1)."MB\n";
        
            return $this->response;
        }
        catch (Exception $e){
            $this->_showErrorMsg($e->getCode());     
            return $this->response;
        }
    }
    
    
    /**
     * 推荐车型.
     * 
     * 返回：车型+r转载率
     */
    private function _rcmdCars($statBDatas)
    {
        $cars = array();
    
        // 分配金杯
        if (!empty($statBDatas['only_jb']))
        {
            $jbRate = $this->_loadRate($statBDatas['only_jb'], 4);
            $jbTgt = $this->_getCalTargetHorizontal($jbRate);
            
            // 满载车数
            $fullyNum = floor($jbRate[$jbTgt['r']]);
            for($i=0; $i<$fullyNum; $i++)
            {
                $cars[] = array('model'=>4, 'rate'=>1);
            }
            
            // 剩余装载
            if ($fullyNum > 0)  // 需要多车；非装载指标按装载指标比例均分
            {
                $leftVol = $leftWgt = 0;
                if ($jbTgt['d'] == 'vol')
                {
                    $leftVol = $statBDatas['only_jb']['vol'] - $fullyNum*self::$Car_Datas[4]['vol'];
                    $leftWgt = $statBDatas['only_jb']['wgt']*$leftVol/$statBDatas['only_jb']['vol'];    //按比例平均
                }
                else
                {
                    $leftWgt = $statBDatas['only_jb']['wgt'] - $fullyNum*self::$Car_Datas[4]['wgt'];
                    $leftVol = $statBDatas['only_jb']['vol']*$leftWgt/$statBDatas['only_jb']['wgt'];    //按比例平均
                }
                
                $cars[] = array(
                    'model' => 4,
                    'rate' => $jbRate[$jbTgt['r']]-$fullyNum,
                    'vol' => $leftVol,
                    'wgt' => $leftWgt,
                );
            }
            else    //不足一车
            {
                $cars[] = array(
                    'model' => 4,
                    'rate' => 0,
                    'vol' => $statBDatas['only_jb']['vol'],
                    'wgt' => $statBDatas['only_jb']['wgt'],
                );
            }
            
        }
        
        // 综合/常规分配
        if (!empty($statBDatas['other_car']))
        {
            // 已分配，并且未满载的车辆，优先分配【最多只有一车为未满载车辆】
            $unFullyCar = array();
            foreach($cars as $cnum => $item)
            {
                if ($item['rate'] < 1)
                {
                    $unFullyCar = $item; break;
                }
            }
            
            // 为未满载车辆，装配
            if (!empty($unFullyCar))
            {
                $unFullyCarData = array(
                    'vol' => self::$Car_Datas[$unFullyCar['model']]['vol']-$unFullyCar['vol'],
                    'wgt' => self::$Car_Datas[$unFullyCar['model']]['wgt']-$unFullyCar['wgt'],
                );
                $oRate = $this->_loadRate($statBDatas['other_car'], $unFullyCarData);
                $oTgt = $this->_getCalTargetHorizontal($jbRate);
                
                if ($oRate[$oTgt['r']] < 1) // 可完全装载
                {
                    $unFullyCar['vol'] += $statBDatas['other_car']['vol'];
                    $unFullyCar['wgt'] += $statBDatas['other_car']['wgt'];
                    $cars[$cnum] = $unFullyCar;
                    
                    return $cars;
                }
                else    //不能完全装配，优先装配改车【按照装配指标装满】
                {
                    unset($unFullyCar['vol']);
                    unset($unFullyCar['wgt']);
                    $unFullyCar['rate'] = 1;
                    $cars[$cnum] = $unFullyCar;
                    
                    // 剩余装配商品，按比例均
                    if ($oTgt['d'] == 'vol')
                    {
                        $statBDatas['other_car']['vol'] -= $unFullyCarData['vol'];
                        $statBDatas['other_car']['wgt'] -= $statBDatas['other_car']['wgt']*$unFullyCarData['vol']/$statBDatas['other_car']['vol'];
                    }
                    else
                    {
                        $statBDatas['other_car']['wgt'] -= $unFullyCarData['wgt'];
                        $statBDatas['other_car']['vol'] -= $statBDatas['other_car']['vol']*$unFullyCarData['wgt']/$statBDatas['other_car']['wgt'];
                    }
                }
            }
            
            // 常规分配
            $_cars = $this->_rcmd4AllCarsModel($statBDatas['other_car']);
            $cars = array_merge($cars, $_cars);
        }
        
        return $cars;
    }
    
    /**
     * 全车型推荐.
     */
    private function _rcmd4AllCarsModel($pdatas)
    {
        $cars = array();
        
        while ($pdatas['wgt']>0 || $pdatas['vol']>0)
        {
            $loadRate = $this->_loadRate($pdatas);
            $tgts = $this->_getCalTargetVertical($loadRate);
            
            $model = $tgts['model'];
            
            if ($tgts['tgt']['v'] < 1) // 一车
            {
                $cars[] = array(
                    'model'=>$model, 
                    'rate'=>0, 
                    'vol'=>$pdatas['vol'], 
                    'wgt'=>$pdatas['wgt'],
                );
                $pdatas['wgt'] = 0;
                $pdatas['vol'] = 0;
            }
            else
            {
                $cars[] = array('model'=>$model, 'rate'=>1);
                
                if($tgts['d'] == 'vol')
                {
                    $pdatas['vol'] -= self::$Car_Datas[$model]['vol'];
                    $pdatas['wgt'] -= $pdatas['wgt']*self::$Car_Datas[$model]['vol']/$pdatas['vol'];
                }
                else
                {
                    $pdatas['wgt'] -= self::$Car_Datas[$model]['wgt'];
                    $pdatas['vol'] -= $pdatas['vol']*self::$Car_Datas[$model]['wgt']/$pdatas['wgt'];
                }
            }
        }
        
        
        return $cars;
    }
    
    /**
     * 计算货位对车型的装载率.
     * 
     * @param array $pdatas 货位的统计数据：体积，重量
     */
    private function _loadRate($pdatas, $carModel=NULL)
    {
        $rate = array();
        
        if (is_numeric($carModel) && array_key_exists($carModel, self::$Car_Datas))  //单车型
        {
            $rate = array(
                'vr' => $pdatas['vol']/self::$Car_Datas[$carModel]['vol'],
                'wr' => $pdatas['wgt']/self::$Car_Datas[$carModel]['wgt'],
            );
        }
        else if (is_array($carModel) && !empty($carModel['vol']) && !empty($carModel['wgt']))  //单车型，并且带了车的容量
        {
            $rate = array(
                'vr' => $pdatas['vol']/$carModel['vol'],
                'wr' => $pdatas['wgt']/$carModel['wgt'],
            );
        }
        else
        {
            foreach(self::$Car_Datas as $model => $data)
            {
                $rate[$model] = array(
                    'vr' => $pdatas['vol']/$data['vol'],
                    'wr' => $pdatas['wgt']/$data['wgt'],
                );
            }
        }
        
        return $rate;
    }
    
    /**
     * 装车指标【横向】：以一个车的体积装车率 or 重量装车率确定装车计算指标.
     * 
     * @rule 装载率越大，对车空间的消耗越大，故选择值大的作为装车参照
     * 
     * @param type $loadRate
     */
    private function _getCalTargetHorizontal($loadRate)
    {
        return $loadRate['vr']>$loadRate['wr']? 
                array('d'=>'vol', 'r'=>'vr', 'v'=>$loadRate['vr']):
                array('d'=>'wgt', 'r'=>'wr', 'v'=>$loadRate['wr']);
    }
    
    /**
     * 装车指标【纵向】：以多个车型选择装载指标.
     * 
     * @rule 
     *      1：横向对比，获取最大装载
     *      2：纵向对比，最少车辆 && 最大装载率     
     * 
     * @param type $loadRates
     */
    private function _getCalTargetVertical($loadRates)
    {
        $midRet = array();
        foreach($loadRates as $model => $rate)
        {
            $midRet[] = array(
                'm' => $model,
                'tgt' => $this->_getCalTargetHorizontal($rate),
            );
        }
        
        $tModel = $midRet[0]['m'];
        $tTarget = $midRet[0]['tgt'];
        for($i=1; ; $i++)
        {
            if (empty($midRet[$i])) break;
            
            //同大于一车，选择最小装载率
            $maxNum4Target = ceil($tTarget['v']);
            $maxNum4MidRet = ceil($midRet[$i]['tgt']['v']);
            
            if ($maxNum4Target > $maxNum4MidRet)    //最少车辆
            {
                $tModel = $midRet[$i]['m'];
                $tTarget = $midRet[$i]['tgt'];
            }
            else if ($maxNum4Target == $maxNum4MidRet && $tTarget['v']<$midRet[$i]['tgt']['v']) //车辆相等，取最大装载率
            {
                $tModel = $midRet[$i]['m'];
                $tTarget = $midRet[$i]['tgt'];
            }
            
        }
        
        return array('model'=>$tModel, 'tgt'=>$tTarget);
    }
    
    /**
     * 统计商品的基础数据，用于下面计算车型.
     * 
     * @param array $products
     * @return array
     *      wgt:         总重量
     *      vol:         总体积
     *      max_len:     最大长度
     *      more_info:   更新商品信息，调试使用
     *      
     *      // maxWidth: 最大宽度，暂不需要，任何宽度都可以装下
     *      
     */
    private function _statBaseDatasFromProducts($products)
    {
        $ret = array(
            'wgt' => 0,
            'vol' => 0,
            'max_len'  => 0,
            'more_info' => array(),
            'only_jb'   => array(),
            'other_car' => array(),
        );
        $sids = Tool_Array::getFields($products, 'sid');
        
        if (empty($sids))
        {
            throw new Exception('', 1003);
        }
        
        $ss = new Shop_Sku();
        $skuInfos = $ss->getBulk($sids);
        
        $jbTWeight = $jbTVolume = 0;    //仅金杯
        $oTWeight = $oTVolume = 0;      //其他车型
        foreach($products as $pinfo)
        {
            $sid = $pinfo['sid'];
            $skuInfo = $skuInfos[$sid];
            if (in_array($sid, self::$Ignore_Skus)) continue;
            
            $len = $skuInfo['length'];
            if (in_array($sid, self::$Datas_From_RealBuy))
            {
                $len = $pinfo['num']*100;   //单位：cm
            }
            
            //裁断问题
            if ($this->_isTruncation($pinfo['note']))
            {
                $len /= 2;
            }
            
            $pWeight = $skuInfo['weight']*$pinfo['num'];
            $pVolume = $skuInfo['length']*$skuInfo['width']*$skuInfo['height']*$pinfo['num'];
            
            //通过长度，对装载的货物按照车型分类 - 仅金杯
            if ($len > self::$Car_Datas[2]['l'])
            {
                $jbTWeight += $pWeight;
                $jbTVolume += $pVolume;
            }
            else
            {
                $oTWeight += $pWeight;
                $oTVolume += $pVolume;
            }
            
            if ($len >= $ret['max_len'])
            {
                $ret['max_len'] = $len;
                $ret['more_info']['max_len'] = array('sid'=>$sid, 'title'=>$skuInfo['title'], 'note'=>$pinfo['note']);
            }
            
            $ret['wgt'] += $skuInfo['weight']*$pinfo['num'];
            $ret['vol'] += $skuInfo['length']*$skuInfo['width']*$skuInfo['height']*$pinfo['num'];
        }
        
        if ($jbTWeight>0 || $jbTVolume>0)
        {
            $ret['only_jb'] = array('wgt'=>$jbTWeight, 'vol'=>$jbTVolume);
        }
        if ($oTWeight>0 || $oTVolume>0)
        {
            $ret['other_car'] = array('wgt'=>$oTWeight, 'vol'=>$oTVolume);
        }
        
        return $ret;
    }
    
    private function _formatCars($cars)
    {
        $carDescs = Conf_Driver::$CAR_MODEL;
        foreach($cars as &$_carInfo)
        {
            $_carInfo['name'] = $carDescs[$_carInfo['model']];
            
            if ($_carInfo['rate'] == 0)
            {
                $vr = $_carInfo['vol']/self::$Car_Datas[$_carInfo['model']]['vol'];
                $wr = $_carInfo['wgt']/self::$Car_Datas[$_carInfo['model']]['wgt'];
                
                $_carInfo['rate'] = max($vr, $wr);
                $_carInfo['vol'] = null;
                $_carInfo['wgt'] = null;
                unset($_carInfo['vol']);
                unset($_carInfo['wgt']);
            }
        }
        
        return $cars;
    }

    // 是否截断
    private function _isTruncation($note)
    {
        return (strpos($note, '截')!==false ||strpos($note, '断')!==false) && strpos($note, '不')===false? true: false;
    }
    
    private function _checkSaleOrder($orderInfo, $orderProducts)
    {
        if (empty($orderInfo) || empty($orderProducts))
        {
            throw new Exception('', 1001);
        }
        
        if ($orderInfo['delivery_type'] == Conf_Order::DELIVERY_BY_YOURSELF)
        {
            throw new Exception('', 1002);
        }
    }
    
    private function _showErrorMsg($errno)
    {
        $errMsgs = array(
            0       => '对不起，车型匹配失败！',
            1001    => '销售单异常！',
            1002    => '自提订单，无需计算',
            1003    => '商品的SKU信息异常',
        );
        
        if (array_key_exists($errno, $errMsgs))
        {
            $this->response['errno'] = $errno;
            $this->response['errmsg'] = $errMsgs[$errno];
        }
        else
        {
            $this->response['errno'] = 0;
            $this->response['errmsg'] = $errMsgs[0];
        }
    }
}