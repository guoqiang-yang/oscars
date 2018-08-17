<?php
/**
 * Created by PhpStorm.
 * User: libaolong
 * Date: 2018/5/28
 * Time: 下午1:42
 */
include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $ips;
    private $res;

    protected function getPara()
    {
        $this->ips = Tool_Input::clean('r', 'ips', TYPE_ARRAY);
    }

    protected function checkAuth()
    {
        parent::checkAuth('/admin/admin_login_log');
    }

    protected function main()
    {
        if (!empty($this->ips))
        {
           $this->res = $this->getCityByIps($this->ips);
        }
    }

    protected function outputBody()
    {
        $response = new Response_Ajax();
        $response->setContent(array('res' => $this->res));
        $response->send();

        exit;
    }

    protected function getCityByIps($ips)
    {
        $ipCity = array();
        $ipCityMap = array();
        foreach ($ips as $ip)
        {
            list($id, $realIp) = explode('_', $ip);
            if (array_key_exists($realIp, $ipCityMap))
            {
                $ipCity[$id] = $ipCityMap[$realIp];
            } else {
                $url = "http://ip.taobao.com/service/getIpInfo.php?ip=$realIp";
                $ipJson = Tool_Http::get($url);
                $ipArr = json_decode($ipJson, true);
                $ipCityMap[$realIp] = $ipCity[$id] = array('country' => $ipArr['data']['country'], 'city' => $ipArr['data']['city'], 'isp' => $ipArr['data']['isp']);
            }
        }

        return $ipCity;
    }
}

$app = new App();
$app->run();