<?php

/**
 * 司机、搬运工结算单
 */
class Logistics_Coopworker_Statement extends Base_Func
{
    private $statementDao;

    public function __construct()
    {
        $this->statementDao = new Data_Dao('t_coopworker_statement');

        parent::__construct();
    }

    public function add($info)
    {
        return $this->statementDao->add($info);
    }

    public function getByWhere($where)
    {
        $data = $this->statementDao->getListWhere($where);
        return $data;
    }

    public function getById($id, $filed=array('*'))
    {
        if (is_array($id))
        {
            $data = $this->statementDao->setFields($filed)->getList($id);
        }
        else
        {
            $data = $this->statementDao->setFields($filed)->get($id);
        }

        return $data;
    }

    public function updateByWhere($where, $upData)
    {
        assert(!empty($where));
        assert(!empty($upData));

        $affectRaw = $this->statementDao->updateWhere($where, $upData);

        return $affectRaw;
    }
}