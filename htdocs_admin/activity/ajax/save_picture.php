<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $id;
    private $picture;

    protected function checkAuth()
    {
        parent::checkAuth('/activity/add_picture');
    }

    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->picture = array(
            'name' => Tool_Input::clean('r', 'name', TYPE_STR),
            'platform' => rtrim(Tool_Input::clean('r', 'platform', TYPE_STR), ','),
            'start_time' => Tool_Input::clean('r', 'start_time', TYPE_STR),
            'end_time' => Tool_Input::clean('r', 'end_time', TYPE_STR),
            'url' => Tool_Input::clean('r', 'url', TYPE_STR),
            'pic_tag' => Tool_Input::clean('r', 'pic_tag', TYPE_STR),
            'display_order' => Tool_Input::clean('r', 'display_order', TYPE_UINT),
            'type' => Tool_Input::clean('r', 'type', TYPE_UINT),
            'suid' => $this->_uid,
            'city_id' => rtrim(Tool_Input::clean('r', 'city_id', TYPE_STR), ','),
            'activity_type'=> Tool_Input::clean('r', 'activity_type', TYPE_INT),
            'commodity_sid'=> str_replace('，',',',preg_replace('# #','',Tool_Input::clean('r', 'commodity_sid', TYPE_STR))),
        );
    }

    protected function checkPara()
    {
        if (empty($this->picture['name']))
        {
            throw new Exception('活动名称不能为空');
        }
        if ($this->picture['type'] == Conf_Picture::PICTURE_TYPE_BANNER && empty($this->picture['platform']))
        {
            throw new Exception('显示平台不能为空');
        }
        if (empty($this->picture['type']))
        {
            throw new Exception('类型不能为空');
        }

        if ($this->picture['activity_type'] == 1)
        {
            if (empty($this->picture['url']))
            {
                throw new Exception('链接地址不能为空');
            }

            if (!empty($this->picture['commodity_sid']))
            {
                throw new Exception('您已选择文章类,请勿填写商品SID');
            }

        }
        else if($this->picture['activity_type'] == 2)
        {
            if (empty($this->picture['commodity_sid']))
            {
                throw new Exception('商品SID不能为空');
            }

            if (!empty($this->picture['url']))
            {
                throw new Exception('您已选择落地页类,请勿填写链接地址');
            }
        }
        if (empty($this->picture['start_time']))
        {
            throw new Exception('开始时间不能为空');
        }
        if (empty($this->picture['end_time']))
        {
            throw new Exception('结束时间不能为空');
        }
        if ($this->picture['start_time'] > $this->picture['end_time'])
        {
            throw new Exception('结束时间不能早于开始时间');
        }
        if ($this->picture['type'] == Conf_Picture::PICTURE_TYPE_BANNER)
        {
            if (empty($this->picture['display_order']) && 0 != $this->picture['display_order'])
            {
                throw new Exception('显示顺序不能为空');
            }
        }
        if (empty($this->picture['city_id']))
        {
            throw new Exception('图片城市不能为空');
        }
        if (count(explode(',',$this->picture['commodity_sid']))>50)
        {
            throw new Exception('商品SID最多为50个');
        }
        if ($this->picture['type'] == Conf_Picture::PICTURE_TYPE_AD)
        {
            $conf = array(
                'type' => Conf_Picture::PICTURE_TYPE_AD,
                'status' => '0',
            );
            $data = Activity_Api::getPictureList($conf);
            if ($data['total'] > 0)
            {
                $cityIds = explode(',', $this->picture['city_id']);
                foreach ($data['list'] as $item)
                {
                    $itemCityIds = explode(',', $item['ori_city_id']);
                    $sameCityIds = array_intersect($cityIds, $itemCityIds);
                    if (!empty($sameCityIds) && $this->id != $item['id'])
                    {
                        throw new Exception('该城市已有在线的广告图！');
                    }
                }
            }
        }
    }

    protected function main()
    {
        if (substr($this->picture['url'], 0, 7) != 'http://')
        {
            $this->picture['url'] = 'http://' . $this->picture['url'];
        }
        if ($this->id)
        {
            $oldPicture = Activity_Api::getPictureInfo($this->id);

            //如果是编辑图片信息,但是不改变图片,从原来的图片信息中获取pic_tag
            if (empty($this->picture['pic_tag']))
            {
                $this->picture['pic_tag'] = $oldPicture[0]['pic_tag'];
            }

            Activity_Api::updatePicture($this->id, $this->picture);
        }
        else
        {
            $this->id = Activity_Api::addActivityPicture($this->picture);
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