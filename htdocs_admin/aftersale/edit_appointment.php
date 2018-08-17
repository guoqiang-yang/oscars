<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $id;
    private $info;

    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
    }

    protected function main()
    {
        if (!empty($this->id))
        {
            $this->info = Forman_Api::getAppointment($this->id);
        }

        $this->addFootJs(array(
                             'js/apps/appointment.js',
                         ));
    }

    protected function outputBody()
    {
        $this->smarty->assign('house_style', Conf_Fit::getHouseStyle());
        $this->smarty->assign('house_type', Conf_Fit::getHouseType());
        $this->smarty->assign('house_space', Conf_Fit::getHouseSpace());
        $this->smarty->assign('house_area', Conf_Fit::getHouseArea());
        $this->smarty->assign('budget', Conf_Fit::getFitBudget());
        $this->smarty->assign('salers', Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_SALES_NEW, Conf_Base::STATUS_NORMAL));
        $this->smarty->assign('info', $this->info);
        $this->smarty->assignRaw('description', $this->info['description']);

        $this->smarty->display('aftersale/edit_appointment.html');
    }
}

$app = new App('pri');
$app->run();
