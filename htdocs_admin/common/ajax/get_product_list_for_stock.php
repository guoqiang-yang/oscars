<?php
/**
 *
 *  添加商品模块
 *  1、添加js文件    $this->addFootJs(array('js/apps/show_product_common.js'));
 *  2、添加模板文件
 *      (1)、弹框模板
 *      需要弹框的模板页，添加下面的代码，params是一个数组，这个数组的每个元素都是添加商品时需要传到后端的数据
 *      {{include "common/block_add_product_for_stock.html"}}
 *      <script>
 *          $('document').ready(function(){
 *              var params = ['sid', 'num', 'price'];
 *              add_product_dlg.init(params);
 *          });
 *      </script>
 *      (2)、button添加id、data-objtype、data-objid
 *      <button type="button" class="btn btn-default" id="show_product_common" data-objid="{{$shift_info.ssid}}" data-objtype="1" style="margin-left:16px;">添加商品</button>
 *      (3)、/template_admin/common/block_product_list_for_stock.html 添加商品列表的模板
 *      每个商品所在的里面必须有一个sid的隐藏域，用来存sid
 *      <tr class="_j_product_item">
 *          <input type="hidden" name="sid" value="{{$product.sku.sid}}">
 *          ...
 *      </tr>
 *  3、ajax
 *      (1)、/common/ajax/get_product_list_for_stock.php 添加查询商品的方法
 *      (2)、/common/ajax/add_product_for_stock.php 添加 添加商品的权限和添加商品的方法
 *
 */


include_once('../../../global.php');

