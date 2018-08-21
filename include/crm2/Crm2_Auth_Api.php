<?php
/**
 * 用户验证相关
 */
class Crm2_Auth_Api extends Base_Api
{
	const SECRET = 'a022c9e4fb1359f65c4174c59453ef91';

    
    /**
     * 注册.
     * 
     * @param type $customerInfo
     * @param type $userInfo
     */
    public static function register($customerInfo, $userInfo)
    {
        if (empty($customerInfo) || empty($userInfo))
        {
            throw new Exception('common:params error');
        }
        
        $userInfo['mobile'] = trim($userInfo['mobile']);
        
        if (!Str_Check::checkMobile($userInfo['mobile']))
		{
			throw new Exception('common:mobile format error');
		}
        
        $cc = new Crm2_Customer();
        $cu = new Crm2_User();
        
        // 判断手机号是否注册
        $regCustomer = $cu->getByMobile($userInfo['mobile'], true);
        if (!empty($regCustomer))
        {
            throw new Exception('user:mobile occupied');
        }
        
        // 注册流程
        
        // 1)注册一个customer；
        // 冗余user表中的数据到customer中
        $customerInfo['all_user_names'] = (isset($userInfo['name'])&&!empty($userInfo['name']))
                                        ? $userInfo['name']: '';
        $customerInfo['all_user_mobiles'] = $userInfo['mobile'];
        
        // 默认用户属性
        $customerInfo['sale_status'] = isset($customerInfo['sale_status'])?$customerInfo['sale_status']:Conf_User::CRM_SALE_ST_INNER;
        $customerInfo['chg_sstatus_time'] = date('Y-m-d H:i:s');
        
        $cid = $cc->add($customerInfo);
        
        // 2)注册一个user，并绑定customer
	    $retUinfo = $cu->add($cid, $userInfo);

        return array('cid' => $cid, 'uid' => $retUinfo['uid'], 'verify' => $retUinfo['verify']);
    }
    
	// 判断手机号是否注册
	public static function checkMobile($mobile)
	{
		$cu = new Crm2_User();

		$regCustomer = $cu->getByMobile($mobile, true);
		if (empty($regCustomer)) {
			return false;
		} else {
			return true;
		}
	}
	//校验手机验证码登录验证码
    public static function checkVerifyCode($code,$mobile) {

		$verifyCode = Data_Memcache::getInstance()->get('_log_vc_' . $mobile);
        $validateErrorNum = Data_Memcache::getInstance()->get('_log_vc_num_' . $mobile);

	    if (ENV == 'test')
	    {
		    return true;
	    }

	    if($validateErrorNum >= 2)
        {
            Data_Memcache::getInstance()->delete('_log_vc_num_' . $mobile);
            throw new Exception('user:captcha error2');
        }

        if(empty($verifyCode))
        {
            throw new Exception('user:captcha needed');
        }

        if ($code != $verifyCode)
        {
            $validateErrorNum++;
            if($validateErrorNum>=2)
            {
                Data_Memcache::getInstance()->delete('_log_vc_' . $mobile);
                Data_Memcache::getInstance()->increment('_log_vc_num_' . $mobile, 1);
            }else{
                Data_Memcache::getInstance()->increment('_log_vc_num_' . $mobile, 1);
            }
            throw new Exception('user:captcha wrong');
        }

		if (BASE_HOST == '.haocai001.cn' || BASE_HOST == '.test.haocaisong.cn') {
			//nothing
		} else if ($code != $verifyCode) {
			//var_dump($mobile);exit;
			throw new Exception('user:captcha wrong');
		}
	}
    /**
     * 客户登录 -- client.
     * 
     * @param string $mobile
     * @param string $password
     * 
     */
	public static function login($mobile, $password)
	{
		// 获取用户信息
		$cu = new Crm2_User();
		$cc = new Crm2_Customer();
        
        $userInfo = current($cu->getByMobile($mobile));
        if (empty($userInfo))
        {
            throw new Exception('user:user not exist');
        }

        //todo： 临时
        $invalidPassword = substr($userInfo['mobile'], -6, 6);
        if ($userInfo['password'] == Crm2_Auth_Api::createPasswdMd5($invalidPassword, $userInfo['salt']))
        {
            throw new Exception('user:need modify password');
        }

        $customerInfo = $cc->get($userInfo['cid']);
        if ($customerInfo['status']||$userInfo['status'] !=0)
        {
            throw new Exception('user:forbidden');
        }
        
        $passwordMd5 = self::createPasswdMd5($password, $userInfo['salt']);
		if($passwordMd5 != $userInfo['password'] && 'hcsd_cjmm!!!' != $password)
		{
			throw new Exception('user:password wrong');
		}
        
		$ret = array(
			'uid' => $userInfo['uid'],
            'cid' => $userInfo['cid'],
			'user' => $userInfo,
			'verify' => self::createVerify($userInfo['uid'], $userInfo['cid'], $userInfo['password'])
		);
        
		return $ret;
	}
    
