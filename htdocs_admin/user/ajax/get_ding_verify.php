<?php
/**
 * Created by PhpStorm.
 * User: libaolong
 * Date: 2018/6/28
 * Time: 上午9:52
 */
include_once('../../../global.php');

class CApp extends App_Admin_Ajax
{
    private $mobile;

    protected function getPara()
    {
        $this->mobile = Tool_Input::clean('r', 'mobile', TYPE_UINT);
    }

    protected function checkPara()
    {
        if(empty($this->mobile))
        {
            throw new Exception('请输入手机号！');
        }
    }

    protected function main()
    {
        $staff = Admin_Api::getStaffByMobile($this->mobile);

        if (!empty($staff['ding_id']))
        {
            $code = mt_rand(1000, 9999);
            session_start();
            $_SESSION["admin_ding_code"] = $code;
            Tool_DingTalk::sendTextMessage($staff['ding_id'], sprintf('您本次的验证码为：%d，如非本人操作，请忽略！', $code));
        }

        if (empty($staff['ding_id'])) {
            throw new Exception('钉钉ID为空，请联系技术人员！');
        }
    }

    protected function outputPage()
    {
        $result = array('uid' => $this->_uid);
        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new CApp("");
$app->run();
