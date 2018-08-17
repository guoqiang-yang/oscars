<?php

/**
 * 均摊成本. 
 * 
 * @rule 单价：元
 * 
 */

include_once('../global.php');

class App extends App_Cli
{
    private $privilegeSequence = array();
    private $baseProducts = array();
    private $privilegeConf = array();
    
    protected function getPara()
    {
        $this->privilegeSequence = array('vip50', 'manjian');
        
        $this->_initBaseDatas();
    }
    
    protected function main()
    {
        $usedPrivilege = array();
        
        foreach($this->privilegeSequence as $privilegeName)
        {
            // 可以享受优惠的商品列表
            $pproducts = $this->_getProductsWithPrivilege($privilegeName);
            
            if (!array_key_exists($privilegeName, $this->privilegeConf))
            {
                echo "GUN-DAN! Where is Privilege Config!\n"; exit;
            }
            if (empty($pproducts)) 
            {
                echo "GUN-DAN! Where is Products!\n"; exit;
            }
            
            // 平摊优惠
            $privilegePrice = $this->privilegeConf[$privilegeName];
            $allPrivilegeProductsPrice = array_sum($pproducts);
            $tcount = count($pproducts);
            $count = 0;
            $hadAve = 0;
            foreach ($pproducts as $sid => $tpprice)
            {
                if ($tcount-$count == 1) //last one
                {
                    $usedPrivilege[$sid][$privilegeName] = $privilegePrice - $hadAve;
                }
                else
                {
                    $p = round($tpprice*$privilegePrice/$allPrivilegeProductsPrice, 2);
                    $hadAve += $p;
                    $count ++ ;
                    
                    $usedPrivilege[$sid][$privilegeName] = $p;
                }
            }
        }
        
        echo "sid\tnum\tprice\ttotal_price\tprivilege\tprivilege_name\n";
        
        foreach($this->baseProducts as $sid => $data)
        {
            $privilege4Sid = array();
            $totalPrivilege4Sid = 0;
            
            foreach($usedPrivilege as $_sid => $_privilegeList)
            {
                if ($_sid != $sid) continue;
                
                foreach($_privilegeList as $_pName => $_ppprice)
                {
                    $privilege4Sid[] = $_pName;
                    $totalPrivilege4Sid += $_ppprice;
                }
            }
            
            $tprice = $data['num']*$data['price'];
            
            if ($tprice < $totalPrivilege4Sid)
            {
                echo "Privilege > Product_Price! $sid\n"; exit;
            }
            
            echo "$sid\t{$data['num']}\t{$data['price']}\t$tprice\t$totalPrivilege4Sid\t".
                  (!empty($privilege4Sid)? implode(',', $privilege4Sid): '无'). "\n";
        }
        
        echo "\nDone\n";
    }
    
    private function _getProductsWithPrivilege($privilageName)
    {
        $pproducts = array();

        foreach($this->baseProducts as $sid => $item)
        {
            if (! in_array($privilageName, $item['use_p'])) continue;

            $pproducts[$sid] = $item['num'] * $item['price'];
        }

        return $pproducts;
    }


    private function _initBaseDatas()
    {
        //'sid' => array('num'=>0, 'price'=>0, 'use_p'=>array(privilege1,privilege2) ),
        $this->baseProducts = array(
            10000 => array('num'=>100, 'price'=>10, 'use_p'=>array('vip50', 'manjian')),
            10001 => array('num'=>10, 'price'=>10, 'use_p'=>array('vip50')),
        );
        
        //'privilege1' => 50,
        $this->privilegeConf = array(
            'manjian' => 91,
            'vip50' => 50,
        );
    }
}

$app = new App();
$app->run();


