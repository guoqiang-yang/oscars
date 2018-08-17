<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    // cgi参数
    private $cate1;
    private $cate2;
    private $cate3;
    // 中间结果
    private $models;

    protected function getPara()
    {
        $this->cate1 = Tool_Input::clean('r', 'cate1', TYPE_UINT);
        $this->cate2 = Tool_Input::clean('r', 'cate2', TYPE_UINT);
        $this->cate3 = Tool_Input::clean('r', 'cate3', TYPE_UINT);
    }

    protected function checkPara()
    {
        if (empty($this->cate1))
        {
            $this->cate1 = 1;
        }
        if (empty($this->cate2))
        {
            $cate2List = Conf_Sku::$CATE2[$this->cate1];
            $this->cate2 = array_shift(array_keys($cate2List));
        }
    }

    protected function main()
    {
        $this->models = Shop_Api::getModelList($this->cate2, $this->cate3);
        $this->addFootJs(array('js/apps/shop.js'));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
        $this->smarty->assign('cate2_list', Conf_Sku::$CATE2[$this->cate1]);
        $this->smarty->assign('cur_cate1', $this->cate1);
        $this->smarty->assign('cur_cate2', $this->cate2);
        $this->smarty->assign('cur_cate3', $this->cate3);
        $this->smarty->assign('models', $this->models);
        $this->smarty->display('shop/model_list.html');
    }
}

$app = new App('pri');
$app->run();

