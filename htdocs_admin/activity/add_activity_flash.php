<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/9/14
 * Time: 上午9:42
 */
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $id;

    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
    }

    protected function main()
    {
        $this->city = Conf_City::$CITY;
        $this->platform = Conf_Activity_Flash_Sale::$PALTFORM;
        $this->type = Conf_Activity_Flash_Sale::$TYPE;
        $this->addFootJs(array('js/apps/flash_sale.js',));
        if (!empty($this->id))
        {
            $info = Activity_Flash_Api::getOne($this->id);
            $info['start_time'] = substr(str_replace(' ', 'T', $info['start_time']), 0, -3);
            $info['end_time'] = substr(str_replace(' ', 'T', $info['end_time']), 0, -3);
            $this->activity = $info;
        }
    }

    protected function outputBody()
    {
        $this->smarty->assign('id', $this->id);
        $this->smarty->assign('city', $this->city);
        $this->smarty->assign('type', $this->type);
        $this->smarty->assign('activity', $this->activity);
        $this->smarty->assign('platform', $this->platform);
        $this->smarty->display('activity/add_activity_flash.html');
    }
}

$app = new App('pri');
$app->run();