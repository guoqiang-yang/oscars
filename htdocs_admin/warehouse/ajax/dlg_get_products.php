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
    private $conf;
    private $num = 100;
    private $total;
    private $products;
    private $brands;
    private $models;
    private $wid;

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

    protected function checkAuth()
    {
        parent::checkAuth('/warehouse/edit_in_order');
    }

    protected function checkPara()
    {
        if (empty($this->keyword))
        {
            if (empty($this->cate1))
            {
                $this->cate1 = 1;
            }
            if (empty($this->cate2))
            {
                $cate2List = Conf_Sku::$CATE2[$this->cate1];
                $this->cate2 = array_shift(array_keys($cate2List));
            }
        }
    }

    protected function main()
    {
        //订单信息
        $this->order = Warehouse_Api::getOrderInfo($this->oid);
        $this->wid = $this->order['info']['wid'];

        //产品信息
        if ($this->keyword)
        {
            $statusTag = Conf_Product::PRODUCT_STATUS_ONLINE | Conf_Product::PRODUCT_STATUS_OFFLINE;
            $res = Shop_Api::searchProduct($this->keyword, $this->start, $this->num, $statusTag, $this->wid);

            $this->_supplyOrderData($res);

            $this->products = $res['list'];
            $this->total = $res['total'];
        }
        else
        {
            $this->conf = array(
                'cate1' => $this->cate1,
                'cate2' => $this->cate2,
                'bid' => $this->bid,
                'mid' => $this->mid,
            );
            $statusTag = Conf_Product::PRODUCT_STATUS_ONLINE | Conf_Product::PRODUCT_STATUS_OFFLINE;
            $res = Shop_Api::getProductList($this->conf, $this->start, $this->num, $statusTag, $this->wid);

            $this->_supplyOrderData($res);

            $this->products = $res['list'];
            $this->total = $res['total'];

            // 品牌列表
            $this->brands = Shop_Api::getBrandList($this->cate2);
            // 型号列表
            $this->models = Shop_Api::getModelList($this->cate2);

            setcookie("_last_cate1_in", $this->cate1, time() + 86400 * 30, "/", Conf_Base::getAdminHost());
            setcookie('_last_cate2_in', $this->cate2, time() + 86400 * 30, '/', Conf_Base::getAdminHost());
            setcookie("_last_bid_in", $this->bid, time() + 86400 * 30, "/", Conf_Base::getAdminHost());
            setcookie('_last_mid_in', $this->mid, time() + 86400 * 30, '/', Conf_Base::getAdminHost());
        }
    }

    protected function outputPage()
    {
        if (empty($this->keyword)) //浏览
        {
            $this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
            $this->smarty->assign('cate2_list', Conf_Sku::$CATE2[$this->conf['cate1']]);
            $this->smarty->assign('search_conf', $this->conf);
            $this->smarty->assign('brands', $this->brands);
            $this->smarty->assign('models', $this->models);
        }

        $this->smarty->assign('search_products', $this->products);
        $this->smarty->assign('keyword', $this->keyword);
        $this->smarty->assign('order', $this->order);
        $html = $this->smarty->fetch('warehouse/dlg_product_list.html');

        $result = array('html' => $html);
        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }

    /**
     * 补充采购单，入库单数据.
     */
    private function _supplyOrderData(&$res)
    {
        $inOrderProducts = $this->order['products'][Conf_In_Order::SRC_COMMON];

        foreach ($res['list'] as &$_product)
        {
            $sid = $_product['sku']['sid'];
            if (array_key_exists($sid, $inOrderProducts))
            {
                $_product['_inorder']['num'] = $inOrderProducts[$sid]['num'];
                $_product['_inorder']['price'] = $inOrderProducts[$sid]['price'];
                $_product['_stockin']['num'] = $inOrderProducts[$sid]['_stock_in'];
            }
            else
            {
                $_product['_inorder']['num'] = 0;
                $_product['_inorder']['price'] = $_product['product']['purchase_price'] ? : $_product['product']['cost'];
                $_product['_stockin']['num'] = 0;
            }
        }
    }
}

$app = new App('pri');
$app->run();
