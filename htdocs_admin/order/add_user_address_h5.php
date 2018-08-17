<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    protected $title = '添加地址';
    private $oid;
    private $cityId;
    private $cid;
    private $uid;
    
    private $num = 100;
    private $relationCities;
    
    private $platform;
    private $version;
    private $contact_phone;
    private $contact_name;
    private $select_city;
    private $select_district;
    private $select_area;
    private $from;
    private $community_name;
    private $community_address;
    private $community_lat;
    private $community_lng;
    private $delivery_type;

    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_INT);
        $this->uid = Tool_Input::clean('r', 'order_uid', TYPE_INT);
        $this->platform = Tool_Input::clean('r', 'platform', TYPE_STR);
        $this->version = Tool_Input::clean('r', 'version', TYPE_STR);
        $this->contact_phone = Tool_Input::clean('r', 'contact_phone', TYPE_INT);
        $this->contact_name = Tool_Input::clean('r', 'contact_name', TYPE_STR);
        $this->select_city = Tool_Input::clean('r', 'select_city', TYPE_INT);
        $this->select_district = Tool_Input::clean('r', 'select_district', TYPE_INT);
        $this->select_area = Tool_Input::clean('r', 'select_area', TYPE_INT);
        $this->from = Tool_Input::clean('r', 'from', TYPE_STR);
        $this->community_name = Tool_Input::clean('r', 'community_name', TYPE_STR);
        $this->community_address = Tool_Input::clean('r', 'community_address', TYPE_STR);
        $this->community_lat = Tool_Input::clean('r', 'community_lat', TYPE_STR);
        $this->community_lng = Tool_Input::clean('r', 'community_lng', TYPE_STR);
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
        $this->addFootJs(
            array(
                'js/core/area.js',
                'js/apps/save_community.js',
            )
        );
        $this->relationCities = array();
        $curCity = City_Api::getCity();
        $this->relationCities = array(
            $curCity['city_id'],
            Conf_City::OTHERCITY,
        );
        
        if ($curCity['city_id'] == Conf_City::BEIJING)
        {
            $this->relationCities[] = Conf_City::OTHER;
        }
        $res = Crm2_Api::getConstructionListByCitys4Customer($this->cid, $this->uid, $this->relationCities, 0, $this->num);
        $this->total = $res['total'];
        $this->addressList = $res['list'];
        
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
        $this->smarty->assign('contact_phone', $this->contact_phone);
        $this->smarty->assign('contact_name', $this->contact_name);
        $this->smarty->assign('select_city', $this->select_city);
        $this->smarty->assign('select_district', $this->select_district);
        $this->smarty->assign('select_area', $this->select_area);
        $this->smarty->assign('from', $this->from);
        $this->smarty->assign('community_name', $this->community_name);
        $this->smarty->assign('community_address', $this->community_address);
        $this->smarty->assign('community_lat', $this->community_lat);
        $this->smarty->assign('community_lng', $this->community_lng);
        $this->smarty->assign('addressList', $this->addressList);
        $this->smarty->assign('platform', $this->platform);
        $this->smarty->assign('version', $this->version);
        $this->smarty->assign('delivery_type', $this->delivery_type);
        $this->smarty->display('order/add_user_address_h5.html');
    }
}

$app = new App('pri');
$app->run();
