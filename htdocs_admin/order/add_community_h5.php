<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    protected $title = '添加小区';
    private $oid;
    private $cityId;
    private $cid;
    private $uid;

    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_INT);
        $this->uid = Tool_Input::clean('r', 'uid', TYPE_INT);
    }

    protected function checkAuth()
    {
        parent::checkAuth('/order/add_order_logistics_h5');
    }

    protected function main()
    {
        $cityInfo = City_Api::getCity();
        $this->cityId = $cityInfo['city_id'];
        $this->addFootJs(
            array(
                'js/apps/save_community.js',
            )
        );
    }

    protected function outputBody()
    {
        $this->smarty->assign('oid', $this->oid);
        $this->smarty->assign('uid', $this->uid);
        $this->smarty->assign('cid', $this->cid);
        $this->smarty->assign('city_id', $this->cityId);
        $this->smarty->assign('city', Tool_Array::jsonEncode(Conf_Area::$CITY));
        $this->smarty->assign('distinct', Tool_Array::jsonEncode(Conf_Area::$DISTRICT));
        $this->smarty->assign('area', Tool_Array::jsonEncode(Conf_Area::$AREA));

        $this->smarty->display('order/add_community_h5.html');
    }
}

$app = new App('pri');
$app->run();
