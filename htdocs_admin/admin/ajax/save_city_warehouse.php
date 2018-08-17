<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $suid;
    private $cities;
    private $wids;

    protected function checkAuth()
    {
        parent::checkAuth('/admin/edit_staff');
    }

    protected function getPara()
    {
        $this->suid = Tool_Input::clean('r', 'suid', TYPE_UINT);
        $this->cities = json_decode(Tool_Input::clean('r', 'cities', TYPE_STR));
        $this->wids = json_decode(Tool_Input::clean('r', 'wids', TYPE_STR));
    }

    protected function checkPara()
    {

    }

    protected function main()
    {
        $info = array(
            'cities' => implode(',', $this->cities),
            'wids' => implode(',', $this->wids),
        );
        
        Admin_Api::updateStaff($this->suid, $info);
    }

    protected function outputPage()
    {
        $result = array('suid' => $this->suid);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }
}

$app = new App('pri');
$app->run();