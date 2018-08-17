<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $id;
    private $cate1;
    private $start;
    private $keyword;
    private $invoice;
    private $invoiceProducts;
    private $conf;
    private $num = 100;
    private $total;
    private $products;

    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->keyword = Tool_Input::clean('r', 'keyword', TYPE_STR);
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);

        if (empty($this->keyword))
        {
            $href = Tool_Input::clean('r', 'href', TYPE_STR);
            $href = trim($href, "?");
            parse_str($href, $paras);
            $this->cate1 = $paras['cate1'] ? $paras['cate1'] : 0;
        }
    }

    protected function checkPara()
    {
        if (empty($this->keyword))
        {
            if (empty($this->cate1))
            {
                $this->cate1 = Tool_Input::clean('c', '_last_cate1', TYPE_UINT);
                if (empty($this->cate1))
                {
                    $this->cate1 = 1;
                }
            }
        }
    }

    protected function checkAuth()
    {
        $id = isset($_REQUEST['id'])? $_REQUEST['id']: '';
        if($id>0){
            $info = Invoice_Api::getOutputInvoiceInfo($id);
            if($info['step'] > Conf_Invoice::INVOICE_OUTPUT_STEP_SALES_AUDIT)
            {
                parent::checkAuth('/finance/edit_input_invoice');
            }else{
                parent::checkAuth('/crm2/edit_invoice');
            }
        }else{
            parent::checkAuth('/crm2/edit_invoice');
        }
    }

    protected function main()
    {
        //发票信息
        if ($this->id > 0)
        {
            $this->invoice = Invoice_Api::getOutputInvoiceInfo($this->id);
            $this->invoiceProducts = Tool_Array::list2Map($this->invoice['products'], 'pid');
        }
        else
        {
            $this->invoice = array();
        }

        //产品信息
        if ($this->keyword)
        {
            $this->products = Invoice_Api::getProductList(array('city_id' => $this->invoice['city_id'],'title' => $this->keyword, 'status' => Conf_Base::STATUS_NORMAL), $this->total, $this->start, $this->num);
        }
        else
        {
            $this->conf = array(
                'cate1' => $this->cate1,
                'status' => Conf_Base::STATUS_NORMAL
            );
            $this->conf['city_id'] = $this->invoice['city_id'];
            if ($this->conf['city_id'] == 102)
            {
                $this->conf['city_id'] = 101;
            }

            $this->products = Invoice_Api::getProductList($this->conf,$this->total, $this->start, $this->num, 0);

            setcookie("_last_cate1", $this->cate1, time() + 86400 * 30, "/", Conf_Base::getAdminHost());
        }
    }

    protected function outputPage()
    {
        $this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
        $this->smarty->assign('search_conf', $this->conf);
        $this->smarty->assign('search_products', $this->products);
        $this->smarty->assign('keyword', $this->keyword);
        $this->smarty->assign('invoice', $this->invoice);
        $this->smarty->assign('invoice_products', $this->invoiceProducts);

        $html = $this->smarty->fetch('finance/dlg_product_output_list.html');
        $result = array('html' => $html);
        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }
}

$app = new App('pri');
$app->run();