<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/9/22
 * Time: 下午4:18
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
        $this->addFootJs(array('js/apps/article.js',));
    }

    protected function outputBody()
    {
        $showCityNames = $this->article['city_ids']==1? '全部': implode(',', array_values(Conf_City::getCityNameByIds($this->article['city_ids'])));
        $this->article['city_ids'] = $showCityNames;
        $this->smarty->assign('policy_type',Conf_Base::articlePolicyType());
        $this->smarty->assignRaw('article', $this->article);
        $this->smarty->display('activity/article_detail.html');
    }
}

$app = new App('pri');
$app->run();