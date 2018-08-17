<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 15/12/23
 * Time: 17:22
 *
 * 做一个ORM的基类，封装了一些增删改查的方法
 *
 * 如果你需要在某个方法之前做些事情，可以重写该方法并考虑在最后用super调用父类的方法
 */

class Base_ORM
{
    protected $one;
    protected $mc;
    protected $idField = 'id';  //表的id字段
    protected $table = '';           //表名
    protected $fields = array();          //表的有效字段，除此之外的其他值都将被忽略

    public function __construct()
    {
        $this->one = Data_One::getInstance();
        $this->mc = Data_Memcache::getInstance();
    }

    public function get($id, $fields = array('*'))
    {
        $id = intval($id);
        assert($id > 0);

        $where = array($this->idField => $id);
        $data = $this->one->select($this->table, $fields, $where);
        if (empty($data['data']))
        {
            return array();
        }

        return $data['data'][0];
    }

    public function getByIds($ids, $fields = array('*'))
    {
        $ids = array_unique(array_filter($ids));
        assert(!empty($ids));

        $where = array($this->idField => $ids);
        $data = $this->one->select($this->table, $fields, $where);
        if (empty($data['data']))
        {
            return array();
        }

        $list = Tool_Array::list2Map($data['data'], $this->idField);
        return $list;
    }

    public function getByFields($fields, $order = '', $start = 0, $num = 0, &$total = 0)
    {
        assert(!empty($fields));

        if (empty($fields))
        {
            return array();
        }

        if (empty($order))
        {
            $order = ' order by ' . $this->idField . ' desc ';
        }
        $data = $this->one->select($this->table, array('*'), $fields, $order, $start, $num);
        if (empty($data['data']))
        {
            return array();
        }

        $total = $this->getTotal($fields);
        $list = Tool_Array::list2Map($data['data'], $this->idField);

        return $list;
    }

    public function getTotal($where)
    {
        // 查询数量
        $data = $this->one->select($this->table, array('count(1)'), $where);
        $total = intval($data['data'][0]['count(1)']);

        return $total;
    }

    public function getSum($where, $field)
    {
        $data = $this->one->select($this->table, array('sum(' . $field . ')'), $where);
        $sum = intval($data['data'][0]['sum(' . $field . ')']);

        return $sum;
    }

    public function getAll($fields = array('*'))
    {
        $data = $this->one->select($this->table, $fields, array());
        if (empty($data['data']))
        {
            return array();
        }

        $list = Tool_Array::list2Map($data['data'], $this->idField);
        return $list;
    }

    //这个方法各个类的实现都不一样，自己写吧~
    //或者后期有了想法再补
    //本来想得是，数字就用等于，字符串就用like，但是有像手机号这样的存在~
//    public function getByFields()
//    {
//
//    }

    public function add($info)
    {
        assert(!empty($info));

        //过滤字段
        $add = Tool_Array::checkCopyFields($info, $this->fields);
        if (empty($add))
        {
            return 0;
        }

        $date = date('Y-m-d H:i:s');
        //补充ctime和mtime
        if (in_array('ctime', $this->fields) && !isset($info['ctime']))
        {
            $add['ctime'] = $date;
        }
        if (in_array('mtime', $this->fields) && !isset($info['mtime']))
        {
            $add['mtime'] = $date;
        }

        $res = $this->one->insert($this->table, $add);

        return $res['insertid'];
    }

    public function update($id, $update, $change = array())
    {
        $id = intval($id);
        assert( $id > 0 );

        $updateNew = Tool_Array::checkCopyFields($update, $this->fields);
        $changeNew = Tool_Array::checkCopyFields($change, $this->fields);

        $where = array($this->idField => $id);
        $ret = $this->one->update($this->table, $updateNew, $changeNew, $where);

        return $ret['affectedrows'];
    }

    public function delete($id)
    {
        $id = intval($id);
        assert($id > 0);

        $where = array($this->idField => $id);
        $ret = $this->one->delete($this->table, $where);

        return $ret['affectedrows'];
    }

    public function getByWhere($where, $order = '', $start = 0, $num = 0, &$total = 0)
    {
        if (empty($order))
        {
            $order = ' order by ' . $this->idField . ' desc ';
        }
        $data = $this->one->select($this->table, array('*'), $where, $order, $start, $num);
        if (empty($data['data']))
        {
            return array();
        }

        $total = $this->getTotal($where);
        $list = Tool_Array::list2Map($data['data'], $this->idField);

        return $list;
    }
}
