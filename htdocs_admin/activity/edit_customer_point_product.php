<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $pid;
    private $product;

    protected function getPara()
    {
        $this->pid = Tool_Input::clean('r', 'pid', TYPE_UINT);
    }

    protected function main()
    {
        if(!empty($this->pid))
        {
            $this->product = Cpoint_Api::getProduct($this->pid);
        }

        $this->addFootJs(array(
            'js/ueditor/ueditor.config.js',
            'js/ueditor/ueditor.all.js',
            'js/apps/cpoint_product.js',
            'js/core/FileUploader.js',
            'js/core/imgareaselect.min.js',
            'js/apps/upload_visit_pic.js'
            ));
        $this->addCss(array(
            'css/imgareaselect-default.css',
        ));
    }

    protected function outputBody()
    {
        $this->smarty->assignRaw('product', $this->product);
        $this->smarty->assign('cate_list', Conf_Cpoint::getCate1());
        $this->smarty->assign('grade_list', Conf_User::getMemberGrade());
        $this->smarty->display('activity/edit_customer_point_product.html');
    }
}

$app = new App('pri');
$app->run();
