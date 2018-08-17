<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/11/18
 * Time: 下午5:58
 */
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $searchConf;

    protected function getPara()
    {
        $this->searchConf = array(
            'wid' => Tool_Input::clean('r', 'wid', TYPE_UINT),
        );

        if (empty($this->searchConf['wid']))
        {
            $this->searchConf['wid'] = array_keys(App_Admin_Web::getAllowedWids4User());
        }

        $this->test = Tool_Input::clean('r', 'test', TYPE_STR);
    }

    protected function main()
    {
        if (!empty($this->searchConf['wid']))
        {
            $wid = is_array($this->searchConf['wid']) ? $this->searchConf['wid'][0] : $this->searchConf['wid'];

            $this->city_name = array_values(Conf_Warehouse::$LOCATION[$wid]);
        }
        else
        {
            $this->city_name = array(
                116.404,
                39.915
            );
        }
        $this->drivers = array();
        $data = $this->getData();
        $this->data = json_decode($data, TRUE);

        $drivers_info = Logistics_Api::getQueueList($this->searchConf, 0, 0);
        foreach ($drivers_info['list'] as $driver)
        {

            if ($driver['step'] == 1)
            {
                $this->drivers['check_in'][] = array(
                    'content' => "<div style='color: green'>" . $driver['_driver']['name'] . '<br>' . $driver['_wid'] . '</div>',
                    'imgurl' => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/icon/check_in.png',
                    'location' => $this->getLocation($driver['did']),
                );
            }
            else if ($driver['step'] == 2 || $driver['step'] == 3 || $driver['step'] == 4)
            {
                $this->drivers['set_out'][] = array(
                    'content' => "<div style='color: red'>" . $driver['_driver']['name'] . '<br>' . $driver['_wid'] . '</div>',
                    'imgurl' => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/icon/set_out.png',
                    'location' => $this->getLocation($driver['did']),
                );
            }
            else if ($driver['step'] == 5)
            {
                $this->drivers['complite'][] = array(
                    'content' => "<div style='color: gray'>" . $driver['_driver']['name'] . '<br>' . $driver['_wid'] . '</div>',
                    'imgurl' => 'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/icon/complite.png',
                    'location' => $this->getLocation($driver['did']),
                );
            }
        }
        $check_in_count = count($this->drivers['check_in']);
        $set_out_count = count($this->drivers['set_out']);
        $complite_count = count($this->drivers['complite']);
        $total = $check_in_count + $set_out_count + $complite_count;
        $this->count = array(
            'total' => array(
                'color' => 'black',
                'detail' => '今日出勤',
                'count' => $total
            ),
            'set_out' => array(
                'color' => 'red',
                'detail' => '配送中车辆',
                'count' => $set_out_count
            ),
            'compilte' => array(
                'color' => '#333333',
                'detail' => '配送完成车辆',
                'count' => $complite_count
            ),
            'check_in' => array(
                'color' => '#72b840',
                'detail' => '等待配送车辆',
                'count' => $check_in_count
            ),
        );

        $this->addFootJs(array(
                             'js/apps/driver_location.js',
                             'http://api.map.baidu.com/api?v=2.0&ak=KrnrrmGcxmZVGB4YcxHywmwjC79NMVqm',
                         ));
        $this->warehouses = App_Admin_Web::getAllowedWids4User();
        $this->drivers = array_values($this->drivers);
        if ($this->test == 'joker')
        {
            echo $this->data['size'];
            die;
        }
    }

    protected function outputBody()
    {
        $this->smarty->assign('counts', $this->count);
        $this->smarty->assign('city_name', json_encode($this->city_name));
        $this->smarty->assign('warehouses', $this->warehouses);
        $this->smarty->assign('wid', $this->searchConf['wid']);
        $this->smarty->assign('drivers', json_encode($this->drivers));
        $this->smarty->display('logistics/drivers_location.html');
    }

    protected function getData()
    {

        $time = time() - 600;
        //page_size最大值为1000，目前我们司机还没超过这个数，如果超过这个数了，就要分页查，配合page_index参数
        $url = "http://api.map.baidu.com/trace/v2/entity/list?ak=KrnrrmGcxmZVGB4YcxHywmwjC79NMVqm&page_size=1000&service_id=129616&active_time" . $time;

        $header = array(
            'Content-Type: application/json',
            'Authorization: token 4e56266f2502936e0378ea6a985dc74a5bec4280'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }

    protected function getLocation($did)
    {
        $locations = $this->data['entities'];
        $driver_location = array();
        foreach ($locations as $location)
        {
            if ($location['entity_name'] == $did)
            {
                $driver_location = $location['realtime_point']['location'];
                break;
            }
        }

        return $driver_location;
    }
}

$app = new App('pri');
$app->run();
