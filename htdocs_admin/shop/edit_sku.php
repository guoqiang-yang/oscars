<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $sid;
    private $skuInfo;

    protected function getPara()
    {
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
    }

    protected function main()
    {
        $sku = Shop_Api::getSkuInfo($this->sid, true);
        $skus[] = $sku;
        Warehouse_Api::appendStock(0, $skus);
        $this->skuInfo = $skus[0];
        
        $this->addFootJs(array(
                             'js/core/jquery.gridly.js',
                             'js/core/cate.js',
                             'js/apps/sku.js',
                             'js/core/FileUploader.js',
                             'js/core/imgareaselect.min.js',
                             'js/apps/upload_sku_pic.js',
                         ));
        $this->addCss(array(
                          'css/imgareaselect-default.css',
                          'css/jquery.gridly.css',
                      ));
    }

    protected function outputBody()
    {
        $this->smarty->assign('sku', $this->skuInfo);
        $this->smarty->assign('qrcode_type', Conf_Qrcode::$QRCODE_TYPE);
        $this->smarty->assign('sku_types', Conf_Sku::getSkuTypes());

        $this->smarty->display('shop/edit_sku.html');
    }
}

$app = new App('pri');
$app->run();
