<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    
    private $wid;
    private $sid;
    
    protected function getPara()
    {
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
    }
    
    protected function checkPara()
    {
        if (empty($this->wid) || empty($this->sid))
        {
            throw new Exception('参数异常：仓库id or skuid 为空');
        }
    }


    protected function main()
    {
        $ws = new Warehouse_Stock();
        $wl = new Warehouse_Location();
        //获取总库存
        $stocks = $ws->get($this->wid, $this->sid);
        
        //获取系统库存
        $lwhere = sprintf('wid=%d and sid=%d and location not like "%s%%"',
                            $this->wid, $this->sid, Conf_Warehouse::VFLAG_PREFIX);
        $locations = $wl->getRawWhere($lwhere);
        
        if (empty($stocks) || empty($locations))
        {
            throw new Exception('清理失败：库存数据异常');
        }
        
        if ($stocks['occupied'] > 0)
        {
            throw new Exception('总库存有占用，不能清理，请联系技术处理');
        }
        
        if ($stocks['num'] < 0)
        {
            throw new Exception('总库存为负数，不能清理，请联系技术处理');
        }
        
        foreach($locations as $item)
        {
            if ($item['occupied'] > 0)
            {
                throw new Exception('货位库存 '.$item['location'].' 有占用，不能清理，请联系技术处理');
            }
            
            if ($item['num'] < 0)
            {
                throw new Exception('货位库存 '.$item['location'].' 存在负库存，不能清理，请联系技术处理');
            }
        }
        
        //清理
        $ws->update($this->wid, $this->sid, array('occupied'=>0));
        
        foreach($locations as $litem)
        {
            if ($litem['occupied'] == 0) continue;
            
            $wl->updateById($litem['id'], array('occupied'=>0));
        }
        
    }
    
    protected function outputPage()
    {
        $response = new Response_Ajax();
		$response->setContent(array('ret'=>1));
		$response->send();

		exit;
    }
    
}

$app = new App();
$app->run();