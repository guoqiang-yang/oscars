<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
    private $cmid;
    private $cdata;
	private $mergeToCmid;
    
    protected function getPara()
    {
        $this->cmid = Tool_Input::clean('r', 'cmid', TYPE_UINT);
        $this->cdata = array(
            'city_id' => Tool_Input::clean('r', 'city_id', TYPE_UINT),
            'district_id' => Tool_Input::clean('r', 'district_id', TYPE_UINT),
	        'ring_road' => Tool_Input::clean('r', 'ring_road', TYPE_UINT),
            'name' => Tool_Input::clean('r', 'name', TYPE_STR),
	        'alias' => Tool_Input::clean('r', 'alias', TYPE_STR),
            'address' => Tool_Input::clean('r', 'address', TYPE_STR),
            'lng' => Tool_Input::clean('r', 'lng', TYPE_NUM),
            'lat' => Tool_Input::clean('r', 'lat', TYPE_NUM),
            'status' => Tool_Input::clean('r', 'status', TYPE_UINT),
        );
	    if ($this->cmid && Conf_Base::STATUS_DELETED == $this->cdata['status'])
	    {
		    $this->mergeToCmid = Tool_Input::clean('r', 'merge_to_cmid', TYPE_UINT);
	    }
    }
    
    protected function checkPara()
    {
        $citys = Conf_Area::$CITY;
        $districts = Conf_Area::$DISTRICT;

        if (empty($this->cdata['name']) || empty($this->cdata['address']))
        {
            throw new Exception('请填写小区名称 或 地址！');
        }
        
        if (empty($this->cdata['lng']) || empty($this->cdata['lat']))
        {
            throw new Exception('经纬度录入失败，请重新再地图上选点！');
        }
        
        if (!array_key_exists($this->cdata['city_id'], $citys))
        {
            throw new Exception('请选择城市！');
        }
        $this->cdata['city'] = $citys[$this->cdata['city_id']];
        
        $thisDistricts = $districts[$this->cdata['city_id']];
        if (!array_key_exists($this->cdata['district_id'], $thisDistricts))
        {
            throw new Exception('请选择城区！');
        }
        $this->cdata['district'] = $thisDistricts[$this->cdata['district_id']];
        
        if (empty($this->cdata['ring_road']))
        {
            throw new Exception('请选择环线位置');
        }
    }
    
    protected function main()
    {
        $communityInfo = Order_Community_Api::getByNameAlias($this->cdata['name']);
        
        if (empty($this->cmid))
        {
	        if (empty($communityInfo) && !empty($this->cdata['alias']))
	        {
		        $communityInfo = Order_Community_Api::getByNameAlias($this->cdata['alias']);
	        }
            if (!empty($communityInfo))
            {
                foreach($communityInfo as $_cminfo)
                {
                    if ($this->cdata['city_id'] == $_cminfo['city_id'])
                    {
                        throw new Exception('小区已经存在！已有小区ID = '.$_cminfo['cmid']);
                    }
                }
//	            $communityInfo = array_shift($communityInfo);
//              throw new Exception('小区已经存在！已有小区ID = '.$communityInfo['cmid']);
            }

            $this->cdata['suid'] = $this->_uid;
            $this->cmid = Order_Community_Api::save($this->cdata);
        }
        else
        {   
            $this->cdata['edit_suid'] = $this->_uid;
            Order_Community_Api::save($this->cdata, $this->cmid);
	        if ($this->mergeToCmid)
	        {
		        Order_Community_Api::mergeCommunity($this->cmid, $this->mergeToCmid);
	        }
        }
    }
    
    protected function outputBody()
    {
        $result = array(
            'errno' => 0,
            'data' => array( 
                'cmid' => $this->cmid,
                'cdata' => $this->cdata,
            ),
        );
        
        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        
        exit;
    }
}

$app = new App();
$app->run();