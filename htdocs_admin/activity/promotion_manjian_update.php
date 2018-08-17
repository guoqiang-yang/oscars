<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $id;
    private $info;
    private $mode;

    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->mode = Tool_Input::clean('r', 'mode', TYPE_STR);
    }

    protected function main()
    {
        if (0 != $this->id)
        {
            $this->info = Activity_Api::getPromotionManjianItem($this->id);
            if ($this->info['m_status'] > 3 && $this->mode != 'show')
            {
                throw new Exception($this->info['title'] . Conf_Activity::$AT_PROMOTION_STATUS_DESC[$this->info['m_status']] . ',不可以进行编辑！');
            }
            $this->info['stime'] = date('Y-m-d', strtotime($this->info['stime'])) . 'T' . date('H:i', strtotime($this->info['stime']));
            $this->info['etime'] = date('Y-m-d', strtotime($this->info['etime'])) . 'T' . date('H:i', strtotime($this->info['etime']));;
        }

        $this->addFootJs(array('js/apps/promotion_activity.js'));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $this->smarty->assign('info', $this->info);
        $this->smarty->assign('city_list', Conf_City::$CITY);
        $this->smarty->assign('user_list', Conf_User::$Activity_User_Types);
        $type_list = Conf_Activity::$AT_PROMOTION_TYPE_DESC;
        $this->smarty->assign('cate_list', Conf_Sku::$CATE1);
        $this->smarty->assign('is_readonly', ($this->info['m_status'] == 2 ? 'readonly' : ''));
        $this->smarty->assign('is_disabled', ($this->info['m_status'] == 2 ? 'disabled' : ''));
        if ($this->mode == 'show')
        {
            $this->smarty->assign('str_list', array(
                0 => '不包含',
                1 => '包含'
            ));
            $this->smarty->assign('brand_list',Shop_Api::getAllBrands());
            $this->smarty->assign('type_list', $type_list);
            $this->smarty->display('promotion/manjian_detail.html');
        }
        else
        {
            unset($type_list[Conf_Activity::AT_PROMOTION_TYPE_DOUBLE]);
            $this->smarty->assign('type_list', $type_list);
            $this->smarty->display('promotion/manjian_update.html');
        }
    }
}

$app = new App('pri');
$app->run();
