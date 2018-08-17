<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/9/12
 * Time: 下午12:11
 */
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $id;

    protected function checkAuth()
    {
        parent::checkAuth('/activity/add_activity_flash');
    }

    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->pid = Tool_Input::clean('r', 'pid', TYPE_UINT);
        $this->fid = Tool_Input::clean('r', 'fid', TYPE_UINT);
    }

    protected function checkPara()
    {
        if (empty($this->pid))
        {
            throw new Exception('参数不合法');
        }
        if (empty($this->id) && empty($this->fid))
        {
            throw new Exception('参数不合法');
        }
    }

    protected function main()
    {
        $this->product = Shop_Api::getProductInfo($this->pid);
        $this->addFootJs(array(
                             'js/core/cate.js',
                             'js/core/FileUploader.js',
                             'js/core/imgareaselect.min.js',
                             'js/apps/uploadpic.js',
                             'js/apps/flash_sale.js',
                         ));
        if (!empty($this->id))
        {
            $info = Activity_Flash_Sale_Api::getOne($this->id);
            $info['sale_price'] = str_split($info['sale_price']);
            $info['start_time'] = substr(str_replace(' ', 'T', $info['start_time']), 0, -3);
            $info['end_time'] = substr(str_replace(' ', 'T', $info['end_time']), 0, -3);
            $this->flash_sale = $info;
        }
    }

    protected function outputBody()
    {
        $this->smarty->assign('product', $this->product);
        $this->smarty->assign('fid', $this->fid);
        $this->smarty->assign('flash_sale', $this->flash_sale);
        $this->smarty->assign('price', Conf_Activity_Flash_Sale::$PRICE);
        $this->smarty->assign('platform', Conf_Activity_Flash_Sale::$PALTFORM);
        $this->smarty->display('activity/add_flash_sale.html');
    }
}

$app = new App('pri');
$app->run();