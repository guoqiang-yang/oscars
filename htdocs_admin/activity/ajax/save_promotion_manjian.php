<?php
/**
 * Created by PhpStorm.
 * User: zouliangwei
 * Date: 16/11/2
 * Time: 下午4:54
 */

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $id;
    private $action_type;
    private $info;

    protected function checkAuth()
    {
        parent::checkAuth('/activity/promotion_manjian_update');
    }

    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->action_type = Tool_Input::clean('r', 'action_type', TYPE_STR);
        if ($this->action_type == 'online') {
            $this->info = array(
                'm_status' => 0,
            );
        } elseif ($this->action_type == 'offline') {
            $this->info = array(
                'm_status' => 4,
            );
        } elseif ($this->action_type == 'delete') {
            $this->info = array(
                'm_status' => 1,
            );
        } else {
            if (!empty($this->id)) {
                $pa = new Activity_Promotion_Manjian();
                $pa_info = $pa->getItem($this->id);
            }
            if (!empty($this->id) && $pa_info['m_status'] == Conf_Activity::AT_PROMOTION_STATUS_ONLINE) {
                $this->info = array(
                    'title' => Tool_Input::clean('r', 'title', TYPE_STR),
                    'user_whitelist' => Tool_Input::clean('r', 'user_whitelist', TYPE_STR),
                    'user_blacklist' => Tool_Input::clean('r', 'user_blacklist', TYPE_STR),
                    'goods_whitelist' => Tool_Input::clean('r', 'goods_whitelist', TYPE_STR),
                    'goods_blacklist' => Tool_Input::clean('r', 'goods_blacklist', TYPE_STR),
                );
            } else {
                $this->info = array(
                    'title' => Tool_Input::clean('r', 'title', TYPE_STR),
                    'activity_type' => Tool_Input::clean('r', 'activity_type', TYPE_UINT),
                    'type_ids' => Tool_Input::clean('r', 'type_ids', TYPE_ARRAY),
                    'activity_bear' => Tool_Input::clean('r', 'activity_bear', TYPE_UINT),
                    'stime' => Tool_Input::clean('r', 'stime', TYPE_STR),
                    'etime' => Tool_Input::clean('r', 'etime', TYPE_STR),
                    'm_type' => Tool_Input::clean('r', 'm_type', TYPE_UINT),
                    'conf_man' => Tool_Input::clean('r', 'conf_man', TYPE_ARRAY),
                    'conf_jian' => Tool_Input::clean('r', 'conf_jian', TYPE_ARRAY),
                    'conf_coupon' => Tool_Input::clean('r', 'conf_coupon', TYPE_ARRAY),
                    'conf_sid' => Tool_Input::clean('r', 'conf_sid', TYPE_ARRAY),
                    'conf_price' => Tool_Input::clean('r', 'conf_price', TYPE_ARRAY),
                    'conf_num' => Tool_Input::clean('r', 'conf_num', TYPE_ARRAY),
                    'city_ids' => Tool_Input::clean('r', 'city_ids', TYPE_ARRAY),
                    'user_type' => Tool_Input::clean('r', 'user_type', TYPE_UINT),
                    'user_type_extand' => Tool_Input::clean('r', 'user_type_extand', TYPE_ARRAY),
                    'user_count' => Tool_Input::clean('r', 'user_count', TYPE_UINT),
                    'user_whitelist' => Tool_Input::clean('r', 'user_whitelist', TYPE_STR),
                    'user_blacklist' => Tool_Input::clean('r', 'user_blacklist', TYPE_STR),
                    'goods_is_sand' => Tool_Input::clean('r', 'goods_is_sand', TYPE_UINT),
                    'goods_is_meichao' => Tool_Input::clean('r', 'goods_is_meichao', TYPE_UINT),
                    'goods_is_special' => Tool_Input::clean('r', 'goods_is_special', TYPE_UINT),
                    'goods_is_hot' => Tool_Input::clean('r', 'goods_is_hot', TYPE_UINT),
                    'goods_type' => Tool_Input::clean('r', 'goods_type', TYPE_UINT),
                    'goods_cate_ids' => Tool_Input::clean('r', 'goods_cate_ids', TYPE_ARRAY),
                    'goods_brand_ids' => Tool_Input::clean('r', 'goods_brand_ids', TYPE_STR),
                    'goods_whitelist' => Tool_Input::clean('r', 'goods_whitelist', TYPE_STR),
                    'goods_blacklist' => Tool_Input::clean('r', 'goods_blacklist', TYPE_STR),
                    'order_mode' => Tool_Input::clean('r', 'order_mode', TYPE_UINT),
                    'pay_mode' => Tool_Input::clean('r', 'pay_mode', TYPE_UINT),
                    'delivery_time_type' => Tool_Input::clean('r', 'delivery_time_type', TYPE_UINT),
                    'delivery_after_day' => Tool_Input::clean('r', 'delivery_after_day', TYPE_UINT),
                    'delivery_stime' => Tool_Input::clean('r', 'delivery_stime', TYPE_STR),
                    'delivery_etime' => Tool_Input::clean('r', 'delivery_etime', TYPE_STR),
                    'activity_content' => Tool_Input::clean('r', 'activity_content', TYPE_STR),
                );
            }
        }
    }

    protected function checkPara()
    {
        if (empty($this->action_type)) {

            if (isset($this->info['title']) && empty($this->info['title'])) {
                throw new Exception('活动名称不能为空！');
            }
            if (isset($this->info['type_ids']) && !empty($this->info['type_ids'])) {
                $this->info['type_ids'] = implode(',', $this->info['type_ids']);
            } elseif (isset($this->info['type_ids'])) {
                $this->info['type_ids'] = '';
            }
            if (isset($this->info['stime']) && empty($this->info['stime'])) {
                throw new Exception('活动开始时间不能为空！');
            } elseif (isset($this->info['stime'])) {
                $this->info['stime'] = date('Y-m-d H:i:s', strtotime($this->info['stime']));
            }

            if (isset($this->info['etime']) && empty($this->info['etime'])) {
                throw new Exception('活动结束时间不能为空！');
            } elseif (isset($this->info['etime'])) {
                $this->info['etime'] = date('Y-m-d H:i:s', strtotime($this->info['etime']));
            }

            isset($this->info['activity_type']) && $this->_checkConf();

            if (isset($this->info['city_ids']) && !empty($this->info['city_ids'])) {
                $this->info['city_ids'] = implode(',', $this->info['city_ids']);
            } elseif (isset($this->info['city_ids'])) {
                throw new Exception('请勾选参加活动的城市！');
            }

            if (isset($this->info['user_type']) && $this->info['user_type'] == 1 && empty($this->info['user_type_extand'])) {
                throw new Exception('参与用户为部分用户时需要勾选最少一项！');
            } elseif (isset($this->info['user_type']) && $this->info['user_type'] == 1) {
                $this->info['user_type_extand'] = implode(',', $this->info['user_type_extand']);
            }
            if (isset($this->info['goods_type']) && $this->info['goods_type'] == 1 && empty($this->info['goods_cate_ids'])) {
                throw new Exception('参与商品分类为部分时需要勾选最少一项分类！');
            } elseif (isset($this->info['goods_type']) && $this->info['goods_type'] == 1) {
                $this->info['goods_cate_ids'] = implode(',', $this->info['goods_cate_ids']);
            }

            if (isset($this->info['delivery_time_type']) && $this->info['delivery_time_type'] == 1 && (empty($this->info['delivery_stime']) || empty($this->info['delivery_etime']))) {
                throw new Exception('配送时间有要求时，配送时间段必填！');
            } elseif (isset($this->info['delivery_time_type']) && $this->info['delivery_time_type'] == 1) {
                $this->info['delivery_time_extand'] = implode(',', array($this->info['delivery_after_day'], $this->info['delivery_stime'], $this->info['delivery_etime']));
            }
            if (isset($this->info['user_whitelist']) && !$this->isGoodsList($this->info['user_whitelist'])) {
                throw new Exception('用户白名单格式不正确');
            }
            if (isset($this->info['user_blacklist']) && !$this->isGoodsList($this->info['user_blacklist'])) {
                throw new Exception('用户黑名单格式不正确');
            }
            if (isset($this->info['goods_whitelist']) && !$this->isGoodsList($this->info['goods_whitelist'])) {
                throw new Exception('商品白名单格式不正确');
            }
            if (isset($this->info['goods_blacklist']) && !$this->isGoodsList($this->info['goods_blacklist'])) {
                throw new Exception('商品黑名单格式不正确');
            }
            unset($this->info['delivery_after_day']);
            unset($this->info['delivery_stime']);
            unset($this->info['delivery_etime']);
        }
    }

    protected function main()
    {
        if (empty($this->id)) {
            $this->info['create_suid'] = $this->_uid;
            $this->info['ctime'] = date('Y-m-d H:i:s');
            $this->id = Activity_Api::addPromotionManjian($this->info);
            $info = array(
                'admin_id' => $this->_uid,
                'obj_id' => $this->id,
                'obj_type' => Conf_Admin_Log::OBJTYPE_PROMOTION_ACTIVITY,
                'action_type' => 1,
                'params' => json_encode(array('id' => $this->id, 'json' => json_encode($this->info))),
            );
            Admin_Common_Api::addAminLog($info);
        } else {
            $oldInfo = Activity_Api::getManjianItem($this->id);
            $this->info['edit_suid'] = $this->_uid;
            Activity_Api::updatePromotionManjian($this->id, $this->info);
            if ($this->action_type == 'online') {
                $info = array(
                    'admin_id' => $this->_uid,
                    'obj_id' => $this->id,
                    'obj_type' => Conf_Admin_Log::OBJTYPE_PROMOTION_ACTIVITY,
                    'action_type' => 3,
                    'params' => json_encode(array('id' => $this->id, 'ctime' => date('Y-m-d H:i:s', time()))),
                );
            } elseif ($this->action_type == 'offline') {
                $info = array(
                    'admin_id' => $this->_uid,
                    'obj_id' => $this->id,
                    'obj_type' => Conf_Admin_Log::OBJTYPE_PROMOTION_ACTIVITY,
                    'action_type' => 4,
                    'params' => json_encode(array('id' => $this->id, 'ctime' => date('Y-m-d H:i:s', time()))),
                );
            } elseif ($this->action_type == 'delete') {
                $info = array(
                    'admin_id' => $this->_uid,
                    'obj_id' => $this->id,
                    'obj_type' => Conf_Admin_Log::OBJTYPE_PROMOTION_ACTIVITY,
                    'action_type' => 5,
                    'params' => json_encode(array('id' => $this->id, 'ctime' => date('Y-m-d H:i:s', time()))),
                );
            } else {
                foreach ($this->info as $key => $value) {
                    if ($oldInfo[$key] == $value) {
                        unset($this->info[$key]);
                    }
                }
                $info = array(
                    'admin_id' => $this->_uid,
                    'obj_id' => $this->id,
                    'obj_type' => Conf_Admin_Log::OBJTYPE_PROMOTION_ACTIVITY,
                    'action_type' => 2,
                    'params' => json_encode(array('id' => $this->id, 'json' => json_encode($this->info))),
                );
            }
            Admin_Common_Api::addAminLog($info);
        }
    }

    private function _checkConf()
    {
        $coupon = new Activity_Coupon();
        $tmp_arr = array();
        switch ($this->info['activity_type']) {
            case Conf_Activity::AT_PROMOTION_TYPE_MANJIAN:
                foreach ($this->info['conf_man'] as $key => $item) {
                    if (empty($item)) {
                        throw new Exception('请填写完整额度配置下单额度!');
                    }
                    if (empty($this->info['conf_jian'][$key])) {
                        throw new Exception('请填写完整额度配置立减额度！');
                    }
                    $tmp_arr[] = $item . ':' . $this->info['conf_jian'][$key];
                }
                break;
            case Conf_Activity::AT_PROMOTION_TYPE_DISCOUNT:
                foreach ($this->info['conf_man'] as $key => $item) {
                    if (empty($item)) {
                        throw new Exception('请填写完整额度配置下单额度!');
                    }
                    if (empty($this->info['conf_coupon'][$key])) {
                        throw new Exception('请填写完整额度配置优惠券ID！');
                    }
                    $coupon_info = $coupon->get($this->info['conf_coupon'][$key]);
                    if (empty($coupon_info)) {
                        throw new Exception('优惠券ID:' . $this->info['conf_coupon'][$key] . ' 不存在！');
                    }
                    if (empty($this->info['conf_num'][$key])) {
                        throw new Exception('请填写完整额度配置送券数量');
                    }
                    $tmp_arr[] = $item . ':' . $this->info['conf_num'][$key] . ':' . $this->info['conf_coupon'][$key];
                }
                break;
            case Conf_Activity::AT_PROMOTION_TYPE_REBATE:
                if ($this->info['m_type'] == 1) {
                    $tmp_rebate = Tool_Input::clean('r', 'conf_man', TYPE_STR);
                    $tmp_amount = Tool_Input::clean('r', 'conf_amount', TYPE_UINT);
                    if ($tmp_rebate < 70 || $tmp_rebate >= 100) {
                        throw new Exception('平台折扣比例区间在70 <= x < 100之间');
                    }
                    $tmp_arr[] = $tmp_rebate . ':' . $tmp_amount;
                }
                break;
            case Conf_Activity::AT_PROMOTION_TYPE_GIFT:
                foreach ($this->info['conf_man'] as $key => $item) {
                    if (empty($item)) {
                        throw new Exception('请填写完整额度配置下单额度!');
                    }
                    if (empty($this->info['conf_sid'][$key])) {
                        throw new Exception('下单额度为' . $item . '，请填写完整额度配置赠送商品SID！');
                    }
                    if (!is_numeric($this->info['conf_sid'][$key])) {
                        throw new Exception('请输入合法的sku-id：单一skuid');
                    }
                    $sidInfos = explode(',', $this->info['conf_sid'][$key]);
                    foreach ($sidInfos as $sid) {
                        $skuInfo = Shop_Api::getSkuInfo($sid);
                        if (empty($skuInfo)) {
                            throw new Exception('下单额度为' . $item . '，商品SID:' . $sid . ' 不存在！');
                        }
                    }
                    if (empty($this->info['conf_num'][$key])) {
                        throw new Exception('下单额度为' . $item . '，请填写完整额度配置赠送商品数量!');
                    }
                    if (!is_numeric($this->info['conf_num'][$key])) {
                        throw new Exception('请输入合法的数量');
                    }
                    $numInfos = explode(',', $this->info['conf_num'][$key]);
                    foreach ($numInfos as $numInfo) {
                        $numInfo = intval($numInfo);
                        if (empty($numInfo)) {
                            throw new Exception('下单额度为' . $item . '，请填写正确的赠送商品数量');
                        }
                    }
                    if (count($sidInfos) != count($numInfos)) {
                        throw new Exception('下单额度为' . $item . '，赠送商品数与商品数量数不一致');
                    }
                    $tmp_arr[] = $item . ':' . $this->info['conf_sid'][$key] . ':' . $this->info['conf_num'][$key];
                }
                break;
            case Conf_Activity::AT_PROMOTION_TYPE_PRICE:
                foreach ($this->info['conf_man'] as $key => $item) {
                    if (empty($item)) {
                        throw new Exception('请填写完整额度配置下单额度!');
                    }
                    if (empty($this->info['conf_sid'][$key])) {
                        throw new Exception('下单额度为' . $item . '，请填写完整额度配置特价商品SID！');
                    }
                    if (!is_numeric($this->info['conf_sid'][$key])) {
                        throw new Exception('请输入合法的sku-id');
                    }
                    $sidInfos = explode(',', $this->info['conf_sid'][$key]);
                    foreach ($sidInfos as $sid) {
                        $skuInfo = Shop_Api::getSkuInfo($sid);
                        if (empty($skuInfo)) {
                            throw new Exception('下单额度为' . $item . '，商品SID:' . $sid . ' 不存在！');
                        }
                    }
                    if (empty($this->info['conf_num'][$key])) {
                        throw new Exception('下单额度为' . $item . '，请填写完整额度配置特价商品数量!');
                    }
                    if (!is_numeric($this->info['conf_num'][$key])) {
                        throw new Exception('请输入合法的数量');
                    }
                    $numInfos = explode(',', $this->info['conf_num'][$key]);
                    foreach ($numInfos as $numInfo) {
                        $numInfo = intval($numInfo);
                        if (empty($numInfo)) {
                            throw new Exception('下单额度为' . $item . '，请填写正确的特价商品数量');
                        }
                    }
                    if (count($sidInfos) != count($numInfos)) {
                        throw new Exception('下单额度为' . $item . '，特价商品数与商品数量数不一致');
                    }
                    $tmp_arr[] = $item . ':' . $this->info['conf_sid'][$key] . ':' . $this->info['conf_price'][$key] . ':' . $this->info['conf_num'][$key];
                }
                break;
            default:
                throw new Exception('活动类型错误');
        }

        $this->info['conf'] = implode("\n", $tmp_arr);
        unset($this->info['conf_man']);
        unset($this->info['conf_jian']);
        unset($this->info['conf_coupon']);
        unset($this->info['conf_sid']);
        unset($this->info['conf_price']);
        unset($this->info['conf_num']);
    }

    protected function outputPage()
    {
        $result = array('id' => $this->id);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }

    private function isGoodsList($goods_list)
    {
        if ($goods_list == '') {
            return true;
        }
        $tmp_list = explode(',', $goods_list);
        if (!empty($tmp_list)) {
            foreach ($tmp_list as $str) {
                $len = strlen($str);
                $tmp_str = (int)$str;
                $len2 = strlen($tmp_str);
                if ($len2 < $len || $tmp_str == 0) {
                    return false;
                }
            }
        }
        return true;
    }

}

$app = new App('pri');
$app->run();