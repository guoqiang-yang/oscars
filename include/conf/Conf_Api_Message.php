<?php

class Conf_Api_Message
{
	
    const Succ = 0;
    const Failure = 1;
    const ServerErr = 500;
    const ClientErr = 400;
    const ParamErr = 401;
    const ReLogin = 302;
    
    public static $Common_Api_Desc = array(
        self::Succ => '成功',
        self::Failure => '失败',
        self::ServerErr => '服务端错误',
        self::ClientErr => '客户端错误',
        self::ParamErr => '参数错误',
        self::ReLogin => '请登录',
    );
    
    
    /**
     * app-api使用.
     */
    public static function genAppapiResponse($errno, $errmsg='')
    {
        $response = new stdClass();
        $response->errno = $errno;
        $response->errmsg = !empty($errmsg)? $errmsg: 
                (array_key_exists($errno, self::$Common_Api_Desc)? 
                    self::$Common_Api_Desc[$errno]:self::$Common_Api_Desc[self::Failure]);
        
        return $response;
    }
    
	const Outer_Api_St_Succ = 0;
	const Outer_Api_St_Fail = 10000;
	const Outer_Api_St_Operate_Fail = 10001;
	
	const Outer_Api_St_ParaErr = 10010;
	
	const Outer_Api_St_User_Unexist = 10020;
    const Outer_Api_St_User_RegErr = 10021;
	
	const Outer_Api_St_Order_Unexist = 10030;
	const Outer_Api_St_NotBelong_User = 10031;
	const Outer_Api_St_Order_Uncanncel = 10032;
    
    const Outer_Api_User_Login_Err          = 10100;
    const Outer_Api_User_Forbidden          = 10101;
    const Outer_Api_User_Invalid_Mobile     = 10102;
	
	public static $Outer_Desc = array(
		self::Outer_Api_St_Succ				=> 'success',
		self::Outer_Api_St_Fail				=> 'failure by inner cause',		// 默认的失败原因
		self::Outer_Api_St_ParaErr			=> 'input params error',
		self::Outer_Api_St_User_Unexist		=> 'user is not exist',
        self::Outer_Api_St_User_RegErr      => 'register user failure',
		self::Outer_Api_St_Order_Unexist	=> 'order is not exist',
		self::Outer_Api_St_NotBelong_User	=> 'order is not belong this user',
		self::Outer_Api_St_Order_Uncanncel	=> '订单正在配送中，不能取消',
        
        self::Outer_Api_User_Login_Err      => '登陆失败',
        self::Outer_Api_User_Forbidden      => '账号非法',
        self::Outer_Api_User_Invalid_Mobile => '手机号非法',
	);
	
    /**
     * openapi 使用的返回.
     */
	public static function genOuterResultDesc($retCode)
	{
		$result = new stdClass();
		$result->status = $retCode;
		$result->msg = isset(self::$Outer_Desc[$retCode]) ? self::$Outer_Desc[$retCode] : '';
		if (empty($result->msg))
		{
			$result->msg = isset(Conf_Exception::$exceptions[$retCode]) ? Conf_Exception::$exceptions[$retCode][1]: self::$Outer_Desc[self::Outer_Api_St_Fail];
		}
		$result->result = array();
		
		return $result;
	}
}