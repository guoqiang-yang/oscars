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
            $this->info = Forman_Api::getForman($this->id);
        }

        $this->addFootJs(array(
                             'js/ueditor/ueditor.config.js',
                             'js/ueditor/ueditor.all.js',
                             'js/core/FileUploader.js',
                             'js/core/imgareaselect.min.js',
                             'js/apps/forman.js',
                         ));
        $this->addCss(array(
                          'css/imgareaselect-default.css',
                      ));
    }

    protected function outputBody()
    {
        $this->smarty->assign('info', $this->info);

        $this->smarty->display('activity/edit_forman.html');
    }
}

$app = new App('pri');
$app->run();
