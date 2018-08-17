<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/9/22
 * Time: 上午11:13
 */
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $start;
    private $num = 20;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->aid = Tool_Input::clean('r', 'aid', TYPE_UINT);
    }

    protected function main()
    {
        $data = Activity_Article_Api::getList($this->aid, $this->start, $this->num);
        $data_list= $this->getArticleArr($data['list']);
        $this->list = $data_list;
        $this->total = $data['total'];
        $this->addFootJs(array('js/apps/article.js'));
    }

    protected function outputBody()
    {
        $app = '/activity/article_list.php?';
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('list', $this->list);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('policy_type',Conf_Base::articlePolicyType());
        $this->smarty->display('activity/article_list.html');
    }

    protected function getArticleArr($articleArr)
    {
        foreach ($articleArr as $key => $v)
        {
            $articleArr[$key]['city_ids'] = $v['city_ids']==1? '全部': implode(',', array_values(Conf_City::getCityNameByIds($v['city_ids'])));
            
        }
        
        return $articleArr;
    }

}

$app = new App('pri');
$app->run();