<?php
header("Content-type: text/html; charset=utf-8");
$nickname = "Dana ?ervinková";
$nickname = preg_replace('#([\%u]{2})([0-9a-zA-Z]{4})#i', '&#x$2', $nickname);
$nickname  = html_entity_decode($nickname , ENT_NOQUOTES, 
                    'UTF-8');


echo '###' . $nickname . '####';

$string = '%u010cMy%u010cName is Jonahtan %u010c';
$pattern = '/([\%]{1}[0-9a-zA-Z]{5})/i';
$replacement = '${1}1,$3';

$message = preg_replace('#([\%u]{2})([0-9a-zA-Z]{4})#i', '&#x$2', $string);

echo "###$message###";
 print html_entity_decode($message, ENT_NOQUOTES, 
                    'UTF-8')."\n";

echo "%u010";
 print html_entity_decode("&#x010c;", ENT_NOQUOTES, 
                    'UTF-8')."\n";

echo utf8_decode("%u010c"); 
echo 'done';

mb_language('uni');
               mb_internal_encoding('UTF-8');
 print html_entity_decode("&#x010c;", ENT_NOQUOTES, 
                    'UTF-8')."\n";

                    
                    
         function utf8_to_unicode_code($utf8_string)
          {
              $expanded = iconv("UTF-8", "UTF-32", $utf8_string);
              return unpack("L*", $expanded);
          }
          function unicode_code_to_utf8($unicode_list)
          { 
              $result = "";
              foreach($unicode_list as $key => $value) {
                  $one_character = pack("L", $value);
                  $result .= iconv("UTF-32", "UTF-8", $one_character);
              }
              return $result;
          }
      
          $q = "&#x010c;";
      
          $r = html_entity_decode($q, ENT_NOQUOTES, 'UTF-8');
          $s = utf8_to_unicode_code($r);
          $t = unicode_code_to_utf8($s);
          print "$r\n";
          print_r($s);
          print "$t\n";
          
          
          
 	$charset = $custom_charset !== null ? $custom_charset : $context['character_set'];
	 $q = "&#x010c;";
	// This is the fun part....
	if (preg_match_all('~&#(\d{3,8});~', $string, $matches) !== 0 && !$hotmail_fix)
	{
		// Let's, for now, assume there are only &#021;'ish characters.
		$simple = true;

		foreach ($matches[1] as $entity)
			if ($entity > 128)
				$simple = false;
		unset($matches);

		if ($simple)
			$string = preg_replace('~&#(\d{3,8});~e', 'chr(\'$1\')', $string);
		else
		{
			// Try to convert the string to UTF-8.
			if (!$context['utf8'] && function_exists('iconv'))
			{
				$newstring = @iconv($context['character_set'], 'UTF-8', $string);
				if ($newstring)
					$string = $newstring;
			}

			$fixchar = create_function('$n', '
				if ($n < 128)
					return chr($n);
				elseif ($n < 2048)
					return chr(192 | $n >> 6) . chr(128 | $n & 63);
				elseif ($n < 65536)
					return chr(224 | $n >> 12) . chr(128 | $n >> 6 & 63) . chr(128 | $n & 63);
				else
					return chr(240 | $n >> 18) . chr(128 | $n >> 12 & 63) . chr(128 | $n >> 6 & 63) . chr(128 | $n & 63);');

			$string = preg_replace('~&#(\d{3,8});~e', '$fixchar(\'$1\')', $string);

			// Unicode, baby.
			$charset = 'UTF-8';
		}
	}
	
	echo 'Final string: ' . $string;

?>
