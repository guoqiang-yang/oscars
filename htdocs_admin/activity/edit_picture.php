<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $id;
    private $pictureInfo;

    protected function checkAuth()
    {
        parent::checkAuth('/activity/add_picture');
    }

    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
    }

    protected function main()
    {
        $picture = Activity_Api::getpictureInfo($this->id);

        $this->pictureInfo = $picture[0];
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
        $this->smarty->assign('picture', $this->pictureInfo);
        $this->smarty->assign('platform_list', Conf_Picture::$PLATFORM);
        $this->smarty->assign('type_list', Conf_Picture::$TYPE);
        $this->smarty->assign('city_list', Conf_City::$CITY);

        $this->smarty->display('activity/edit_picture.html');
    }
}

$app = new App('pri');
$app->run();
