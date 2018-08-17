<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/9/22
 * Time: 下午3:52
 */
include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $aid;
    private $info;

    protected function checkAuth()
    {
        parent::checkAuth('/activity/add_article');
    }

    protected function getPara()
    {
        $this->aid = Tool_Input::clean('r', 'aid', TYPE_UINT);
        $this->info = array(
            'title' => Tool_Input::clean('r', 'title', TYPE_STR),
            'content' => Tool_Input::clean('r', 'content', TYPE_STR),
            'pic_url' => Tool_Input::clean('r', 'pic_url', TYPE_STR),
            'city_ids' =>  Tool_Input::clean('r', 'city_ids', TYPE_STR),
            'article_type'=> Tool_Input::clean('r', 'article_type', TYPE_INT),
        );
    }

    protected function checkPara()
    {
        if (empty($this->info['title']) || empty($this->info['content'])) {
            throw new Exception('参数不合法');
        }
    }

    protected function main()
    {
        if (empty($this->aid)) {
            $this->aid = Activity_Article_Api::add($this->info);
        }else {
            $this->aid = Activity_Article_Api::update($this->aid,$this->info);
        }
    }

    protected function outputPage()
    {
        $result = array('id' => $this->aid);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App('pri');
$app->run();