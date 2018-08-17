<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $keyword;
    private $cityId;
    private $num = 10;
    private $communityList = array();

    protected function getPara()
    {
        $this->keyword = Tool_Input::clean('r', 'keyword', TYPE_STR);
        $this->cityId = Tool_Input::clean('r', 'city_id', TYPE_UINT);
    }

    protected function checkPara()
    {
        if (empty($this->cityId))
        {
            $cityInfo = City_Api::getCity();
            $this->cityId = $cityInfo['city_id'];
        }
    }

    protected function main()
    {
        if ($_SERVER['SERVER_ADDR'] == '127.0.0.1')
        {
            $communityList = Order_Community_Api::searchForOrder($this->keyword, $this->cityId, 0, $this->num);
        }
        else
        {
            $communityList = Order_Community_Api::sphinxSearch($this->keyword, $this->cityId, 0, $this->num);
        }

        if (isset($communityList['data']))
        {
            $communityList = $communityList['data'];
        }

        foreach ($communityList as $c)
        {
            $obj = new stdClass();
            $obj->id = $c['cmid'];
            $obj->label = $c['name'];
            $obj->detail = $c['city'] . ' ' . $c['district'] . ' ' . $c['address'];
            $obj->value = urlencode(json_encode(array(
                                                    'cmid' => $c['cmid'],
                                                    'city_id' => $c['city_id'],
                                                    'district_id' => $c['district_id'],
                                                    'ring_road' => $c['ring_road'],
                                                    'pos' => $c['lng'] . ':' . $c['lat'],
                                                    'status' => $c['status'],
                                                    'address' => $c['address'],
                                                )));

            $this->communityList[] = $obj;
        }
    }

    protected function outputBody()
    {
        echo Response_Ajax::safeJSONEncode($this->communityList);

        exit;
    }
}

$app = new App('pub');
$app->run();