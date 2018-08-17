<?php
/**
 * Created by PhpStorm.
 * User: zouliangwei
 * Date: 16/11/9
 * Time: 下午1:57
 */

include_once("../../global.php");

class App extends App_Admin_Page
{
    // cgi参数
    private $start;
    private $num = 20;
    private $total;
    private $list;
    private $searchConf;
    private $level;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->level = Tool_Input::clean('r', 'level', TYPE_UINT);
        $this->searchConf = array(
            'oid' => Tool_Input::clean('r', 'oid', TYPE_UINT),
            'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
            'city_id' => Tool_Input::clean('r', 'city_id', TYPE_UINT),
            'level' => $this->level,
        );
    }

    protected function main()
    {
        $data = Comment_Api::getList($this->searchConf, $this->start, $this->num);
        $this->list = $data['list'];
        $this->total = $data['total'];

        if (!empty($this->list))
        {
            $oids = Tool_Array::getFields($this->list, 'oid');
            $orders = Order_Api::getBulk($oids, array('oid', 'wid', 'price', 'customer_carriage', 'freight', 'privilege'));
            $cids = Tool_Array::getFields($this->list, 'cid');
            $customers = Crm2_Api::getCustomers($cids);
            foreach ($this->list as &$item)
            {
                $oid = $item['oid'];
                $order = $orders[$oid];
                $cid = $item['cid'];
                $customer = $customers[$cid];
                $item['city'] = Conf_City::$CITY[$item['city_id']];
                $item['wname'] = Conf_Warehouse::$WAREHOUSES[$order['wid']];
                $item['cname'] = $customer['name'];
                $item['price'] = round(($order['price'] + $order['customer_carriage'] + $order['freight'] - $order['privilege']) / 100, 2);
                $item['level_desc'] = Conf_Comment::$COMMENT_DESC[$item['level']];
                $item['tag_desc'] = '无';
                $item['comment'] = $item['note'] ? $item['note'] : '无';
                if (!empty($item['tag']))
                {
                    $tagsDesc = array();
                    $tags = explode(',', $item['tag']);
                    foreach ($tags as $tag)
                    {
                        $tagsDesc[] = Conf_Comment::$COMMENT_TAGS[$item['level']][$tag];
                    }

                    $item['tag_desc'] = implode(',', $tagsDesc);
                }
            }
        }
    }

    protected function outputBody()
    {
        $app = '/activity/customer_comment.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);
        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('list', $this->list);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('city_list', Conf_City::$CITY);
        $this->smarty->assign('level', $this->level);

        $this->smarty->display('activity/customer_comment.html');
    }
}

$app = new App('pri');
$app->run();