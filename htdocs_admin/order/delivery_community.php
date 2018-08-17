<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $num = 20;
    private $searchMode;
    private $start;
    private $search;
    private $total;
    private $communityList;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->searchMode = Tool_Input::clean('r', 'search_mode', TYPE_UINT);

        $this->search = array(
            'cmid' => Tool_Input::clean('r', 'cmid', TYPE_UINT),
            'full_keyword' => Tool_Input::clean('r', 'full_keyword', TYPE_STR),
            'city_id' => Tool_Input::clean('r', 'city_id', TYPE_UINT),
            'district_id' => Tool_Input::clean('r', 'district_id', TYPE_UINT),
            'ring_road' => Tool_Input::clean('r', 'ring_road', TYPE_UINT),
            'status' => Tool_Input::clean('r', 'status', TYPE_INT),
            'ring_road_status' => Tool_Input::clean('r', 'ring_road_status', TYPE_STR),
            'source' => Tool_Input::clean('r', 'source', TYPE_STR),
            'has_order' => Tool_Input::clean('r', 'has_order', TYPE_INT),
        );
    }

    protected function main()
    {
        if ($this->searchMode)
        {
            $this->search['status'] = -1;
        }
        $this->communityList = Order_Community_Api::search($this->search, $this->start, $this->num);
        $this->total = $this->communityList['total'];
        $this->addFootJs(array(
                             'js/apps/order.js',
                             'js/core/area.js',
                             'http://api.map.baidu.com/api?v=2.0&ak=' . Conf_Base::BAIDU_KEY,
                         ));
    }

    protected function outputBody()
    {
        $app = '/order/delivery_community.php?' . http_build_query($this->search);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('start', $this->start);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('community_list', $this->communityList['data']);
        $this->smarty->assign('search', $this->search);

        $this->smarty->assign('city', Tool_Array::jsonEncode(Conf_Area::$CITY));
        $this->smarty->assign('distinct', Tool_Array::jsonEncode(Conf_Area::$DISTRICT));
        $this->smarty->assign('area', Tool_Array::jsonEncode(Conf_Area::$AREA));
        $this->smarty->assign('citys', Conf_Area::$CITY);

        $this->smarty->display('order/delivery_community.html');
    }
}

$app = new App();
$app->run();