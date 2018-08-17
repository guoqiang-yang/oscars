<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $id;
    private $info;

    protected function checkAuth($permission = '')
    {
        parent::checkAuth('/aftersale/edit_appointment');
    }

    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->info = array(
            'city' => Tool_Input::clean('r', 'city', TYPE_STR),
            'district' => Tool_Input::clean('r', 'district', TYPE_STR),
            'area' => Tool_Input::clean('r', 'area', TYPE_STR),
            'name' => Tool_Input::clean('r', 'name', TYPE_STR),
            'mobile' => Tool_Input::clean('r', 'mobile', TYPE_STR),
            'house_style' => Tool_Input::clean('r', 'house_style', TYPE_UINT),
            'house_type' => Tool_Input::clean('r', 'house_type', TYPE_UINT),
            'house_area' => Tool_Input::clean('r', 'house_area', TYPE_UINT),
            'budget' => Tool_Input::clean('r', 'budget', TYPE_UINT),
            'fit_time' => Tool_Input::clean('r', 'fit_time', TYPE_STR),
            'note' => Tool_Input::clean('r', 'note', TYPE_STR),
            'hc_note' => Tool_Input::clean('r', 'hc_note', TYPE_STR),
            'saler_suid' => Tool_Input::clean('r', 'saler_suid', TYPE_UINT),
            'step' => Tool_Input::clean('r', 'step', TYPE_UINT),
            'case_id' => Tool_Input::clean('r', 'case_id', TYPE_UINT),
            'fid' => Tool_Input::clean('r', 'fid', TYPE_UINT),
        );
    }

    protected function main()
    {
        if (empty($this->id))
        {
            $this->id = Forman_Api::appointment($this->info);
        }
        else
        {
            Forman_Api::updateAppointment($this->id, $this->info);
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

