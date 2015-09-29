<?php
//From http://www.devpapers.com/article/326
define('GOOGLE_MAGIC', 0xE6359A60);
class pageRank{
var $pr; 
 function zeroFill($a, $b){
 $z = hexdec(80000000);
  if ($z & $a){
   $a = ($a>>1);
   $a &= (~$z);
   $a |= 0x40000000;
   $a = ($a>>($b-1));
  }else{
   $a = ($a>>$b);
  }
 return $a;
 } 
 
 function mix($a,$b,$c) {
   $a -= $b; $a -= $c; $a ^= ($this->zeroFill($c,13));
   $b -= $c; $b -= $a; $b ^= ($a<<8);
   $c -= $a; $c -= $b; $c ^= ($this->zeroFill($b,13));
   $a -= $b; $a -= $c; $a ^= ($this->zeroFill($c,12));
   $b -= $c; $b -= $a; $b ^= ($a<<16);
   $c -= $a; $c -= $b; $c ^= ($this->zeroFill($b,5));
   $a -= $b; $a -= $c; $a ^= ($this->zeroFill($c,3));
   $b -= $c; $b -= $a; $b ^= ($a<<10);
   $c -= $a; $c -= $b; $c ^= ($this->zeroFill($b,15));
   return array($a,$b,$c);
 }
 
 function GoogleCH($url, $length=null, $init=GOOGLE_MAGIC) {
  if(is_null($length)) {
   $length = sizeof($url);
  }
  $a = $b = 0x9E3779B9;
  $c = $init;
  $k = 0;
  $len = $length;
  while($len >= 12) {
   $a += ($url[$k+0] +($url[$k+1]<<8) +($url[$k+2]<<16) +($url[$k+3]<<24));
   $b += ($url[$k+4] +($url[$k+5]<<8) +($url[$k+6]<<16) +($url[$k+7]<<24));
   $c += ($url[$k+8] +($url[$k+9]<<8) +($url[$k+10]<<16)+($url[$k+11]<<24));
   $mix = $this->mix($a,$b,$c);
   $a = $mix[0]; $b = $mix[1]; $c = $mix[2];
   $k += 12;
   $len -= 12;
  }
  $c += $length;
  switch($len){
   case 11: $c+=($url[$k+10]<<24);
   case 10: $c+=($url[$k+9]<<16);
   case 9 : $c+=($url[$k+8]<<8);
   /* the first byte of c is reserved for the length */
   case 8 : $b+=($url[$k+7]<<24);
   case 7 : $b+=($url[$k+6]<<16);
   case 6 : $b+=($url[$k+5]<<8);
   case 5 : $b+=($url[$k+4]);
   case 4 : $a+=($url[$k+3]<<24);
   case 3 : $a+=($url[$k+2]<<16);
   case 2 : $a+=($url[$k+1]<<8);
   case 1 : $a+=($url[$k+0]);
  }
  $mix = $this->mix($a,$b,$c);
 /* report the result */
 return $mix[2];
 }
 
 //converts a string into an array of integers containing the numeric value of the char
 
 function strord($string) {
  for($i=0;$i<strlen($string);$i++) {
   $result[$i] = ord($string{$i});
  }
 return $result;
 }
 
 function printrank($url){
  $ch = "6".$this->GoogleCH($this->strord("info:" . $url));
  
  $fp = fsockopen("www.google.com", 80, $errno, $errstr, 30);
  if (!$fp) {
     echo "$errstr ($errno)<br />\n";
  } else {
     $out = "GET /search?client=navclient-auto&ch=" . $ch .  "&features=Rank&q=info:" . $url . " HTTP/1.1\r\n" ;
     $out .= "Host: www.google.com\r\n" ;
     $out .= "Connection: Close\r\n\r\n" ; 
     fwrite($fp, $out);
     while (!feof($fp)) {
       $data = fgets($fp, 128);
       $pos = strpos($data, "Rank_");
         if($pos === false){
         }else{
           $pagerank = substr($data, $pos + 9);
           $pr = $pagerank;
         }
     }
     fclose($fp);
  }
 }

 function get_pr(){
  return $this->pr;
 }
}
?>