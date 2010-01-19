<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Error extends Controller_MasterTemplate {
	public $template = "error";
	public function not_found(){
		$this->status = 404;
		$this->master->title = "Page Not Found";
		$this->template->test = "Sorry, someone somewhere got something somewhat wrong:(";
	}

}
