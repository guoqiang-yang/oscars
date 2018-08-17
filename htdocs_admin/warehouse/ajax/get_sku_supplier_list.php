<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $sid;
    private $html;

    protected function getPara()
    {
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
    }

    protected function checkPara()
    {
        if (empty($this->sid))
        {
            throw new Exception('参数错误！');
        }
    }

    protected function checkAuth()
    {
        parent::checkAuth('/warehouse/ajax/create_inorder_4_supplier');
    }

    protected function main()
    {
        $wssl = new Warehouse_Supplier_Sku_List();
        $supplierList = $wssl->getSupplierListBySku($this->sid);
        $html = <<<TABLEHEADER
            <table class="table">
                <thead>
                <tr>
                    <th>供应商id</th>
                    <th>供应商名称</th>
                    <th>采购价(单位：元)</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>
TABLEHEADER;

        if (!empty($supplierList))
        {
            $supplierIds = Tool_Array::getFields($supplierList, 'supplier_id');
            $ws = new Warehouse_Supplier();
            $supplierMapping = $ws->getBulk($supplierIds);
            foreach ($supplierList as $supplier)
            {
                if ($supplier['status'] != 0)
                {
                    continue;
                }
                $price = $supplier['purchase_price']/10/10;
                $name = mb_substr($supplierMapping[$supplier['supplier_id']]['name'], 0, 15);
                $html .= <<<EOF
            <tr>
                <td>{$supplier['supplier_id']}</td>
                <td>{$name}</td>
                <td>{$price}</td>
                <td>
                    <a href="/warehouse/supplier_sku_list.php?supplier_id={$supplier['supplier_id']}&sku_id={$this->sid}">选择</a>
                </td>
            </tr>
EOF;
            }
            $html .= <<<TABLEFOOTER
                    </tbody>
                </table>
TABLEFOOTER;

        }
        else
        {
            $html = '<h1 style="color: red">该商品暂无供货商！</h1>';
        }


        $this->html = $html;

    }

    protected function outputBody()
    {
        $result = array('sid' => $this->sid, 'html' => $this->html);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }
}

$app = new App();
$app->run();