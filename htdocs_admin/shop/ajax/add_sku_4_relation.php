<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $num=20;
    private $start;
    
    private $optype;
    private $parentSkuid;
    private $relSid;
    private $hadRelSkuFromHtml; //从页面上获取的关联sku，新建sku的时候使用
    private $relTitle;
    private $bindNum;
    private $keyword;
    
    private $responseData = array();
    private $skuInfo = array();
    private $selectedSkuInfos = array();
    
    protected function getPara()
    {
        $this->optype = Tool_Input::clean('r', 'optype', TYPE_STR);
        $this->parentSkuid = Tool_Input::clean('r', 'parent_sid', TYPE_UINT);
        $this->relSid = Tool_Input::clean('r', 'rel_sid', TYPE_UINT);
        $this->hadRelSkuFromHtml = Tool_Input::clean('r', 'had_rel_sku', TYPE_STR);
        $this->relTitle = Tool_Input::clean('r', 'rel_title', TYPE_STR);
        $this->bindNum = Tool_Input::clean('r', 'bind_num', TYPE_UINT);
        $this->keyword = Tool_Input::clean('r', 'keyword', TYPE_STR);
        
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
    }
    
    protected function checkPara()
    {
        if (!empty($this->parentSkuid))
        {
            $this->skuInfo = Shop_Api::getSkuInfo($this->parentSkuid, true);
          
            $this->selectedSkuInfos = Tool_Array::list2Map($this->skuInfo['_rel_sku'], 'sid');
        }
        else
        {
            $this->selectedSkuInfos = Shop_Helper::parseRelationSkus($this->hadRelSkuFromHtml);
            
            if (!empty($this->selectedSkuInfos))
            {
                $this->selectedSkuInfos = Tool_Array::list2Map($this->selectedSkuInfos, 'sid');
                $_sids = array_keys($this->selectedSkuInfos);
                $ss = new Shop_Sku();
                $_skuInfos = $ss->getBulk($_sids);
                
                foreach($this->selectedSkuInfos as &$_onesku)
                {
                    $_onesku['title'] = $_skuInfos[$_onesku['sid']]['title'];
                }
            }
        }
        
        if ($this->optype=='bind' &&(empty($this->relSid)||empty($this->bindNum)) )
        {
            throw new Exception('绑定sku，需要被绑定的skuid 或 数量');
        }
        
        if ($this->optype=='unbind' && (empty($this->parentSkuid)||empty($this->relSid)))
        {
            throw new Exception('解绑sku，数据异常');
        }
        
        if (!empty($this->parentSkuid) && ($this->optype=='bind' || $this->optype=='unbind') )
        {
            $hadProcessed = Shop_Api::skuHadProcessed($this->parentSkuid);
            
            if ($hadProcessed)
            {
                throw new Exception('sku已经被加工生产，不能再修改！'.($hadProcessed?1:0));
            }
        }
    }
    
    protected function main()
    {
        switch($this->optype)
        {
            case 'show':
                $this->_getSkuList();
                break;
            case 'bind':
                $this->_bindSku();
                break;
            case 'unbind':
                $this->_unBindSku();
                break;
            default :
                throw new Exception('操作类型异常！');
        }
    }
    
    private function _getSkuList()
    {
        $skuList = Shop_Api::searchSku($this->keyword, $this->start, $this->num);
        
        foreach($skuList['list'] as $_sid => &$one)
        {
            if (array_key_exists($_sid, $this->selectedSkuInfos))
            {
                $one['selected'] = 1;
                $one['num'] = $this->selectedSkuInfos[$_sid]['num'];
            }
            else
            {
                $one['selected'] = 0;
            }
        }
        
        $this->smarty->assign('pageHtml', Str_Html::getJsPagehtml2($this->start,  $this->num, $skuList['total'], 'searchSkuList'));
        $this->smarty->assign('total', $skuList['total']);
        $this->smarty->assign('sku_list', $skuList['list']);
        $this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
        $this->smarty->assign('cate2_list', Conf_Sku::$CATE2);
        
        $this->responseData['html'] = $this->smarty->fetch('shop/block_sku_list.html');
        
    }
    
    private function _bindSku()
    {
        // 检测sku是否被重复绑定
        foreach($this->skuInfo['_rel_sku'] as $one)
        {
            if ($one['sid'] == $this->relSid)
            {
                throw new Exception('该sku已经被关联！');
            }
        }
        if ($this->relSid == $this->parentSkuid)
        {
            throw new Exception('请不要绑定自己！！你傻啊！！！');
        }
        
        // 被绑定的sku存在，更新绑定关系
        $newRelSkus = array('sid'=>$this->relSid, 'num'=>$this->bindNum, 'title'=>$this->relTitle);
        if (!empty($this->parentSkuid))
        {
            $relSkus = array_merge($this->skuInfo['_rel_sku'], array($newRelSkus));
            $relSkuStr = Shop_Helper::genRelationSkus($relSkus);
            
            $upSkuData = array(
                'rel_sku' => $relSkuStr,
                'type' => Conf_Sku::SKU_TYPE_PROCESSED,
            );
            Shop_Api::updateSku($this->parentSkuid, $upSkuData);

            $info = array(
                'admin_id' => $this->_uid,
                'obj_id' => $this->parentSkuid,
                'obj_type' => Conf_Admin_Log::OBJTYPE_SKU,
                'action_type' => 3,
                'params' => json_encode(array('sid' => $this->relSid)),
            );
            Admin_Common_Api::addAminLog($info);
        }
        else
        {
            $relSkus = array_merge($this->selectedSkuInfos, array($newRelSkus));
            $relSkuStr = Shop_Helper::genRelationSkus($relSkus);
        }
        
        // 生成页面显示的html，data
        $this->smarty->assign('rel_skus', $relSkus);
        $this->responseData['html'] = $this->smarty->fetch('shop/block_relation_sku_list.html');
        $this->responseData['rel_sku'] = $relSkuStr;
    }
    
    private function _unBindSku()
    {
        // 检测sku是否被重复绑定
        $chkSt = false;
        foreach($this->skuInfo['_rel_sku'] as $k => $one)
        {
            if ($one['sid'] == $this->relSid)
            {
                $chkSt = true;
                unset($this->skuInfo['_rel_sku'][$k]);
                break;
            }
        }
        if (!$chkSt)
        {
            throw new Exception('解绑sid跟组合sid无关系！');
        }
        
        $upSkuData = array(
            'rel_sku' => Shop_Helper::genRelationSkus($this->skuInfo['_rel_sku']),
        );
        Shop_Api::updateSku($this->parentSkuid, $upSkuData);
        
        $this->responseData['rel_sku'] = $upSkuData['rel_sku'];

        $info = array(
            'admin_id' => $this->_uid,
            'obj_id' => $this->parentSkuid,
            'obj_type' => Conf_Admin_Log::OBJTYPE_SKU,
            'action_type' => 4,
            'params' => json_encode(array('sid' => $this->relSid)),
        );
        Admin_Common_Api::addAminLog($info);
    }
    
    protected function outputBody()
    {
		$response = new Response_Ajax();
		$response->setContent($this->responseData);
		$response->send();
		exit;
    }
    
}

$app = new App();
$app->run();