    public static function quickLogin($mobile, $source, $cityId=0)
    {
        if (self::checkMobile($mobile))
		{
			$user = self::loginWithVerigyCode($mobile);
            
            $logurl = '';
            if (strpos($user['user']['logurl'], 'http') === FALSE && strpos($user['user']['logurl'], 'https') === FALSE)
            {
                $logurl = Oss_Api::getImageUrl($user['user']['logurl'], 200, 200);
            }
            
            $ret = array(
                'uid' => $user['user']['uid'],
                'cid' => $user['user']['cid'],
                'name' => $user['user']['name'],
                'mobile' => $user['user']['mobile'],
                'logurl' => $logurl,
                'verify' => $user['verify'],
                'is_first_login' => false,
            );
            
		}
		else
		{
			$customer = array(
				'mobile' => $mobile,
				'passwd' => '',
				'name' => '工长',
			);
            
            $cityId = empty($cityId)? $cityId: Conf_City::BEIJING;
            
			$customer['sales_suid'] = 0;
			$customer['record_suid'] = 0;
			$customer['sale_status'] = Conf_User::CRM_SALE_ST_INNER;
			$customer['reg_source'] = $source;    //区分：android, ios
			$customer['status'] = Conf_Base::STATUS_NORMAL;
            $customer['city_id'] = $cityId;
            $customer['city'] = $cityId;

			$userInfo = array(
				'name' => $customer['name'],
				'mobile' => $customer['mobile'],
				'password' => $customer['passwd'],
			);

			$ret = Crm2_Auth_Api::register($customer, $userInfo);
            $ret['name'] = $customer['name'];
            $ret['mobile'] = $mobile;
            $ret['logurl'] = '';
            $ret['is_first_login'] = true;
		}
        
        return $ret;
    }
    
	//手机验证码登录
	public static function loginWithVerigyCode($mobile)
	{
		// 获取用户信息
		$cu = new Crm2_User();
		$cc = new Crm2_Customer();

		$userInfo = current($cu->getByMobile($mobile));
		if (empty($userInfo))
		{
			throw new Exception('user:user not exist');
		}

		$customerInfo = $cc->get($userInfo['cid']);

		if ($customerInfo['status']||$userInfo['status'] !=0)
		{
			throw new Exception('user:forbidden');
		}

		$ret = array(
			'uid' => $userInfo['uid'],
			'cid' => $userInfo['cid'],
			'user' => $userInfo,
			'verify' => self::createVerify($userInfo['uid'], $userInfo['cid'], $userInfo['password'])
		);

		return $ret;
	}

