<?php
/**
 * Created by PhpStorm.
 * User: libaolong
 * Date: 2018/5/28
 * Time: 下午2:51
 */
include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
    private $id;
    private $html;

    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
    }

    protected function checkAuth()
    {
        parent::checkAuth('/finance/stockin_statement_detail');
    }

    protected function checkPara()
    {
        if (empty($this->id))
        {
            throw new Exception('参数错误！');
        }
    }

    protected function main()
    {
        $fss = new Finance_StockIn_Statements();
        $wsir = new Warehouse_Stock_In_Refund();
        $wsip = new Warehouse_Stock_In_Product();
        $skuDao = new Data_Dao('t_sku');

        //入库单相关
        $statementOrd = $fss->get($this->id);
        $res = Warehouse_Api::getSupplierProductList($statementOrd['supplier_id'], array('statement_id' => $this->id));
        $stockInInfo = $res['data'];
        $stockInPrice = $refundPrice = 0;

        $stockIds = Tool_Array::getFields($stockInInfo, 'id');
        $productList = $wsip->getProductsByIds($stockIds);
        $stockInProductList = array();
        foreach ($productList as $key => &$stockIn)
        {
            if (!empty($stockIn['srid']))
            {
                unset($stockInProductList[$key]);
            } else {
                $sku = $skuDao->get($stockIn['sid']);
                $stockInProductList[$stockIn['sid']][$stockIn['price']]['title'] = $sku['title'];
                $stockInProductList[$stockIn['sid']][$stockIn['price']]['unit'] = $sku['unit'];
                $stockInProductList[$stockIn['sid']][$stockIn['price']]['num'] += $stockIn['num'];
                $stockInProductList[$stockIn['sid']][$stockIn['price']]['price'] += $stockIn['price']*$stockIn['num']/100;
                $stockInPrice += ($stockIn['price'] * $stockIn['num'])/100;
            }
        }

        //退货单相关
        $refundOrdList = $wsir->getByStatementId($this->id);
        $refundProductList = array();
        if (!empty($refundOrdList))
        {
            foreach ($refundOrdList as $refund)
            {
                $refundPrice += $refund['price']/100;
                $res = Warehouse_Api::getRefundProductsBySrid($refund['srid']);
                foreach ($res as $product)
                {
                    $refundProductList[$product['sid']][$product['price']]['title'] = $product['sku']['title'];
                    $refundProductList[$product['sid']][$product['price']]['unit'] = $product['sku']['unit'];
                    $refundProductList[$product['sid']][$product['price']]['num'] += $product['num'];
                    $refundProductList[$product['sid']][$product['price']]['price'] += $product['price'] * $product['num']/100;
                }
            }
        }

        $this->smarty->assign('id', $this->id);
        $this->smarty->assign('stockin_list', $stockInProductList);
        $this->smarty->assign('refund_list', $refundProductList);
        $this->smarty->assign('refund_price', $refundPrice);
        $this->smarty->assign('stockin_price', $stockInPrice);

        $this->html = $this->smarty->fetch('finance/aj_statement_product_detail.html');
    }

    protected function outputBody()
    {
        $response = new Response_Ajax();
        $response->setContent(array('msg' => 'suc', 'html' => $this->html));
        $response->send();

        exit;
    }
}

$app = new App();
$app->run();