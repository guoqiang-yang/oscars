<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $oid;
    private $cate1;
    private $cate2;
    private $bid;
    private $mid;
    private $start;
    private $keyword;
    private $order;
    private $orderProducts;
    private $conf;
    /* 为避免商品过多每次查询限制30 */
    private $num = 30;
    private $total;
    private $products;
    private $brands;
    private $models;

    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->keyword = Tool_Input::clean('r', 'keyword', TYPE_STR);
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);

        if (empty($this->keyword))
        {
            $href = Tool_Input::clean('r', 'href', TYPE_STR);
            $href = trim($href, "?");
            parse_str($href, $paras);
            $this->cate1 = $paras['cate1'] ? $paras['cate1'] : 0;
            $this->cate2 = $paras['cate2'] ? $paras['cate2'] : 0;
            $this->bid = $paras['bid'] ? $paras['bid'] : 0;
            $this->mid = $paras['mid'] ? $paras['mid'] : 0;
        }
    }

    protected function checkPara()
    {
        if (empty($this->keyword))
        {
            if (empty($this->cate1))
            {
                $this->cate1 = Tool_Input::clean('c', '_last_cate1', TYPE_UINT);
                if (empty($this->cate1))
                {
                    $this->cate1 = 1;
                }
            }
            if (empty($this->cate2))
            {
                $cate2List = Conf_Sku::$CATE2[$this->cate1];
                $this->cate2 = array_shift(array_keys($cate2List));
            }
        }
    }

    protected function checkAuth()
    {
        parent::checkAuth('/order/edit_order');
    }

    protected function main()
    {
        //订单信息
        if ($this->oid > 0)
        {
            $this->order = Order_Api::getOrderInfo($this->oid);
            $orderProducts = Order_Api::getOrderProducts($this->oid);
            $this->orderProducts = Privilege_Api::getRealBuyProducts($orderProducts['products'],$this->oid, $tmp_amount);
        }
        else
        {
            $this->order = array();
        }

        //产品信息
        if ($this->keyword)
        {
            $statusTag = Conf_Product::PRODUCT_STATUS_ONLINE;
            $cityId = !empty($this->order['city_id'])? $this->order['city_id']: 0;
            $res = Shop_Api::searchProduct($this->keyword, $this->start, $this->num, $statusTag, 0, $cityId);
            $this->products = $res['list'];
            $this->total = $res['total'];
        }
        
    }

    protected function outputPage()
    {
        /* 向后兼容，避免商品过多添加分页 */
        $pageHtml = Str_Html::getJsPagehtml2($this->start, $this->num, $this->total, "btn btn-primary _j_order_search_product");
        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('keyword', $this->keyword);
        $this->smarty->assign('search_products', $this->products);
        $this->smarty->assign('keyword', $this->keyword);
        $this->smarty->assign('order', $this->order);
        $this->smarty->assign('order_products', $this->orderProducts);
        $html = $this->smarty->fetch('order/dlg_product_list.html');
        $result = array('html' => $html);
        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }

    private static function sortProducts($a, $b)
    {
        if ($a['product']['frequency'] == $b['product']['frequency'])
        {
            return 0;
        }

        return $a['product']['frequency'] > $b['product']['frequency'] ? -1 : 1;
    }
}

$app = new App('pri');
$app->run();