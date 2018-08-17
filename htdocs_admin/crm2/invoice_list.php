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
    private $type;
    private $isCustomer = 1;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->step = Tool_Input::clean('r', 'step', TYPE_STR);
        $this->type = Tool_Input::clean('r', 'type', TYPE_STR);
        $this->searchConf = array(
            'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
            'id' => Tool_Input::clean('r', 'id', TYPE_UINT),
            'title' => Tool_Input::clean('r', 'title', TYPE_STR),
            'city_id' => Tool_Input::clean('r', 'city_id', TYPE_UINT),
            'invoice_type' => Tool_Input::clean('r', 'invoice_type', TYPE_UINT),
            'from_ctime' => Tool_Input::clean('r', 'from_ctime', TYPE_STR),
            'end_ctime' => Tool_Input::clean('r', 'end_ctime', TYPE_STR),
            'bill_ids' => Tool_Input::clean('r', 'bill_ids', TYPE_UINT),
        );
    }

    protected function checkPara()
    {
        switch ($this->step){
            case 1:
                $this->searchConf['step'] = Conf_Invoice::INVOICE_OUTPUT_STEP_NEW;
                $this->searchConf['status'] = Conf_Base::STATUS_NORMAL;
                break;
            case 2:
                $this->searchConf['step'] = Conf_Invoice::INVOICE_OUTPUT_STEP_REBUT;
                $this->searchConf['status'] = Conf_Base::STATUS_NORMAL;
                break;
            case 3:
                $this->searchConf['step'] = array(Conf_Invoice::INVOICE_OUTPUT_STEP_SALES_AUDIT, Conf_Invoice::INVOICE_OUTPUT_STEP_FINANCE_CONFIRM);
                $this->searchConf['status'] = Conf_Base::STATUS_NORMAL;
                break;
            case 5:
                $this->searchConf['step'] = Conf_Invoice::INVOICE_OUTPUT_STEP_FINISHED;
                $this->searchConf['status'] = Conf_Base::STATUS_NORMAL;
                break;
            case 99:
                $this->searchConf['status'] = Conf_Base::STATUS_DELETED;
                break;
            default:
                break;
        }
    }

    protected function main()
    {
        $is_sale = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_SALES_NEW);
        if($is_sale)
        {

            $this->searchConf['create_suid'] = array_unique(array_merge($this->_user['team_member'],array($this->_uid)));
            if(!empty($this->searchConf['cid']))
            {
                $customer = Crm2_Api::getCustomerInfo($this->searchConf['cid']);
                if($customer['customer']['sales_suid'] != $this->_uid)
                {
                    $this->isCustomer = 0;
                }
            }
        }
        $this->invoiceList = Invoice_Api::getOutputInvoiceList($this->searchConf, $this->total ,$this->start, $this->num);

        $this->addFootJs(array('js/apps/output_invoice.js'));
    }

    protected function outputBody()
    {
        $this->searchConf['step'] = $this->step;
        $app = '/crm2/invoice_list.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);
        $this->smarty->assign('download_url', $app);
        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('invoice_list', $this->invoiceList);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('city_list', Conf_City::$CITY);
        unset($this->searchConf['step']);
        unset($this->searchConf['status']);
        $app2 = '/crm2/invoice_list.php?' . http_build_query($this->searchConf);
        $this->smarty->assign('page_url', $app2);
        $this->smarty->assign('step', $this->step);
        $this->smarty->assign('step_list', Conf_Invoice::$INVOICE_OUTPUT_STEP_CUSTOMER);
        $this->smarty->assign('is_assis', Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_ASSIS_SALER_NEW));
        $this->smarty->assign('invoice_types', Conf_Invoice::$INVOICE_TYPES);
        $this->smarty->assign('is_customer', $this->isCustomer);
        $this->smarty->display('crm2/invoice_list.html');
    }
}

$app = new App('pri');
$app->run();