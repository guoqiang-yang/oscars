<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $id;
    private $mode;
    private $info;

    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->mode = Tool_Input::clean('r', 'mode', TYPE_STR);
    }

    protected function main()
    {
        if (0 != $this->id)
        {
            $this->info = Activity_Api::getCouponItem($this->id);
            if ($this->info['m_status'] > 3 && $this->mode != 'show')
            {
                throw new Exception($this->info['title'] . Conf_Activity::$AT_PROMOTION_STATUS_DESC[$this->info['m_status']] . ',不可以进行编辑！');
            }
        }

        $this->addFootJs(array('js/apps/promotion_coupon.js'));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $this->smarty->assign('info', $this->info);
        $this->smarty->assign('is_readonly', ($this->info['m_status'] == 2 ? 'readonly' : ''));
        $this->smarty->assign('is_disabled', ($this->info['m_status'] == 2 ? 'disabled' : ''));
        $type_list = Conf_Activity::$AT_PROMOTION_TYPE_DESC;
        unset($type_list[Conf_Activity::AT_PROMOTION_TYPE_DOUBLE]);
        $this->smarty->assign('type_list', $type_list);
        $this->smarty->assign('coupon_type_list', Conf_Coupon::$couponName);
        if ($this->mode == 'show')
        {
            $this->smarty->assign('str_list', array(
                0 => '不包含',
                1 => '包含'
            ));
            $this->smarty->display('promotion/coupon_detail.html');
        }
        else
        {
            $this->smarty->display('promotion/coupon_update.html');
        }
    }
}

$app = new App('pri');
$app->run();
