<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $id;
    private $info;
    private $submit;
    private $file;

    protected function checkAuth()
    {
        parent::checkAuth('/admin/edit_version');
    }

    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'bid', TYPE_UINT);
        $this->info = array(
            'platform' => Tool_Input::clean('r', 'platform', TYPE_UINT),
            'cate' => Tool_Input::clean('r', 'cate', TYPE_UINT),
            'dev' => Tool_Input::clean('r', 'dev', TYPE_UINT),
            'channel' => Tool_Input::clean('r', 'channel', TYPE_STR),
            'version_code' => Tool_Input::clean('r', 'version_code', TYPE_UINT),
            'version' => Tool_Input::clean('r', 'version', TYPE_STR),
            'description' => Tool_Input::clean('r', 'description', TYPE_STR),
            'is_force' => Tool_Input::clean('r', 'is_force', TYPE_UINT),
        );
        $this->file = Tool_Input::clean('r', 'file', TYPE_STR);
        $this->submit = Tool_Input::clean('r', 'submit', TYPE_STR);
        if (empty($this->file) && !empty($this->submit) && $this->info['platform']==1)
        {
            echo "<script>alert('文件上传失败！请重试！');window.location = \"".$_SERVER['HTTP_REFERER']."\";</script>";
            exit;
        }
    }

    protected function main()
    {
        if (!empty($this->submit))
        {
            if (!empty($this->file))
            {
                $this->info['file'] = $this->file;
            }

            $this->info['suid'] = $this->_uid;

            if (!empty($this->id))
            {
                Version_Api::update($this->id, $this->info);
            }
            else
            {
                Version_Api::add($this->info);
            }

            header('Location: /activity/version_list.php');
            exit;
        }
        else
        {
            if (!empty($this->id))
            {
                $this->info = Version_Api::get($this->id);
            }
        }

        $this->addFootJs(array('js/apps/app_version.js'));
    }

    protected function outputBody()
    {
        $this->smarty->assign('cate_list', Conf_App_Version::$CATE_LIST);
        $this->smarty->assign('dev_list', Conf_App_Version::$DEV_LIST);
        $this->smarty->assign('id', $this->id);
        $this->smarty->assign('info', $this->info);
        $this->smarty->assign('channelList', Conf_Channel::$NEW_CHANNEL);

        $this->smarty->display('version/edit.html');
    }
}

$app = new App('pri');
$app->run();
