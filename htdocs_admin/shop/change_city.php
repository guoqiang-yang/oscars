<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $cityId;

    protected function getPara()
    {
        $this->cityId = Tool_Input::clean('r', 'city_id', TYPE_UINT);
    }

    protected function checkPara()
    {
//        if (isset($_SERVER['HTTP_REFERER']))
//        {
//            if (strpos($_SERVER['HTTP_REFERER'], 'order/edit_order'))
//            {
//                throw new Exception('order:change city error');
//            }
//        }
    }
    
    protected function main()
    {
        $cityId = City_Api::setCity($this->cityId);
        setcookie('shop_city_id', $cityId, 0, '/');

        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
}

$app = new App('pub');
$app->run();
