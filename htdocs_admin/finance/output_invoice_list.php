<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $step;
    private $start;
    private $searchConf;
    private $num = 20;
    private $total;
    private $invoiceList;
    private $action;
    private $type;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->step = Tool_Input::clean('r', 'step', TYPE_STR);
        $this->action = Tool_Input::clean('r', 'action', TYPE_STR);
        $this->type = Tool_Input::clean('r', 'type', TYPE_STR);
        $this->searchConf = array(
            'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
            'id' => Tool_Input::clean('r', 'id', TYPE_UINT),
            'title' => Tool_Input::clean('r', 'title', TYPE_STR),
            'city_id' => Tool_Input::clean('r', 'city_id', TYPE_UINT),
            'from_day' => Tool_Input::clean('r', 'from_day', TYPE_STR),
            'end_day' => Tool_Input::clean('r', 'end_day', TYPE_STR),
            'number' => Tool_Input::clean('r', 'number', TYPE_STR),
            'from_ctime' => Tool_Input::clean('r', 'from_ctime', TYPE_STR),
            'end_ctime' => Tool_Input::clean('r', 'end_ctime', TYPE_STR),
            'bill_ids' => Tool_Input::clean('r', 'bill_ids', TYPE_UINT),
            'invoice_type' => Tool_Input::clean('r', 'invoice_type', TYPE_UINT),
        );
    }

    protected function checkPara()
    {
        if(empty($this->step))
        {
            $this->searchConf['step'] = array(Conf_Invoice::INVOICE_OUTPUT_STEP_SALES_AUDIT, Conf_Invoice::INVOICE_OUTPUT_STEP_FINANCE_CONFIRM, Conf_Invoice::INVOICE_OUTPUT_STEP_FINISHED);
        }else{
            $this->searchConf['step'] = $this->step;
        }
    }

    protected function main()
    {
        //下载发票信息
        if($this->action == 'download')
        {
            switch ($this->type)
            {
                case 'invoice':
                    Invoice_Api::exportOutputInvoiceListByWhere($this->searchConf);
                    break;
                case 'invoice_products':
                    Invoice_Api::exportOutputInvoiceProductsListByWhere($this->searchConf);
                    break;
                default:
                    throw new Exception('参数错误');
            }
            exit;
        }
        $this->invoiceList = Invoice_Api::getOutputInvoiceList($this->searchConf, $this->total ,$this->start, $this->num);

        $this->addFootJs(array('js/apps/output_invoice.js'));
    }

    protected function outputBody()
    {
        if(is_array($this->searchConf['step']))
        {
            unset($this->searchConf['step']);
        }
        $app = '/finance/output_invoice_list.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);
        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('invoice_list', $this->invoiceList);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('city_list', Conf_City::$CITY);
        unset($this->searchConf['step']);
        $app2 = '/finance/output_invoice_list.php?' . http_build_query($this->searchConf);
        $this->smarty->assign('download_url', $app2);
        $this->smarty->assign('page_url', $app2);
        $this->smarty->assign('step', $this->step);
        $this->smarty->assign('step_list', Conf_Invoice::$INVOICE_OUTPUT_STEP_FINANCE);
        $this->smarty->assign('is_buyer', Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_BUYER_NEW));
        $this->smarty->assign('invoice_types', Conf_Invoice::$INVOICE_TYPES);

        $this->smarty->display('finance/output_invoice_list.html');
    }
}

$app = new App('pri');
$app->run();