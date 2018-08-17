<?php
/**
 * 品牌相关业务
 */
class Shop_Brand extends Base_Func
{
    private $branDao;
    private $cateBrandDao;

    public function __construct()
    {
        $this->branDao = new Data_Dao('t_brand');
        $this->cateBrandDao = new Data_Dao('t_cate_brand');

        parent::__construct();
    }

    /**
     * 添加品牌
     * @param $name
     * @param $cate2
     * @return mixed
     */
    public function add($name, $cate2)
    {
        $where = array('name' => $name);
        $list = $this->branDao->getListWhere($where);
        if (empty($list))
        {
            $info = array('name' => $name);
            $bid = $this->branDao->add($info);
        }
        else
        {
            $brand = array_shift($list);
            $bid = $brand['bid'];
        }

        $arr = array(
            'bid' => $bid,
            'cate2' => intval($cate2),
        );
        $this->cateBrandDao->add($arr);

        return $bid;
    }

    /**
     * 删除品牌
     * @param $bid
     * @param $cate2
     * @return array
     */
    public function delete($bid, $cate2)
    {
        assert($cate2 > 0);

        $where = array(
            'bid' => $bid,
            'cate2'=> $cate2
        );

        return $this->cateBrandDao->deleteWhere($where);
    }

    /**
     * 更新品牌
     * @param $bid
     * @param $info
     * @return mixed
     */
    public function update($bid, $info)
    {
        return $this->branDao->update($bid, $info);
    }

    /**
     * 更新品牌所属分类
     * @param $bid
     * @param array $info
     * @return mixed
     */
    public function updateCate($bid, array $info)
    {
        $where = array('bid' => $bid);

        return $this->cateBrandDao->updateWhere($where, $info);
    }

    /**
     * 获取品牌信息
     * @param $bid
     * @return array
     */
    public function get($bid)
    {
        return $this->branDao->get($bid);
    }

    /**
     * 获取多个信息
     * @param array $bids
     * @return array
     */
    public function getBulk(array $bids)
    {
        return $this->branDao->getList($bids);
    }

       
    public function getBrandByWhere($where, $field=array('*'), $start=0, $num=20, $order='')
    {
        assert(!empty($where));
        
        return $this->branDao->setFields($field)->order($order)->limit($start, $num)->getListWhere($where);
    }
    
    /**
     * 获取列表
     * @param $cate2
     * @return array
     */
    public function getListOfCate($cate2)
    {
        $where = array('cate2' => $cate2);

        $cateBrands = $this->cateBrandDao->order('sortby', 'desc')->getListWhere($where);
        if (empty($cateBrands))
        {
            return array();
        }

        $bids = Tool_Array::getFields($cateBrands, 'bid');
        $brands = $this->getBulk($bids);

        // 拼装
        foreach ($cateBrands as $idx => $item)
        {
            $bid = $item['bid'];
            $brand = $brands[$bid];
            $cateBrands[$idx]['name'] = $brand['name'];
        }

        return $cateBrands;
    }

    public function getNormalListOfCate($cate2)
    {
        $where = array('cate2' => $cate2);

        $cateBrands = $this->cateBrandDao->order('sortby', 'desc')->getListWhere($where);
        if (empty($cateBrands))
        {
            return array();
        }

        $bids = Tool_Array::getFields($cateBrands, 'bid');
        $brands = $this->getBulk($bids);
        $brandList = array();
        // 拼装
        foreach ($cateBrands as $idx => $item)
        {
            $bid = $item['bid'];
            $brand = $brands[$bid];
            if($brand['status'] == Conf_Base::STATUS_NORMAL)
            {
                $brandList[] = array(
                    'bid' => $bid,
                    'name' => $brand['name']
                );
            }
        }

        return $brandList;
    }
 
    /**
     * 获取全部
     * @return mixed
     */
    public function getAll()
    {
        return $this->cateBrandDao->getAll();
    }

	public function getAllBrands()
	{
		return $this->branDao->getAll();
	}
    public function getByName($keyword)
    {
        $where = "name like '%".$keyword."%'";
        return $this->branDao->getListWhere($where);
    }
}
