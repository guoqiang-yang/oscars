<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $id;
	private $info;

	protected function getPara()
	{
		$this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
		$this->info = array(
			'city_id' => Tool_Input::clean('r', 'city_id', TYPE_UINT),
			'water_1' => str_replace('，',',',preg_replace('# #','',Tool_Input::clean('r', 'water_1', TYPE_STR))),
            'electric_2' => str_replace('，',',',preg_replace('# #','',Tool_Input::clean('r', 'electric_2', TYPE_STR))),
            'wood_3' => str_replace('，',',',preg_replace('# #','',Tool_Input::clean('r', 'wood_3', TYPE_STR))),
            'tile_4' => str_replace('，',',',preg_replace('# #','',Tool_Input::clean('r', 'tile_4', TYPE_STR))),
            'oil_5' => str_replace('，',',',preg_replace('# #','',Tool_Input::clean('r', 'oil_5', TYPE_STR))),
            'tools_6' => str_replace('，',',',preg_replace('# #','',Tool_Input::clean('r', 'tools_6', TYPE_STR))),
		);
	}

    protected function checkPara()
    {
        if(empty($this->info['city_id']))
        {
            throw new Exception('请选择城市');
        }
        if(empty($this->info['water_1']))
        {
            throw new Exception('水类推荐品牌为空');
        }
        $_empty_ids = array();
        $_brandIds = explode(',', $this->info['water_1']);
        $_brandList = Shop_Api::getBrandByIds($_brandIds);
        if(!empty($_brandList))
        {
            $_empty_ids = array_unique(array_merge($_empty_ids, array_diff($_brandIds, Tool_Array::getFields($_brandList, 'bid'))));
        }else{
            $_empty_ids = array_unique(array_merge($_empty_ids, $_brandIds));
        }
        if(empty($this->info['electric_2']))
        {
            throw new Exception('电类推荐品牌为空');
        }
        $_brandIds = explode(',', $this->info['electric_2']);
        $_brandList = Shop_Api::getBrandByIds($_brandIds);
        if(!empty($_brandList))
        {
            $_empty_ids = array_unique(array_merge($_empty_ids, array_diff($_brandIds, Tool_Array::getFields($_brandList, 'bid'))));
        }else{
            $_empty_ids = array_unique(array_merge($_empty_ids, $_brandIds));
        }
        if(empty($this->info['wood_3']))
        {
            throw new Exception('木类推荐品牌为空');
        }
        $_brandIds = explode(',', $this->info['wood_3']);
        $_brandList = Shop_Api::getBrandByIds($_brandIds);
        if(!empty($_brandList))
        {
            $_empty_ids = array_unique(array_merge($_empty_ids, array_diff($_brandIds, Tool_Array::getFields($_brandList, 'bid'))));
        }else{
            $_empty_ids = array_unique(array_merge($_empty_ids, $_brandIds));
        }
        if(empty($this->info['tile_4']))
        {
            throw new Exception('瓦类推荐品牌为空');
        }
        $_brandIds = explode(',', $this->info['tile_4']);
        $_brandList = Shop_Api::getBrandByIds($_brandIds);
        if(!empty($_brandList))
        {
            $_empty_ids = array_unique(array_merge($_empty_ids, array_diff($_brandIds, Tool_Array::getFields($_brandList, 'bid'))));
        }else{
            $_empty_ids = array_unique(array_merge($_empty_ids, $_brandIds));
        }
        if(empty($this->info['oil_5']))
        {
            throw new Exception('油类推荐品牌为空');
        }
        $_brandIds = explode(',', $this->info['oil_5']);
        $_brandList = Shop_Api::getBrandByIds($_brandIds);
        if(!empty($_brandList))
        {
            $_empty_ids = array_unique(array_merge($_empty_ids, array_diff($_brandIds, Tool_Array::getFields($_brandList, 'bid'))));
        }else{
            $_empty_ids = array_unique(array_merge($_empty_ids, $_brandIds));
        }
        if(empty($this->info['tools_6']))
        {
            throw new Exception('工具类推荐品牌为空');
        }
        $_brandIds = explode(',', $this->info['tools_6']);
        $_brandList = Shop_Api::getBrandByIds($_brandIds);
        if(!empty($_brandList))
        {
            $_empty_ids = array_unique(array_merge($_empty_ids, array_diff($_brandIds, Tool_Array::getFields($_brandList, 'bid'))));
        }else{
            $_empty_ids = array_unique(array_merge($_empty_ids, $_brandIds));
        }
        if(!empty($_empty_ids))
        {
            throw new Exception('品牌：'.implode(',', $_empty_ids).' 不存在，请填写有效的品牌ID');
        }
    }

    protected function main()
	{
	    $cb = new Activity_City_Brand();
        if(empty($this->id))
        {
            $_info = $cb->getByCity($this->info['city_id']);
            if(!empty($_info))
            {
                throw new Exception('该城市已存在');
            }
            $this->id = $cb->add($this->info);
        }else{
            unset($this->info['city_id']);
            $cb->update($this->id, $this->info);
        }
	}

	protected function outputPage()
	{
		$result = array('id' => $this->id);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
	}
}

$app = new App('pri');
$app->run();

