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

    protected function checkPara()
    {
        if(empty($this->pid))
        {
            throw new Exception('common: param error');
        }
    }

    protected function main()
    {
        $this->product = Cpoint_Api::getProduct($this->pid);

        $this->addFootJs(array('js/apps/cpoint_product.js'));
        $this->addCss(array(
            'css/imgareaselect-default.css',
        ));
    }

    protected function outputBody()
    {
        $this->smarty->assign('product', $this->product);
        $this->smarty->assign('cate_list', Conf_Cpoint::getCate1());
        $this->smarty->assign('grade_list', Conf_User::getMemberGrade());
        $this->smarty->display('activity/show_customer_point_product.html');
    }
}

$app = new App('pri');
$app->run();
