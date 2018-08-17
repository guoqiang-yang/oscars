<?php

/**
 * Key-value DB
 *
 * 使用mysql表存储简单的key-value形式的数据，除了key，value，还附加了过期时间，创建时间，修改时间
 * 等辅助字段。如果你的逻辑中用不到这几个字段，可以放空。
 */
class Data_Kvdb extends Base_Func
{
    private $cache = array();
    const DB_NAME = 't_kvstore';

    public function set($name, $value, $expireAt = 0)
    {
        $name = trim($name);
        $value = trim($value);

        if (empty($name) || empty($value))
        {
            return TRUE;
        }

        if ($expireAt == 0)
        {
            $expireAt = '0000-00-00 00:00:00';
        }

        if (isset($this->cache[$name]) && $this->cache[$name] == $value)
        {
            return TRUE;
        }

        $res = $this->one->select(self::DB_NAME, array('*'), array('name' => $name));
        if (empty($res['data']))
        {
            $t = date('Y-m-d H:i:s');
            $arr = array(
                'name' => $name, 'value' => $value, 'expire_at' => $expireAt, 'ctime' => $t, 'mtime' => $t,
            );
            $this->cache[$name] = $arr;
            $ret = $this->one->setDefaultDB()->insert(self::DB_NAME, $arr);
        }
        else
        {
            $arr = array(
                'value' => $value,
            );
            $expireAt && $arr['expire_at'] = $expireAt;
            $where = array(
                'name' => $name,
            );
            $this->cache[$name]['value'] = $value;
            $expireAt && ($this->cache[$name]['expire_at'] = $expireAt);
            $ret = $this->one->setDefaultDB()->update(self::DB_NAME, $arr, array(), $where);
        }

        return intval($ret['affectedrows']);
    }

    public function increase($name, $expireAt = 0)
    {
        $name = trim($name);

        if (empty($name))
        {
            return FALSE;
        }

        if ($expireAt == 0)
        {
            $expireAt = '0000-00-00 00:00:00';
        }

        $res = $this->one->select(self::DB_NAME, array('*'), array('name' => $name));
        if (empty($res['data']))
        {
            $t = date('Y-m-d H:i:s');
            $arr = array(
                'name' => $name, 'value' => 1, 'expire_at' => $expireAt, 'ctime' => $t, 'mtime' => $t,
            );
            $this->cache[$name] = $arr;
            $ret = $this->one->setDefaultDB()->insert(self::DB_NAME, $arr);
        }
        else
        {
            $arr = array(
                'value' => 1,
            );
            $update = array();
            $expireAt && $update['expire_at'] = $expireAt;
            $where = array(
                'name' => $name,
            );
            $this->cache[$name]['value']++;
            $expireAt && ($this->cache[$name]['expire_at'] = $expireAt);
            $ret = $this->one->setDefaultDB()->update(self::DB_NAME, $update, $arr, $where);
        }

        return intval($ret['affectedrows']);
    }

    public function get($name)
    {
        $name = trim($name);
        assert(strlen($name) > 0);

        if (!isset($this->cache[$name]))
        {
            $res = $this->one->select(self::DB_NAME, array('*'), array('name' => $name));
            if (empty($res['data']))
            {
                $this->cache[$name] = array();
            }
            else
            {
                $this->cache[$name] = $res['data'][0];
            }
        }

        $value = isset($this->cache[$name]) ? $this->cache[$name] : array();

        return $value;
    }

    public function getByPrefix($prefix)
    {
        $prefix = trim($prefix);
        assert(strlen($prefix) > 0);

        $where = sprintf('name like "%s%%"', $prefix);
        $res = $this->one->select(self::DB_NAME, array('*'), $where);
        $values = empty($res['data']) ? array() : $res['data'];

        return $values;
    }

    public function remove($name)
    {
        $name = trim($name);
        assert(strlen($name) > 0);

        $ret = $this->one->delete(self::DB_NAME, array('name' => $name));
        unset($this->cache[$name]);

        return $ret['affectedrows'];
    }
}