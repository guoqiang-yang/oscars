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
    private $line_id;

    protected function getPara()
    {
        $this->line_id = Tool_Input::clean('r', 'line_id', TYPE_UINT);
    }

    protected function checkPara()
    {
        if (empty($this->line_id))
        {
            throw new Exception('参数错误！');
        }
    }

    protected function main()
    {
        $color = array(
            '#3A3AD4',
            '#808000',
            '#FF4500',
            '#7b68ee',
            '#4169E1',
            '#2F4F4F',
            '#1E90FF',
            '#2E8B57',
            '#32CD32',
            '#2BAE18',
            '#8F502C',
            '#006400',
            '#6B8E23',
            '#8B4513',
            '#B22222',
            '#48525A',
            '#65723F',
            '#4F8848',
            '#965A25',
            '#264095',
            '#E8EDF2'
        );

        $lineInfo = Logistics_Api::getLineDetail($this->line_id);
        $oids = explode(',', $lineInfo['oids']);
        $ret = array();
        $co_orders = Logistics_Coopworker_Api::getByOids($oids);
        $orders = Order_Api::getListByPk($oids);
        $communitys = array_unique(Tool_Array::getFields($orders, 'community_id'));
        $community_points = Order_Community_Api::getByIds($communitys);
        $index = 0;
        //第一次遍历，判断是一车多单还是多车一单，目前只考虑这两种情况。
        foreach ($co_orders as $co_order)
        {
            $oid = $co_order['oid'];
            if ($co_order['user_type'] == Conf_Base::COOPWORKER_DRIVER && $co_order['type'] == 1)
            {

                $drivers[] = $co_order['cuid'];
                $wid = $co_order['wid'];
                $new_co_orders[] = $co_order;
                $points[] = array(
                    $community_points[$orders[$oid]['community_id']]['lng'],
                    $community_points[$orders[$oid]['community_id']]['lat'],
                    'Oid:' . $oid . '<br/>' . $community_points[$orders[$oid]['community_id']]['name'] . '<br/>' . $co_order['arrival_time'],
                    'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/icon/set_out.png',
                );
            }
        }

        $data = array(
            'line_id' => $this->line_id,
            'warehouse' => Conf_Warehouse::$WAREHOUSES[$wid],
        );
        $ocf = new Order_Community_Fee();

        $where = sprintf('wid=%d and cmid in (%s) and distance <> 0', $wid, implode(',', $communitys));
        $distance_lists = $ocf->getListWhere($where);
        $distance_lists = Tool_Array::sortByField($distance_lists, 'distance');
        $sort_cmids = Tool_Array::getFields($distance_lists, 'cmid');

        foreach ($sort_cmids as $sort_cmid)
        {
            $new_cmd_points[] = array(
                $community_points[$sort_cmid]['lng'],
                $community_points[$sort_cmid]['lat'],
            );
        }

        array_unshift($new_cmd_points, array(
            Conf_Warehouse::$LOCATION[$wid]['lng'],
            Conf_Warehouse::$LOCATION[$wid]['lat'],
        ));
        $this->linepoints = json_encode($new_cmd_points);
        $drivers = array_unique($drivers);
        $driverInfos = Logistics_Api::getByDids($drivers);

        //判断是一车多单还是多车一单
        if (count($drivers) == 1)
        {
            $new_co_orders = array_values(Tool_Array::sortByField($new_co_orders, 'arrival_time', 'asc'));
            //取出所有的领单时间，理论上应该是一致的，取最小的一个为该路线的出发时间
            $start_times = Tool_Array::getFields($new_co_orders, 'confirm_time');
            $start_time = $start_times[0];
            foreach ($new_co_orders as $key => $new_co_order)
            {
                $delivery_time = $key > 0 ? $new_co_orders[$key - 1]['arrival_time'] : $start_time;
                $end_time = $new_co_order['arrival_time'] > $new_co_order['delivery_time'] ? $new_co_order['arrival_time'] : date('Y-m-d H:i:s');
                $baiduData = $this->getData(array(
                                                'entity_name' => $drivers[0],
                                                'start_time' => strtotime($delivery_time),
                                                'end_time' => strtotime($end_time),
                                            ));
                $baiduData = json_decode($baiduData, TRUE);
                $o_info[] = array(
                    'oid' => $new_co_order['oid'],
                    'distance' => round($baiduData['distance'] / 1000, 2),
                    'date' => $new_co_order['arrival_time'],
                );
            }
            $ret[] = array(
                'entity_name' => $drivers[0],
                'start_time' => $start_time,
                'end_time' => $new_co_orders[count($new_co_orders) - 1]['arrival_time'] > 0 ? $new_co_orders[count($new_co_orders) - 1]['arrival_time'] : date('Y-m-d H:i:s'),
            );
            $first = array(
                'oid' => Conf_Warehouse::$WAREHOUSES[$wid],
                'distance' => 0,
                'date' => $start_time,
            );
            array_unshift($o_info, $first);
            $totalDistance = 0;
            foreach ($o_info as $info)
            {
                $totalDistance += $info['distance'];
            }
            $totalTime = strtotime($o_info[count($o_info) - 1]['date']) - strtotime($o_info[0]['date']);
            $totalDate = $this->time2date($totalTime);
            $data['items'][] = array(
                'name' => $driverInfos[0]['name'],
                'did' => $driverInfos[0]['did'],
                'color' => $color[0],
                'infos' => $o_info,
                'total_distance' => $totalDistance,
                'total_date' => $totalDate
            );
            $delivery_time = $start_time;
        }
        else if (count($drivers) > 1)
        {
            foreach ($driverInfos as $driverInfo)
            {
                $o_info = array();
                $ret_index = 0;
                //$ret = array();
                foreach ($new_co_orders as $new_co_order)
                {
                    if ($driverInfo['did'] == $new_co_order['cuid'])
                    {
                        $end_time = $new_co_order['arrival_time'] > $new_co_order['delivery_time'] ? $new_co_order['arrival_time'] : date('Y-m-d H:i:s');

                        if ($ret_index > 0)
                        {
                            $start_time = $ret[$driverInfo['did']]['end_time'];
                            $ret[$driverInfo['did']] = array(
                                'entity_name' => $new_co_order['cuid'],
                                'start_time' => $ret[$driverInfo['did']]['start_time'],
                                'end_time' => $end_time,
                            );
                        }
                        else
                        {
                            $start_time = $new_co_order['delivery_time'] > $new_co_order['alloc_time'] ? $new_co_order['delivery_time'] : $new_co_order['alloc_time'];
                            $ret[$driverInfo['did']] = array(
                                'entity_name' => $new_co_order['cuid'],
                                'start_time' => $start_time,
                                'end_time' => $end_time,
                            );
                        }

                        $baiduData = $this->getData(array(
                                                        'entity_name' => $new_co_order['cuid'],
                                                        'start_time' => strtotime($start_time),
                                                        'end_time' => strtotime($end_time),
                                                    ));
                        $baiduData = json_decode($baiduData, TRUE);
                        $o_info[] = array(
                            'oid' => $new_co_order['oid'],
                            'distance' => round($baiduData['distance'] / 1000, 2),
                            'date' => $new_co_order['arrival_time'],
                        );

                        $first = array(
                            'oid' => Conf_Warehouse::$WAREHOUSES[$wid],
                            'distance' => 0,
                            'date' => $start_time,
                        );
                        $ret_index++;
                    }
                }

                array_unshift($o_info, $first);
                $totalDistance = 0;
                foreach ($o_info as $info)
                {
                    $totalDistance += $info['distance'];
                }
                $totalTime = strtotime($o_info[count($o_info) - 1]['date']) - strtotime($o_info[0]['date']);
                $totalDate = $this->time2date($totalTime);
                $data['items'][] = array(
                    'name' => $driverInfo['name'],
                    'did' => $driverInfo['did'],
                    'color' => $color[$index],
                    'infos' => $o_info,
                    'total_distance' => $totalDistance,
                    'total_date' => $totalDate
                );
                $index++;
            }
            $new_ret = array();
            foreach ($data['items'] as $d_info)
            {
                foreach ($ret as $item)
                {
                    if ($item['entity_name'] == $d_info['did'] && isset($new_ret[$d_info['did']]))
                    {
                        $new_ret[$d_info['did']]['end_time'] = $item['end_time'];
                    }
                    else
                    {
                        $new_ret[$d_info['did']] = $item;
                    }
                }
            }
            $ret = array_values(Tool_Array::sortByField($new_ret, 'entity_name', 'asc'));
            $data['items'] = Tool_Array::sortByField($data['items'], 'did', 'asc');
            $delivery_time = $start_time;
        }
        $this->centerPoint = array(
            Conf_Warehouse::$LOCATION[$wid]['lng'],
            Conf_Warehouse::$LOCATION[$wid]['lat'],
        );
        $points[] = array(
            Conf_Warehouse::$LOCATION[$wid]['lng'],
            Conf_Warehouse::$LOCATION[$wid]['lat'],
            Conf_Warehouse::$WAREHOUSES[$wid] . '<br/>' . $delivery_time,
            'http://haocaisong.oss-cn-hangzhou.aliyuncs.com/icon/star_new.png',
        );
        $this->points = json_encode($points);

        $this->ret = json_encode($ret);
        $this->data = $data;
    }

    protected function outputBody()
    {
        $this->smarty->assign('ret', $this->ret);
        $this->smarty->assign('linepoints', $this->linepoints);
        $this->smarty->assign('data', $this->data);
        $this->smarty->assign('points', $this->points);
        $this->smarty->assign('host', ADMIN_HOST);
        $this->smarty->assign('center', json_encode($this->centerPoint));
        $this->smarty->display('logistics/logistics_track.html');
    }

    protected function getData($data)
    {
        $url = "http://api.map.baidu.com/trace/v2/track/gethistory?ak=KrnrrmGcxmZVGB4YcxHywmwjC79NMVqm&service_id=129616&is_processed=1&supplement_mode=driving";
        $parameter = '';
        foreach ($data as $key => $value)
        {
            $parameter .= '&' . $key . '=' . $value;
        }

        $url .= $parameter;

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

    public function time2date($seconds)
    {
        if ($seconds < 3600)
        {//不到一小时
            $format_time = gmstrftime('%M分%S秒', $seconds);
        }
        else if ($seconds < 86400)
        {//不到一天
            $format_time = gmstrftime('%H时%M分%S秒', $seconds);
        }
        else
        {
            $time = explode(' ', gmstrftime('%j %H %M %S', $seconds));//Array ( [0] => 04 [1] => 14 [2] => 14 [3] => 35 )
            $format_time = ($time[0] - 1) . '天' . $time[1] . '时' . $time[2] . '分' . $time[3] . '秒';
        }

        return $format_time;
    }
}

$app = new App('pri');
$app->run();
