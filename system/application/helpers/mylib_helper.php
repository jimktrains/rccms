<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

function decB62($num){
	switch ($num) {
		case  0: return "a";
		case  1: return "b";
		case  2: return "c";
		case  3: return "d";
		case  4: return "e";
		case  5: return "f";
		case  6: return "g";
		case  7: return "h";
		case  8: return "i";
		case  9: return "j";
		case 10: return "k";
		case 11: return "l";
		case 12: return "m";
		case 13: return "n";
		case 14: return "o";
		case 15: return "p";
		case 16: return "q";
		case 17: return "r";
		case 18: return "s";
		case 19: return "t";
		case 20: return "u";
		case 21: return "v";
		case 22: return "w";
		case 23: return "x";
		case 24: return "y";
		case 25: return "z";
		case 26: return "A";
		case 27: return "B";
		case 28: return "C";
		case 29: return "D";
		case 30: return "E";
		case 31: return "F";
		case 32: return "G";
		case 33: return "H";
		case 34: return "I";
		case 35: return "J";
		case 36: return "K";
		case 37: return "L";
		case 38: return "M";
		case 39: return "N";
		case 40: return "O";
		case 41: return "P";
		case 42: return "Q";
		case 43: return "R";
		case 44: return "S";
		case 45: return "T";
		case 46: return "U";
		case 47: return "V";
		case 48: return "W";
		case 49: return "X";
		case 50: return "Y";
		case 51: return "Z";
		case 52: return "1";
		case 53: return "2";
		case 54: return "3";
		case 55: return "4";
		case 56: return "5";
		case 57: return "6";
		case 58: return "7";
		case 59: return "8";
		case 60: return "9";
		case 61: return "0";
	}
}

function getUUID($len, $varLength = false){
	if($varLength){
		$len = 1 + (rand() % $len);
	}
	$p62[0] = 1;
	$p62[1] = 62;
	$p62[2] = 3844;
	$p62[3] = 238328;
	$p62[4] = 14776336;
	$p62[5] = 916132832;
	$p62[6] = getrandmax() < 56800235584 ? getrandmax() : 56800235584;

	$x = rand() % $p62[$len];
	$uuid = "";
	for($i = $len-1; $i > -1; $i--){
		$uuid .= decB62(floor($x / $p62[$i]));
		$x = $x % $p62[$i];
	}
	return $uuid;
}

function myURLClean($url){
	$url = preg_replace("/[^a-zA-z0-9]/","_",$url);
	$url = preg_replace("/_{2,}/","_",$url);
	$url = preg_replace("/^_/","",$url);
	$url = preg_replace("/_$/","",$url);	
	return $url;
}
?>
