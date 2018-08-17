<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $id;
    private $info;

    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
    }

    protected function main()
    {
        if (!empty($this->id))
        {
            $this->info = Case_Api::get($this->id);
        }

        $this->addFootJs(array(
                             'js/ueditor/ueditor.config.js',
                             'js/ueditor/ueditor.all.js',
                             'js/core/FileUploader.js',
                             'js/core/imgareaselect.min.js',
                             'js/apps/case.js',
                         ));
        $this->addCss(array(
                          'css/imgareaselect-default.css',
                      ));
    }

    protected function outputBody()
    {
        $this->smarty->assign('house_style', Conf_Fit::getHouseStyle());
        $this->smarty->assign('house_type', Conf_Fit::getHouseType());
        $this->smarty->assign('house_space', Conf_Fit::getHouseSpace());
        $this->smarty->assign('house_area', Conf_Fit::getHouseArea());
        $this->smarty->assign('info', $this->info);
        $this->smarty->assign('city_list', Conf_City::$CITY);
        $this->smarty->assign('TOC_H5_MAIN_HOST', TOC_H5_MAIN_HOST);
        $this->smarty->assignRaw('description', $this->info['description']);

        $this->smarty->display('activity/edit_case.html');
    }
}

$app = new App('pri');
$app->run();
