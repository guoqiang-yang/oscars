<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $communityId;
    private $wid;
    private $origin;
    private $dest;
    private $distance;
    private $status;
    private $destName;
    private $originName;
    private $note;
    private $cityId4Wid = 0;

    protected function getPara()
    {
        $this->communityId = Tool_Input::clean('r', 'community_id', TYPE_UINT);
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);

        $wid2Citys = Conf_Warehouse::$WAREHOUSE_CITY_MAPPING;
        $this->cityId4Wid = $wid2Citys[$this->wid];
    }

    protected function main()
    {
        $data = Order_Community_Api::getDistanceAndFeeListNew($this->communityId, $this->wid);

        $this->status = $data['status'];
        $this->distance = $data['distance'];
        $this->note = $data['note'];

        $community = Order_Community_Api::get($this->communityId);
        if (empty($community) || $community['lng'] == 0 || $community['lat'] == 0)
        {
            throw new Exception('小区数据不全');
        }
        $this->dest = array(
            'lat' => $community['lat'],
            'lng' => $community['lng'],
        );

        //仓库经纬度（起点）
        $this->origin = Conf_Warehouse::$LOCATION[$this->wid];

        $this->originName = Conf_Warehouse::$WAREHOUSES[$this->wid];
        $this->destName = $community['name'];

        $key = Conf_Base::BAIDU_KEY;
        $this->addFootJs(array(
                             'http://api.map.baidu.com/api?v=2.0&ak=' . $key,
                             'js/apps/order.js',
                             'js/apps/driving_route.js',
                             'js/footer.js',
                         ));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $this->smarty->assign('dest', $this->dest);
        $this->smarty->assign('origin', $this->origin);
        $this->smarty->assign('distance', $this->distance);
        $this->smarty->assign('status', $this->status);
        $this->smarty->assign('key', Conf_Base::BAIDU_KEY);
        $this->smarty->assign('origin_name', $this->originName);
        $this->smarty->assign('dest_name', $this->destName);
        $this->smarty->assign('community_id', $this->communityId);
        $this->smarty->assign('wid', $this->wid);
        $this->smarty->assign('note', $this->note);
        $this->smarty->assign('city4wid', $this->cityId4Wid);

        $this->smarty->display('order/edit_community_fee.html');
    }
}

$app = new App('pri');
$app->run();
