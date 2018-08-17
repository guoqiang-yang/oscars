<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $pid;
	private $productInfo;

	protected function getPara()
	{
		$this->pid = Tool_Input::clean('r', 'pid', TYPE_UINT);
		$this->productInfo = array(
		    'title' => Tool_Input::clean('r', 'title', TYPE_STR),
            'spec' => Tool_Input::clean('r', 'spec', TYPE_STR),
            'unit' => Tool_Input::clean('r', 'unit', TYPE_STR),
            'cate1' => Tool_Input::clean('r', 'cate1', TYPE_UINT),
            'city_id' => Tool_Input::clean('r', 'city_id', TYPE_UINT)
        );
	}

	protected function checkPara()
	{

        if (empty($this->productInfo['title']) || $this->productInfo['title'] == '')
        {
            throw new Exception('商品名不能为空');
        }

        if (empty($this->productInfo['spec']) || $this->productInfo['spec'] == '')
        {
            throw new Exception('规格不能为空');
        }

        if (empty($this->productInfo['unit']) || $this->productInfo['unit'] == '')
        {
            throw new Exception('单位不能为空');
        }

        if (empty($this->productInfo['cate1']))
        {
            throw new Exception('请选择分类');
        }

        if (empty($this->productInfo['city_id']))
        {
            throw new Exception('请选择城市');
        }
	}

	protected function main()
	{
        
		if (empty($this->pid))   //新建商品
		{
            $this->pid = Invoice_Api::addProduct($this->productInfo['title'], $this->productInfo['city_id'], $this->productInfo);
		}
		else    //编辑商品
		{
			Invoice_Api::updateProduct($this->pid, $this->productInfo);
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
}

$app = new App('pri');
$app->run();

