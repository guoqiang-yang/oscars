<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $id;
    private $info;

    protected function checkAuth($permission = '')
    {
        parent::checkAuth('/activity/edit_case');
    }

    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->info = array(
            'title' => Tool_Input::clean('r', 'title', TYPE_STR),
            'cover' => Tool_Input::clean('r', 'cover', TYPE_STR),
            'status' => Tool_Input::clean('r', 'status', TYPE_UINT),
            'fid' => Tool_Input::clean('r', 'fid', TYPE_UINT),
            'house_style' => Tool_Input::clean('r', 'house_style', TYPE_UINT),
            'house_type' => Tool_Input::clean('r', 'house_type', TYPE_UINT),
            'house_space' => Tool_Input::clean('r', 'house_space', TYPE_UINT),
            'house_area' => Tool_Input::clean('r', 'house_area', TYPE_UINT),
            'description' => Tool_Input::clean('r', 'description', TYPE_STR),
            'index_sortby' => Tool_Input::clean('r', 'index_sortby', TYPE_UINT),
            'city_id' => Tool_Input::clean('r', 'city_id', TYPE_UINT),
        );
    }

    protected function main()
    {
        if (empty($this->id))
        {
            $this->info['suid'] = $this->_uid;
            $this->id = Case_Api::add($this->info);
        }
        else
        {
            Case_Api::update($this->id, $this->info);
        }
    }

    protected function outputPage()
    {
        $result = array('id' => $this->id);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App('pri');
$app->run();

