<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $fid;
    private $info;

    protected function checkAuth($permission = '')
    {
        parent::checkAuth('/activity/edit_forman');
    }

    protected function getPara()
    {
        $this->fid = Tool_Input::clean('r', 'fid', TYPE_UINT);
        $this->info = array(
            'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
            'name' => Tool_Input::clean('r', 'name', TYPE_STR),
            'logo' => Tool_Input::clean('r', 'logo', TYPE_STR),
            'work_age' => Tool_Input::clean('r', 'work_age', TYPE_UINT),
            'birthplace' => Tool_Input::clean('r', 'birthplace', TYPE_STR),
            'address' => Tool_Input::clean('r', 'address', TYPE_STR),
            'workarea' => Tool_Input::clean('r', 'workarea', TYPE_STR),
            'status' => Tool_Input::clean('r', 'status', TYPE_UINT),
            'intro' => Tool_Input::clean('r', 'intro', TYPE_STR),
            'work_community' => Tool_Input::clean('r', 'work_community', TYPE_STR),
        );
    }

    protected function main()
    {
        if (empty($this->fid))
        {
            $this->info['suid'] = $this->_uid;
            $this->fid = Forman_Api::addForman($this->info);
        }
        else
        {
            Forman_Api::updateForman($this->fid, $this->info);
        }
    }

    protected function outputPage()
    {
        $result = array('fid' => $this->fid);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App('pri');
$app->run();

