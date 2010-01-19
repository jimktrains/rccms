<?php defined('SYSPATH') or die('No direct script access.');

class Version_ORM extends Kohana_ORM{
	protected $_version_table_name;
	protected $_version_of;
	protected $_version_table_suffix = '_versions';
	protected $_version_coloumn_name = 'version';
	protected $_version_coloumn_date = 'version_date';
	protected $_version_coloumn_reason = 'version_reason';
	protected $_real_table;
	protected $_read_only = FALSE;
	
	public function __construct($id=NULL){
		parent::__construct($id);
		$this->_version_table_name = $this->_table_name.$this->_version_table_suffix;
		$this->_version_of = $this->_object_name.'_'.$this->_primary_key;
		$this->_ignored_columns[] = $this->_version_coloumn_reason;
		$this->_ignored_columns[] = $this->_version_of;
		$this->_real_table = $this->_table_name;
	}
	
	public function save(){
		if(! array_key_exists($this->_version_coloumn_name, $this->_object) or
			is_null($this->_object[$this->_version_coloumn_name]) or
			$this->_object[$this->_version_coloumn_name] < 1
		){
			$this->_object[$this->_version_coloumn_name] = 1;
		}
		parent::save();
	}

	
	protected function _update(){
		if (empty($this->_changed)){
			return $this;
		}
		if($this->_read_only){
			return $this;
		}
		$vr = $this->_version_coloumn_reason;
		$old_item = ORM::factory($this->_object_name, $this->pk());
		$data = $old_item->as_array();
		$data[$vr] = $this->$vr;
		$data[$this->_version_of] = $data[$this->_primary_key];
		unset($data[$this->_primary_key]);

		$result = DB::insert($this->_version_table_name)
			->columns(array_keys($data))
			->values(array_values($data))
			->execute($this->_db);
		if ($result){
			if ($this->empty_pk()){
				// $result is array(insert_id, total_rows)
				$this->_object[$this->_primary_key] = $result[0];
			}
		}else{
			throw  "Error saving";
		}
		unset($data[$this->_version_coloumn_reason]);
		$v = $this->_version_coloumn_name;
		$this->$v += 1;
		if (is_array($this->_created_column)){
			// Fill the created column
			$column = $this->_created_column['column'];
			$format = $this->_created_column['format'];

			$this->$column = $this->_object[$column] = ($format === TRUE) ? time() : date($format);
		}
		parent::_update();
	}
	
	function previous_version(){
		return $this->version(-1);
	}
	
	private function set_to_version_table(){
		$this->_table_name = $this->_version_table_name;
	}
	
	private function set_to_real_table(){
		$this->_table_name = $this->_real_table;
	}
	
	public function set_read_only(){
		$this->_read_only = TRUE;
	}
	
	function version($version = -1){
		$vo = $this->_version_of;
		$v = $this->_version_coloumn_name;
		if($version < 1){
			$version = $this->$v - 1;
		}
		
		$old_item = ORM::factory($this->_object_name);
		$old_item->$vo = $this->pk();
		$old_item->$v = $version;
		$old_item->set_to_version_table();
		$old_item->find();
		$old_item->set_to_real_table();
		$old_item->set_read_only();
		return $old_item;
	}
}
?>