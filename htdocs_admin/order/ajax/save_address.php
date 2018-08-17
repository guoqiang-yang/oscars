<?php

include_once('../../../global.php');

class App extends App_Ajax
{
    private $id;
    private $oid;
    private $cid;
    private $uid;
    private $address;
    private $from;
    private $addrArea;
    private $addrAreaCodes;
    private $addrCommunity;
    private $addrAddress;
    private $addrLat;
    private $addrLng;
    private $addrDetail;
    private $city;
    private $district;
    private $ringRoad;
    private $addressDetail;

    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_INT);
        $this->uid = Tool_Input::clean('r', 'uid', TYPE_INT);
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->from = Tool_Input::clean('r', 'from', TYPE_STR);
        $this->addrArea = Tool_Input::clean('r', 'addr_area', TYPE_STR);
        $this->addrAreaCodes = Tool_Input::clean('r', 'addr_code', TYPE_STR);
        $this->addrCommunity = Tool_Input::clean('r', 'addr_communtiy', TYPE_STR);
        $this->addrAddress = Tool_Input::clean('r', 'addr_address', TYPE_STR);
        $this->addrLat = Tool_Input::clean('r', 'addr_lat', TYPE_STR);
        $this->addrLng = Tool_Input::clean('r', 'addr_lng', TYPE_STR);
        $this->addrDetail = Tool_Input::clean('r', 'addr_detail', TYPE_STR);
        $this->address = array(
            'contact_name' => Tool_Input::clean('r', 'contact_name', TYPE_STR),
            'contact_phone' => Tool_Input::clean('r', 'contact_phone', TYPE_STR),
        );
    }

    protected function checkPara()
    {
        if (empty($this->address['contact_name']))
        {
            throw new Exception('order:empty contact name');
        }
        if (empty($this->address['contact_phone']))
        {
            throw new Exception('order:empty contact phone');
        }

        if (empty($this->addrCommunity) || empty($this->addrAddress) || empty($this->addrLat) || empty($this->addrLng))
        {
            throw new Exception('order:empty community');
        }
        if (empty($this->addrDetail) || empty($this->addrArea) || empty($this->addrAreaCodes))
        {
            throw new Exception('order:empty address area');
        }
        if (empty($this->addrDetail))
        {
            throw new Exception('order:empty address detail');
        }

        list($this->city, $this->district, $this->ringRoad) = explode('-', $this->addrAreaCodes);

        if (!array_key_exists($this->city, Conf_Area::$DISTRICT) || !array_key_exists($this->district, Conf_Area::$DISTRICT[$this->city]) || !array_key_exists($this->ringRoad, Conf_Area::$AREA[$this->district]))
        {
            throw new Exception('order:error address');
        }

        $cityInfo = City_Api::getCity();
        if ($this->city != Conf_City::OTHERCITY && $this->city != $cityInfo['city_id'])
        {
            throw new Exception('选择的小区和选择的城市不一致，请修正选择！');
        }
    }
    
    protected function checkAuth()
    {
        parent::checkAuth('/order/add_order_logistics_h5');
    }

    protected function main()
    {
        // 匹配小区
        $oc = new Order_Community();
        $rawCommunityInfo = array(
            'lat' => $this->addrLat,
            'lng' => $this->addrLng,
            'city' => $this->city,
            'district' => $this->district,
            'community_name' => $this->addrCommunity,
        );
        $this->address['community_id'] = $oc->matchCommunity($rawCommunityInfo);
        $this->address['city'] = $this->city;
        $this->address['district'] = $this->district;
        $this->address['address'] = $this->address['community_id'] > 0 ? $this->addrDetail : $this->addrCommunity . Conf_Area::Separator_Construction . $this->addrDetail;
        $this->address['community_name'] = $this->addrCommunity;
        $this->address['community_addr'] = $this->addrAddress;
        $this->address['ring_road'] = $this->ringRoad;
        $this->address['lng'] = $this->addrLng;
        $this->address['lat'] = $this->addrLat;

        //如果是重庆，且没有匹配到小区，则新增一个小区
        if ($this->address['community_id'] == 0)
        {
            $name = $this->addrCommunity;
            $pos = strrpos($this->addrCommunity, '-');
            if (false !== $pos)
            {
                $name = substr($this->addrCommunity, 0, $pos + 1);
            }
            $info = array(
                'name' => $name,
                'city_id' => $this->city,
                'city' => Conf_City::$CITY[$this->city],
                'district_id' => $this->district,
                'district' => Conf_Area::$DISTRICT[$this->city][$this->district],
                'ring_road' => $this->ringRoad,
                'lng' => $this->addrLng,
                'lat' => $this->addrLat,
                'address' => $this->addrAddress,
                'alias' => $this->addrDetail,
            );
            $this->address['community_id'] = Order_Community_Api::save($info);
            $this->address['community_name'] = $name;
        }

        if ($this->id != 0)
        {
            Crm2_Api::updateConstructionSite($this->id, $this->address);
        }
        else
        {
            $this->address['cid'] = $this->cid;
            $this->address['uid'] = $this->uid;
            $this->id = Crm2_Api::saveConstructionSite($this->address);
        }

        $this->addressDetail = Conf_Area::$CITY[$this->address['city']] . Conf_Area::$DISTRICT[$this->address['city']][$this->address['district']] .
                            Conf_Area::$AREA[$this->address['district']][$this->address['ring_road']] . $this->address['community_name'] . $this->address['address'];
    }

    protected function outputPage()
    {
        $result = array(
            'id' => $this->id,
            'from' => !empty($this->from) ? $this->from : 'back',
            'community_id' => $this->address['community_id'],
            'contact_name' => $this->address['contact_name'],
            'contact_phone' => $this->address['contact_phone'],
            'address_detail' => $this->addressDetail,
        );

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App('pub');
$app->run();