Class App extends App_Admin_Ajax
{
    private $num = 10;
    private $start;
    private $keyword;
    private $total;

    private $conf;
    private $products;
    private $objId;
    private $objType;
    private $wid;
    private $orderProducts;

    protected function getPara()
    {
        $this->objType = Tool_Input::clean('r', 'obj_type', TYPE_UINT);
        $this->objId = Tool_Input::clean('r', 'obj_id', TYPE_UINT);
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->keyword = Tool_Input::clean('r', 'keyword', TYPE_STR);
    }

    protected function checkPara()
    {
        if (empty($this->keyword))
        {
            throw new Exception('请输入搜索关键字！');
        }
        if (empty($this->objId) || empty($this->objType))
        {
            throw new Exception('错误使用：请联系技术人员！');
        }
    }

    protected function main()
    {
        switch ($this->objType)
        {
            case 1:     //调拨单
                $info = Warehouse_Api::getStockShiftInfo($this->objId);
                $this->orderProducts = Tool_Array::list2Map($info['products'], 'sid');
                $this->wid = $info['src_wid'];
                $this->_getProductsForStock();
                break;
            case 2:     //其他出库单
                $info = Warehouse_Api::getOtherStockOutOrderDetail($this->objId);
                $this->orderProducts = Tool_Array::list2Map($info['products'], 'sid');
                $this->wid = $info['wid'];
                if (!empty($info) && $this->objType == 2 && $info['order_type'] == Conf_Stock::OTHER_STOCK_ORDER_TYPE_OUT && $info['type'] == Conf_Stock::OTHER_STOCK_OUT_TYPE_BROKEN)        //报损
                {
                    $this->_getProductsForStockBroken();
                }
                else        //其他单据
                {
                    $this->_getProductsForStock();
                }
                break;
            case 3:     //其他入库单
                $info = Warehouse_Api::getOtherStockOutOrderDetail($this->objId);
                $this->orderProducts = Tool_Array::list2Map($info['products'], 'sid');
                $this->wid = $info['wid'];
                $this->_getProductsForStock();
                break;
            case 4:     //采购单（普采部分）
                $info = Warehouse_Api::getOrderInfo($this->objId);
                $this->orderProducts = $info['products'][Conf_In_Order::SRC_COMMON];
                $this->wid = $info['info']['wid'];
                $this->_getProductsForInOrder($info);
                break;
            default:
                $this->orderProducts = array();
                $this->wid = 0;
                break;
        }

        if (empty($this->wid))
        {
            throw new Exception('单据异常：请通知技术人员处理！');
        }
    }

    protected function outputBody()
    {
        $app = 'change_product_list_page';
        $pageHtml = Str_Html::getJsPagehtml2($this->start, $this->num, $this->total, $app);
        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('search_conf', $this->conf);
        $this->smarty->assign('search_products', $this->products);
        $this->smarty->assign('keyword', $this->keyword);
        $this->smarty->assign('obj_type', $this->objType);
        $this->smarty->assign('managing_modes', Conf_Base::getManagingModes());

        $html = $this->smarty->fetch('common/block_product_list_for_stock.html');

        $result = array('html' => $html);
        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }
    
    //调拨单、其他出库单、其他入库单
    private function _getProductsForStock()
    {
        $this->conf = array('keyword' => $this->keyword);
        $order = 'order by sid desc';
        $res = Warehouse_Api::getProductsStockByCates($this->conf, $this->wid, $order, $this->start, $this->num);
        $this->products = $res['data'];
        $this->total = $res['total'];
        foreach ($this->products as &$_product)
        {
            if (array_key_exists($_product['sid'], $this->orderProducts))
            {
                $_product['has_selected'] = true;
                $_product['num'] = $this->orderProducts[$_product['sid']]['num'];
            }
            else
            {
                $_product['has_selected'] = false;
                $_product['num'] = 0;
            }
        }
    }

    //报损单
    private function _getProductsForStockBroken()
    {
        $this->conf = array('keyword' => $this->keyword);

        // 查询sku
        $ss = new Shop_Sku();
        $total = 0;
        if (isset($this->conf['keyword']) && !empty($this->conf['keyword']))
        {
            $list = $ss->search($this->conf['keyword'], $total, 0, 0);
        }
        else
        {
            $list = $ss->getList($this->conf, $total, 0, 0);
        }
        $sids = Tool_Array::getFields($list, 'sid');

        $products = array();
        if (!empty($sids))
        {
            $wl = new Warehouse_Location();
            $where = array('sid' => $sids, 'location' => Conf_Warehouse::$Virtual_Flags[Conf_Warehouse::VFLAG_DAMAGED]['flag'], 'wid' => $this->wid);
            $locations = Tool_Array::list2Map($wl->getRawWhere($where, $this->start, $this->num), 'sid');
            foreach($locations as $_sid => $_loc)
            {
                $products[$_sid] = $list[$_sid];
                $products[$_sid]['_stock']['num'] = '-';
                $products[$_sid]['_stock']['occupied'] = '-';
                $products[$_sid]['_stock']['damaged_num'] = '-';
                $products[$_sid]['available_num'] = $locations[$_sid]['num'];

                $pic_ids = explode(',', $list[$_sid]['pic_ids']);
                $products[$_sid]['_pic'] = array(
                    'small' => Data_Pic::getPicUrlFromOss($pic_ids[0], 'small'),
                    'middle' => Data_Pic::getPicUrlFromOss($pic_ids[0], 'middle'),
                    'big' => Data_Pic::getPicUrlFromOss($pic_ids[0], 'big'),
                );
            }
        }

        $this->products = $products;
        foreach ($this->products as &$_product)
        {
            if (array_key_exists($_product['sid'], $this->orderProducts))
            {
                $_product['has_selected'] = true;
                $_product['num'] = $this->orderProducts[$_product['sid']]['num'];
            }
            else
            {
                $_product['has_selected'] = false;
                $_product['num'] = 0;
            }
        }

        return array('total'=>$total, 'data'=>$this->products);
    }

    //采购单
    private function _getProductsForInOrder($info)
    {
        $statusTag = Conf_Product::PRODUCT_STATUS_ONLINE | Conf_Product::PRODUCT_STATUS_OFFLINE;
        $res = Shop_Api::searchProduct($this->keyword, $this->start, $this->num, $statusTag, $this->wid);
        $wssl = new Warehouse_Supplier_Sku_List();
        $supplierSku = Tool_Array::list2Map($wssl->getSupplierSkuList($info['info']['sid']), 'sku_id');
        $this->_supplyOrderData($res, $supplierSku);
        $this->products = $res['list'];
        $this->total = $res['total'];
        $this->smarty->assign('source', $info['info']['source']);
    }

    /**
     * 补充采购单，入库单数据.
     */
    private function _supplyOrderData(&$res, $supplierSku)
    {
        foreach ($res['list'] as &$_product)
        {
            $sid = $_product['sku']['sid'];
            if (array_key_exists($sid, $this->orderProducts))
            {
                $_product['_inorder']['num'] = $this->orderProducts[$sid]['num'];
                $_product['_inorder']['price'] = $this->orderProducts[$sid]['price'];
                $_product['supplier_purchase_price'] = $supplierSku[$sid]['purchase_price'];
                $_product['has_selected'] = true;
            }
            else
            {
                $_product['_inorder']['num'] = 0;
                $_product['_inorder']['price'] = $supplierSku[$sid]['purchase_price'];
                $_product['supplier_purchase_price'] = $supplierSku[$sid]['purchase_price'];
                $_product['has_selected'] = false;
            }
        }
    }
}

$app = new App('pub');
$app->run();