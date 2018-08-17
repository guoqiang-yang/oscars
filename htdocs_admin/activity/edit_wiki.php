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
            $this->info = Wiki_Api::get($this->id);
        }

        $this->addFootJs(array(
                             'js/ueditor/ueditor.config.js',
                             'js/ueditor/ueditor.all.js',
                             'js/core/FileUploader.js',
                             'js/core/imgareaselect.min.js',
                             'js/apps/wiki.js',
                         ));
        $this->addCss(array(
                          'css/imgareaselect-default.css',
                      ));
    }

    protected function outputBody()
    {
        $this->smarty->assign('design', Conf_Fit::getDesign());
        $this->smarty->assign('fit_step', Conf_Fit::getFitStep());
        $this->smarty->assign('main_material', Conf_Fit::getMainMaterial());
        $this->smarty->assign('other_material', Conf_Fit::getOtherMaterial());
        $this->smarty->assign('info', $this->info);
        $this->smarty->assign('TOC_H5_MAIN_HOST', TOC_H5_MAIN_HOST);
        $this->smarty->assignRaw('description', $this->info['description']);

        $this->smarty->display('activity/edit_wiki.html');
    }
}

$app = new App('pri');
$app->run();
