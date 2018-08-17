<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $id;
    private $info;

    protected function checkAuth()
    {
        parent::checkAuth('/activity/coupon_update');
    }

    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->info = array(
            'title' => Tool_Input::clean('r', 'title', TYPE_STR),
            'coupon_type' => Tool_Input::clean('r', 'coupon_type', TYPE_STR),
            'type_ids' => Tool_Input::clean('r', 'type_ids', TYPE_ARRAY),
            'validity_type' => Tool_Input::clean('r', 'validity_type', TYPE_UINT),
            'validity_stime' => Tool_Input::clean('r', 'validity_stime', TYPE_STR),
            'validity_etime' => Tool_Input::clean('r', 'validity_etime', TYPE_STR),
            'validity_lastdate' => Tool_Input::clean('r', 'validity_lastdate', TYPE_UINT),
            'conf' => Tool_Input::clean('r', 'conf', TYPE_STR),
            'contain_manjian' => Tool_Input::clean('r', 'contain_manjian', TYPE_UINT),
            'contain_sand' => Tool_Input::clean('r', 'contain_sand', TYPE_UINT),
            'contain_meichao' => Tool_Input::clean('r', 'contain_meichao', TYPE_UINT),
            'contain_special' => Tool_Input::clean('r', 'contain_special', TYPE_UINT),
            'contain_hot' => Tool_Input::clean('r', 'contain_hot', TYPE_UINT),
            'goods_whitelist' => Tool_Input::clean('r', 'goods_whitelist', TYPE_STR),
            'goods_blacklist' => Tool_Input::clean('r', 'goods_blacklist', TYPE_STR),
            'coupon_content' => Tool_Input::clean('r', 'coupon_content', TYPE_STR),
        );
    }

    protected function checkPara()
    {
        if (empty($this->info['title']))
        {
            throw new Exception('优惠券名称不能为空！');
        }

        if (!empty($this->info['type_ids'])) {
            $this->info['type_ids'] = implode(',', $this->info['type_ids']);
        }else{
            $this->info['type_ids'] = '';
        }

        if ($this->info['validity_type'] == 1)
        {
            if (empty($this->info['validity_stime']))
            {
                throw new Exception('有效期开始时间不能为空！');
            }

            if (!$this->isDate($this->info['validity_stime']))
            {
                throw new Exception('有效期开始时间格式错误！');
            }

            if (empty($this->info['validity_etime']))
            {
                throw new Exception('有效期结束时间不能为空！');
            }

            if (!$this->isDate($this->info['validity_etime']))
            {
                throw new Exception('有效期结束时间格式错误！');
            }
            $this->info['validity_extand'] = implode(',', array(
                $this->info['validity_stime'],
                $this->info['validity_etime']
            ));
        }
        else
        {
            if (empty($this->info['validity_lastdate']))
            {
                throw new Exception('有效期天数不能为空！');
            }
            $this->info['validity_extand'] = $this->info['validity_lastdate'];
        }
        unset($this->info['validity_stime']);
        unset($this->info['validity_etime']);
        unset($this->info['validity_lastdate']);
        if (empty($this->info['conf']))
        {
            throw new Exception('额度配置不能为空！');
        }

        if ($this->info['coupon_type'] != Conf_Coupon::CATE_COUPON_FRIGHT && !$this->isConf($this->info['conf']))
        {
            throw new Exception('额度配置格式不正确！');
        }elseif($this->info['coupon_type'] == Conf_Coupon::CATE_COUPON_FRIGHT && !is_numeric($this->info['conf']))
        {
            throw new Exception('额度配置格式不正确');
        }
        if (!$this->isGoodsList($this->info['goods_whitelist']))
        {
            throw new Exception('商品白名单格式不正确');
        }
        if (!$this->isGoodsList($this->info['goods_blacklist']))
        {
            throw new Exception('商品黑名单格式不正确');
        }
    }

    protected function main()
    {
        if (empty($this->id))
        {
            $this->info['create_suid'] = $this->_uid;
            $this->info['ctime'] = date("Y-m-d H:i:s");
            $this->id = Activity_Api::addCoupon($this->info);
            $info = array(
                'admin_id' => $this->_uid,
                'obj_id' => $this->id,
                'obj_type' => Conf_Admin_Log::OBJTYPE_COUPON,
                'action_type' => 1,
                'params' => json_encode(array('id' => $this->id, 'json' => json_encode($this->info))),
            );
            Admin_Common_Api::addAminLog($info);
        }
        else
        {
            $this->info['edit_suid'] = $this->_uid;
            Activity_Api::updateCoupon($this->id, $this->info);
            $info = array(
                'admin_id' => $this->_uid,
                'obj_id' => $this->id,
                'obj_type' => Conf_Admin_Log::OBJTYPE_COUPON,
                'action_type' => 2,
                'params' => json_encode(array('id' => $this->id, 'json' => json_encode($this->info))),
            );
            Admin_Common_Api::addAminLog($info);
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

    /**
     * 校验日期格式是否正确
     *
     * @param string $date 日期
     * @param string $formats 需要检验的格式数组
     *
     * @return boolean
     */
    private function isDate($date, $formats = "Y-m-d H:i:s")
    {
        $unixTime = strtotime($date);

        if (!$unixTime)
        { //strtotime转换不对，日期格式显然不对。
            return FALSE;
        }

        if (date($formats, $unixTime) == $date)
        {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * 校验额度配置格式是否正确
     *
     * @param string $conf
     *
     * @return boolean
     */
    private function isConf($conf)
    {
        $conf_list = explode('\n', $conf);
        if (!empty($conf_list))
        {
            foreach ($conf_list as $str)
            {
                list($price, $privilege) = explode(':', $str);
                $price = (int)$price;
                $privilege = (int)$privilege;
                if ($price == 0)
                {
                    return FALSE;
                }
                if ($privilege == 0)
                {
                    return FALSE;
                }
            }
        }

        return TRUE;
    }

    private function isGoodsList($goods_list)
    {
        if ($goods_list == '')
        {
            return TRUE;
        }
        $tmp_list = explode(',', $goods_list);
        if (!empty($tmp_list))
        {
            foreach ($tmp_list as $str)
            {
                $len = strlen($str);
                $tmp_str = (int)$str;
                $len2 = strlen($tmp_str);
                if ($len2 < $len || $tmp_str == 0)
                {
                    return FALSE;
                }
            }
        }

        return TRUE;
    }
}

$app = new App('pri');
$app->run();

