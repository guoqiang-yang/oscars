<?php
include_once('../../../global.php');

class CApp extends App_Admin_Ajax
{
    private $mobile;
    private $password;
    private $return_url;
    private $picture;
    private $dingCode;
    private $type;

    protected function getPara()
    {
        $this->mobile = Tool_Input::clean('r', 'mobile', TYPE_UINT);
        $this->password = Tool_Input::clean('r', 'password', TYPE_STR);
        $this->return_url = Tool_Input::clean('r', 'return_url', TYPE_STR);
        $this->picture = Tool_Input::clean('r', 'picture', TYPE_STR);
        $this->dingCode = Tool_Input::clean('r', 'ding_code', TYPE_UINT);
        $this->type = Tool_Input::clean('r', 'type', TYPE_STR);
    }

    protected function checkPara()
    {
        $ip = Tool_Ip::getClientIP();
        $_logInfo = array($ip, $this->mobile, 'sa', 'login-require');
        Tool_Log::addFileLog('tmp_login.log', implode("\t", $_logInfo));

        if (empty($this->type))
        {
            throw new Exception('参数错误！');
        }
        if (empty($this->mobile))
        {
            throw new Exception('请输入手机号！');
        }
        $outsideMobile = array(15210953648);
        $flag = in_array($this->mobile, $outsideMobile) && BASE_HOST != '.haocaisong.cn' ? true : false;
        session_start();
        if ($this->type == 'ding' && !$flag)
        {
            if (empty($this->picture))
            {
                throw new Exception('请输入验证码！');
            }
            if(strtolower($this->picture) != $_SESSION['admin_picture_code'])
            {
                throw new Exception('图片验证码错误!');
            }
            if (empty($_SESSION['admin_ding_code']))
            {
                throw new Exception('请获取钉钉验证码！');
            }
            if (empty($this->dingCode))
            {
                throw new Exception('请输入钉钉验证码！');
            }
            if ($this->dingCode != $_SESSION['admin_ding_code'])
            {
                throw new Exception('钉钉验证码错误!');
            }
        }

        if ($this->type == 'pwd' && !$flag)
        {
            if (empty($this->picture))
            {
                throw new Exception('请输入验证码！');
            }
            if(strtolower($this->picture) != $_SESSION['admin_picture_code'])
            {
                $_logInfo = array($ip, $this->mobile, 'sa', $this->picture, $_SESSION['admin_picture_code'], 'login-verify-error');
                Tool_Log::addFileLog('tmp_login.log', implode("\t", $_logInfo));
                
                if (ENV != 'dev')
                {
                    throw new Exception('图片验证码错误!');
                }
            }
            
        }
    }

    protected function main()
    {
        $_SESSION['admin_ding_code'] = '';
        if ($this->type == 'pwd')
        {
            $ret = Admin_Auth_Api::login($this->mobile, $this->password, 'sa');
        }

        if ($this->type == 'ding')
        {
            $ret = Admin_Auth_Api::loginByDing($this->mobile, 'sa');
        }
        $this->_uid = $ret['uid'];
        $this->setSessionVerifyCookie($ret['verify'], Conf_Base::WEB_TOKEN_EXPIRED);

        $user = Admin_Api::getStaff($this->_uid);
        if (!empty($user['city_id']))
        {
            $cityList = explode(',', $user['city_id']);
            if (count($cityList) > 1)
            {
                //默认北京，可以选
            }
            else
            {
                //是哪就是哪，不能选
                setcookie('shop_city_id', $cityList[0], 0, '/');
            }
        }
        else
        {
            //默认北京，可以选
        }
        $ipInfo = $this->getIpInfo();
        $this->remindStaffUser($user, $ipInfo);
        
        $ip = Tool_Ip::getClientIP();
        $_logInfo = array($ip, $this->mobile, 'login-succ');
        Tool_Log::addFileLog('tmp_login.log', implode("\t", $_logInfo));
    }

    protected function outputPage()
    {
        if (empty($this->return_url))
        {
            $this->return_url = Conf_Admin_Page::getFirstPage($this->_uid, $this->_user);
        }

        $result = array('uid' => $this->_uid, 'return_url' => $this->return_url);
        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }

    /**
     * 获取登录IP信息
     *
     * @author libaolong
     * @return mixed
     */
    protected function getIpInfo()
    {
        $ip = Tool_Ip::getClientIP();
        $url = "http://ip.taobao.com//service/getIpInfo.php?ip=$ip";
        $citys = array ('北京','天津','河北','山西','内蒙古','辽宁','吉林','黑龙江','上海','江苏','浙江','安徽','福建','江西','山东','河南','湖北','湖南','广东','广西','海南','重庆','四川','贵州','云南','西藏','陕西','甘肃','青海','宁夏','新疆');
        $isps = array('阿里云', '腾讯云', '华为云', '百度云', '京东云');

        $ipJson = Tool_Http::get($url);
        $ipArr = json_decode($ipJson, true);
        if (($ipArr['data']['country'] != '中国' || in_array($ipArr['data']['isp'], $isps) || !in_array($ipArr['data']['region'], $citys)) && $ipArr['data']['isp'] != '内网IP' && BASE_HOST == '.haocaisong.cn')
        {
            $info = $ipArr['data']['country'].$ipArr['data']['region'].$ipArr['data']['city'].$ipArr['data']['isp'];
            Tool_DingTalk::sendTextMessage('02012515227498', sprintf('IP:%s, %s', $ip, $info));
        }

        return $ipArr;
    }

    /**
     * 登陆后钉钉提醒用户
     *
     * @author libaolong
     * @param $user
     * @param $ipInfo
     */
    protected function remindStaffUser($user, $ipInfo)
    {
        if (!empty($user['ding_id']) && BASE_HOST == '.haocaisong.cn')
        {
            $info = $ipInfo['data']['country'].$ipInfo['data']['region'].$ipInfo['data']['city'];
            Tool_DingTalk::sendTextMessage($user['ding_id'], sprintf('您于%s登录后台管理系统，登录地点：%s，如非本人操作，请及时更改密码！', date('Y-m-d H:i:s'), $info));
        }
    }
}

$app = new CApp("");
$app->run();
