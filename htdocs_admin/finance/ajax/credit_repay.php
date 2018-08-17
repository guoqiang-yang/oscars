<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
    
    private $cid;
    private $oid;
    private $otype;
    private $price;
    private $paymentType;
    private $note;
    
    private $response;
    
    protected function getPara()
    {
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->otype = Tool_Input::clean('r', 'otype', TYPE_STR);
        
        $this->price = Tool_Input::clean('r', 'price', TYPE_UINT);
        $this->paymentType = Tool_Input::clean('r', 'payment_type', TYPE_UINT);
        $this->note = Tool_Input::clean('r', 'note', TYPE_STR);
    }
    
    protected function checkAuth()
    {
        parent::checkAuth();
        
        if (ENV == 'online')
        {
            throw new Exception('生产环境不能使用该功能！！');
        }
    }

    protected function checkPara()
    {
        if (empty($this->oid))
        {
            throw new Exception('请输入订单id！');
        }
        
        // oid，cid一致性校验，订单支付状态等校验，略.....
    }
    
    protected function main()
    {
        switch ($this->otype) {
            case 'detail':
                $this->_setDetailInfo();
                break;
            
            case 'repay':
                $moreDatas = array(
                    'uid' => 0,
                    'suid' => $this->_uid,
                    'note' => $this->note,
                );
                
                Tpfinance_Api::creditRepayByOid($this->cid, $this->oid, $this->price, $this->paymentType, $moreDatas);
                $this->response['st'] = 0;
                break;
            
            default:
                throw new Exception('操作类型错误，请核对！！');
        }
        
    }
    
    protected function outputBody()
    {
		$response = new Response_Ajax();
		$response->setContent($this->response);
		$response->send();

		exit;
        
    }
    
    
    private function _setDetailInfo()
    {
        $repayDetail = Tpfinance_Api::calCreditRepayPriceByOid($this->oid);
        
        $this->response['st'] = 0;
        $this->response['data']['repay'] = $repayDetail['repay_price'];
        
        $html = '';
        foreach($repayDetail['detail'] as $item)
        {
            if ($item['price'] == 0) continue;
            
            $html .= '<p>'.$item['desc'].'：'. ($item['price']/100). ' 元</p>';
        }
        $this->response['data']['html'] = $html;
    }
    
}

$app = new App();
$app->run();