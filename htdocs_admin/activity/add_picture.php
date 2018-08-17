<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    protected function getPara()
    {
    }

    protected function main()
    {
        $this->addFootJs(array(
                             'js/core/cate.js',
                             'js/apps/picture.js',
                             'js/core/FileUploader.js',
                             'js/core/imgareaselect.min.js',
                             'js/apps/uploadpic.js'
                         ));
    }

    protected function outputBody()
    {
        $this->smarty->assign('platform_list', Conf_Picture::$PLATFORM);
        $this->smarty->assign('type_list', Conf_Picture::$TYPE);
        $this->smarty->assign('city_list', Conf_City::$CITY);
        $this->smarty->display('activity/add_picture.html');
    }
}

$app = new App('pri');
$app->run();
