<?php
include_once ('../../global.php');

class App extends App_Admin_Page
{
	private $id;

    protected function checkAuth()
    {
        parent::checkAuth('/admin/edit_version');
    }

	protected function getPara()
	{
		$this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
	}

	protected function main()
	{
		Version_Api::delete($this->id);

		header('Location: /activity/version_list.php');
		exit;
	}

	protected function outputBody()
	{

	}
}

$app = new App('pri');
$app->run();
