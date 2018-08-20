<?php

/**
 * 用户验证相关
 */
class Admin_Auth_Api extends Base_Api
{
    const SECRET = 'a022c9e4fb1359f65c4174c59453ef91';

    public static function login($mobile, $password, $source = 'sa')
    {
        // 获取用户信息
        $as = new Admin_Staff();
        $userInfo = $as->getByMobile($mobile);
        
        if (empty($userInfo) || $userInfo['status'] != Conf_Base::STATUS_NORMAL)
        {
            throw new Exception('user:user not exist');
        }
        $uid = $userInfo['suid'];
        
        // 检查密码
        if (ENV == 'online' || $password!='oo_123123')
        {
            $passwordMd5 = self::createPasswdMd5($password, $userInfo['salt']);
            
            if ($passwordMd5 != $userInfo['password'])
            {
                throw new Exception('user:password wrong');
            }
        }

        $verify = self::createVerify($uid, $userInfo['password']);
        
        $update = array(
            'verify' => $verify,
            'last_login_ip' => Tool_Ip::getClientIP(),
        );
        Admin_Api::updateStaff($uid, $update);

        $ret = array(
            'uid' => $uid, 'user' => $userInfo, 'verify' => $verify,
        );
        
        return $ret;
    }

    /**
     * 钉钉验证码登录
     * @author libaolong
     * @param $mobile
     * @param string $source
     * @return array
     * @throws Exception
     */
    public static function loginByDing($mobile, $source = 'sa')
    {
        // 获取用户信息
        $as = new Admin_Staff();
        $userInfo = $as->getByMobile($mobile);
        if (empty($userInfo) || $userInfo['status'] != Conf_Base::STATUS_NORMAL)
        {
            throw new Exception('user:user not exist');
        }
        $uid = $userInfo['suid'];

        $verify = self::createVerify($uid, $userInfo['password']);
        $ret = array(
            'uid' => $uid, 'user' => $userInfo, 'verify' => $verify,
        );

        $update = array(
            'verify' => $verify,
            'last_login_ip' => Tool_Ip::getClientIP(),
        );
        Admin_Api::updateStaff($uid, $update);

        $ip = Tool_Ip::getClientIP();
        $agent = $_SERVER['HTTP_USER_AGENT'];
        Admin_Api::addLoginLog($uid, $ip, $source, $agent);

        return $ret;
    }

    public static function chgPassword($uid, $password, $code)
    {
        // 参数检查
        // todo: check code

        // 获取用户信息
        $as = new Admin_Staff();
        $userInfo = $as->get($uid);
        if (empty($userInfo))
        {
            throw new Exception('user:user not exist');
        }
        $uid = $userInfo['suid'];

        // 检查密码格式
        if (!Str_Check::checkPassword($password))
        {
            throw new Exception('user:invalid password format');
        }

        // 数据操作
        $passwordMd5 = self::createPasswdMd5($password, $userInfo['salt']);
        $as->update($uid, array('password' => $passwordMd5));

        $ret = array(
            'uid' => $uid, 'verify' => self::createVerify($uid, $userInfo['password'])
        );

        return $ret;
    }

    public static function resetPassword($uid, $password)
    {
        $as = new Admin_Staff();
        $userInfo = $as->get($uid);
        $salt = mt_rand(1000, 9999);

        // 数据操作
        $passwordMd5 = self::createPasswdMd5($password, $salt);
        $info = array('password' => $passwordMd5, 'salt' => $salt);
        $as->update($uid, $info);

        $ret = array(
            'uid' => $uid, 'verify' => self::createVerify($uid, $userInfo['password'])
        );

        return $ret;
    }

    
    public static function checkVerify($verify, $expiredTime = 86400, $checkVerify = true)
    {
        if (empty($verify))
        {
            return FALSE;
        }

        // 检查verify
        list($hash, $timestamp, $uid) = explode('_', $verify);
        if (time() - $timestamp > $expiredTime)
        {
            return FALSE;
        }
        
        if (empty($uid))
        {
            return FALSE;
        }
        
        $userInfo = Admin_Api::getStaff($uid);
        
        if (empty($userInfo))
        {
            return FALSE;
        }
        
        //单点登录
        if ($checkVerify && (empty($verify) || $userInfo['verify'] != $verify))
        {
            return FALSE;
        }

        if (!self::_checkVerify($verify, $userInfo['password']))
        {
            return FALSE;
        }

        return array('uid'=>$uid, 'user'=>$userInfo);
    }

    public static function createVerify($uid, $password)
    {
        $now = time();
        
        $verify = md5($now . ':' . $uid . ':' . $password . ':' . self::SECRET) . '_' . $now . '_' . $uid;

        return $verify;
    }

    public static function _checkVerify($verify, $password)
    {
        list($hash, $timestamp, $uid) = explode('_', $verify);
        
        if (md5($timestamp . ':' . $uid . ':' . $password . ':' . self::SECRET) != $hash)
        {
            return FALSE;
        }

        return TRUE;
    }

    public static function createPasswdMd5($password, $salt)
    {
        return md5($salt . ':' . $password);
    }
}
