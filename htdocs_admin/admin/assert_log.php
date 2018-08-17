<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $strs;

    protected function main()
    {
        $ph = popen('tail -n 200 /haocai/multilog/assert_simple.log', 'r');
        while ($r = fgets($ph))
        {
            $this->strs[] = $r;
        }
        //$this->strs = array_reverse($this->strs);

        pclose($ph);
    }

    protected function outputBody()
    {
        $this->smarty->assign('strs', $this->strs);

        $this->smarty->display('admin/error_log.html');
    }
}

$app = new App('pri');
$app->run();

