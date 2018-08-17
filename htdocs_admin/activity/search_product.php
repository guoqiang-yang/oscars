<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/9/12
 * Time: 下午2:09
 */
include_once ('../../global.php');

class App extends App_Admin_Page
{
    private $start;
    private $num = 20;
    private $total;
    private $keyword;

    protected function checkAuth()
    {
        parent::checkAuth('/activity/add_activity_flash');
    }

    protected function getPara()
    {
        $this->keyword = Tool_Input::clean('r', 'keyword', TYPE_STR);
        $this->start = Tool_Input::clean('r', 'start', TYPE_INT);
        $this->fid = Tool_Input::clean('r', 'fid', TYPE_INT);
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_INT);
        $this->url = Tool_Input::clean('r', 'url', TYPE_STR);
        $this->type = Tool_Input::clean('r', 'type', TYPE_UINT);
        $this->position= Tool_Input::clean('r', 'position', TYPE_UINT);
    }

    protected function main()
    {
        if ($this->keyword)
        {
            $res = Shop_Api::searchProduct($this->keyword, $this->start, $this->num, false);
            $this->products = $res['list'];
            $this->total = $res['total'];
        }
        $this->addFootJs(array('js/apps/flash_sale.js'));
    }

    protected function outputBody()
    {
        $app = '/activity/search_product.php?' . http_build_query(array('keyword' => $this->keyword, 'url' => $this->url, 'fid' => $this->fid, 'type' => $this->type, 'position' => $this->postion, 'sid' => $this->sid));
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('fid', $this->fid);
        $this->smarty->assign('products', $this->products);
        $this->smarty->assign('url', $this->url);
        $this->smarty->assign('position', $this->position);
        $this->smarty->assign('sid', $this->sid);
        $this->smarty->assign('type', $this->type);
        $this->smarty->assign('keyword', $this->keyword);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('city_list', Conf_City::$CITY);
        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->display('activity/search_product.html');
    }
}

$app = new App('pri');
$app->run();