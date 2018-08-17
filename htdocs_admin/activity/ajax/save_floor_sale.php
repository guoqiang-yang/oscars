<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/9/19
 * Time: 下午2:37
 */
include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $fid;
    private $info;
    private $method;
    private $sort;

    protected function checkAuth()
    {
        parent::checkAuth('/activity/add_floor_sale');
    }

    protected function getPara()
    {
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
        $this->method = Tool_Input::clean('r', 'method', TYPE_STR);
        $this->fid = Tool_Input::clean('r', 'fid', TYPE_UINT);
        $this->sort = Tool_Input::clean('r', 's_sort', TYPE_UINT);
        $this->type = Tool_Input::clean('r', 'type', TYPE_UINT);
        $this->start_time = Tool_Input::clean('r', 'start_time', TYPE_STR);
        $this->end_time = Tool_Input::clean('r', 'end_time', TYPE_STR);
        $this->info = array(
            'fid' => Tool_Input::clean('r', 'fid', TYPE_UINT),
            'pid' => Tool_Input::clean('r', 'pid', TYPE_UINT),
            'type' => Tool_Input::clean('r', 'type', TYPE_UINT),
            'position' => Tool_Input::clean('r', 'position', TYPE_UINT),
            'name' => Tool_Input::clean('r', 'name', TYPE_STR),
            'url' => Tool_Input::clean('r', 'url', TYPE_STR),
            'mark' => Tool_Input::clean('r', 'mark', TYPE_UINT),
            'sort' => Tool_Input::clean('r', 'sort', TYPE_UINT),
            'sale_price' => Tool_Input::clean('r', 'price', TYPE_STR),
            'sale_num' => Tool_Input::clean('r', 'sale_num', TYPE_UINT),
            'limit_count' => Tool_Input::clean('r', 'limit_count', TYPE_UINT),
            'start_time' => Tool_Input::clean('r', 'start_time', TYPE_STR),
            'end_time' => Tool_Input::clean('r', 'end_time', TYPE_STR),
            'pic_url' => Tool_Input::clean('r', 'pic_url', TYPE_STR),
            'detail' => Tool_Input::clean('r', 'detail', TYPE_STR),
            'activity_type'=> Tool_Input::clean('r', 'activity_type', TYPE_INT),
            'commodity_sid'=> str_replace('，',',',preg_replace('# #','',Tool_Input::clean('r', 'commodity_sid', TYPE_STR))),
        );
    }

    protected function checkPara()
    {
        if (empty($this->sort) && empty($this->method))
        {
            if ($this->info['type'] == 1)
            {
                $this->info['position'] = 1;
                if ($this->info['activity_type'] == 1)
                {
                    if(empty($this->info['url']))
                    {
                        throw new Exception('参数不合法');
                    }

                    if(!empty($this->info['commodity_sid']))
                    {
                        throw new Exception('您已选择文章类,请勿填写商品SID');
                    }
                    if (empty($this->info['fid']) || empty($this->info['name']) || empty($this->info['pic_url']) ||  empty($this->info['url']))
                    {
                        throw new Exception('参数不合法');
                    }
                }
                else if($this->info['activity_type'] == 2)
                {
                    if(empty($this->info['commodity_sid']))
                    {
                        throw new Exception('参数不合法');
                    }
                    if(!empty($this->info['url']))
                    {
                        throw new Exception('您已选择落地页类,请勿填写链接地址');
                    }
                    if (count(explode(',',$this->info['commodity_sid']))>50)
                    {
                        throw new Exception('商品SID最多为50个');
                    }
                    if (empty($this->info['fid']) || empty($this->info['name']) || empty($this->info['pic_url']) ||  empty($this->info['commodity_sid']))
                    {
                        throw new Exception('参数不合法');
                    }
                }
            }
            else if ($this->info['type'] >= 2)
            {
                if ($this->info['position'] == 1)
                {
                    if (empty($this->info['fid']) || empty($this->info['sale_price']) || empty($this->info['name']) || empty($this->info['url']) || empty($this->info['pic_url']))
                    {
                        throw new Exception('参数不合法');
                    }
                }
                else if ($this->info['position'] == 2)
                {
                    if (empty($this->info['fid']) || empty($this->info['sale_price']) || empty($this->info['name']) || empty($this->info['mark']) || empty($this->info['pic_url']))
                    {
                        throw new Exception('参数不合法');
                    }
                }
                else
                {
                    throw new Exception('参数不合法');
                }
            }
            else
            {
                throw new Exception('参数不合法');
            }
            if (empty($this->sid))
            {
                if (!empty($this->info['start_time']) && !empty($this->info['end_time']))
                {
                    if ($this->info['start_time'] >= $this->info['end_time'])
                    {
                        throw new Exception('活动开始时间不能小于结束时间');
                    }
                    if ($this->info['start_time'] <= date('Y-m-d H:i:s'))
                    {
                        throw new Exception('活动开始时间不能小于当前时间');
                    }
                }
                else
                {
                    throw new Exception('参数不合法');
                }
            }
        }
    }

    protected function main()
    {
        if (empty($this->info['sale_price']))
        {
            $this->info['sale_price'] = 0;
        }
        $this->info['start_time'] = str_replace('T', ' ', $this->info['start_time']) . ':00';
        $this->info['end_time'] = str_replace('T', ' ', $this->info['end_time']) . ':00';
        $this->start_time = str_replace('T', ' ', $this->start_time) . ':00';
        $this->end_time = str_replace('T', ' ', $this->end_time) . ':00';
        if (empty($this->sid) && empty($this->method))
        {
            //查验该楼层在该时间段内是否有商品活动
            $data = Activity_Floor_Sale_Api::getList(array('online' => 0, 'fid' => $this->fid, 'type' => $this->type, 'date' => date('Y-m-d H:i:s'),));
            if ($data['total'] >= 1)
            {
                foreach ($data['list'] as $key => $value)
                {
                    if ($key != $this->sid)
                    {
                        if (!($value['start_time'] > $this->end_time || $value['end_time'] < $this->start_time))
                        {
                            throw new Exception('该商品活动时间段内已有其他商品活动');
                        }
                    }
                }
            }
            $this->id = Activity_Floor_Sale_Api::add($this->info);
        }
        else if (!empty($this->sid) && (empty($this->method) && empty($this->sort)))
        {
            //查验该楼层在该时间段内是否有商品活动
            $data = Activity_Floor_Sale_Api::getList(array('online' => 0, 'fid' => $this->fid, 'type' => $this->type, 'date' => date('Y-m-d H:i:s'),));
            if ($data['total'] >= 1)
            {
                foreach ($data['list'] as $key => $value)
                {
                    if ($key != $this->sid)
                    {
                        if (!($value['start_time'] > $this->end_time || $value['end_time'] < $this->start_time))
                        {
                            throw new Exception('该商品活动时间段内已有其他商品活动');
                        }
                    }
                }
            }
            $this->id = Activity_Floor_Sale_Api::update($this->sid, $this->info);
        }
        else if (!empty($this->sid) && !empty($this->method))
        {
            if ($this->method == 'up')
            {
                //查验该楼层在该时间段内是否有商品活动
                $data = Activity_Floor_Sale_Api::getList(array('online' => 0, 'fid' => $this->fid, 'type' => $this->type));
                if ($data['total'] >= 1)
                {
                    foreach ($data['list'] as $key => $value)
                    {
                        if ($key != $this->sid)
                        {
                            if (!($value['start_time'] > $this->end_time || $value['end_time'] < $this->start_time))
                            {
                                throw new Exception('该商品活动时间段内已有其他商品活动');
                            }
                        }
                    }
                }
                $date = date('Y-m-d H:i:s');
                if ($date <= $this->start_time)
                {
                    throw new Exception('该商品上架时间晚于当前时间，若想立即上架该商品，请手动修改上架时间！');
                }
                $this->id = Activity_Floor_Sale_Api::update($this->sid, array('online' => 0));
            }
            else if ($this->method == 'down')
            {
                $saleInfo = Activity_Floor_Sale_Api::getOne($this->sid);
                $fid = $saleInfo['fid'];

                //查验该楼层活动是否有大图，小图是否小于三个
                $date = date('Y-m-d H:i:s');
                $products = Activity_Floor_Sale_Api::getList(array('online' => 0, 'date' => $date, 'fid' => $fid));
                $has_big = 0;
                $pic_count = 0;
                foreach ($products['list'] as $product)
                {
                    if ($product['type'] == 1)
                    {
                        $has_big++;
                    }
                    else
                    {
                        $pic_count++;
                    }
                }
                if ($has_big == 1 && $saleInfo['type'] == 1)
                {
                    throw new Exception('楼层活动必须有在线大图');
                }
                if ($pic_count <= 3 && $saleInfo['type'] != 1)
                {
                    throw new Exception('楼层活动在线小图数量不能小于3个');
                }
                $this->id = Activity_Floor_Sale_Api::update($this->sid, array('online' => 1));
            }
        }
        if (!empty($this->sid) && !empty($this->sort))
        {
            $this->id = Activity_Floor_Sale_Api::update($this->sid, array('sort' => $this->sort));
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