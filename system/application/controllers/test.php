<?php
class Test extends Controller
{
	function __construct(){
		parent::Controller();
		$this->load->model('Item');
	}
	
	function index(){
		$myItem = new Item();
		$myItem->guid=getUUID(5);
		$myItem->save();

		$myText = new Text();
		$myText->title = "Testing";
		$myText->text = "Howdy";
		$myText->comments = "blah";
		$myText->summary = "init";
		$myText->rev_num = 0;
		$myText->creator = "me";
		$myText->save($myItem);
		echo($myText->id);
		echo($myText->title);

		foreach ($myText->error->all as $e){
		    echo $e . "<br />";
		}
	}
	function get($uuid){
		$myItem = new Item($uuid);
		echo($myItem->guid."<br/>");
		$myItem->text->get();
		var_dump($myItem->text);
	}
}	
