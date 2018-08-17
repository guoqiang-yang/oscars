<?php
/**
 * 型号相关业务
 */
class Shop_Model extends Base_Func
{
    private $modelDao;

    public function __construct()
    {
        $this->modelDao = new Data_Dao('t_model');

        parent::__construct();
    }

    /**
     * 添加型号
     * @param array $info
     * @return mixed
     */
    public function add(array $info)
    {
        return $this->modelDao->add($info);
    }

    /**
     * 删除
     * @param $mid
     * @return bool
     */
    public function delete($mid)
    {
        return $this->modelDao->delete($mid);
    }

    /**
     * 更新
     * @param $mid
     * @param array $info
     * @return int
     */
    public function update($mid, array $info)
    {
        return $this->modelDao->update($mid, $info);
    }

    /**
     * 获取
     * @param $mid
     * @return array
     */
    public function get($mid)
    {
        return $this->modelDao->get($mid);
    }

    /**
     * 获取多个
     * @param array $mids
     * @return array
     */
    public function getBulk(array $mids)
    {
        return $this->modelDao->getList($mids);
    }

    /**
     * 获取全部
     * @return array
     */
    public function getAll()
    {
        return $this->modelDao->getAll();
    }

    /**
     * 根据分类获取
     * @param $cate2
     * @return array
     */
    public function getListOfCate($cate2)
    {
        $where = array('status' => Conf_Base::STATUS_NORMAL);
        $cate2 > 0 && $where['cate2'] = $cate2;

        return $this->modelDao->order('sortby', 'desc')->getListWhere($where);
    }
    public function getByName($keyword)
    {
        $where = "name like '%".$keyword."%'";
        return $this->modelDao->getListWhere($where);
    }

	public function getModelName($mids)
	{
		static $models = array();
		if (empty($models))
		{
			$models = $this->getAll();
			$models = Tool_Array::list2Map($models, 'mid');
		}

		if ($mids)
		{
			$strs = array();
			if (is_string($mids))
			{
				$mids = explode(',', $mids);
			}
			foreach ($mids as $mid)
			{
				$strs []= $models[$mid]['name'];
			}

			return implode(',', $strs);
		}
		return '';
	}
}
