<?php
include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $cate2;
    private $mid;
    private $bid;
    private $models;

    protected function getPara()
    {
        $this->cate2 = Tool_Input::clean('r', 'cate2', TYPE_UINT);
        $this->mid = Tool_Input::clean('r', 'mid', TYPE_STR);
        $this->bid = Tool_Input::clean('r', 'bid', TYPE_UINT);
    }

    protected function main()
    {
        $this->models = Shop_Api::getModelList($this->cate2);
    }

    private function getSelectList()
    {
        $list = array();
        foreach ($this->models as $model)
        {
            $midArr = explode(',', $this->mid);
            $selected = in_array($model['mid'], $midArr) ? 1 : 0;
            $list[] = array(
                'id' => $model['mid'],
                'name' => $model['name'],
                'selected' => $selected
            );
        }

        return $list;
    }

    protected function outputPage()
    {
        $result = array(
            'list' => $this->getSelectList(),
        );

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App('pub');
$app->run();

