<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $pid;
    private $online;
    private $product;
    private $sid;
    private $changed;
    private $pickNote;

    protected function getPara()
    {
        $this->pid = Tool_Input::clean('r', 'pid', TYPE_UINT);
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
        $this->online = Tool_Input::clean('r', 'online', TYPE_UINT);
        $this->product = array(
            'cost' => round(100 * Tool_Input::clean('r', 'cost', TYPE_STR)),
            'price' => round(100 * Tool_Input::clean('r', 'price', TYPE_STR)),
            'work_price' => round(100 * Tool_Input::clean('r', 'work_price', TYPE_STR)),
            'ori_price' => round(100 * Tool_Input::clean('r', 'ori_price', TYPE_STR)),
            'carrier_fee' => round(100 * Tool_Input::clean('r', 'carrier_fee', TYPE_STR)),
            'carrier_fee_ele' => round(100 * Tool_Input::clean('r', 'carrier_fee_ele', TYPE_STR)),
            'worker_ca_fee' => round(100 * Tool_Input::clean('r', 'worker_ca_fee', TYPE_STR)),
            'worker_ca_fee_ele' => round(100 * Tool_Input::clean('r', 'worker_ca_fee_ele', TYPE_STR)),
            'detail' => '',
            'city_id' => Tool_Input::clean('r', 'city_id', TYPE_UINT),
            'sales_type' => Tool_Input::clean('r', 'sales_type', TYPE_UINT),
            'buy_type' => Tool_Input::clean('r', 'buy_type', TYPE_UINT),
            'managing_mode' => Tool_Input::clean('r', 'managing_mode', TYPE_UINT),
            'recommend_pids' => Tool_Input::clean('r', 'recommend_pids', TYPE_STR),
            'picking_note' => Tool_Input::clean('r', 'picking_note', TYPE_STR),
            'alias' => Tool_Input::clean('r', 'alias', TYPE_STR),
        );
        $this->pickNote = Tool_Input::clean('r', 'pick_note', TYPE_STR);

        if ($this->product['carrier_fee'] != 0 && $this->product['carrier_fee_ele'] == 0)
        {
            $this->product['carrier_fee_ele'] = $this->product['carrier_fee'];
        }
    }

    protected function checkPara()
    {
        if (empty($this->product['price']) && empty($this->online))
        {
            throw new Exception('shop:can not online when price zero');
        }
        if (empty($this->product['cost']))
        {
            throw new Exception('请填写商品成本！');
        }

        if (empty($this->product['managing_mode']))
        {
            throw new Exception('请选择经营模式！');
        }

        if ($this->product['work_price'] > 0 && $this->product['work_price'] > $this->product['price'])
        {
            throw new Exception('shop:work_price invalid');
        }

        if ($this->product['ori_price'] > 0 && $this->product['ori_price'] < $this->product['price'])
        {
            throw new Exception('shop: ori_price invalid');
        }
        if ($this->product['price'] < 0 || $this->product['work_price'] < 0 || $this->product['ori_price'] < 0 || $this->product['carrier_fee'] < 0 ||
            $this->product['carrier_fee_ele'] < 0 || $this->product['cost'] < 0)
        {
            throw new Exception('费用不能是负数！');
        }
        if (parent::checkPermission('edit_shop_product_price'))
        {
            unset($this->product['price']);
            unset($this->product['work_price']);
            unset($this->product['ori_price']);
        }

        if (!empty($this->product['recommend_pids']))
        {
            if (strpos($this->product['recommend_pids'], '，') !== false)
            {
                alert('请使用半角逗号分隔pid！');
            }
            $recommandPids = explode(',', $this->product['recommend_pids']);
            $recommandPids = array_unique(array_filter($recommandPids));
            if (!empty($recommandPids))
            {
                $products = Shop_Api::getProductInfos($recommandPids);
                foreach ($products as $p)
                {
                    if ($p['product']['city_id'] != $this->product['city_id'])
                    {
                        throw new Exception("pid: {$p['product']['pid']}城市和当前商品城市不一致！");
                    }
                }
            }
        }
    }

    protected function main()
    {

        if ($this->pid)
        {
            $oldProduct = Shop_Api::getProductInfo($this->pid);
            Shop_Api::updateProduct($this->pid, $this->product);
            
            if ($oldProduct['sku']['type']==Conf_Sku::SKU_TYPE_PACKAGE)
            {
                if (empty($this->product['ori_price']))
                {
                    throw new Exception('套餐商品，原价不能为空！');
                }
                
                if ($this->product['buy_type'] != Conf_Product::BUY_TYPE_COMMON)
                {
                    throw new Exception('套餐商品，必须是普采商品属性！');
                }
            }
            
            foreach ($this->product as $k => $v)
            {
                if ($v != $oldProduct['product'][$k])
                {
                    $this->_generateLog($k, $v, $oldProduct);
                }
            }
            if (!empty($this->changed))
            {
                $info = array(
                    'admin_id' => $this->_uid,
                    'obj_id' => $this->pid,
                    'obj_type' => Conf_Admin_Log::OBJTYPE_PRODUCT,
                    'action_type' => 2,
                    'params' => json_encode(array('pid' => $this->pid, 'changed' => $this->changed)),
                );
                Admin_Common_Api::addAminLog($info);
            }
        }
        else
        {
            $this->product['status'] = intval($this->online);// ? Conf_Base::STATUS_NORMAL : Conf_Base::STATUS_OFFLINE;
            $exist = Shop_Api::isProductExist($this->sid, $this->product['city_id']);
            if ($exist)
            {
                throw new Exception('shop:product exists');
            }
            $this->pid = Shop_Api::addProduct($this->sid, $this->product);

            $info = array(
                'admin_id' => $this->_uid,
                'obj_id' => $this->pid,
                'obj_type' => Conf_Admin_Log::OBJTYPE_PRODUCT,
                'action_type' => 1,
                'params' => json_encode(array('pid' => $this->pid)),
            );
            Admin_Common_Api::addAminLog($info);
        }
    }

    protected function outputPage()
    {
        $result = array('pid' => $this->pid);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }

    private function _generateLog($k, $v, $oldInfo)
    {
        $buyTypes = Conf_Product::getBuyTypeDesc();
        $salesType = array(
            0 => '无',
            1 => '特价',
            2 => '热卖',
        );
        $status = array(0=>'上架',4=>'下架');
        switch ($k)
        {
            case 'cost':
                $this->changed .= '成本:' . $oldInfo['product'][$k]/100 . '=>' . $v/100 . ',';
                break;
            case 'price':
                $this->changed .= '价格:' . $oldInfo['product'][$k]/100 . '=>' . $v/100 . ',';
                break;
            case 'work_price':
                $this->changed .= '工装价:' . $oldInfo['product'][$k]/100 . '=>' . $v/100 . ',';
                break;
            case 'ori_price':
                $this->changed .= '原价:' . $oldInfo['product'][$k]/100 . '=>' . $v/100 . ',';
                break;
            case 'carrier_fee':
                $this->changed .= '上楼费(楼梯):' . $oldInfo['product'][$k]/100 . '=>' . $v/100 . ',';
                break;
            case 'carrier_fee_ele':
                $this->changed .= '上楼费(电梯):' . $oldInfo['product'][$k]/100 . '=>' . $v/100 . ',';
                break;
            case 'sales_type':
                $this->changed .= '活动类型:' . $salesType[$oldInfo['product'][$k]] . '=>' . $salesType[$v] . ',';
                break;
            case 'buy_type':
                $this->changed .= '采购类型:' . $buyTypes[$oldInfo['product'][$k]] . '=>' . $buyTypes[$v] . ',';
                break;
            case 'status':
                $this->changed .= '状态:' . $status[$oldInfo['product'][$k]] . '=>' . $status[$v] . ',';
                $flag = TRUE;
                if ($_SERVER['SERVER_ADDR'] == '127.0.0.1')
                {
                    $flag = FALSE;
                }
                Shop_Api::setTopCategoryProduct($oldInfo['product']['city_id'], $flag);
                Shop_Api::setTopCategoryBrandProduct($oldInfo['product']['city_id'], $flag);
                break;
            default :
                break;
        }
    }
}

$app = new App('pri');
$app->run();

