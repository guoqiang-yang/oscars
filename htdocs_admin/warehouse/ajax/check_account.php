<?php
/**
 * 兑账.
 */

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    
    private $id;
    private $oid;
    private $flag;
    private $role;
    
    private $stockinIds = array();
    private $stockInInfo;
    private $stockInInfos;
    
    private $res = array();
    
    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->flag = Tool_Input::clean('r', 'flag', TYPE_STR);
        $this->role = Tool_Input::clean('r', 'role', TYPE_STR);
        
        $this->res = array(
            'errno' => 0,
            'errmsg' => '',
            'data' => array(),
        );
    }
    
    protected function checkPara()
    {
        if ( (empty($this->id)&&empty($this->oid)) || empty($this->flag))
        {
            throw new Exception('common:params error');
        }
    }

    protected function main()
    {   
        
        $isAdmin = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_ADMIN_NEW);
        $isBuyer = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_BUYER_NEW);
        $isFinance = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_FINANCE_NEW);
        
        if (!empty($this->id))
        {   
            $this->stockInInfo = Warehouse_Api::getStockInInfo($this->id);
            if ($this->stockInInfo['paid'] == Conf_Stock_In::HAD_PAID)
            {
                $this->res['errno'] = -1;
                $this->res['errmsg'] = '入库单已支付，不能兑账！';
                return;
            }
              
            if ($this->stockInInfo['paid']==Conf_Stock_In::UN_PAID && !($isBuyer||$isAdmin||$isFinance) )
            {
                $this->res['errno'] = -1;
                $this->res['errmsg'] = '请联系 采购兑账！';
                return;
            }
            else if ($this->stockInInfo['paid']==Conf_Stock_In::CHECKED_ACCOUNT && !($isFinance||$isAdmin) )
            {
                $this->res['errno'] = -1;
                $this->res['errmsg'] = '请联系 财务兑账！';
                return;
            }
        }
        else if (!empty($this->oid)) // 综合采购单兑账
        {
            $this->stockInInfos = Warehouse_Api::getStockinListByOid($this->oid);
            foreach($this->stockInInfos as $stockin)
            {
                if ($stockin['paid'] == Conf_Stock_In::HAD_PAID)
                {
                    $this->res['errno'] = -1;
                    $this->res['errmsg'] = '入库单已支付，不能兑账！';
                    return;
                }
                
                if($this->role=='buyer' && ($stockin['paid']!=Conf_Stock_In::UN_PAID || !($isBuyer||$isAdmin)) )
                {
                    $this->res['errno'] = -1;
                    $this->res['errmsg'] = '请联系 采购兑账！';
                    return;
                }
                else if ($this->role=='finance' && ($stockin['paid']!=Conf_Stock_In::CHECKED_ACCOUNT || !($isFinance||$isAdmin)) )
                {
                    $this->res['errno'] = -1;
                    $this->res['errmsg'] = '请联系 财务兑账！';
                    return;
                }
                
                $this->stockinIds[] = $stockin['id'];
            }
        }
        
        switch($this->flag)
        {
            case 'get':
                $this->_getContent();
                break;
            case 'check':
                $this->_checkAccount();
                break;
            default :
                break;
        }
    }
    
    protected function outputBody()
    {
        $response = new Response_Ajax();
		$response->setContent($this->res);
		$response->send();
		exit;
    }
    
    private function _getContent()
    {
        if (!empty($this->oid))
        {
            $allProducts = Warehouse_Api::getStockInProducts($this->stockinIds);
        }
        else if (!empty($this->id))
        {
            $allProducts = Warehouse_Api::getStockInProducts($this->id);
        }
        
        $refundPrices = array();
        foreach($allProducts['refunds'] as $p)
        {
            if (!array_key_exists($p['sid'], $refundPrices))
            {
                $refundPrices[$p['sid']] = array('num'=>0, 'price'=>0);
            }
            $refundPrices[$p['sid']]['num'] += $p['num'];
            $refundPrices[$p['sid']]['price'] += $p['price']*$p['num'];
        }
        
        $stockinProducts = array();
        if (!empty($this->id))
        {
            $stockinProducts = Tool_Array::list2Map($allProducts['products'],'sid');
        }
        else
        {
            foreach($allProducts['products'] as $p)
            {
                if (!array_key_exists($p['sid'], $stockinProducts))
                {
                    $stockinProducts[$p['sid']] = $p;
                }
                else
                {
                    $stockinProducts[$p['sid']]['num'] += $p['num'];
                }
            }
        }
        
        foreach($stockinProducts as $sid => &$pp)
        {
            $pp['refund_num'] = $pp['refund_price'] = 0;
            if (array_key_exists($sid, $refundPrices))
            {
                $pp['refund_num'] = $refundPrices[$sid]['num'];
                $pp['refund_price'] = $refundPrices[$sid]['price'];
            }
            $pp['remainder_num'] = $pp['num'] - $pp['refund_num'];
            $pp['remainder_price'] = $pp['num']*$pp['price'] - $pp['refund_price'];
        }
        
        $this->smarty->assign('products', $stockinProducts);
        $this->res['data']['html'] = $this->smarty->fetch('warehouse/aj_check_account_content.html');
        $this->res['data']['id'] = $this->id;
        $this->res['data']['oid'] = $this->oid;
    }
    
    // 兑账
    private function _checkAccount()
    {
        if($this->role=='buyer')
        {
            $upData = array(
                'paid' => Conf_Stock_In::CHECKED_ACCOUNT,
                'check1_suid' => $this->_uid,
            );
        }
        else
        {
            $upData = array(
                'paid' => Conf_Stock_In::FINANCE_ACCOUNT,
                'check2_suid' => $this->_uid,
            );
        }
        
        if (!empty($this->id))
        {
            Warehouse_Api::updateStockIn($this->_uid, $this->id, $upData);
            Finance_StockIn_Statements_Api::updateStatementPaidByStockInID($this->id, $this->_uid);
        } 
        else if (!empty($this->oid))
        {
            Warehouse_Api::updateStockinByOid($this->oid, $upData);
            $stockIn_List = Warehouse_Api::getStockinListByOid($this->oid);
            if(!empty($stockIn_List))
            {
                foreach ($stockIn_List as $item)
                {
                    Finance_StockIn_Statements_Api::updateStatementPaidByStockInID($item['id'], $this->_uid);
                }
            }
        }
    }
    
}

$app = new App();
$app->run();