<?php

class DB_Exp
{
	
	public $exp = '';
	
	function __construct($exp)
	{
		$this->exp = $exp;
	}
	
	public function __toString()
	{
		return $this->exp;
	}
}
