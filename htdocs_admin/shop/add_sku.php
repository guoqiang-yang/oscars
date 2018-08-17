<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $cate1 = 1;

    protected function getPara()
    {
    }

    protected function main()
    {
        $this->addFootJs(array(
                             'js/core/jquery.gridly.js',
                             'js/core/cate.js',
                             'js/apps/sku.js',
                             'js/core/FileUploader.js',
                             'js/core/imgareaselect.min.js',
                             'js/apps/upload_sku_pic.js'
                         ));
        $this->addCss(array(
                          'css/imgareaselect-default.css',
                          'css/jquery.gridly.css',
                      ));
    }

    protected function outputBody()
    {
        $this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
        $this->smarty->assign('cate2_list', Conf_Sku::$CATE2[$this->cate1]);
        $this->smarty->assign('qrcode_type', Conf_Qrcode::$QRCODE_TYPE);
        $this->smarty->assign('sku_types', Conf_Sku::getSkuTypes());

        $this->smarty->display('shop/add_sku.html');
    }
}

$app = new App('pri');
$app->run();
