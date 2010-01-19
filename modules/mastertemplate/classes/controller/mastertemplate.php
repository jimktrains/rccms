<?php defined('SYSPATH') or die('No direct script access.');

abstract class Controller_MasterTemplate extends Controller {
	protected $_template = '';
	protected $_master = 'master';
	protected $_auto_render = TRUE;

	// To be used later
	protected $_allowed = array();
	
	protected $_master_vars = array('title', 'styles', 'scripts', 'metas', 'httpequivs', 'body', 'links');
	
	public function before(){
		if(!strlen($this->_template)){
			$this->_template = $this->request->controller.'/'.$this->request->action;
		}
		if ($this->_auto_render){
			$this->_master = View::factory($this->_master);
			try{
				$this->_template = View::factory($this->_template);
			}catch(Exception $e){
				$this->_template = $this->request->controller;
				try{
					$this->_template = View::factory($this->_template);
				}catch(Exception $e){
					$this->_template = "";
					$this->_auto_render = FALSE;
				}
			}
			$this->styles = array();
			$this->scripts = array();
			$this->metas = array();
			$this->httpequivs = array();
			$this->links = array();
		}
	}

	public function __get($key){
		var_dump( $key);
		if($this->_auto_render){
			if(in_array($key, $this->_master_vars)){
				return $this->_master->$key;
			}else{
				return $this->_template->$key;
			}
		}
	}

	public function __set($key, $value){
		if($this->_auto_render){
			if(in_array($key, $this->_master_vars)){
				$this->_master->$key = $value;
			}else{
				$this->_template->$key = $value;
			}
		}
	}

	/**
	 * Assigns the template as the request response.
	 *
	 * @param   string   request method
	 * @return  void
	 */
	public function after(){
		if ($this->_auto_render === TRUE){
			$this->body = $this->_template;
			$this->request->response = $this->_master;
		}
	}
	
	abstract public function not_found();
}
