<?php

/**
 * 添加货位.
 */

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $area;
    private $shelf;
    private $layer;
    private $pos;
    private $wid;
    private $sid;
    
    private $location;
    private $response;


    protected function getPara()
    {
        $this->area = strtoupper(Tool_Input::clean('r', 'area', TYPE_STR));
        $this->shelf = Tool_Input::clean('r', 'shelf', TYPE_UINT);
        $this->layer = Tool_Input::clean('r', 'layer', TYPE_UINT);
        $this->pos = Tool_Input::clean('r', 'pos', TYPE_UINT);
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
        
        $this->response = array('errno'=>0, 'errmsg'=>'');
    }
    
    protected function checkPara()
    {
        if (!array_key_exists($this->wid, $this->getAllowedWarehouses()))
        {
            $this->response['errno'] = 10;
            $this->response['errmsg'] = '仓库不存在，不能创建该仓库货位！';
        }
        else if (empty($this->sid))
        {
            $this->response['errno'] = 16;
            $this->response['errmsg'] = 'skuid不能为空！';
        }
        else
        {
            $this->_checkLocation();
        }
    }
    
    protected function main()
    {
        if ($this->response['errno'] != 0)
        {
            return;
        }
        
        $this->location = $this->_genLocation();
        
        $ret = Warehouse_Location_Api::addLocation($this->location, $this->wid, $this->sid);
        
        if ($ret == -1)
        {
            $this->response['errno'] = 20;
            $this->response['errmsg'] = '货位编号不正确，请联系管理员处理！';
        }
        else if ($ret == -2)
        {
            $this->response['errno'] = 21;
            $this->response['errmsg'] = '货位已经存在，请再次确认！';
        }
    }
    
    protected function outputBody()
    {
        $response = new Response_Ajax();
		$response->setContent($this->response);
		$response->send();
    }


    private function _checkLocation()
    {
        // 货区：取值范围[A - Z]
        if (empty($this->area)||strlen($this->area)!==1
            || ord($this->area)<65 || ord($this->area)>90)
        {
            $this->response['errno'] = 11;
            $this->response['errmsg'] = '货区输入错误：范围 A ~ Z';
        }
        // 货架：取值范围 [1 - 99]
        else if(empty($this->shelf) || $this->shelf<=0 || $this->shelf>=100)
        {
            $this->response['errno'] = 12;
            $this->response['errmsg'] = '货架输入错误：范围 1 ~ 99';
        }
        else if (!empty($this->layer) && ($this->layer<=0 || $this->layer>=100))
        {
            $this->response['errno'] = 13;
            $this->response['errmsg'] = '架层输入错误：范围 1 ~ 99';
        }
        else if (!empty($this->pos))
        {
            if (empty($this->layer))
            {
                $this->response['errno'] = 14;
                $this->response['errmsg'] = '请输入架层！';
            }
            else if($this->pos<=0 || $this->pos>=100)
            {
                $this->response['errno'] = 15;
                $this->response['errmsg'] = '层位输入错误：范围 1 ~ 99';
            }
        }
    }
    
    private function _genLocation()
    {
        $loc = $this->area;
        
        $levels = array($this->shelf, $this->layer, $this->pos);
        
        foreach($levels as $one)
        {
            if ($one >= 10)
            {
                $loc .= '-'. $one;
            }
            else if ($one > 0)
            {
                $loc .= '-0'. $one;
            }
            else if ($one == 0)
            {
                $loc .= '-00';
            }
        }
        
        return $loc;
    }
    
}

$app = new App();
$app->run();