<?php
/**
 * Func 基础
 */

class Base_Func
{
	protected $one;
	protected $mc;
    
    protected $response;

    public function __construct()
	{
		$this->one = Data_One::getInstance();
		$this->mc = Data_Memcache::getInstance();
        
        // 初始化返回结果; 如果需要其他结构，可以重写$this->response
        $this->response = array(
            'total' => 0,
            'data' => array(),
        );
	}
    
    protected function is($param)
    {
        return (isset($param)&&!empty($param))? true: false;
    }
}
