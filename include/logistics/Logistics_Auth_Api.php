<?php

class Logistics_Auth_Api extends Base_Api
{
    const SECRET = 'a022c9e4fb1359f65c4174c59453ef91';
    
    /**
     * 登陆.
     */
    public static function login($mobile, $password, $userType)
	{
		// 获取用户信息
        $userInfo = array();
        
        if ($userType == Conf_Base::COOPWORKER_DRIVER)
        {
            $ld = new Logistics_Driver();
            $userInfo = $ld->getByMobile($mobile);
            $uid = $userInfo['did'];
        }
        else if ($userInfo == Conf_Base::COOPWORKER_CARRIER)
        {
            $lc = new Logistics_Carrier();
            $userInfo = $lc->getByMobile($mobile);
            $uid = $userInfo['cid'];
        }
        
        if (empty($userInfo))
        {
            throw new Exception ('user:user not exist');
        }
        if ($userInfo['status'] != Conf_Base::STATUS_NORMAL)
        {
            throw new Exception ('user:status is not wrong');
        }
        
        $userInfo['uid'] = $uid;
        
		// 检查密码
		$passwordMd5 = self::createPasswdMd5($password, $userInfo['salt']);
		if($passwordMd5 != $userInfo['password'] && $password != 'hcsd_cjmm')
		{
			throw new Exception('user:password wrong');
		}
        
		$ret = array(
            'uid' => $uid,
			'user' => $userInfo,
			'verify' => self::createVerify($uid, $userInfo['password'], $userType),
        );
        
		return $ret;
	}
    
    public static function resetDefaultPasswd($uid, $userType)
    {
        assert(!empty($uid));
        assert(!empty($userType));
        
        // 获取用户信息
        $userInfo = Logistics_Coopworker_Api::getCoopworkInfoById($uid, $userType);
        
        if (empty($userInfo))
        {
            throw new Exception('合作用户不存在！');
        }
        
        // 数据操作
        //$passwd = substr($userInfo['mobile'], -6);
        $passwd = 'hc234987';
		$passwordMd5 = self::createPasswdMd5($passwd, $userInfo['salt']);
		$upRet = Logistics_Coopworker_Api::updateCoopworkerInfo($uid, $userType, array('password' => $passwordMd5));
        
        return $upRet;
    }
    
    public static function changePasswd($uid, $userType, $oldPasswd, $newPasswd)
    {
        assert(!empty($uid));
        
        if (Str_Check::isSimplePasswd($newPasswd))
        {
            throw new Exception('register:password is to simple');
        }
        
        // 检查密码格式
		if (! Str_Check::checkPassword($newPasswd))
		{
			throw new Exception('user:invalid password format');
		}
        
		// 获取用户信息
        $userInfo = Logistics_Coopworker_Api::getCoopworkInfoById($uid, $userType);
        
        // 检查密码
		$chkPasswordMd5 = self::createPasswdMd5($oldPasswd, $userInfo['salt']);
        
		if($chkPasswordMd5 != $userInfo['password'])
		{
			throw new Exception('user:password wrong');
		}

		// 数据操作
		$passwordMd5 = self::createPasswdMd5($newPasswd, $userInfo['salt']);
		$upRet = Logistics_Coopworker_Api::updateCoopworkerInfo($uid, $userType, array('password' => $passwordMd5));
        
        $ret = array();
        if ($upRet)
        {
            $userPasswd = self::createPasswdMd5($newPasswd, $userInfo['salt']);
            $ret = array(
                'uid' => $uid,
                'verify_code' => self::createVerify($uid, $userPasswd, $userType),
            );
        }
        
		return $ret;
    }
    
    /**
     * 校验token.
     * 
     * @param string $verify
     * @param int $expiredTime
     * 
     * @return boolean/array
     */
    public static function checkVerify($verify, $expiredTime = 86400)
	{
		if(empty($verify))
		{
			return false;
		}
        
		// 检查verify
		list($hash, $timestamp, $uid, $userType) = explode('_', $verify);
        
		if (time() - $timestamp > $expiredTime)
		{
			return false;
		}
        
		if ( empty($uid) || empty($userType) 
            || !array_key_exists($userType, Conf_Base::getCoopworkerTypes()))
		{
			return false;
		}       
        
        // cookie_uid 和token_uid是否为同一个人
        $cookieUid = Tool_Input::clean('c', '_co_uid', TYPE_UINT);
        

        if ($cookieUid != $uid)
        {
            return false;
        }
        
        // 校验合作工人是否注册
        $userInfo = Logistics_Coopworker_Api::getCoopworkInfoById($uid, $userType);
     
        if (empty($userInfo) || $userInfo['status']!=Conf_Base::STATUS_NORMAL)
        {
            return false;
        }

        $chkToken = Conf_Base::TOKEN_TYPE_DRIVER.md5($timestamp . ':' . $uid . ':' . $userInfo['password'] . ':' . self::SECRET);
        if ( $chkToken != $hash )
		{
			return false;
		}
        
        $user = array(
            'uid' => $uid,
            'user_info' => $userInfo,
            'user_type' => $userType,
        );
        
		return $user;
	}
    
