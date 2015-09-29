<?
//From http://php4all.org/?nav=tutorials&showtut&tid=85&cat=PHP
class arank
{
function getarank ( $url )
{
$aadress = 'http://data.alexa.com/data?cli=10&dat=snbamz&url='.$url;
preg_match ( '@" TEXT=" ( ( d|, ) +? ) "@i', file_get_contents ( $aadress ) , $ainfo ) ;
if ( empty ( $ainfo [ 1 ] ) )
{
$rank = '-';
}
else
{
$rank = $ainfo [ 1 ] ;
}
return number_format ( $rank ) ;
}
}
?>