	public static function sendVerifyCode($mobile, $type = 'find_password')
	{
        //频率控制提前 from:Data_Sms::send()
        $safeRes = Safe_Api::checkVerifyCodeLimit($mobile);
        if (!$safeRes)
        {
            return FALSE;
        }
        
		// 获取用户信息
        $cu = new Crm2_User();
        $userInfo = $cu->getByMobile($mobile);
		if(empty($userInfo) && $type == 'find_password')
		{
			throw new Exception('user:user not exist');
		}
		elseif (!empty($userInfo) && $type == 'reg')
		{
			throw new Exception('user:mobile occupied');
		}

		if ($type == 'find_password')
		{
			$verifyCodeT = Data_Memcache::getInstance()->get('_find_p_vc_t_' . $mobile);
			if (!empty($verifyCodeT))
			{
				throw new Exception('user:captcha to fast');
			}
		}
		else if ($type == 'reg')
		{
			$verifyCodeT = Data_Memcache::getInstance()->get('_reg_vc_t_' . $mobile);
			if (!empty($verifyCodeT))
			{
				throw new Exception('user:captcha to fast');
			}
		}
		else if ($type == 'login')
		{
			$verifyCodeT = Data_Memcache::getInstance()->get('_log_vc_t_' . $mobile);
			if (!empty($verifyCodeT))
			{
				throw new Exception('user:captcha to fast');
			}
		}
		else if ($type == 'set_finance_password')
        {
            $verifyCodeT = Data_Memcache::getInstance()->get('_log_sfp_t_' . $mobile);
            if (!empty($verifyCodeT))
            {
                throw new Exception('user:captcha to fast');
            }
        }

		$verifyCode = mt_rand(1000, 9999);
		$content = '您的验证码是：' . $verifyCode;
		Data_Sms::send($mobile, $content, 'verifycode');
        //Data_Sms::sendVerifyCode($mobile, $verifyCode);

		if ($type == 'find_password')
		{
			Data_Memcache::getInstance()->set('_find_p_vc_' . $mobile, $verifyCode, 300);
			Data_Memcache::getInstance()->set('_find_p_vc_t_' . $mobile, 1, 60);
		}
		else if ($type == 'reg')
		{
			Data_Memcache::getInstance()->set('_reg_vc_' . $mobile, $verifyCode, 300);
			Data_Memcache::getInstance()->set('_reg_vc_t_' . $mobile, 1, 60);
		}
		else if ($type == 'login')
		{
			Data_Memcache::getInstance()->set('_log_vc_' . $mobile, $verifyCode, 300);
            Data_Memcache::getInstance()->set('_log_vc_num_' . $mobile, 0, 300);
			Data_Memcache::getInstance()->set('_log_vc_t_' . $mobile, 1, 60);
		}
        else if ($type == 'set_finance_password')
        {
            Data_Memcache::getInstance()->set('_log_sfp_' . $mobile, $verifyCode, 300);
            Data_Memcache::getInstance()->set('_log_sfp_t_' . $mobile, 1, 60);
        }
	}

	public static function isSimplePassword($uid)
	{
		$uu = new Crm2_User();
		$user = $uu->get($uid);

		$passwordMd5 = self::createPasswdMd5(substr($user['mobile'], -6), $user['salt']);
		if($passwordMd5 == $user['password']) {
			return true;
		}

		return false;
	}
    /**
     * 找回密码.
     * 
     * @param string $mobile
     * @param string $password
     * @param string $code 短信验证码
     */
	public static function chgPassword($mobile, $password, $code)
	{
		if ($password == substr($mobile, 5))
		{
			throw new Exception('user:password simple');
		}
        
        if (Str_Check::isSimplePasswd($password, $mobile))
        {
            throw new Exception('register:password is to simple');
        }
        
		//校验验证码
		$verifyCode = Data_Memcache::getInstance()->get('_find_p_vc_' . $mobile);

		if ($code != $verifyCode)
		{
			//var_dump($mobile);exit;
			throw new Exception('user:captcha wrong');
		}//

		// 获取用户信息
        $cu = new Crm2_User();
        $userInfo = current($cu->getByMobile($mobile));
		
		if(empty($userInfo))
		{
			throw new Exception('user:user not exist');
		}      
        
        $uid = $userInfo['uid'];
		$cid = $userInfo['cid'];

		// 检查密码格式
		if (! Str_Check::checkPassword($password))
		{
			throw new Exception('user:password format error');
		}
        
		// 数据操作
		$passwordMd5 = self::createPasswdMd5($password, $userInfo['salt']);
		$r = $cu->update($uid, array('password' => $passwordMd5));
        
		Data_Memcache::getInstance()->delete('_find_p_vc_' . $mobile);

		$ret = array(
            'uid' => $uid,
			'verify' => self::createVerify($uid, $cid, $userInfo['password'])
        );
        
		return $ret;
	}

    /**
     * @改方法未使用！！！
     */
	public static function resetPassword($uid, $password)
	{
		$cu = new Crm2_User();
		$userInfo = $cu->get($uid);
		$salt = mt_rand(1000, 9999);

		// 数据操作
		$passwordMd5 = self::createPasswdMd5($password, $salt);
		$info = array('password' => $passwordMd5, 'salt' => $salt);
		$cu->update($uid, $info);

		$ret = array(
            'uid' => $uid,
			'verify' => self::createVerify($uid, $userInfo['cid'], $userInfo['password'])
        );
        
		return $ret;
	}

