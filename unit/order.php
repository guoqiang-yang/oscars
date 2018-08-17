<?php

/**
 * 自动下单 - 检测订单流程.
 * 
 *  1 对指定的 cid，uid，wid，city_id，products 下单（自提订单）
 *  2 检测库存是否存在，否则盘盈
 *       >> Chk：库存，出入库历史
 *  3 根据步骤1）设定的数据，自动生成订单
 *       >> IO：订单状态
 *  4 客服确认，自动使用优惠，【先不支持 运费，搬运费计算】
 *       >> IO：订单状态，订单金额，优惠金额，运费，搬运费，库存占用
 *  5 自提订单，无需安排司机
 *       >> PASS
 *  6 出库
 *       >> Chk：订单状态，库存，占用，客户财务流水
 *  7 回单
 *       >> Chk：订单状态，返券，转余额
 *  8 收款 （如果有余额使用余额，然后现金收款，小于1元抹零）
 *       >> Chk：订单状态，财务流水，返券， 转余额
 */

include_once ('../global.php');

class App extends App_Cli
{
    private $cityId;
    private $wid;
    private $cid;
    private $uid;
    private $products;
    
    
    private $hOrder = null;
    private $hLocation = null;
    private $hStock = null;
    private $hStockHistory = null;
    private $hProduct = null;
    
    protected function getPara()
    {
        if (ENV == 'online')
        {
            echo "Sorry!! Online is Forbidden!!\n\n"; exit;
        }
        
        $this->cityId = Conf_City::BEIJING;
        $this->wid = Conf_Warehouse::WID_8;
        $this->cid = 6000;
        $this->uid = 6000;
        $this->products = array(
            array('pid'=>10847, 'sid'=>10847, 'num'=>20),
            array('pid'=>10014, 'sid'=>10014, 'num'=>10),
            array('pid'=>11796, 'sid'=>11796, 'num'=>100),
            array('pid'=>10381, 'sid'=>10381, 'num'=>25),
        );
        
    }
    
    protected function main()
    {
        try{
            $this->_checkPara();
        }
        catch (Exception $e){
            $this->_showErrorMsg($e->getMessage()); 
        }
        
        
        /**
         * 
         * 1 扣点发票
         * 2 入库流程。。。
         * 
         * ** 平台
         * 
         * 
         */
        
    }
    
    private function _checkPara()
    {
        // 检测客户
        $this->_checkUser();
        
        // 检测商品，库存
        $this->_checkProducts();
        
        // 检测库存
        $this->_checkStocks();
    }
    
    private function _checkUser()
    {
        $cu = new Crm_User();
        $userInfo = $cu->get($this->uid);
        
        if ($this->cid != $userInfo['cid'])
        {
            throw new Exception("客户数据异常：Cid，Uid不对应！\n");
        }
        else
        {
            echo "[Customer]\t客户数据校验正确！\n";
        }
    }
    
    private function _checkProducts()
    {
        $pids = Tool_Array::getFields($this->products, 'pid');
        $productInfos = Shop_Api::getProductInfos($pids, Conf_Activity_Flash_Sale::PALTFORM_BOTH, TRUE);
        
        foreach($this->products as $pinfo)
        {
            $_pid = $pinfo['pid'];
            if ($productInfos[$_pid]['product']['sid'] != $pinfo['sid'])
            {
                throw new Exception("商品信息异常：pid，sid不对应 - $_pid : ". $pinfo['sid']."\n");
            }
        }
        
        echo "[Product]\t商品数据校验正确！\n";
       
    }
    
    
    private function _checkStocks()
    {
        $sids = Tool_Array::getFields($this->products, 'sid');
        
        // 获取库存
        $stockAndLocs = $this->_getStocksAndLocations($sids);
        
        
    }
    
    private function _getStocksAndLocations($sids)
    {
        $stocks = Warehouse_Api::getStockDetail($this->wid, $sids);
    }
   
    private function _showErrorMsg($errmsg)
    {
        echo !empty($errmsg)? $errmsg: "扯淡，不写原因！！\n";
        echo "\n流程执行失败，请检查原因...\n";
        exit;
    }
}

$app = new App();
$app->run();