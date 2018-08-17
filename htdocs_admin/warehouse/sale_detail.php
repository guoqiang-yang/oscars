<?php 
include_once ('../../global.php');

class App extends App_Admin_Page
{
	// cgi参数
	private $start;
	private $bdate;  //时间 格式 YYYY-MM-DD
	private $edate;
	private $btype;	//采购类型 {0: 全部; 1: 实采; 2: 空采}
	private $pSearchCate;	//商品检索类型 {0: 全部; 1: 沙子水泥砖; 3: 其他}
	private $wid;		//仓库id
	private $num = 120;
	private $plist = array();	//商品列表

    protected function checkAuth()
    {
        parent::checkAuth('/order/sale_detail');
    }

	protected function getPara()
	{
		$this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
		$this->bdate = Tool_Input::clean('r', 'bdate', TYPE_STR);
		$this->edate = Tool_Input::clean('r', 'edate', TYPE_STR);
		$this->btype = Tool_Input::clean('r', 'btype', TYPE_UINT);
		$this->pSearchCate = Tool_Input::clean('r', 'product_search_cate', TYPE_UINT);
		$this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
		
		if (empty($this->bdate) || strlen($this->bdate) != 10 || count(explode('-', $this->bdate)) != 3)
        {
			$this->bdate = date('Y-m-d');
		}
		if (empty($this->edate) || strlen($this->edate) != 10 || count(explode('-', $this->edate)) !=3)
        {
			$this->edate = date('Y-m-d');
		}
		if (strtotime($this->edate) < strtotime($this->bdate))
        {
			$this->edate = $this->bdate;
		}
	}

    protected function checkPara()
    {
        $curCity = City_Api::getCity();
        if (empty($this->wid))
        {
            if (empty($this->_user['city_wid_map'][$curCity['city_id']]))
            {
                $this->wid = -1;
            }
            else
            {
                $this->wid = $this->_user['city_wid_map'][$curCity['city_id']];
            }
        }
    }

	protected function main()
    {
		$this->plist = Order_Api::getSaleProductDetails($this->bdate, $this->edate, $this->btype, $this->pSearchCate, $this->wid, $this->start, $this->num);
	}

	protected function outputBody()
    {
		$app = '/warehouse/sale_detail.php?bdate=' . $this->bdate . '&edate=' . $this->edate . '&btype=' . $this->btype . '&product_search_cate=' . $this->pSearchCate;
        if (!is_array($this->wid))
        {
            $app .= '&wid=' . $this->wid;
        }
		$pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->plist['total'], $app);
		
		$this->smarty->assign('pageHtml', $pageHtml);
		$this->smarty->assign('bdate', $this->bdate);
		$this->smarty->assign('edate', $this->edate);
		$this->smarty->assign('btype', $this->btype);
		$this->smarty->assign('pSearchCate', $this->pSearchCate);
		$this->smarty->assign('wid', $this->wid);
		$this->smarty->assign('pdatas', $this->plist['data']);
		$this->smarty->assign('total', $this->plist['total']);
		$this->smarty->assign('totalPrice', $this->plist['total_cost'] / 100);

		$this->smarty->display('order/sale_detail.html');
	}
}

$app = new App('pri');
$app->run();
