<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    protected $title = '选择小区';
    private $oid;
    private $cid;
    private $uid;
    private $platform;
    private $version;
    private $contact_phone;
    private $contact_name;
    private $select_city;
    private $select_district;
    private $select_area;
    private $from;
    private $delivery_type;

    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_INT);
        $this->uid = Tool_Input::clean('r', 'uid', TYPE_INT);
        $this->platform = Tool_Input::clean('r', 'platform', TYPE_STR);
        $this->version = Tool_Input::clean('r', 'version', TYPE_STR);
        $this->contact_phone = Tool_Input::clean('r', 'contact_phone', TYPE_INT);
        $this->contact_name = Tool_Input::clean('r', 'contact_name', TYPE_STR);
        $this->select_city = Tool_Input::clean('r', 'select_city', TYPE_INT);
        $this->select_district = Tool_Input::clean('r', 'select_district', TYPE_INT);
        $this->select_area = Tool_Input::clean('r', 'select_area', TYPE_INT);
        $this->from = Tool_Input::clean('r', 'from', TYPE_STR);
        $this->delivery_type = Tool_Input::clean('r', 'delivery_type', TYPE_UINT);
    }

    protected function checkAuth()
    {
        parent::checkAuth('/order/add_order_logistics_h5');
    }

    protected function main()
    {
        $cityInfo = City_Api::getCity();
        $this->cityId = $cityInfo['city_id'];
        $this->addCss(
            array(
                'css/style.css'
            )
        );
        $this->addFootJs(
            array(
                'js/apps/add_community.js',
                'http://api.map.baidu.com/api?v=2.0&ak=' . Conf_Base::BAIDU_KEY,
                'js/core/area.js',
            )
        );
    }

    protected function outputBody()
    {
        $this->smarty->assign('oid', $this->oid);
        $this->smarty->assign('uid', $this->uid);
        $this->smarty->assign('cid', $this->cid);
        $this->smarty->assign('contact_phone', $this->contact_phone);
        $this->smarty->assign('contact_name', $this->contact_name);
        $this->smarty->assign('select_city', $this->select_city);
        $this->smarty->assign('select_district', $this->select_district);
        $this->smarty->assign('select_area', $this->select_area);
        $this->smarty->assign('from', $this->from);
        $this->smarty->assign('platform', $this->platform);
        $this->smarty->assign('version', $this->version);
        $this->smarty->assign('delivery_type', $this->delivery_type);
        $this->smarty->display('order/add_select_map_h5.html');
    }
}

$app = new App('pri');
$app->run();
