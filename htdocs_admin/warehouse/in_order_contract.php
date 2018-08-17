<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
	private $oid;

	private $orderInfo;
    private $productList;
    private $supplier;
    private $total = array();

	protected $headTmpl = '';
	protected $tailTmpl = '';

	protected function getPara()
	{
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
	}

	protected function main()
	{ 
        
        $orderInfos = Warehouse_Api::getOrderInfo($this->oid);
        $this->orderInfo = $orderInfos['info'];
        $this->productList = $orderInfos['products'];
        $this->supplier = $orderInfos['supplier'];
        foreach ($this->productList as $products)
        {
            foreach($products as $value)
            {
                $this->total['num'] += $value['num'];
                $this->total['price'] += $value['num'] * $value['price'] / 100;
            }
        }

		$this->addFootJs(array('js/apps/in_order_print.js'));
	}

    protected function outputHead()
    {
        
    }
    protected function outputTail()
    {
        
    }
    protected function outputBody()
	{
        $fileName = '好材采购合同_HC'.$this->oid;
        header("Content-type: text/html; charset=utf8"); //页面编码
        header("Content-Type:application/msword");   //表示这个页面将要导出为word
        header("Content-Disposition:attachment;filename=".mb_convert_encoding($fileName,"gbk","utf8").".doc");   //该页面导出为word的文档名
        header("Pragma:no-cache");
        header("Expires:0");
        
        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office"  xmlns:w="urn:schemas-microsoft-com:office:word"  xmlns="http://www.w3.org/TR/REC-html40"><head><meta http-equiv=Content-Type content="text/html; charset=utf-8"><xml><w:WordDocument><w:View>Print</w:View></xml></head><body>';

		$this->smarty->assign('order_info', $this->orderInfo);
		$this->smarty->assign('suppllier', $this->supplier);
        $this->smarty->assign('products', $this->productList);
        
		$this->smarty->assign('payment_types', Conf_Stock::$PAYMENT_TYPES);
        $this->smarty->assign('receiver', Conf_In_Order::$Products_Receivers[$this->orderInfo['wid']]);

        $this->smarty->assign('total', $this->total);

		$this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
		$this->smarty->assign('cate2_list_all', Conf_Sku::$CATE2);

		echo $this->smarty->fetch('warehouse/in_order_contract.html');
 
        echo '</body></html>';
        
    
	}

}

$app = new App();
$app->run();