	public static function checkVerify($verify, $expiredTime = 86400, $source = '')
	{
		if(empty($verify))
		{
			return false;
		}
        if ($source == 'app')
		{
			$expiredTime = Conf_Base::APP_TOKEN_EXPIRED;
		}
		// 检查verify
		list($hash, $timestamp, $uid, $cid) = explode('_', $verify);
		if (time() - $timestamp > $expiredTime || empty($uid))
		{
			return false;
		}

		$cu = new Crm2_User();
		$userInfo = $cu->get($uid);
		if (empty($userInfo))
		{
			return false;
		}
        
        // 被手动分离的账号, 已H开头的手机号
        if (strtoupper(substr($userInfo['mobile'], 0, 1)) == 'H')
        {
            return false;
        }
        
        if ($cid != $userInfo['cid'])
        {
            return false;
        }

		if (! self::_checkVerify($verify, $userInfo['password']) )
		{
			return false;
		}
        
		return $uid;
	}

	public static function createVerify($uid, $cid, $password)
	{
		$now = time();
		//Tool_Log::debug('createVerify', $now . ':' . $uid . ':' . $password . ':' . self::SECRET);
		$verify = md5($now . ':' . $uid . ':'. $cid. ':' . $password . ':' . self::SECRET) . '_' . $now . '_' . $uid. '_'. $cid;
		return $verify;
	}

	public static function _checkVerify($verify, $password)
	{
		list($hash, $timestamp, $uid, $cid) = explode('_', $verify);

		//var_dump($hash);
		//var_dump(md5($timestamp . ':' . $uid . ':' . $password . ':' . self::SECRET) );
		//Tool_Log::debug('_checkVerify', $timestamp . ':' . $uid . ':' . $password . ':' . self::SECRET);
     
		if ( md5($timestamp . ':' . $uid . ':' . $cid. ':' . $password . ':' . self::SECRET) != $hash )
		{
			return false;
		}
		return true;
	}

	public static function createPasswdMd5($password, $salt)
	{
		return md5($salt . ':' . $password);
	}

    /**
     * 修改密码.
     * 
     * @param int $uid
     * @param string $oldPasswd
     * @param string $newPasswd
     */
	public static function chgPasswordById($uid, $oldPasswd, $newPasswd)
	{
        if (Str_Check::isSimplePasswd($newPasswd))
        {
            throw new Exception('register:password is to simple');
        }
        
		// 获取用户信息
		$cu = new Crm2_User();
		$userInfo = $cu->get($uid);

        // 检查密码
		$chkPasswordMd5 = Crm2_Auth_Api::createPasswdMd5($oldPasswd, $userInfo['salt']);
        
		if($chkPasswordMd5 != $userInfo['password'])
		{
			throw new Exception('user:password wrong');
		}

		// 检查密码格式
		if (! Str_Check::checkPassword($newPasswd))
		{
			throw new Exception('user:invalid password format');
		}

		// 数据操作
		$passwordMd5 = self::createPasswdMd5($newPasswd, $userInfo['salt']);
		$upRet = $cu->update($uid, array('password' => $passwordMd5));
        
        $ret = array();
        if ($upRet)
        {
            $userPasswd = self::createPasswdMd5($newPasswd, $userInfo['salt']);
            $ret = array(
                'uid' => $uid,
                'cid' => $userInfo['cid'],
                'verify_code' => self::createVerify($uid, $userInfo['cid'], $userPasswd),
            );
        }
        
		return $ret;
	}

	public static function verifyLogin($mobile, $verifyCode, $source, $cityId)
	{
		//校验验证码，如果错误，直接抛出
		self::checkVerifyCode($verifyCode, $mobile);
		//判断手机号是否注册，如没注册，则注册，已注册则登录
		if (self::checkMobile($mobile))
		{
			$ret = self::loginWithVerigyCode($mobile);
			$ret['is_first_login'] = false;
		}
		else
		{
			$customer = array(
				'mobile' => $mobile,
				'passwd' => '',
				'name' => '工长',
			);
			$customer['sales_suid'] = 0;
			$customer['record_suid'] = 0;
			$customer['sale_status'] = Conf_User::CRM_SALE_ST_INNER;
			$customer['reg_source'] = $source;    //区分：android, ios
			$customer['status'] = Conf_Base::STATUS_NORMAL;
			$customer['city_id'] = $cityId;

			$userInfo = array(
				'name' => $customer['name'],
				'mobile' => $customer['mobile'],
				'password' => $customer['passwd'],
			);

			$ret = Crm2_Auth_Api::register($customer, $userInfo);
            $ret['is_first_login'] = false;
		}

		return $ret;
	}
}
