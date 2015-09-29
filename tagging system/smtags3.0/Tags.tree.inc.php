<?php
/*
Tagging System
Version 3.0+stef
http://www.smfhacks.com
  by: vbgamer45 and stefann
  license: this modification is licensed under the Creative Commons BY-NC-SA 3.0 License

Included icons are from Silk Icons 1.3 available at http://www.famfamfam.com/lab/icons/silk/
  and are licensed under the Creative Commons Attribution 2.5 License
*/

// this file is only used if and when a tag entry tree is required
//   either in the form of a custom tree or an optgroup select box

// render custom tag tree
// flag describes which types of tags are editable
//   values: 0 = neither editable (default), 1 = approved editable, 2 = all editable
function tag_draw_branch($flag = 0, $pid = 0, $origindent = 0, $depth = 0, $indent = 0, $visible = TRUE) {
	global $context, $settings;
	$row = 0;
	if ($depth > 0) {
		// increase indent
		$indent += 2;
	}
	else if ($depth == 0) {
		// set root indent
		$indent = $origindent;
	}
	// ideally this would be a css class, but luckily it's only for each column
	echo str_repeat("\t",$indent + 1) . '<ul ' . (($pid) ? 'id="tagu' . $pid . '" ' : ''), 'style="list-style-type:none;' . (($visible) ? '' : ' display:none;') , ((!$pid) ? ' float:left;padding:5px;margin:0px;">' : '" id="tagu' . $pid . '">'), "\n";
	foreach ($context['tags']['by_parent'][$pid] as $branch) {
		list($id,$tag,$approved1,$taggable,$tagged,$approved2,$quantity) = $branch;
		$has_children = array_key_exists($id,$context['tags']['by_parent']);
		$children_tagged = isset($context['tags']['has_tagged_children'][$id]);
		if ($has_children || $taggable) {
			if ($row >= $context['tags']['rows']) {
				$row = 0;
				echo str_repeat("\t",$indent + 1).'</ul>'."\n".str_repeat("\t",$indent + 1).'<ul style="list-style-type:none;float:left;padding:5px;margin:0px;">'."\n";
			}
			echo str_repeat("\t",$indent + 2).'<li style="clear:left;">'."\n";
			if ($has_children) {
				echo str_repeat("\t",$indent + 3).'<img src="' . $settings['images_url'] . '/icons/tagplus.png" alt="+" id="tagh'.$id.'" ' , (($children_tagged) ? 'style="display:none;" ' : ''), 'onclick="tagtog('.$id.');" height="17" width="17">'."\n";
				echo str_repeat("\t",$indent + 3).'<img src="' . $settings['images_url'] . '/icons/tagminus.png" alt="-" id="tags'.$id.'"' , (($children_tagged) ? '' : 'style="display:none;" ') , 'onclick="tagtog('.$id.',1);" height="17" width="17">'."\n";
			}
			if ($taggable) {
				$onclick = '';
				$suffix = array();
				if ($tagged) {
					if ($approved2) { $suffix[] = 'checked'; }
					if ($flag == 0 || ($flag == 1 && !$approved2)) { $suffix[] = 'disabled'; }
				}
				echo str_repeat("\t",$indent + 3).'<input type="checkbox" name="tag'.$id.'" value="1" id="tagcs'.$id.'"' , (($has_children && !$children_tagged) ? ' style="display:none;" ' : ' '), implode(' ', $suffix), ">\n";
			}
			echo str_repeat("\t",$indent + 3), (($tagged && !$approved2) ? '<font color="red">'.$tag.'</font>' : $tag), (($taggable) ? ' (' . $quantity . ')' : ''), "\n";
			if ($has_children)
				tag_draw_branch($flag,$id,$origindent,$depth + 1,$indent,$children_tagged,$visible);
			echo str_repeat("\t",$indent + 2).'</li>'."\n";
			if ($depth == 0)
				$row++;
		}
	}
	echo str_repeat("\t",$indent + 1) . '</ul>' . "\n";
}


