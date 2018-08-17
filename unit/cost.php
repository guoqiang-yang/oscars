<?php

/**
 * 成本测试.
 * 
 */

include_once('../global.php');

class App extends App_Cli
{
    private $wid;
    private $skuList;
    
    private $hProduct;
    private $hStock;
    private $hFifo;
    
    private $sid2pid;
    
    private $calType;
    
    protected function getPara()
    {
        global $argv;
        $this->calType = $argv[1];
        
        $this->wid = Conf_Warehouse::WID_8;
        $this->skuList = $this->_baseDatas();
        
        $this->hProduct = new Shop_Product();
        $this->hStock = new Warehouse_Stock();
        $this->hFifo = new Shop_Fifo_Cost();
        
        $this->_check();
        
    }
    
    protected function main()
    {
        switch ($this->calType)
        {
            case 'ave': //库均成本
                $this->_calAveProducts(); break;
            
            case 'build':  //创建测试数据
                $this->_createData(); break;
            
            case 'up':
                $this->_execCalCosts(); break;
            
            case 'show':
            default:
                $this->_showCalCosts();
        }
        
        
        echo "Done!!\n";
    }
    
    private function _calAveProducts()
    {
        foreach ($this->_testCases() as $caseName => $caseVals)
        {
            $sid = Tool_Array::getFields($caseVals, 'sid');
            
            $ret = Shop_Cost_Api::getAveCost($this->wid, $sid);
            
            print_r($ret);
            
            break;
        }
    }
    
    private function _execCalCosts()
    {
        foreach ($this->_testCases() as $caseName => $caseVals)
        {
            $ret = Shop_Cost_Api::getCostsWithSkuAndNums($this->wid, $caseVals);
            
            foreach ($ret as $sid => $fifoDatas)
            {
                $billDatas = array('out_id'=>0, 'out_type'=>Conf_Warehouse::STOCK_HISTORY_CHK_LOSS);
            
                Shop_Cost_Api::dequeue4FifoCost($sid, $this->wid, $billDatas, $fifoDatas['_cost_fifo']);
            }
            
            echo ">>>$caseName Deal Done!\n";
        }
    }
    
    private function _showCalCosts()
    {
        echo "### out_field:\n";
        echo "\tsid\tave_cost\n\t\tnum\tcost\n";
        
        foreach($this->_testCases() as $caseName => $caseVals)
        {
            $ret = Shop_Cost_Api::getCostsWithSkuAndNums($this->wid, $caseVals);
            
            echo ">>> $caseName:\n";
            
            foreach($ret as $_sid => $val)
            {
                echo "\t$_sid\t". ($val['cost']/100). "\n";
                
                foreach($val['_cost_fifo'] as $item)
                {
                    echo "\t\t{$item['num']}\t". ($item['cost']/100)."\n";
                }
            }
            
        }
    }
    
    private function _createData()
    {
        foreach ($this->skuList as $sid => $datas)
        {
            foreach($datas as $flag => $data)
            {
                if ($flag == 't_fifo')
                {
                    $this->_setCostTFifo($sid, $data);
                }
                else if ($flag == 't_stock')
                {
                    $this->_setCostTStock($sid, $data);
                }
                else if ($flag == 't_product')
                {
                    $this->_setCostTProduct($sid, $data);
                }
            }
        }
        
        echo "创建商品成功!\n";
    }
    
    private function _setCostTFifo($sid, $datas)
    {
        foreach($datas as $v)
        {
            $inData = array(
                'num' => $v['num'],
                'cost' => $v['cost'],
                'in_id' => 0,
                'in_type' => Conf_Warehouse::STOCK_HISTORY_CHK_GAIN,
            );
            $this->hFifo->insert($sid, $this->wid, $inData);
        }
    }
    
    private function _setCostTStock($sid, $cost)
    {
        $this->hStock->save($this->wid, $sid, array('cost'=>$cost));
    }
    
    private function _setCostTProduct($sid, $cost)
    {
        $this->hProduct->update($this->sid2pid[$sid], array('cost'=>$cost));
    }


    private function _check()
    {
        $log = '';
        
        //商品是否存在
        $cityId = Conf_Warehouse::getCityByWarehouse($this->wid);
        
        $sids = array_keys($this->skuList);
        $products = $this->hProduct->getBySku($sids, $cityId, Conf_Product::PRODUCT_STATUS_ONLINE);
        $products = Tool_Array::list2Map($products, 'sid');
        
        foreach($sids as $sid)
        {
            if (!array_key_exists($sid, $products))
            {
                $log .= "sku不存在，或下线：城市: $cityId\tsku_id:$sid\n";
            }
            
            $this->sid2pid[$sid] = $products[$sid]['pid'];
        }
        
        
        if (!empty($log))
        {
            echo $log; exit;
        }
        else
        {
            echo "Check OK!\n";
        }
    }
    
    private function _testCases()
    {
        return array(
            'case_1' => array(array('sid'=>10835, 'num'=>80), array('sid'=>10836, 'num'=>100), array('sid'=>13912, 'num'=>100),),
            'case_2' => array(array('sid'=>10835, 'num'=>105), array('sid'=>10836, 'num'=>100), array('sid'=>13912, 'num'=>100),),
            'case_3' => array(array('sid'=>10835, 'num'=>200), array('sid'=>10836, 'num'=>100), array('sid'=>13912, 'num'=>100),),
            'case_4' => array(array('sid'=>10835, 'num'=>4000), array('sid'=>10836, 'num'=>100), array('sid'=>13912, 'num'=>100),),
        );
    }
    
    private function _baseDatas()
    {
        return array(
            10835 => array (
                't_fifo' => array(
                    array('num'=>100, 'cost'=>1000),
                    array('num'=>10, 'cost'=>1500),
                    array('num'=>2000, 'cost'=>800),
                ),
                't_stock' => 1122,
                't_product' => 1111,
            ), 
            10836 => array (
                't_fifo' => array(),
                't_stock' => 3900,
                't_product' => 4000,
            ),
            13912 => array (
                't_fifo' => array(),
                't_stock' => 0,
                't_product' => 900,
            ),
        );
    }
    
}

$app = new App();
$app->run();