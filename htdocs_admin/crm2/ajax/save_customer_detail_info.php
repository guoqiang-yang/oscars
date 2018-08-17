<?php
/**
 * Created by PhpStorm.
 * User: leeblong
 * Date: 2018/3/15
 * Time: 下午4:28
 */
include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
    protected $cid;
    protected $customer;
    protected $msg;

    protected function getPara()
    {
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
        $this->customer = array(
            'age' => Tool_Input::clean('r', 'age', TYPE_UINT),
            'birthday' => Tool_Input::clean('r', 'birthday', TYPE_STR),
            'work_age' => Tool_Input::clean('r', 'work_age', TYPE_UINT),
            'interest' => Tool_Input::clean('r', 'interest', TYPE_STR),
            'address' => Tool_Input::clean('r', 'address', TYPE_STR),
            'work_area' => Tool_Input::clean('r', 'work_area', TYPE_STR),
            'character_tag' => Tool_Input::clean('r', 'character_tag', TYPE_STR),
            'weixin' => Tool_Input::clean('r', 'weixin', TYPE_STR),
            'qq' => Tool_Input::clean('r', 'qq', TYPE_UINT),
            'email' => Tool_Input::clean('r', 'email', TYPE_STR),
        );
    }

    protected function checkAuth()
    {
        parent::checkAuth();
    }

    protected function main()
    {
        $cusDao = new Data_Dao('t_customer');
        $result = $cusDao->update($this->cid, $this->customer);

        if ($result)
        {
            $this->msg = '保存成功！';
        }
    }

    protected function outputBody()
    {
        $result = array('msg' => $this->msg);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App();
$app->run();