// render optgroup select tree 
function tag_draw_optgroup($pid = 0, $origindent = 0, $depth = 0, $indent = 0, $offset = 0) {
	global $context, $settings;
    if ($depth > 0) {
        $offset += 2;
	}
	else if ($depth == 0) {
		$indent = $origindent;
	}
	foreach ($context['tags']['by_parent'][$pid] as $branch) {
		list($id,$tag,$approved1,$taggable,$tagged,$approved2,$quantity) = $branch;
		$has_children = array_key_exists($id,$context['tags']['by_parent']);
		echo str_repeat("\t", $indent) . '<option value="'.$id.'">' . str_repeat("&nbsp;",$offset) . $tag.'</option>'."\n";
		if ($has_children)
			tag_draw_optgroup($id, $origindent, $depth + 1, $indent, $offset);
	}
}

function tag_draw_js () {
?>

<script language="JavaScript">
// tag tree toggle 
function tagtog(id, t) {
        ul = "tagu" + id;
        hide = "tagh" + id;
        show = "tags" + id;
        show2 = "tagcs" + id;
        if (t == 1) { a = 'none'; au = 'none'; b = 'inline'; }
        else { a = 'inline'; au = 'list-item'; b = 'none'; }
        if (document.getElementById) {
                document.getElementById(ul).style.display = au;
                document.getElementById(hide).style.display = b;
                document.getElementById(show).style.display = a;
                document.getElementById(show2).style.display = a;
        } else {
                if (document.layers) {
                        document.ul.display = au;
                        document.hide.display = b;
                        document.show.display = a;
                        document.show2.display = a;
                } else {
                        document.all.ul.style.display = au;
                        document.all.hide.style.display = b;
                        document.all.show.style.display = a;
                        document.all.show2.style.display = a;
                }
        }
}
</script>

<?php
}

function tagadmin_draw_branch($pid = 0, $origindent = 0, $depth = 0, $bg = "", $indent = 0, $type = 'checkbox', $offset = 0) {
	// depth = -1 will stop recursion, = -2 will force drawing of a root element
	// type can be 'checkbox' or 'radio' only
	global $context, $settings, $txt, $scripturl;
	$row = 0;
	if ($depth > 0) {
		// add &nbsp; offset
		$offset += 2;
	}
	else if ($depth < 0) {
		// no recursion
		$bg = 2;
        echo str_repeat("\t",$indent).'<tr>'."\n";
        echo str_repeat("\t",$indent + 1), '<td class="windowbg', $bg, '" colspan="3">', str_repeat("&nbsp;", $offset), '<a href="', $scripturl, '?action=tags;id=0"><i>[', $txt['smftags_roottag'], ']</i></a></td>', "\n";
        if ($type == "checkbox") { echo str_repeat("\t",$indent + 1), '<td class="windowbg', $bg, '">', '<input type="checkbox" name="tag[0]" value="1"></td>', "\n"; }
        else if ($type == "radio") { echo str_repeat("\t",$indent + 1), '<td class="windowbg', $bg, '">', '<input type="radio" name="master" value="tag[0]"</td>', "\n"; }
	}
	else {
		// set root indent
		$indent = $origindent;
	}
	foreach ($context['tags']['by_parent'][$pid] as $branch) {
		$bg = (empty($bg)) ? 2 : "";
		list($id,$tag,$approved1,$taggable,$tagged,$approved2,$quantity) = $branch;
		$has_children = array_key_exists($id,$context['tags']['by_parent']);
		echo str_repeat("\t",$indent).'<tr>'."\n";
		echo str_repeat("\t",$indent + 1), '<td class="windowbg', $bg, '">', str_repeat("&nbsp;", $offset), '<a href="', $scripturl, '?action=tags;id=', $id, '">', $tag, '</a></td>', "\n";
		echo str_repeat("\t",$indent + 1), '<td class="windowbg', $bg, '">', $quantity, '</td>', "\n";
		echo str_repeat("\t",$indent + 1), '<td class="windowbg', $bg, '">', (($taggable) ? strtolower($txt['smftags_taggable']) : strtolower($txt['smftags_untaggable'])), '</td>', "\n";
        if ($type == "checkbox") { echo str_repeat("\t",$indent + 1), '<td class="windowbg', $bg, '">', '<input type="checkbox" name="tag[', $id, ']" value="1"></td>', "\n"; }
		else if ($type == "radio") { echo str_repeat("\t",$indent + 1), '<td class="windowbg', $bg, '">', '<input type="radio" name="master" value="tag[', $id, ']"</td>', "\n"; }
		echo str_repeat("\t",$indent).'</tr>'."\n";
		if ($has_children && $depth >= 0)
			tagadmin_draw_branch($id, $origindent, $depth + 1, $bg, $indent, $type, $offset);
	}
}

?>
