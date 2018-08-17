<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/10/14
 * Time: 下午4:08
 */
class Order_Quick_Api extends Base_Api
{
    public static function add($info)
    {
        $aa = new Order_Quick();
        return $aa->add($info);
    }

    public static function getList($searchConf, $start=0, $num=20)
    {
        $aa = new Order_Quick();
        $data = $aa->getList($searchConf, $start, $num);
        if ($data['total'] <= 0)
        {
            return $data;
        }

        $list = $data['list'];
        $total = $data['total'];
        foreach ($list as  &$item ) {
            $item['pic_url'] = Oss_Api::getImageUrl($item['pic_url']);
            $item['_platform'] = Conf_Activity_Flash_Sale::$PALTFORM[$item['platform']];
        }

        if (empty($list))
        {
            return $list;
        }
        return array('list' => $list, 'total' => $total);
    }
    public static function getOne($pk)
    {
        $aa = new Order_Quick();
        $data = $aa->getOne($pk);
        $data['_art_url'] = ADMIN_HOST.$data['art_url'];
        return $data;
    }
    public static function update($id, $update, $change = array()){
        $aa = new Order_Quick();
        return $aa->update($id, $update, $change);
    }
}