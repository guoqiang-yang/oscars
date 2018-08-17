<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/11/1
 * Time: 下午6:51
 */
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $total;

    protected function getPara()
    {
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
    }

    protected function main()
    {

        if (!empty($this->sid))
        {
            $this->data = Activity_Service_Feature_Api::getOne($this->sid);
            $this->data['start_time'] = substr(str_replace(' ', 'T', $this->data['start_time']), 0, -3);
            $this->data['end_time'] = substr(str_replace(' ', 'T', $this->data['end_time']), 0, -3);
        }

        $this->addFootJs(array('js/apps/service_feature.js'));
        $this->city = Conf_City::$CITY;
    }

    protected function outputBody()
    {
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('city', $this->city);
        $this->smarty->assign('service_feature', $this->data);

        $this->smarty->display('activity/add_service_feature.html');
    }
}

$app = new App('pri');
$app->run();