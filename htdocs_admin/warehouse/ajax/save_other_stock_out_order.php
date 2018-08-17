<?php

include_once('../../../global.php');


class App extends App_Admin_Ajax
{
    private $oid;
    private $order;
    private $products;

    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->order = array(
            'wid' => Tool_Input::clean('r', 'wid', TYPE_UINT),
            'order_type' => Tool_Input::clean('r', 'order_type', TYPE_UINT),
            'type' => Tool_Input::clean('r', 'type', TYPE_UINT),
            'reason' => Tool_Input::clean('r', 'reason', TYPE_UINT),
            'note' => Tool_Input::clean('r', 'note', TYPE_STR),
        );

        if (($this->order['order_type'] == Conf_Stock::OTHER_STOCK_ORDER_TYPE_OUT && ($this->order['type'] == Conf_Stock::OTHER_STOCK_OUT_TYPE_BROKEN
            || $this->order['type'] == Conf_Stock::OTHER_STOCK_OUT_TYPE_CHANGE || $this->order['type'] == Conf_Stock::OTHER_STOCK_OUT_TYPE_REFUND))
            || ($this->order['order_type'] == Conf_Stock::OTHER_STOCK_ORDER_TYPE_IN && $this->order['type'] == Conf_Stock::OTHER_STOCK_IN_TYPE_CHANGE))
        {
            $this->order['supplier_id'] = Tool_Input::clean('r', 'supplier_id', TYPE_UINT);
        }
        $products = Tool_Input::clean('r', 'products', TYPE_ARRAY);
        foreach($products as $one)
        {
            list($_sid, $_loc, $_num, $_note) = explode(':', $one);
            $this->products[$_sid][$_loc] = array('num' =>$_num, 'note' => $_note);
        }
    }
    
    protected function checkPara()
    {
        if (empty($this->order['wid']))
        {
            throw new Exception('请选择仓库！');
        }
        if (($this->order['order_type'] != Conf_Stock::OTHER_STOCK_ORDER_TYPE_OUT && $this->order['type'] != Conf_Stock::OTHER_STOCK_OUT_TYPE_SELF_USE) && empty($this->order['supplier_id']))
        {
            throw new Exception('请选择供应商！');
        }
        if (empty($this->order['note']))
        {
            throw new Exception('请填写备注！');
        }
    }

    protected function checkAuth()
    {
        if ($_REQUEST['order_type'] == Conf_Stock::OTHER_STOCK_ORDER_TYPE_OUT)
        {
            parent::checkAuth('/warehouse/ajax/save_other_stock_out_order');
        }
        elseif ($_REQUEST['order_type'] == Conf_Stock::OTHER_STOCK_ORDER_TYPE_IN)
        {
            parent::checkAuth('/warehouse/ajax/save_other_stock_in_order');
        }

    }

    protected function main()
    {
        if (empty($this->oid))
        {
            $this->oid = Warehouse_Api::saveOtherStockOutOrder($this->_uid, $this->order);
        }
        else
        {
            Warehouse_Api::updateOtherStockOutOrder($this->oid, $this->order);
            if (!empty($this->products))
            {
                Warehouse_Api::updateOtherStockOutOrderProducts($this->oid, $this->products);
            }
        }
    }
    
    protected function outputBody()
    {
        $result = array('oid' => $this->oid);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
    }
}

$app = new App();
$app->run();