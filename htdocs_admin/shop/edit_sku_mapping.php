<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $id;
    // 中间结果
    private $mapping;

    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
    }

    protected function main()
    {
        if (!empty($this->id))
        {
            $this->mapping = Merchant_Api::get($this->id);
        }

        $this->addFootJs(array('js/apps/merchant.js'));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $this->smarty->assign('mapping', $this->mapping);
        $this->smarty->assign('merchant_list', Conf_Merchant::$MERCHANT);

        $this->smarty->display('shop/edit_sku_mapping.html');
    }
}

$app = new App('pri');
$app->run();

