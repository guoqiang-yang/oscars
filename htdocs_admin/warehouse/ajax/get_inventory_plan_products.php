<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $pid;
    private $products;
    private $times;
    private $html;
    private $step;
    private $location;

    protected function getPara()
    {
        $this->pid = Tool_Input::clean('r', 'pid', TYPE_UINT);
        $this->times = Tool_Input::clean('r', 'times', TYPE_UINT);
        $this->step = Tool_Input::clean('r', 'step', TYPE_UINT);
        if (isset($_REQUEST['location']))
        {
            $this->location = Tool_Input::clean('r', 'location', TYPE_STR);
        }
    }
    

    protected function checkPara()
    {
        if (empty($this->pid) || empty($this->step))
        {
            throw new Exception('参数错误！');
        }
    }

    protected function checkAuth()
    {
        parent::checkAuth('/warehouse/ajax/add_inventory_plan_products');
    }

    protected function main()
    {
        $this->products = Warehouse_Api::getInventoryPlanProducts($this->pid, $this->times);

        if (!empty($this->products))
        {
            $sids = array_unique(Tool_Array::getFields($this->products, 'sid'));
            $ss = new Shop_Sku();
            $skuList = $ss->getBulk($sids);

            $locationList = array();
            foreach ($this->products as &$product)
            {
                $product['title'] = $skuList[$product['sid']]['title'];
                $product['unit'] = $skuList[$product['sid']]['unit'];
                $location = explode('-', $product['location']);
                $locationList[$location[0]] = $location[0];
            }
            $locations = array_unique(array_keys($locationList));
            sort($locations);
            $this->smarty->assign('location_list', $locations);
        }

        $this->smarty->assign('product_list', $this->products);
        $this->smarty->assign('total', count($this->products));
        $this->smarty->assign('step', $this->step);

        $this->html = $this->smarty->fetch('warehouse/aj_get_inventory_plan_products.html');
    }
    
    protected function outputBody()
    {
        $st = !empty($this->html)? 1 : 0;
        $result = array('st'=>$st, 'html'=> $this->html);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
    }
}

$app = new App();
$app->run();