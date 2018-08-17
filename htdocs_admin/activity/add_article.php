<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/9/22
 * Time: 下午2:08
 */
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $aid;

    protected function getPara()
    {
        $this->aid = Tool_Input::clean('r', 'aid', TYPE_UINT);
    }

    protected function main()
    {
        if (!empty($this->aid))
        {
            $this->article = Activity_Article_Api::getOne($this->aid);
        }
        $this->addFootJs(array(
                             'js/core/cate.js',
                             'js/core/FileUploader.js',
                             'js/core/imgareaselect.min.js',
                             'js/apps/uploadpic.js',
                             'js/apps/article.js',
                             'js/ueditor/ueditor.config.js',
                             'js/ueditor/ueditor.all.js',
                         ));
    }

    protected function outputBody()
    {
        $city_arr = Conf_City::$CITY;
        $this->smarty->assignRaw('article', $this->article);
        $this->smarty->assign('policy_type',Conf_Base::articlePolicyType());
        $this->smarty->assign('city', $city_arr);
        //$this->smarty->assignRaw('content',$this->article['content']);
        $this->smarty->display('activity/add_article.html');
    }
}

$app = new App('pri');
$app->run();