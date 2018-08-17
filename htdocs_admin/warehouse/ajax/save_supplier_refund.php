<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $wid;
    private $supplierId;
    private $srid;
    private $products;
    private $note;

    protected function getPara()
    {
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
        $this->supplierId = Tool_Input::clean('r', 'supplier_id', TYPE_UINT);
        $this->note = Tool_Input::clean('r', 'note', TYPE_STR);
        $this->products = json_decode(Tool_Input::clean('r', 'products', TYPE_STR), true);
    }
    
    protected function checkPara()
    {
        if (empty($this->wid)||empty($this->supplierId)||empty($this->products))
        {
            throw new Exception('参数错误！');
        }
        
        // 第三方仓库/经销商仓库 不允许退货
        if (Conf_Warehouse::isAgentWid($this->wid))
        {
            throw new Exception('经销商入库单，请通过调拨退货！');
        }
    }

    protected function checkAuth()
    {
        parent::checkAuth('/warehouse/ajax/refund_stockin');
    }

    protected function main()
    {
        $total = 0;
        $sids = array();
        foreach ($this->products as $_p)
        {
            $sids[] = $_p['sid'];
            $total += $_p['num'] * $_p['price'] * 100;
        }

        $diff = array_diff_assoc($sids, array_unique($sids));

        if (!empty($diff))
        {
            throw new Exception('请将多货位商品(sid:'.implode('、', $diff).')移到一个货位上，然后再退货！');
        }

        $datas = array(
            'supplier_id' => $this->supplierId,
            'wid' => $this->wid,
            'price' => $total,
            'step' => Conf_Stockin_Refund::UN_REFUND,
            'note' => $this->note,
            'suid' => $this->_uid,
        );

        if (Conf_Base::switchForManagingMode())
        {
            $sids = Tool_Array::getFields($this->products, 'sid');
            $sp = new Shop_Product();
            $productInfo = Tool_Array::list2Map($sp->getBySku($sids, Conf_Warehouse::$WAREHOUSE_CITY_MAPPING[$this->wid], 3), 'sid');
            $managingModeFlag = 0;

            foreach ($this->products as $_p)
            {
                if (empty($productInfo[$_p['sid']]['managing_mode']))
                {
                    throw new Exception('商品(pid:' . $_p['pid'] . ')经营模式属性不存在！');
                }
                if (empty($managingModeFlag))
                {
                    $managingModeFlag = $productInfo[$_p['sid']]['managing_mode'];
                }
                if ($managingModeFlag != $productInfo[$_p['sid']]['managing_mode'])
                {
                    throw new Exception('选择的商品经营模式不一致！');
                }
            }

            $supplier = Warehouse_Api::getSupplier($this->supplierId);
            if ($supplier['managing_mode'] != $managingModeFlag)
            {
                throw new Exception('商品与供应商经营模式不一致!');
            }
            $datas['managing_mode'] = $supplier['managing_mode'];
        }

        $wsir = new Warehouse_Stock_In_Refund();
        $this->srid = $wsir->create($datas);

        // 插入退货商品
        if (!empty($this->srid))
        {
            $wsip = new Warehouse_Stock_In_Product();
            foreach($this->products as $_product)
            {
                $pdata = array(
                    'id' => 0,
                    'sid' => $_product['sid'],
                    'srid' => $this->srid,
                    'num' => $_product['num'],
                    'price' => $_product['price'] * 100,
                    'location' => $_product['loc'],
                );
                $wsip->insertRefund($pdata);
            }
        }
    }

    protected function outputBody()
    {
		$response = new Response_Ajax();
		$response->setContent(array('srid'=>$this->srid));
		$response->send();

		exit;
    }
}

$app = new App();
$app->run();