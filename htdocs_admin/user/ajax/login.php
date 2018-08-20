<?php
include_once('../../../global.php');

class CApp extends App_Admin_Ajax
{
    private $mobile;
    private $password;
    private $return_url;
    private $picture;
    
    private $loginRes;

    protected function getPara()
    {
        $this->mobile = Tool_Input::clean('r', 'mobile', TYPE_UINT);
        $this->password = Tool_Input::clean('r', 'password', TYPE_STR);
        $this->return_url = Tool_Input::clean('r', 'return_url', TYPE_STR);
        $this->picture = Tool_Input::clean('r', 'picture', TYPE_STR);
        $this->dingCode = Tool_Input::clean('r', 'ding_code', TYPE_UINT);
    }

    protected function checkPara()
    {
        if (empty($this->mobile))
        {
            throw new Exception('请输入手机号！');
        }
        
        session_start();
        
        if (empty($this->picture))
        {
            throw new Exception('请输入验证码！');
        }
        if(strtolower($this->picture) != $_SESSION['admin_picture_code'])
        {
            throw new Exception('图片验证码错误!');
        }
    }

    protected function main()
    {
        $this->loginRes = Admin_Auth_Api::login($this->mobile, $this->password, 'sa');
        
        $currCityId = $this->loginRes['user']['_city_ids'][0];
        $waitSetCookies = array(
            Conf_Base::COKEY_VERIFY_SA  => $this->loginRes['verify'],
            Conf_Base::COKEY_SUID_SA    => $this->loginRes['uid'],
            Conf_Base::COKEY_CITY_SA    => $currCityId,
        );
        
        $this->setCookie4LoginSucc($waitSetCookies, Conf_Base::WEB_TOKEN_EXPIRED);
    }

    protected function outputPage()
    {
        if (empty($this->return_url))
        {
            $this->return_url = Conf_Admin_Page::getFirstPage($this->loginRes['uid'], $this->loginRes['user']);
        }
        
        $result = array('uid' => $this->loginRes['uid'], 'return_url' => $this->return_url);
        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        
        //exit;
    }

}

$app = new CApp("");
$app->run();
