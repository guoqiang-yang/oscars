<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
	private $id;
    private $info;

	protected function getPara()
	{
		$this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
    }

	protected function checkPara()
    {
        if (empty($this->id))
        {
            throw new Exception('common: param error');
        }
    }

    protected function main()
	{
        $this->info = Crm2_Customer_Visit_Api::get($this->id);
        $this->addCss(array(
            'css/imgareaselect-default.css',
            'css/jquery.gridly.css',
        ));
	}

	protected function outputBody()
	{
        $this->smarty->assign('info', $this->info);
        $this->smarty->assign('visit_types', Conf_Crm::getTypeList());
        $this->smarty->display('crm2/show_customer_visit.html');
	}
}

$app = new App('pri');
$app->run();
