<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $id;
    private $info;

    protected function checkAuth($permission = '')
    {
        parent::checkAuth('/activity/edit_wiki');
    }

    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->info = array(
            'title' => Tool_Input::clean('r', 'title', TYPE_STR),
            'sub_title' => Tool_Input::clean('r', 'sub_title', TYPE_STR),
            'cover' => Tool_Input::clean('r', 'cover', TYPE_STR),
            'status' => Tool_Input::clean('r', 'status', TYPE_UINT),
            'fid' => Tool_Input::clean('r', 'fid', TYPE_UINT),
            'design' => Tool_Input::clean('r', 'design', TYPE_UINT),
            'fit_step' => Tool_Input::clean('r', 'fit_step', TYPE_UINT),
            'main_material' => Tool_Input::clean('r', 'main_material', TYPE_UINT),
            'other_material' => Tool_Input::clean('r', 'other_material', TYPE_UINT),
            'description' => Tool_Input::clean('r', 'description', TYPE_STR),
        );
    }

    protected function main()
    {
        if (empty($this->id))
        {
            $this->info['suid'] = $this->_uid;
            $this->id = Wiki_Api::add($this->info);
        }
        else
        {
            Wiki_Api::update($this->id, $this->info);
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

