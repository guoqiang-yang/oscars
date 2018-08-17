<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $pid;
	private $productInfo;

    protected function checkAuth($permission = '')
    {
        parent::checkAuth('/activity/edit_customer_point_product');
    }

    protected function getPara()
	{
		$this->pid = Tool_Input::clean('r', 'pid', TYPE_UINT);
		$this->productInfo = array(
		    'title' => Tool_Input::clean('r', 'title', TYPE_STR),
            'abstract' => Tool_Input::clean('r', 'abstract', TYPE_STR),
            'cate1' => Tool_Input::clean('r', 'cate1', TYPE_UINT),
            'price' => round(100 * Tool_Input::clean('r', 'price', TYPE_STR)),
            'cost' => round(100 * Tool_Input::clean('r', 'cost', TYPE_STR)),
            'point' => Tool_Input::clean('r', 'point', TYPE_UINT),
            'stime' => Tool_Input::clean('r', 'stime', TYPE_STR),
            'etime' => Tool_Input::clean('r', 'etime', TYPE_STR),
            'member_level' => Tool_Input::clean('r', 'member_level', TYPE_STR),
            'status' => Tool_Input::clean('r', 'status', TYPE_UINT),
            'detail' => Tool_Input::clean('r', 'detail', TYPE_STR),
            'pics' => Tool_Input::clean('r', 'pics', TYPE_STR)
        );
        if(empty($this->pid))
        {
            $this->productInfo['stock_num'] = Tool_Input::clean('r', 'stock_num', TYPE_UINT);
        }
	}

	protected function main()
	{
        
		if (empty($this->pid))   //新建商品
		{
            $this->pid = Cpoint_Api::addProduct($this->productInfo, $this->_user);
		}
		else    //编辑商品
		{
			Cpoint_Api::updateProduct($this->pid, $this->productInfo);
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

