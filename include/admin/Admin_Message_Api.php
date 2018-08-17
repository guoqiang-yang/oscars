<?php

class Admin_Message_Api extends Base_Api
{
    /**
     * 创建任务.
     *
     * @param array $messageParam
     *
     * @return int
     */
    public static function create($messageParam)
    {
        $at = new Admin_Message();
        $id = $at->create($messageParam);

        return $id;
    }

    /**
     * 更新任务.
     *
     * @param array $messageInfo
     * @param array $upData
     */
    public static function update($messageInfo, $upData)
    {
        $id = $messageInfo['id'];
        assert(!empty($id));

        $at = new Admin_Message();

        if (!empty($upData))
        {
            $at->update($id, $upData);
        }
    }

    public static function updateWhere($searchConf, $upData)
    {
        assert(!empty($searchConf));
        $at = new Admin_Message();
        if (!empty($upData))
        {
            $at->updateWhere($searchConf, $upData);
        }
    }

    /**
     * 获取任务列表.
     *
     * @param array $searchConf
     * @param int   $start
     * @param int   $num
     *
     * @return array
     */
    public static function getList($searchConf, $start = 0, $num = 20)
    {
        $at = new Admin_Message();
        $messageList = $at->getList($searchConf, $start, $num);

        return $messageList;
    }

    public static function getTotal($searchConf)
    {
        $at = new Admin_Message();
        $count = $at->getTotal($searchConf);

        return $count;
    }

    public static function getUnreadNumByType($suid, $type)
    {
        $where = sprintf('receive_suid=%d AND m_type=%d AND has_read=0', $suid, $type);
        $am = new Admin_Message();

        return $am->getTotal($where);
    }

    public static function markAsRead($id)
    {
        assert(!empty($id));
        $at = new Admin_Message();
        $at->update($id, array('has_read' => 1));
    }
}