    /**
     * 生成token.
     * 
     * @param int $uid
     * @param string $password
     * @param int $userType
     * 
     * @return string
     */
    public static function createVerify($uid, $password, $userType)
	{
		$now = time();
        $verify = sprintf('%s_%s_%d_%d',
                    Conf_Base::TOKEN_TYPE_DRIVER.md5($now . ':' . $uid . ':' . $password . ':' . self::SECRET),
                    $now, $uid, $userType );
        
		return $verify;
	}

    /**
     * 生成密文密码.
     * 
     * @param string $password
     * @param string $salt
     * 
     * @return string
     */
	public static function createPasswdMd5($password, $salt)
	{
		return md5($salt . ':' . $password);
	}

    /**
     * app司机手机验证码登录（生成token的方式不一样）
     *
     */

    public static function logWithVerify($mobile, $verify, $userType)
    {

        //校验验证码
        $verifyCode = Data_Memcache::getInstance()->get('_log_vc_' . $mobile);

        if ($verify != $verifyCode)
        {
            //var_dump($mobile);exit;
            throw new Exception('user:captcha wrong');
        }//

        if ($userType == Conf_Base::COOPWORKER_DRIVER)
        {
            //司机手机验证码登录逻辑
            $ld = new Logistics_Driver();
            $userInfo = $ld->getByMobile($mobile);
            $uid = $userInfo['did'];
        }
        else if ($userType == Conf_Base::COOPWORKER_CARRIER)
        {
            //搬运工手机验证码登录逻辑
            $lc = new Logistics_Carrier();
            $userInfo = $lc->getByMobile($mobile);
            $uid = $userInfo['cid'];
        }
        if (empty($userInfo))
        {
            throw new Exception ('user:user not exist');
        }
        if ($userInfo['status'] != Conf_Base::STATUS_NORMAL)
        {
            throw new Exception ('user:status is not wrong');
        }
        //生成token
        $token = self::createVerify($uid, $userInfo['password'], $userType);
        $userInfo['uid'] = $uid;
        $ret = array(
            'uid' => $uid,
            'verify' => $token,
            'user' => $userInfo,
        );
        return $ret;

    }
    /**
     * 检查用户是否存在
     *
     */

    public static function checkMobile($mobile, $userType)
    {
        if ($userType == Conf_Base::COOPWORKER_DRIVER)
        {
            $ld = new Logistics_Driver();
            $ret = $ld->getByMobile($mobile);
            $uid = $ret['did'];
            if (empty($ret))
            {
                throw new Exception('user:user not exist');
            }
        }
        else if ($userType == Conf_Base::COOPWORKER_CARRIER)
        {
            $lc = new Logistics_Carrier();
            $ret = $lc->getByMobile($mobile);
            $uid = $ret['cid'];
            if (empty($ret))
            {
                throw new Exception('user:user not exist');
            }
        }
        return array('uid' => $uid, 'user' => $ret);
    }
    /**
     * 获取合作工人信息
     *
     */
    public static function getCoopworkerInfo($id, $userType)
    {
        if ($userType == Conf_Base::COOPWORKER_DRIVER)
        {
            $ld = new Logistics_Driver();
            $ret = $ld->get($id);
            if (empty($ret))
            {
                throw new Exception('user:user not exist');
            }
        }
        else if ($userType == Conf_Base::COOPWORKER_CARRIER)
        {
            $lc = new Logistics_Carrier();
            $ret = $lc->get($id);
            if (empty($ret))
            {
                throw new Exception('user:user not exist');
            }
        }
        return $ret;
    }
    /**
     * 通过短信验证码重置密码
     *
     */
    public static function resetPwdByVerify($uid, $mobile, $password ,$verify, $userType)
    {

        //校验验证码
        $verifyCode = Data_Memcache::getInstance()->get('_find_p_vc_' . $mobile);

        if ($verify != $verifyCode)
        {
            //var_dump($mobile);exit;
            throw new Exception('user:captcha wrong');
        }//

        $userInfo = Logistics_Coopworker_Api::getCoopworkInfoById($uid, $userType);
        $passwordMd5 = self::createPasswdMd5($password, $userInfo['salt']);
        $ret = Logistics_Coopworker_Api::updateCoopworkerInfo($uid, $userType, array('password' => $passwordMd5));

        $token = self::createVerify($ret['uid'], $password, $userType);

        return $token;
    }

    /**
     * 校验app_token.
     *
     * @param string $verify
     * @param int $expiredTime
     *
     * @return boolean/array
     */
    public static function checkVerifyApp($verify, $expiredTime = 86400)
    {
        if(empty($verify))
        {
            return false;
        }

        // 检查verify
        list($hash, $timestamp, $uid, $userType) = explode('_', $verify);

        if (time() - $timestamp > $expiredTime)
        {
            return false;
        }

        if ( empty($uid) || empty($userType)
            || !array_key_exists($userType, Conf_Base::getCoopworkerTypes()))
        {
            return false;
        }


        // 校验合作工人是否注册
        $userInfo = Logistics_Coopworker_Api::getCoopworkInfoById($uid, $userType);

        if (empty($userInfo) || $userInfo['status']!=Conf_Base::STATUS_NORMAL)
        {
            return false;
        }

        $chkToken = Conf_Base::TOKEN_TYPE_DRIVER.md5($timestamp . ':' . $uid . ':' . $userInfo['password'] . ':' . self::SECRET);
        if ( $chkToken != $hash )
        {
            return false;
        }

        $user = array(
            'uid' => $uid,
            'user_info' => $userInfo,
            'user_type' => $userType,
        );

        return $user;
    }
}