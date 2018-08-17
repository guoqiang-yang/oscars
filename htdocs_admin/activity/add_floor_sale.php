<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/9/18
 * Time: 下午2:08
 */
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $sid;

    protected function getPara()
    {
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
        $this->pid = Tool_Input::clean('r', 'pid', TYPE_UINT);
        $this->fid = Tool_Input::clean('r', 'fid', TYPE_UINT);
        $this->s_type = Tool_Input::clean('r', 'type', TYPE_UINT);
        $this->position = Tool_Input::clean('r', 'position', TYPE_UINT);
    }

    protected function main()
    {
        if (!empty($this->pid))
        {
            $this->product = Shop_Api::getProductInfo($this->pid);
        }
        if (!empty($this->sid))
        {
            $info = Activity_Floor_Sale_Api::getOne($this->sid);
            $info['sale_price'] = str_split($info['sale_price']);
            $info['start_time'] = substr(str_replace(' ', 'T', $info['start_time']), 0, -3);
            $info['end_time'] = substr(str_replace(' ', 'T', $info['end_time']), 0, -3);
            $this->sale = $info;
        }
        $this->addFootJs(array(
                             'js/core/cate.js',
                             'js/core/FileUploader.js',
                             'js/core/imgareaselect.min.js',
                             'js/apps/uploadpic.js',
                             'js/apps/floor_activity.js',
                         ));
        $this->floor = Conf_Floor_Activity::$PICTURE;
        $this->type = Conf_Floor_Activity::$TYPE;
        $this->mark = Conf_Floor_Activity::$MARK;
    }

    protected function outputBody()
    {
        $this->smarty->assign('product', $this->product);
        $this->smarty->assign('fid', $this->fid);
        $this->smarty->assign('pid', $this->pid);
        $this->smarty->assign('picture', $this->floor);
        $this->smarty->assign('sale', $this->sale);
        $this->smarty->assign('type', $this->type);
        $this->smarty->assign('s_type', $this->s_type);
        $this->smarty->assign('position', $this->position);
        $this->smarty->assign('mark', $this->mark);
        $this->smarty->assign('price', Conf_Activity_Flash_Sale::$PRICE);
        $this->smarty->assign('platform', Conf_Activity_Flash_Sale::$PALTFORM);
        $this->smarty->display('activity/add_floor_sale.html');
    }
}

$app = new App('pri');
$app->run();