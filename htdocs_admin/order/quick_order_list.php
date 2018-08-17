<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/10/18
 * Time: 上午11:09
 */
include_once ('../../global.php');

class App extends App_Admin_Page
{
    // cgi参数
    private $start;
    private $searchConf;
    // 中间结果
    private $refunds;
    private $num = 20;
    private $total;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->searchConf = array(
            'oid' => Tool_Input::clean('r', 'oid', TYPE_UINT),
            'contact_phone' => Tool_Input::clean('r', 'contact_phone', TYPE_STR),
            'contact_name' => Tool_Input::clean('r', 'contact_name', TYPE_STR),
            'status' => Tool_Input::clean('r', 'status', TYPE_UINT),
            'from_date' => Tool_Input::clean('r', 'start_time', TYPE_STR),
            'end_date' => Tool_Input::clean('r', 'end_time', TYPE_STR),
            'sale_id' => Tool_Input::clean('r', 'sale_id', TYPE_UINT),
            'ensure_id' => Tool_Input::clean('r', 'ensure_id', TYPE_UINT),
            'platform' => Tool_Input::clean('r', 'platform', TYPE_UINT),
        );

        if ($this->searchConf['status'] == 1)
        {
            $this->searchConf['ensure_status'] = 0;
        }
        else if ($this->searchConf['status'] == 2)
        {
            $this->searchConf['ensure_status'] = 1;
        }


        if (!empty($this->searchConf['from_date']))
        {
            $this->searchConf['from_date'] = str_replace('T',' ',$this->searchConf['from_date']).':00';

        }

        if (!empty($this->searchConf['end_date']))
        {
            $this->searchConf['end_date'] = str_replace('T',' ',$this->searchConf['end_date']).':00';
        }
    }

    protected function main()
    {
        $this->data = Order_Quick_Api::getList($this->searchConf, $this->start, $this->num);
        $platform = Conf_Activity_Flash_Sale::$PALTFORM;
        unset($platform[3]);
        $this->platform = $platform;
        $this->cs = Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_CS);
        $this->sales = Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_SALES_NEW);
        $this->list = $this->data['list'];
        $this->total = $this->data['total'];
        //检查权限
        //$cids = Tool_Array::getFields($this->cs,'suid');

        foreach ($this->list as &$dv) {
            foreach ($this->sales as $sv) {
                if ($dv['sale_id'] == $sv['suid']) {
                    $dv['_sale'] = $sv['name'];
                }
            }
            foreach ($this->cs as  $cv) {
                if ($dv['ensure_id'] == $cv['suid']) {
                    $dv['_ensure'] = $cv['name'];
                }
            }

        }

        if (!empty($this->searchConf['from_date']))
        {
            $this->searchConf['from_date'] = substr(str_replace(' ','T',$this->searchConf['from_date']),0,-3);

        }

        if (!empty($this->searchConf['end_date']))
        {
            $this->searchConf['end_date'] = substr(str_replace(' ','T',$this->searchConf['end_date']),0,-3);
        }
        $this->addFootJs(array('js/apps/quick_order.js'));
        $this->addFootJs(array('js/layer/layer.js'));
        $this->addFootJs(array('js/jquery.rotate.js'));

    }

    protected function outputBody()
    {
        $app = '/order/quick_order_list.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('list', $this->list);
        $this->smarty->assign('platform', $this->platform);
        $this->smarty->assign('sales', $this->sales);
        $this->smarty->assign('cs', $this->cs);
        $this->smarty->assign('searchConf', $this->searchConf);


        $this->smarty->display('order/quick_order_list.html');
    }
}

$app = new App('pri');
$app->run();