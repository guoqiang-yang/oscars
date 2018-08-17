<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/9/18
 * Time: 上午11:18
 */
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $fid;

    protected function getPara()
    {
        $this->fid = Tool_Input::clean('r', 'id', TYPE_UINT);
    }

    protected function main()
    {
        $this->city = Conf_City::$CITY;
        $this->type = Conf_Floor_Activity::$FLOOR_TYPE;
        $this->addFootJs(array('js/apps/floor_activity.js'));
        if (!empty($this->fid))
        {
            $this->floor = Activity_Floor_Api::getOne($this->fid);
            $this->floor['_city'] = explode(',', $this->floor['city']);
        }
    }

    protected function outputBody()
    {
        $this->smarty->assign('id', $this->id);
        $this->smarty->assign('city', $this->city);
        $this->smarty->assign('type', $this->type);
        $this->smarty->assign('floor', $this->floor);
        $this->smarty->assign('platform', Conf_Activity_Flash_Sale::$PALTFORM);
        $this->smarty->display('activity/add_floor_activity.html');
    }
}

$app = new App('pri');
$app->run();