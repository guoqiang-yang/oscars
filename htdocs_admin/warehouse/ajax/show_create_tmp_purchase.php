<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $wid;
    private $plist;
    
    private $html;
    
    protected function getPara()
    {
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
        $this->plist = json_decode(Tool_Input::clean('r', 'plist', TYPE_STR), true);
    }
    
    protected function checkPara()
    {
        if (empty($this->wid))
        {
            throw new Exception('请选择仓库');
        }
        if (!empty($this->_user['wid']) && $this->_user['wid']!=$this->wid)
        {
            throw new Exception('只能创建自己仓库的临采单！');
        }
        if (empty($this->plist))
        {
            throw new Exception('商品列表不能为空！');
        }
    }
    
    protected function checkAuth()
    {
        parent::checkAuth('/warehouse/ajax/create_tmp_2_inorder');
    }
    
    protected function main()
    {
        $checkVal = Warehouse_Temp_Purchase_Api::isLegalTmpPurchase($this->plist, $this->wid);
        
        if ($checkVal['st'] != 0)
        {
            throw new Exception($checkVal['msg']);
        }
        
        $products = array();
        $sids = array();
        foreach($this->plist as $one)
        {
            if(!array_key_exists($one['sid'], $products))
            {
                $products[$one['sid']] = array(
                    'pid' => $one['pid'],
                    'vnum' => $one['vnum'],
                    'vnum_diff' => 0,
                    'orders' => array(array(
                        'oid' => $one['oid'],
                        'vnum' => $one['vnum'],
                    )),
                );
            }
            else
            {
                $products[$one['sid']]['vnum'] += $one['vnum'];
                $products[$one['sid']]['orders'][] = array(
                    'oid' => $one['oid'],
                    'vnum' => $one['vnum'],
                );
            }
            
            $sids[] = $one['sid'];
        }
        
        $skuInfos = Shop_Api::getSkuInfos($sids);
        Warehouse_Api::appendStock($this->wid, $skuInfos);
        
        foreach($products as $sid=> &$pone)
        {
            $pone['title'] = $skuInfos[$sid]['title'];
            $pone['price'] = !empty($skuInfos[$sid]['_stock'][$this->wid]['purchase_price'])?
                    $skuInfos[$sid]['_stock'][$this->wid]['purchase_price']: $skuInfos[$sid]['_stock'][$this->wid]['cost'];
            $pone['unit'] = $skuInfos[$sid]['unit'];
        }
        
        $this->smarty->assign('products', $products);
        $this->html = $this->smarty->fetch('warehouse/aj_show_tmp_purchase.html');
    }
    
    protected function outputBody()
    {
        $result = array('html' => $this->html);
        
		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
    }
}

$app = new App();
$app->run();