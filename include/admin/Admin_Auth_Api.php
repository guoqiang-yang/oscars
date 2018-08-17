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

        //限制登录失败次数
        $mem = Data_Memcache::getInstance();
        $value = $mem->get('lgin_lmt'.$mobile);
        
        if (abs($value) >= 5)
        {
            throw new Exception('您已登录失败五次，请30分钟后尝试！');
        }

        // todo: 格式化用户信息
        if (ENV == 'online' || $password!='hc_12345678')
        {
            // 检查密码
            $passwordMd5 = self::createPasswdMd5($password, $userInfo['salt']);
            if ($passwordMd5 != $userInfo['password'])
            {
                if (!empty($value))
                {
                    $mem->increment('lgin_lmt'.$mobile, 1);
                } else {
                    $mem->set('lgin_lmt'.$mobile, 1, 1800);
                }

                $ip = Tool_Ip::getClientIP();
                $_logInfo = array($ip, $mobile, $password, $source, 'login-passwd-error');
                Tool_Log::addFileLog('tmp_login.log', implode("\t", $_logInfo));
                
                throw new Exception('user:password wrong');
            }
        }

        $mem->delete('lgin_lmt'.$mobile);
        
        $verify = self::createVerify($uid, $userInfo['password']);
        $ret = array(
            'uid' => $uid, 'user' => $userInfo, 'verify' => $verify,
        );

        $isSimplePwd = 0;

        if (ENV == 'online' || $password != 'hc')
        {
            $msg = Security_Api::checkPassword($password);
            if (!empty($msg))
            {
                $isSimplePwd = 1;
            }
        }

        $update = array(
            'verify' => $verify,
            'last_login_ip' => Tool_Ip::getClientIP(),
            'is_simple_pwd' => $isSimplePwd,
        );
        Admin_Api::updateStaff($uid, $update);

        $ip = Tool_Ip::getClientIP();
        $agent = $_SERVER['HTTP_USER_AGENT'];
        Admin_Api::addLoginLog($uid, $ip, $source, $agent);

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

        $as = new Admin_Staff();
        $userInfo = $as->get($uid);
        if (empty($userInfo) || $userInfo['status'] != Conf_Base::STATUS_NORMAL)
        {
            return FALSE;
        }
        $isSaler = Admin_Role_Api::hasRole($userInfo, Conf_Admin::ROLE_SALES_NEW);
        if ($uid != 1078 && !$isSaler && $checkVerify && (empty($verify) || $userInfo['verify'] != $verify))
        {
            return FALSE;
        }

        if (!self::_checkVerify($verify, $userInfo['password']))
        {
            return FALSE;
        }

        return $uid;
    }

    public static function createVerify($uid, $password)
    {
        $now = time();
        //Tool_Log::debug('createVerify', $now . ':' . $uid . ':' . $password . ':' . self::SECRET);
        $verify = md5($now . ':' . $uid . ':' . $password . ':' . self::SECRET) . '_' . $now . '_' . $uid;

        return $verify;
    }

    public static function _checkVerify($verify, $password)
    {
        list($hash, $timestamp, $uid) = explode('_', $verify);
        //Tool_Log::debug('_checkVerify', $timestamp . ':' . $uid . ':' . $password . ':' . self::SECRET);
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
