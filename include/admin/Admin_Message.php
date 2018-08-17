<?php

/*
 * 消息管理
 */

class Admin_Message extends Base_Func
{
    private $_messageDao;

    function __construct()
    {
        parent::__construct();

        $this->_messageDao = new Data_Dao('t_message');
    }

    /**
     * 创建消息.
     *
     * @param array $messageParams
     *
     * @return int $id
     */
    public function create($messageParams)
    {
        assert(!empty($messageParams));
        assert(!empty($messageParams['m_type']) || !empty($messageParams['content']));
        assert(!empty($messageParams['receive_suid']));
        $messageParams['ctime'] = date('Y-m-d H:i:s');
        $messageParams['mtime'] = date('Y-m-d H:i:s');

        $id = $this->_messageDao->add($messageParams);

        return $id;
    }

    public function update($id, $upData)
    {
        assert(!empty($id));
        $upData['mtime'] = date('Y-m-d H:i:s');
        $affectedrows = $this->_messageDao->update($id, $upData);

        return $affectedrows;
    }

    public function updateWhere($where, $upData)
    {
        assert(!empty($where));
        $upData['mtime'] = date('Y-m-d H:i:s');
        $affectedrows = $this->_messageDao->updateWhere($where, $upData);

        return $affectedrows;
    }

    public function get($id)
    {
        assert(intval($id) > 0);

        return $this->_messageDao->setSlave()->get($id);
    }

    public function getByWhere($where, $start = 0, $num = 20, $order = '')
    {
        assert(!empty($where));

        $ret = $this->_messageDao->setSlave()->order(empty($order) ? 'order by id desc' : $order)->limit($start, $num)->getListWhere($where);

        return $ret;
    }

    /**
     * 获取任务列表.
     *
     * @param array $searchConf
     * @param int $start
     * @param int $num
     *
     * @return array
     */
    public function getList($searchConf, $start = 0, $num = 20)
    {
        $total = $this->_messageDao->setSlave()->getTotal($searchConf);

        $order = 'order by has_read asc, id desc';

        $list = $this->_messageDao->setSlave()->order($order)->limit($start, $num)->getListWhere($searchConf);

        return array(
            'total' => $total,
            'list' => $list
        );
    }

    public function getTotal($searchConf)
    {
        $total = $this->_messageDao->setSlave()->getTotal($searchConf);

        return $total;
    }
}