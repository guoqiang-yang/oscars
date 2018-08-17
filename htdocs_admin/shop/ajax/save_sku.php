<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $sid;
    private $online;
    private $sku;
    private $changed;

    protected function checkAuth()
    {
        parent::checkAuth('/shop/edit_sku');
    }

    protected function getPara()
    {
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
        $this->online = Tool_Input::clean('r', 'online', TYPE_UINT);
        $this->sku = array(
            'title' => Tool_Input::clean('r', 'title', TYPE_STR),
            'alias' => Tool_Input::clean('r', 'alias', TYPE_STR),
            'cate1' => Tool_Input::clean('r', 'cate1', TYPE_UINT),
            'cate2' => Tool_Input::clean('r', 'cate2', TYPE_UINT),
            'cate3' => Tool_Input::clean('r', 'cate3', TYPE_UINT),
            'bid' => Tool_Input::clean('r', 'bid', TYPE_UINT),
            'unit' => Tool_Input::clean('r', 'unit', TYPE_STR),
            'package' => Tool_Input::clean('r', 'package', TYPE_STR),
            'picking_note' => Tool_Input::clean('r', 'picking_note', TYPE_STR),
            'detail' => Tool_Input::clean('r', 'detail', TYPE_STR),
            'mids' => implode(',', (array)Tool_Input::clean('r', 'mid', TYPE_ARRAY)),
            'qrcode_type' => Tool_Input::clean('r', 'qrcode_type', TYPE_UINT),
            'pic_ids' => Tool_Input::clean('r', 'pic_ids', TYPE_STR),
            'length' => Tool_Input::clean('r', 'length', TYPE_NUM) * 100,
            'width' => Tool_Input::clean('r', 'width', TYPE_NUM) * 100,
            'height' => Tool_Input::clean('r', 'height', TYPE_NUM) * 100,
            'weight' => Tool_Input::clean('r', 'weight', TYPE_NUM) * 1000,
            'type' => Tool_Input::clean('r', 'type', TYPE_UINT),
            'rel_sku' => Tool_Input::clean('r', 'rel_sku', TYPE_STR),
        );
        if($this->sku['type'] != Conf_Sku::SKU_TYPE_PROCESSED && $this->sku['type'] != Conf_Sku::SKU_TYPE_PACKAGE)
        {
            $this->sku['rel_sku'] = '';
        }   
    }

    protected function checkPara()
    {
        if (empty($this->sku['title']))
        {
            throw new Exception('shop:empty product name');
        }
        if (empty($this->sku['cate1']))
        {
            throw new Exception('shop:empty cate1');
        }
        if (empty($this->sku['cate2']))
        {
            throw new Exception('shop:empty cate2');
        }
    }

    protected function main()
    {
        $dupSid = Shop_Api::isSkuTitleDuplicate($this->sku['title']);
        if ($dupSid > 0 && $dupSid != $this->sid)
        {
            throw new Exception("sku 名称已存在！复制链接查看：\n\n" . ADMIN_HOST . '/shop/edit_sku.php?sid=' . $dupSid);
        }

        //todo: 暂时去掉sku的上下架状态
        $this->sku['status'] = Conf_Base::STATUS_NORMAL;

        if ($this->sid)
        {
            $oldSku = Shop_Api::getSkuInfo($this->sid);

            Shop_Api::updateSku($this->sid, $this->sku);

            foreach ($this->sku as $k => $v)
            {
                if ($v != $oldSku[$k])
                {
                    $this->_generateLog($k, $v, $oldSku);
                }
            }

            if (!empty($this->changed))
            {
                $info = array(
                    'admin_id' => $this->_uid,
                    'obj_id' => $this->sid,
                    'obj_type' => Conf_Admin_Log::OBJTYPE_SKU,
                    'action_type' => 6,
                    'params' => json_encode(array('sid' => $this->sid, 'changed' => $this->changed)),
                );
                Admin_Common_Api::addAminLog($info);
            }
        }
        else
        {
            $this->sid = Shop_Api::addSku($this->sku);

            $info = array(
                'admin_id' => $this->_uid,
                'obj_id' => $this->sid,
                'obj_type' => Conf_Admin_Log::OBJTYPE_SKU,
                'action_type' => 2,
                'params' => json_encode(array('sid' => $this->sid)),
            );
            Admin_Common_Api::addAminLog($info);

            if ($this->sku['type'] == Conf_Sku::SKU_TYPE_PROCESSED)
            {
                $info = array(
                    'admin_id' => $this->_uid,
                    'obj_id' => $this->sid,
                    'obj_type' => Conf_Admin_Log::OBJTYPE_SKU,
                    'action_type' => 5,
                    'params' => json_encode(array('sid' => $this->sid, 'rel_sid' => $this->sku['rel_sku'])),
                );
                Admin_Common_Api::addAminLog($info);
            }

            Admin_Api::addActionLog($this->_uid, Conf_Admin_Log::$ACTION_ADD_SKU, array(
                'name' => $this->sku['title'],
                'id' => $this->sid
            ));
        }
    }

    protected function outputPage()
    {
        $result = array('sid' => $this->sid);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }

    private function _generateLog($k, $v, $oldSku)
    {
        switch ($k)
        {
            case 'title':
                $this->changed .= '名称:' . $oldSku[$k] . '=>' . $v . ',';
                break;
            case 'alias':
                $this->changed .= '其他名称:' . $oldSku[$k] . '=>' . $v . ',';
                break;
            case 'cate1':
                $this->changed .= '类别1:' . Conf_Sku::$CATE1[$oldSku[$k]]['name'] . '=>' . Conf_Sku::$CATE1[$v]['name'] . ',';
                break;
            case 'cate2':
                $this->changed .= '类别2:' . Conf_Sku::$CATE2[$oldSku['cate1']][$oldSku[$k]]['name'] . '=>' . Conf_Sku::$CATE2[$this->sku['cate1']][$v]['name'] . ',';
                break;
            case 'bid':
                $this->changed .= '品牌ID:' . $oldSku[$k] . '=>' . $v . ',';
                break;
            case 'unit':
                $this->changed .= '单位:' . $oldSku[$k] . '=>' . $v . ',';
                break;
            case 'package':
                $this->changed .= '规格&包装:' . $oldSku[$k] . '=>' . $v . ',';
                break;
            case 'picking_note':
                $this->changed .= '包装含量:' . $oldSku[$k] . '=>' . $v . ',';
                break;
            case 'mids':
                if ($this->sku['bid'] == $oldSku['bid'])
                {
                    $models = Shop_Api::getModelList($this->sku['cate2']);

                    $mids = explode(',', $v);
                    foreach ($mids as &$mid)
                    {
                        $mid = $models[$mid]['name'];
                    }

                    $oldMids = explode(',', $oldSku['mids']);
                    foreach ($oldMids as &$oldMid)
                    {
                        $oldMid = $models[$oldMid]['name'];
                    }

                    $this->changed .= '型号:' . implode(',', $oldMids) . '=>' . implode(',', $mids) . ',';
                }
                break;
            case 'qrcode_type':
                $this->changed .= '二维码类型:' . Conf_Qrcode::$QRCODE_TYPE[$oldSku[$k]] . '=>' . Conf_Qrcode::$QRCODE_TYPE[$v] . ',';
                break;
            case 'pic_ids':
                $this->changed .= '单位:' . $oldSku[$k] . '=>' . $v . ',';
                break;
            case 'length':
                $this->changed .= '长度:' . $oldSku[$k]/100 . '=>' . $v/100 . ',';
                break;
            case 'width':
                $this->changed .= '宽度:' . $oldSku[$k]/100 . '=>' . $v/100 . ',';
                break;
            case 'height':
                $this->changed .= '高度:' . $oldSku[$k]/100 . '=>' . $v/100 . ',';
                break;
            case 'weight':
                $this->changed .= '重量:' . $oldSku[$k]/1000 . '=>' . $v/1000 . ',';
                break;
            case 'type':
                $this->changed .= 'sku类型:' . $oldSku[$k] . '=>' . $v . ',';
                break;
            case 'rel_sku':
                $this->changed .= '单位:' . $oldSku[$k] . '=>' . $v . ',';
                break;
            default :
                break;
        }
    }
}

$app = new App('pri');
$app->run();