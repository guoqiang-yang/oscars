<?php
include_once('../../../global.php');

class CApp extends App_Admin_Ajax
{
    private $old_password;
    private $new_password;

    protected function getPara()
    {
        $this->old_password = Tool_Input::clean('r', 'old_password', TYPE_STR);
        $this->new_password = Tool_Input::clean('r', 'new_password', TYPE_STR);
    }

    protected function checkAuth($permission='')
    {
        parent::checkAuth('/user/chgpwd');
    }

    protected function checkPara()
    {
        //$this->checkIsLegal();
    }

    protected function main()
    {
        // 检查密码
        $passwordMd5 = Admin_Auth_Api::createPasswdMd5($this->old_password, $this->_user['salt']);
        if ($passwordMd5 != $this->_user['password'])
        {
            throw new Exception('原密码错误！');
        }
        if ($this->new_password == $this->old_password)
        {
            throw new Exception('新密码与原密码相同，请重新输入！');
        }

        $ret = Admin_Auth_Api::chgPassword($this->_uid, $this->new_password, '');
        
        $this->setCookie4LoginSucc(array(Conf_Base::COKEY_VERIFY_SA=>$ret['verify']), Conf_Base::WEB_TOKEN_EXPIRED);
    }

    protected function outputPage()
    {
        $result = array('uid' => $this->_uid);
        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }

    /**
     * 检查密码是否合法
     * @author libaolong
     * @throws Exception
     */
    protected function checkIsLegal()
    {
        $pinyinStr = Str_Chinese::hz2py2($this->_user['name'], true);
        $pinyinArr = explode(' ', $pinyinStr);

        //判断是否跟姓名拼音有关
        $shorthand = '';//简拼
        $removeXingShi = '';//去除姓氏拼音
        foreach ($pinyinArr as $k => $pinyin)
        {
            $firstWord = substr($pinyin, 0, 1);
            $shorthand .= $firstWord;
            if ($k != 0)
            {
                $removeXingShi .= $pinyin;
            }
            if (stripos($this->new_password, $pinyin) !== false)
            {
                throw new Exception('请不要使用姓名拼音或者简拼！');
            }
        }

        if (stripos($this->new_password, $shorthand) !== false || stripos($this->new_password,         $removeXingShi) !== false)
        {
            throw new Exception('请不要使用姓名拼音或者简拼！');
        }

        //判断字符是否有过多重复
        $chunks = str_split($this->new_password, 1);
        $tmp = array();
        foreach ($chunks as $k => $chunk)
        {
            $tmp[$chunk] += 1;
            if ($tmp[$chunk] >= 3)
            {
                throw new Exception('重复字符过多，请重新输入！');
            }

            if (isset($chunks[$k-1]) && isset($chunks[$k+1]) && is_numeric($chunks[$k-1]) && is_numeric($chunks[$k+1]))
            {
                if ($chunks[$k] - $chunks[$k-1] == 1 && $chunks[$k] - $chunks[$k-1] == $chunks[$k+1] - $chunks[$k])
                {
                    throw new Exception('请不要输入连续数字！');
                }
            }
        }
    }
}

$app = new CApp("");
$app->run();
