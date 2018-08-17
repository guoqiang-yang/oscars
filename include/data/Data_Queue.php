<?php
/**
 * 队列工具类
 * User: wangshen
 * Date: 17/3/15
 * Time: 12:07
 *
 * 表结构：
 * CREATE TABLE t_queue (
    id              int             not null auto_increment,
    type            int             not null default 0,
    info            text            not null              comment 'data',
    status          tinyint(4)      not null default 0    comment '0-pending 1-finished 2-failed',
    ctime           timestamp       not null default current_timestamp,
    mtime           timestamp       not null default current_timestamp ON UPDATE CURRENT_TIMESTAMP,
    index (type, status),
    primary key (id)
    ) ENGINE=INNODB DEFAULT CHARSET=utf8;
 */
class Data_Queue extends Base_Func
{
    const
        QUEUE_ITEM_PENDING = 0,
        QUEUE_ITEM_FINISHED = 1,
        QUEUE_ITEM_FAILED = 2;

    private $table = 't_queue';

    public function enqueue($type, array $info)
    {
        $info = json_encode($info);
        $this->one->insert($this->table, array('type'=>$type, 'info'=>$info, 'ctime' => date('Y-m-d H:i:s',time())));
    }

    public function dequeue($type=0, $cnt = 10)
    {
        $where = array('status'=>self::QUEUE_ITEM_PENDING);
        $type = intval($type);
        if (!empty($type))
        {
            $where['type']= $type;
        }
        $res = $this->one->select($this->table, array('*'), $where, 'order by ctime asc', 0, $cnt);
        if($res['rownum'] == 0)
        {
            return array();
        }

        $items = $res['data'];
        foreach($items as &$item)
        {
            $item['info'] = json_decode($item['info'], true);
        }
        return $items;
    }

    public function markItem($id, $status)
    {
        $id = intval($id);
        if(empty($id))
        {
            return;
        }
        $this->one->update($this->table, array('status'=>intval($status)), array(), array('id'=>$id));
    }

    public function markItems(array $ids, $status)
    {
        if(empty($ids))
        {
            return;
        }
        $this->one->update($this->table, array('status'=>intval($status)), array(), array('id'=>$ids));
    }
}
