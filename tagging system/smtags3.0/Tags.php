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

if (!defined('SMF'))
    die('Hacking attempt...');

function TagsMain()
{

    // Load the main Tags template
    loadtemplate('Tags');

    // Load the language files
    if (loadlanguage('Tags') == false) {
        loadLanguage('Tags','english');
    }


    // Tags actions
    $subActions = array(
        'edittopic' => 'EditTopic',
        'edittopic2' => 'EditTopic2',
        'suggesttopic' => 'SuggestTopic',
        'suggesttopic2' => 'SuggestTopic2',
        'approvetopic' => 'ApproveTopic',
        'deletetopic' => 'DeleteTopic',
        'rename' => 'RenameTag',
        'viewall' => 'ViewAllTags',
        'merge' => 'MergeTag',
        'move' => 'MoveTag',
        'admin' => 'TagsSettings',
        'admin2' => 'TagsSettings2',
        'cleanup' => 'TagCleanUp',
    );


    // Follow the sa or just go to main links index.
    if (!empty($subActions[@$_GET['sa']])) {
        $subActions[$_GET['sa']]();
    }
    else {
        if (allowedTo('smftags_manage')) {
            if (isset($_REQUEST['todo']) || isset($_REQUEST['create'])) {
                ManageTags2();
            }
            ManageTags();
        }
        ViewTags();
    }
}

// build arrays for moderators to manage new tags and tagged topics
// keep newtags_pp high as pagination does not work for new tags - this is flood control only
function ManageTags($newtags_pp = 50, $newtagged_pp = 20)
{
    global $context, $txt, $mbname, $db_prefix, $scripturl;

        if (allowedTo('smftags_create')) { filltags(); }
        // no pagination for new tags
        $context['tags_newtags_start'] = 0;
        $context['tags_newtagged_start'] = (int) (isset($_REQUEST['start']) ? $_REQUEST['start'] : 0);
        $context['tags_newtagged_pp'] = (int) $newtagged_pp;
        $context['tags_newtags_pp'] = (int) $newtags_pp;
        $limits = $context['tags_newtags_start'] . ',' . $context['tags_newtags_pp'];
        $limited = $context['tags_newtagged_start'] . ',' . $context['tags_newtagged_pp'];

        // this function is also called externally
        // so only set the title if action=tags
        if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'tags') {
            $context['page_title'] = $mbname . ' - ' . $txt['smftags_manage'];
        }

        // find pending tags
        $dbresult = db_query("
            SELECT ID_TAG, tag, parent_id, approved, taggable
                FROM {$db_prefix}tags
                WHERE approved < 1 LIMIT $limits",
            __FILE__, __LINE__);

        $context['tags_newtags'] = array();
        $parentcache = array();
        while ($row = mysql_fetch_assoc($dbresult))
        {
            $parentarray = array();
            if (!empty($row['parent_id'])) {
                $parent = $row['parent_id'];
                while (!empty($parent)) {
                    if (isset($parentcache[$parent])) {
                        $parentarray[$parent] = $parentcache[$parent];
                    }
                    else {
                        $dbchild = db_query("
                            SELECT ID_TAG, tag, parent_id, approved, taggable
                                FROM {$db_prefix}tags
                                WHERE ID_TAG = $parent", __FILE__, __LINE__);
                        $childrow = mysql_fetch_assoc($dbchild);
                        $parentarray[$parent] = array(
                            'tag' => $childrow['tag'],
                            'parent_id' => $childrow['parent_id'],
                            'approved' => $childrow['approved'],
                            'taggable' => $childrow['taggable'],
                        );
                        $parentcache[$parent] = $parentarray[$parent];
                        mysql_free_result($dbchild);
                    }
                    $parent = $parentarray[$parent]['parent_id'];
                }
            }
            $context['tags_newtags'][] = array(
                'ID_TAG' => $row['ID_TAG'],
                'tag' => $row['tag'],
                'approved' => $row['approved'],
                'parent_id' => $row['parent_id'],
                'parent_array' => $parentarray,
                'taggable' => $row['taggable'],
            );
        }

        mysql_free_result($dbresult);
        unset($parentcache);

        // and pending tagged
        if (isset($context['tags_idarray'])) {
            // if this is set, we're expanding these tags
            $context['tags_expand'] = 1;
            $idarray = $context['tags_idarray'];
        }
        else {
            // else, look up the database for ourselves
            $context['tags_expand'] = 0;
            $idarray = array();
            $dbresult = db_query("
                SELECT ID_TOPIC, ID_MEMBER
                    FROM {$db_prefix}tags_log
                    WHERE approved < 1
                    GROUP BY ID_TOPIC, ID_MEMBER
                    ORDER BY ID_TOPIC, ID_MEMBER
                    LIMIT $limited",
                __FILE__, __LINE__);

            while ($row = mysql_fetch_array($dbresult)) {
                $idarray[] = intval($row[0]) . 'x' . intval($row[1]);
            }

            mysql_free_result($dbresult);
        }

        $dbresult = db_query("
            SELECT tl.ID, tl.ID_TOPIC, tl.ID_MEMBER, tl.ID_TAG, t.tag, ul.realName AS memberName, tl.approved, m.subject, um.realName as posterName, m.ID_MEMBER as posterID
                FROM {$db_prefix}tags_log AS tl
                LEFT JOIN {$db_prefix}topics AS top ON tl.ID_TOPIC = top.ID_TOPIC
                LEFT JOIN {$db_prefix}messages AS m ON top.id_first_msg = m.id_msg
                LEFT JOIN {$db_prefix}members AS ul ON tl.id_member = ul.id_member
                LEFT JOIN {$db_prefix}members AS um ON m.ID_MEMBER = um.id_member
                LEFT JOIN {$db_prefix}tags AS t ON tl.ID_TAG = t.ID_TAG
                WHERE CONCAT(tl.ID_TOPIC,'x',tl.ID_MEMBER) IN ('" . implode("','", $idarray) . "')
                ORDER BY ID_TOPIC, ID_MEMBER",
            __FILE__, __LINE__);

        $context['tags_newtagged'] = array();
        $prev = array();
        while ($row = mysql_fetch_assoc($dbresult))
        {
            if (isset($prev['ID_TOPIC'],$prev['ID_MEMBER']) && $prev['ID_TOPIC'] == $row['ID_TOPIC'] && $prev['ID_MEMBER'] == $row['ID_MEMBER']) {
                $prev['tags'][] = array(
                    'ID' => $row['ID'],
                    'ID_TAG' => $row['ID_TAG'],
                    'tag' => $row['tag'],
                    'approved' => $row['approved'],
                );
            }
            else {
                if (!empty($prev)) { $context['tags_newtagged'][] = $prev; }

                $prev = array(
                    'ID_TOPIC' => $row['ID_TOPIC'],
                    'ID_MEMBER' => $row['ID_MEMBER'],
                    'approved' => $row['approved'],
                    'subject' => $row['subject'],
                    'memberName' => $row['memberName'],
                    'posterName' => $row['posterName'],
                    'posterID' => $row['posterID'],
                    'tags' => array()
                );

                $prev['tags'][] = array(
                    'ID' => $row['ID'],
                    'ID_TAG' => $row['ID_TAG'],
                    'tag' => $row['tag'],
                    'approved' => $row['approved'],
                );
            }
        }
        if (!empty($prev)) { $context['tags_newtagged'][] = $prev; }

        mysql_free_result($dbresult);

        // find totals for pagination
        // the constructPageIndex function only allows a single pagination per http request
        //   so we just have to hope nobody floods us with new tag requests

        // if we're on the first page and have less than the max results, we can safely ignore pagination
        if (!$context['tags_newtagged_start'] && count($context['tags_newtagged']) < $newtagged_pp) {
            $context['tags_newtagged_count'] = count($context['tags_newtagged']);
            $context['tags_index'] = "";
        }
        else {
            $dbresult = db_query("
                SELECT COUNT(distinct CONCAT(ID_MEMBER,'x',ID_TOPIC))
                    FROM {$db_prefix}tags_log
                    WHERE approved < 1", __FILE__, __LINE__);
            $context['tags_newtagged_count'] = (int) mysql_result($dbresult, 0);
            mysql_free_result($dbresult);

            $context['tags_index'] = constructPageIndex($scripturl . '?action=tags', $context['tags_newtagged_start'], $context['tags_newtagged_count'], $context['tags_newtagged_pp']);
        }
}

// called for all changes made on the manage tags base pages
function ManageTags2 ()
{
    global $context, $txt, $mbname, $db_prefix, $user_info;
    // permissions checking already done, so just do a sanity check
    if (isset($_REQUEST['pendingtags'])) {
        // manage tagging pending tag functions (approve|delete)
        if (in_array(strtolower($_REQUEST['todo']),array('approve','delete'))) {
            $idarray = array();
            foreach ($_REQUEST as $key => $value) {
                if (preg_match('/^a[0-9]+$/', $key)) { $idarray[] = (int) substr($key,1); }
            }
        }
        if (strtolower($_REQUEST['todo']) == 'approve') {
            $dbresult = db_query("
                UPDATE {$db_prefix}tags
                SET approved = 1
                WHERE `ID_TAG` IN (" . implode(",", $idarray) . ")"
                , __FILE__, __LINE__);
        }
        else if (strtolower($_REQUEST['todo']) == 'delete') {
            $dbresult = db_query("
                DELETE FROM {$db_prefix}tags
                WHERE `ID_TAG` IN (".implode(",", $idarray) . ")"
                , __FILE__, __LINE__);
        }
    }
    else if (isset($_REQUEST['tagusertopic'])) {
        // manage topic tagging (expand|approve|delete)topic
        if (strtolower($_REQUEST['todo']) == 'expandusertopic') {
            // if expanding we'll need to store some data for later
            $idarray = array();
            foreach ($_REQUEST as $key => $value) {
                if (preg_match('/^[0-9]+x[0-9]+$/', $key)) {
                    $idarray[] = $key;
                }
            }
            $context['tags_idarray'] = $idarray;
        }
        else if (in_array(strtolower($_REQUEST['todo']),array('approveusertopic','deleteusertopic'))) {
            // if not expanding, process only, and we'll be sent back to the display page 
            $conditions = array();
            foreach ($_REQUEST as $key => $value) {
                if (preg_match('/^[0-9]+x[0-9]+$/', $key)) {
                    list ($topic_id, $member_id) = array_map('intval', split('x', $key));
                    $conditions[] = "(ID_MEMBER = $member_id AND ID_TOPIC = $topic_id)";
                }
                else if (preg_match('/^i[0-9]+$/', $key)) {
                    $conditions[] = "ID = " . intval(substr($key,1));
                }
            }
            if (strtolower($_REQUEST['todo']) == 'approveusertopic') {
                $dbresult = db_query("
                    UPDATE {$db_prefix}tags_log
                    SET approved = 1
                    WHERE ".implode(' OR ',$conditions) 
                    , __FILE__, __LINE__);
            }
            else if (strtolower($_REQUEST['todo']) == 'deleteusertopic') {
                $dbresult = db_query("
                    DELETE FROM {$db_prefix}tags_log
                    WHERE ".implode(' OR ',$conditions) 
                    , __FILE__, __LINE__);
            }
        }
        // no else required, return and continue as planned
    }
    else if (isset($_REQUEST['create'])) {
        // create new tags
        $carray = array();
        foreach ($_REQUEST as $key => $value) {
            if (preg_match('/^tag[0-9]+$/', $key)) {
                $id = intval(substr($key, 3));
                if (!isset($_REQUEST['tag'.$id]) || empty($_REQUEST['tag'.$id])) { continue 1; } 
                $tag = trim($_REQUEST['tag'.$id]);
                $parent = ((isset($_REQUEST['parent'.$id]) && $_REQUEST['parent'.$id] != 0) ? (int) $_REQUEST['parent'.$id] : 'NULL');
                $taggable = (isset($_REQUEST['taggable'.$id]) ? (int) $_REQUEST['taggable'.$id] : 0);
                $approved = (isset($_REQUEST['approved'.$id]) ? (int) $_REQUEST['approved'.$id] : 0);
                $carray[] = "(\"" . mysql_real_escape_string($tag) . "\", $parent, $taggable, $approved)";
            }
        }
        $q = db_query("
            INSERT INTO {$db_prefix}tags (`tag`, `parent_id`, `taggable`, `approved`)
                VALUES ".implode(',', $carray),
                __FILE__, __LINE__);
    }
}

// used for admin only, may be resource and bandwidth intensive for large forums
function ViewAllTags ($displaytags = 0, $sort = 0) {
    global $context, $mbname, $txt, $db_prefix;

    if (!allowedTo('smftags_manage')) {
        fatal_error($txt['cannot_smftags_manage'],false);
    }

    $render = true;

    if (!empty($_REQUEST['todo'])) {
        $idarray = array_map('intval', array_keys($_REQUEST['tag']));
        if (in_array($_REQUEST['todo'],array('move','merge'))) {
            $act = $_REQUEST['todo'];
            $context['smftags_list'] = array_map('intval',array_keys($_REQUEST['tag']));
            $context['sub_template']  = 'viewall2';
            $context['page_title'] = $mbname . ' - ' . $txt['smftags_act_'.$act];
            $render = false;

            filltags(0, $context['smftags_list']); 

            // hacky fix: move any orphaned tags to the root
            foreach ($context['tags']['by_parent'] as $parent => $children) {
                foreach ($children as $i => $child) {
                    $foundtags[$child[0]] = 1;
                }
            }
            if (!isset($context['tags']['by_parent'][0])) { $context['tags']['by_parent'][0] = array(); }
            foreach ($context['tags']['by_parent'] as $parent => $children) {
                if ($parent > 0 && !isset($foundtags[$parent])) {
                    $context['tags']['by_parent'][0] = array_merge($context['tags']['by_parent'][0],$context['tags']['by_parent'][$parent]);
                    unset($context['tags']['by_parent'][$parent]);
                }
            }
            unset($foundtags);
            ViewTags();
        }
        else if (in_array($_REQUEST['todo'],array('untaggable','taggable','unapprove'))) {
            $set = (($_REQUEST['todo'] == 'unapprove') ? 'approved = 0' : 'taggable = ' . (($_REQUEST['todo'] == 'taggable') ? '1' : '0'));
            $dbresult = db_query("
                UPDATE {$db_prefix}tags
                SET $set 
                WHERE `ID_TAG` IN (" . implode(",", $idarray) . ")"
                , __FILE__, __LINE__);
            $context['tags']['modifiedtxt'] = sprintf($txt['smftags_success_'.strtolower($_REQUEST['todo'])],count(($idarray)));
        }
        else if (in_array($_REQUEST['todo'],array('delete'))) {
            $dbresult = db_query("
                DELETE t, l FROM {$db_prefix}tags AS t
                LEFT JOIN {$db_prefix}tags_log AS l ON t.`ID_TAG` = l.`ID_TAG`
                WHERE t.`ID_TAG` IN (" . implode(",", $idarray) . ")"
                , __FILE__, __LINE__);
            $context['tags']['modifiedtxt'] = sprintf($txt['smftags_success_delete'],count($idarray),db_affected_rows());
        }
    }
    else if (isset($_REQUEST['move']) || isset($_REQUEST['merge'])) {
        $master = (int) substr($_REQUEST['master'],4,-1);
        $slaves = array_map('intval', explode(',', $_REQUEST['slaves']));
        // it's futile trying to merge/move tags to themselves
        while (in_array($master, $slaves)) { unset($slaves[array_search($master, $slaves)]); }
        if (isset($_REQUEST['move'])) {
            $dbresult = db_query("
                UPDATE {$db_prefix}tags
                SET `parent_id` = $master
                WHERE `ID_TAG` IN (" . implode(",", $slaves) . ")"
                , __FILE__, __LINE__);
            $context['tags']['modifiedtxt'] = sprintf($txt['smftags_success_move'],count($slaves));
        }
        else if (isset($_REQUEST['merge'])) {
            // ignore key collisions on (ID_TAG, ID_TOPIC)
            $dbresult = db_query("
                UPDATE IGNORE {$db_prefix}tags_log
                SET `ID_TAG` = $master
                WHERE `ID_TAG` IN (" . implode(",", $slaves) . ")"
                , __FILE__, __LINE__);
            $count = db_affected_rows();
            $dbresult = db_query("
                DELETE t.*, tl.* FROM {$db_prefix}tags AS t
                LEFT JOIN {$db_prefix}tags_log AS tl ON tl.ID_TAG = t.ID_TAG
                WHERE t.ID_TAG IN (" . implode(",", $slaves) . ")"
                , __FILE__, __LINE__);
            $count += db_affected_rows();
            $context['tags']['modifiedtxt'] = sprintf($txt['smftags_success_merge'],count($slaves),$count);
        }
    }

    if ($render) {  
        filltags(0, array(), 1);
        ViewTags($displaytags, $sort);
        $context['sub_template']  = 'viewall';
        $context['page_title'] = $mbname . ' - ' . $txt['smftags_all'];
    }
}

// print tag cloud
function ViewTags($displaytags = -1, $sort = 0)
{
    global $context, $txt, $mbname, $db_prefix, $scripturl, $user_info, $modSettings;

    $a_view = allowedTo('smftags_view');

    if ($a_view == false)
        fatal_error($txt['cannot_smftags_view'],false);
    
    // Views that tag results and popular tags
    if (isset($_REQUEST['id']))
    {
        $context['tag_search'] = array();
        // create an array with all the tag ids
        $idr = array_map('intval',explode(',', $_REQUEST['id']));
        $topics = array();

        // find the name of tags in the array, just in case one isn't used
        $dbresult = db_query("
            SELECT ID_TAG, tag
            FROM {$db_prefix}tags 
            WHERE approved AND taggable AND ID_TAG IN(".implode(',', $idr).")
            ORDER BY tag ASC", __FILE__, __LINE__);

        while ($row = mysql_fetch_assoc($dbresult)) {
            $context['tag_search'][$row['ID_TAG']] = $row['tag'];
        }
        mysql_free_result($dbresult);

        if (empty($context['tag_search'])) { fatal_error($txt['smftags_err_invalidtag'], false); }
        
        $context['page_title'] = $mbname . ' - ' . $txt['smftags_resultsfor'] . implode(', ', $context['tag_search']);
        $context['start'] = (int) $_REQUEST['start'];

        // find the topics where all requested tags are used
        $dbresult = db_query("
            SELECT ID_TOPIC, COUNT(*) AS count
            FROM {$db_prefix}tags_log AS l
            WHERE approved AND ID_TAG IN(".implode(',', $idr).")
            GROUP BY ID_TOPIC
            HAVING count = " . count($idr) . "
            ORDER BY ID_TOPIC ASC",
            __FILE__, __LINE__);

        while ($row = mysql_fetch_row($dbresult)) { $topics[] = $row[0]; }
        mysql_free_result($dbresult);

        if (empty($topics)) { fatal_error($txt['smftags_err_emptytag'], false); }

        // Find Results
        $dbresult = db_query("
            SELECT t.numReplies, t.numViews, f.subject, f.ID_TOPIC, f.ID_MEMBER AS first_ID_MEMBER, u.ID_MEMBER AS last_ID_MEMBER, fm.realName AS first_posterName, um.realName as last_posterName, f.posterTime AS first_posterTime, u.posterTime AS last_posterTime, t.ID_BOARD, ll.ID_TAG, lt.tag
            FROM {$db_prefix}topics AS t
            LEFT JOIN {$db_prefix}boards AS b ON t.ID_BOARD = b.ID_BOARD
            LEFT JOIN {$db_prefix}messages AS f ON t.ID_FIRST_MSG = f.ID_MSG
            LEFT JOIN {$db_prefix}messages AS u ON t.ID_LAST_MSG = u.ID_MSG
            LEFT JOIN {$db_prefix}tags_log AS l ON t.ID_TOPIC = l.ID_TOPIC
            LEFT JOIN {$db_prefix}tags_log AS ll ON l.ID_TOPIC = ll.ID_TOPIC AND ll.approved AND ll.ID_TAG NOT IN (".implode(',', array_keys($context['tag_search'])).")
            LEFT JOIN {$db_prefix}tags AS lt ON ll.ID_TAG = lt.ID_TAG AND lt.approved
            LEFT JOIN {$db_prefix}members AS fm ON f.ID_MEMBER = fm.ID_MEMBER
            LEFT JOIN {$db_prefix}members AS um ON u.ID_MEMBER = um.ID_MEMBER
            WHERE l.approved AND t.ID_TOPIC IN (".implode(',', array_values($topics)).") AND " . $user_info['query_see_board'] . "
            ORDER BY u.posterTime DESC, lt.tag ASC
        ", __FILE__, __LINE__);
        
        $context['tags_topics'] = array();
        $prev = array();
        while ($row = mysql_fetch_assoc($dbresult))
        {
            if (isset($prev['ID_TOPIC']) && $prev['ID_TOPIC'] == $row['ID_TOPIC']) {
                if (!empty($row['ID_TAG']))
                    $prev['othertags'][$row['ID_TAG']] = $row['tag'];
            }
            else {
                if (!empty($prev))
                    $context['tags_topics'][] = $prev;
                $prev = array(
                    'subject' => $row['subject'],
                    'ID_TOPIC' => $row['ID_TOPIC'],
                    'first_ID_MEMBER' => $row['first_ID_MEMBER'],
                    'first_posterName' => $row['first_posterName'],
                    'first_posterTime' => $row['first_posterTime'],
                    'last_ID_MEMBER' => $row['last_ID_MEMBER'],
                    'last_posterName' => $row['last_posterName'],
                    'last_posterTime' => $row['last_posterTime'],
                    'numViews' => $row['numViews'],
                    'numReplies' => $row['numReplies'],
                    'othertags' => array(),
                );
                if (!empty($row['ID_TAG']))
                    $prev['othertags'][$row['ID_TAG']] = $row['tag'];
            }
        }
        if (!empty($prev))
            $context['tags_topics'][] = $prev;
        mysql_free_result($dbresult);

        $context['sub_template']  = 'results';
    }
    else {
        // so we're wanting a cloud of all the tags then

        // this function is also called externally
        // so only set the title if action=tags
        if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'tags' && !isset($_REQUEST['sa'])) {
            $context['page_title'] = $mbname . ' - ' . $txt['smftags_popular'];
        }

        if ($displaytags == -1) {
            $displaytags = (int) $modSettings['smftags_set_cloud_tags_to_show'];
        }

        //Tag cloud from http://www.prism-perfect.net/archive/php-tag-cloud-tutorial/
        // order by logic revised to ensure optimal tags are selected, this is then resorted later on request
        $dbresult = db_query("
            SELECT t.tag AS tag, l.ID_TAG, COUNT(l.ID_TAG) AS quantity
            FROM {$db_prefix}tags as t, {$db_prefix}tags_log as l
            WHERE t.ID_TAG = l.ID_TAG AND t.taggable AND t.approved AND l.approved
            GROUP BY l.ID_TAG
            ORDER BY COUNT(l.ID_TAG) DESC, RAND()" . (($displaytags < 1) ? "" : "
            LIMIT $displaytags
        "), __FILE__, __LINE__);

        // here we loop through the results and put them into a two arrays
        // $tagq[id] = $quantity; so we can do a popularity sort
        // $tagn[id] = 'name';
        $tagq = array();
        $tagn = array();

        while ($row = mysql_fetch_array($dbresult)) {
            $tagq[$row['ID_TAG']] = $row['quantity'];
            $tagn[$row['ID_TAG']] = $row['tag'];
        }

        // if there are tags, sort them,
        //  and define $tagsort so we know how to loop through the array later
        if ((empty($sort) && $modSettings['smftags_set_cloud_sort'] == 'alpha') || strval($sort) == 'alpha') {
            if (!empty($tagn)) {
                natcasesort($tagn);
            }
            $tagsort = &$tagn;
        }
        elseif ((empty($sort) && $modSettings['smftags_set_cloud_sort'] == 'count') || strval($sort) == 'count') {
            if (!empty($tagq)) {
                arsort($tagq, SORT_NUMERIC);
            }
            $tagsort = &$tagq;
        }
        else {
            // there's no shuffle_assoc function
            //  based on code by andjones at gmail dot com from php.net
            if (!empty($tagq)) {
                $keys = array_keys($tagq);
                shuffle($keys); 
                $newtagq = array(); 
                foreach ($keys as $key) {
                    $newtagq[$key] = $tagq[$key];
                }
                $tagq = $newtagq;
                unset($newtagq);
            }
            $tagsort = &$tagq;
        }

        $row_count = 0;
        if (count($tagn) > 0) {

            // max and min font sizes in precent [sic] configurable in admin settings
            $max_size = $modSettings['smftags_set_cloud_max_font_size_precent'];
            $min_size = $modSettings['smftags_set_cloud_min_font_size_precent'];

            // get the largest and smallest array values
            $max_qty = max(array_values($tagq));
            $min_qty = min(array_values($tagq));

            // find the range of values
            $spread = $max_qty - $min_qty;
            if (0 == $spread)
            { // we don't want to divide by zero
                $spread = 1;
            }

            // determine the font-size increment
            // this is the increase per tag quantity (times used)
            $step = ($max_size - $min_size)/($spread);

            $context['poptags'] = '';

            // loop through our tag array
            foreach ($tagsort as $id => $null) {
                // get data - we don't know which array we've been passed
                $quantity = $tagq[$id];
                $name = $tagn[$id];

                $row_count++;

                // calculate CSS font-size
                // find the $value in excess of $min_qty
                // multiply by the font-size increment ($size)
                // and add the $min_size set above
                $size = $min_size + (($quantity - $min_qty) * $step);
                // comment if you don't want sizes in whole %:
                $size = ceil($size);

                // link to the tag details page, with a hover title
                //  make spaces non-breaking for auto-wrapped dynamic width
                $context['poptags'] .= '<a href="' . $scripturl . '?action=tags;id=' . $id . '" style="font-size: '.$size.'%" title="'.sprintf($txt['smftags_ntopicstaggedwithx'], $quantity, $name).'">'.str_replace(' ','&nbsp;',$name).'</a> ';

            }
        }
        $context['tagCount'] = $row_count;

        $latest_limit = $modSettings['smftags_set_latest_limit'];

        // Find Results
        $dbresult = db_query("
            SELECT DISTINCT l.ID_TOPIC, t.numReplies, t.numViews, m.ID_MEMBER, u.realName AS posterName, m.subject, m.ID_TOPIC, m.posterTime, t.ID_BOARD
            FROM {$db_prefix}tags_log as l
            LEFT JOIN {$db_prefix}topics AS t ON l.ID_TOPIC = t.ID_TOPIC
            LEFT JOIN {$db_prefix}boards AS b ON b.ID_BOARD = t.ID_BOARD
            LEFT JOIN {$db_prefix}messages AS m ON t.ID_FIRST_MSG = m.ID_MSG
            LEFT JOIN {$db_prefix}members AS u ON m.ID_MEMBER = u.ID_MEMBER
            WHERE l.approved AND " . $user_info['query_see_board'] . "
            ORDER BY l.ID DESC LIMIT $latest_limit", __FILE__, __LINE__);

        $context['tags_topics'] = array();
        while ($row = mysql_fetch_assoc($dbresult))
        {
            $context['tags_topics'][] = array(
                'ID_MEMBER' => $row['ID_MEMBER'],
                'posterName' => $row['posterName'],
                'subject' => $row['subject'],
                'ID_TOPIC' => $row['ID_TOPIC'],
                'posterTime' => $row['posterTime'],
                'numViews' => $row['numViews'],
                'numReplies' => $row['numReplies'],
            );
        }
        mysql_free_result($dbresult);
    }
}

// look up relevant tags, for the topic provided if applicable
function filltags ($ID_TOPIC = NULL, $LIST = array(), $flag = 0) {
    // flag = 0 => default, both approved and suggested
    // flag = -1 => suggested tags only
    // flag = 1 => approved tags only
    global $db_prefix, $context;
    $context['tags']['topic'] = $ID_TOPIC;
    $context['tags']['by_parent'] = $context['tags']['has_tagged_children'] = array();
    $context['tags']['show_rmsuggest'] = (allowedTo('smftags_manage') ? 0 : -1);

    if (empty($ID_TOPIC)) {
        // use of this query needs much refinement as it will be massive on larger forums
        $where = array();
        if (!empty($LIST)) { $where[] = "t.ID_TAG IN ('" . implode("','", $LIST) . "')"; }
        if (!empty($flag)) { $where[] = "t.approved = ".max(0,$flag); }
        $query = db_query("
            SELECT t.ID_TAG AS ID_TAG, t.tag AS tag, COUNT(l.ID_TAG) AS quantity, t.approved AS approved1, t.parent_id AS parent_id, t.taggable AS taggable
            FROM {$db_prefix}tags AS t
            LEFT JOIN {$db_prefix}tags_log AS l ON t.ID_TAG = l.ID_TAG
            " . (empty($where) ? '' : 'WHERE ' . implode(' AND ', $where)) . "
            GROUP BY t.ID_TAG
            ORDER BY t.tag ASC
        ", __FILE__, __LINE__);

        while ($array = mysql_fetch_assoc($query)) {

            if (!$array['parent_id']) { $array['parent_id'] = 0; }
            // define an array of tags by their parent ids - yes it looks ugly
            $context['tags']['by_parent'][$array['parent_id']][] = array(
                $array['ID_TAG'],
                $array['tag'],
                $array['approved1'],
                $array['taggable'],
                0,
                -1,
                $array['quantity']
            );
        }
    }
    else {
        $query = db_query("
            SELECT t.ID_TAG AS ID_TAG, t.tag AS tag, COUNT(l.ID_TAG) AS quantity, t.approved AS approved1, t.parent_id AS parent_id, t.taggable AS taggable, tl.ID_TAG AS tagged, tl.approved AS approved2
            FROM {$db_prefix}tags AS t
            LEFT JOIN {$db_prefix}tags_log AS tl ON t.ID_TAG = tl.ID_TAG AND tl.ID_TOPIC = $ID_TOPIC
            LEFT JOIN {$db_prefix}tags_log AS l ON t.ID_TAG = l.ID_TAG
            GROUP BY t.ID_TAG
            ORDER BY t.tag ASC
        ", __FILE__, __LINE__);

        while ($array = mysql_fetch_assoc($query)) {
            if ($context['tags']['show_rmsuggest'] == 0 && isset($array['approved2']) && $array['approved2'] == 0 && $array['approved1'] == 1) { $context['tags']['show_rmsuggest'] = 1; }
            if (!$array['parent_id']) { $array['parent_id'] = 0; }
            // define an array of tags by their parent ids - yes it looks ugly
            $context['tags']['by_parent'][$array['parent_id']][] = array(
                $array['ID_TAG'],
                $array['tag'],
                $array['approved1'],
                $array['taggable'],
                $array['tagged'],
                $array['approved2'],
                $array['quantity']
            );
            $parents_by_children[$array['ID_TAG']] = $array['parent_id'];
            if ($array['parent_id'] && $array['tagged']) {
                $tempid = $array['ID_TAG'];
                // expand self if tagged and has a parent
                // TODO simplify this convoluted logic and unneccesary data storage
                $context['tags']['has_tagged_children'][$tempid] = TRUE;
                while (isset($parents_by_children[$tempid])) { $tempid = $parents_by_children[$tempid]; $context['tags']['has_tagged_children'][$tempid] = TRUE;}
            }
        }
        $context['tags']['show_rmsuggest'] = max(0, $context['tags']['show_rmsuggest']);
    }
}

// called only from Post for new topics
function AddTopic()
{
    global $context, $txt, $mbname, $db_prefix;

    // Check permission
    $a_add = allowedTo('smftags_add');

    if ($a_add == false)
        fatal_error($txt['cannot_smftags_add'],false);

    // get query results to build array of all tags
    $query = db_query("
        SELECT t.tag AS tag, t.ID_TAG AS ID_TAG, COUNT(l.ID_TAG) AS quantity, t.approved AS approved
        FROM {$db_prefix}tags AS t
        LEFT JOIN {$db_prefix}tags_log AS l ON t.ID_TAG = l.ID_TAG
        GROUP BY t.ID_TAG
        ORDER BY t.tag ASC",
    __FILE__, __LINE__);

    $context['tags'] = array();
    while ($row = mysql_fetch_assoc($query))
    {
        $context['tags'][] = array(
            'ID_TAG' => $row['ID_TAG'],
            'tag' => $row['tag'],
            'quantity' => $row['quantity'],
            'approved' => $row['approved'],
            'tagged' => 0,
        );
    }
    // don't load the subtemplate
}

function EditTopic()
{
    global $context, $txt, $mbname, $db_prefix, $ID_MEMBER;

    // Get the Topic (id)
    $topic = (int) $_REQUEST['topic'];

    if (empty($topic))
        fatal_error($txt['smftags_err_notopic'],false);

    // Check permission
    $a_edit = allowedTo('smftags_edit_any');

    if (!$a_edit && allowedTo('smftags_edit_own')) {
        $dbresult = db_query("
            SELECT m.ID_MEMBER
            FROM {$db_prefix}topics as t, {$db_prefix}messages as m
            WHERE t.ID_FIRST_MSG = m.ID_MSG AND t.ID_TOPIC = $topic
            LIMIT 1
        ", __FILE__, __LINE__);

        $row = mysql_fetch_assoc($dbresult);
        mysql_free_result($dbresult);
        if ($ID_MEMBER == $row['ID_MEMBER'])
        $a_edit = true;
    }

    if (!$a_edit)
        fatal_error($txt['cannot_smftags_edit'],false);

    $context['tags_topic'] = $topic;

    filltags($topic);

    // if allowed to manage, all editable, otherwise only approved editable
    $context['smftags_flag'] = (allowedTo('smftags_manage') ? 2 : 1);
    $context['smftags_act'] = 'edit';

    // Load the subtemplate
    $context['sub_template']  = 'edittopic';
    $context['page_title'] = $mbname . ' - ' . $txt['smftags_edittag'];
}

// called from Display.php form with manual tag data, or sa=edittopic with list tag data
// this entire function needs to be taken out the back and shot
function EditTopic2()
{
    global $db_prefix, $txt, $modSettings, $ID_MEMBER;
    $topic = (int) $_REQUEST['topic'];
    $type = (int) $_REQUEST['type']; // 1 = manual; 2 = list

    if (empty($topic))
        fatal_error($txt['smftags_err_notopic'],false);

    // Check Permission
    $a_edit = allowedTo('smftags_edit_any');
    $a_suggest = allowedTo('smftags_suggest_any');

    if ((!$a_edit && allowedTo('smftags_edit_own')) || (!$a_suggest && allowedTo('smftags_suggest_own'))) {
        $dbresult = db_query("
            SELECT m.ID_MEMBER
            FROM {$db_prefix}topics as t, {$db_prefix}messages as m
            WHERE t.ID_FIRST_MSG = m.ID_MSG AND t.ID_TOPIC = $topic
            LIMIT 1
        ", __FILE__, __LINE__);

        $row = mysql_fetch_assoc($dbresult);
        mysql_free_result($dbresult);

        if ($ID_MEMBER == $row['ID_MEMBER']) {
            if (!$a_edit)
                $a_edit = allowedTo('smftags_edit_own');
            if (!$a_suggest)
                $a_suggest = allowedTo('smftags_suggest_own');
        }
    }
    if ((!$a_edit && !$a_suggest) || ($type == 1 && !$modSettings['smftags_set_manualtags']) || ($type == 2 && !$modSettings['smftags_set_listtags']))
        fatal_error($txt['cannot_smftags_edit'],false);

    $dbresult= db_query("
        SELECT t.tag,l.ID,t.ID_TAG,l.approved
        FROM {$db_prefix}tags_log as l, {$db_prefix}tags as t
        WHERE t.approved && t.ID_TAG = l.ID_TAG && l.ID_TOPIC = $topic
        ORDER BY l.approved DESC, t.tag ASC", __FILE__, __LINE__);

    $tags[0] = $tags[1] = array();
    $tagcount[0] = $tagcount[1] = 0;
    $tagnames = array();

    // topic tags that are already applied 
    $oldtags[0] = $oldtags[1] = array();        // keyed by ID_TAG
    // topic tags that must now be applied
    $addtags[0] = $addtags[1] = array();        // keyed by ID_TAG
    // tags which exist, but require a new topic tag entry
    $reusetags[0] = $reusetags[1] = array();    // keyed by ID_TAG
    // tags which must first be created
    $createtags[0] = $createtags[1] = array();  // keyed by lowercase tag name

    while($row = mysql_fetch_assoc($dbresult))
    {
        $tagcount[$row['approved']]++;
        $oldtags[$row['approved']][$row['ID_TAG']] = $row['tag'];
        if ($type == 1) { $tagnames[strtolower($row['tag'])] = $row['ID_TAG']; }
    }
    mysql_free_result($dbresult);

    if ($type == 1) {
        // determine how new tags are created
        $createtype = (allowedTo('smftags_suggesttag') ? 0 : (allowedTo('smftags_createtag') ? 1 : -1));
        // do manual tagging only
        $rawtags[0] = $rawtags[1] = array();
        if ($a_edit || $a_suggest) { $rawtags[0] = array_unique(array_map('trim', explode(chr($modSettings['smftags_set_delimiter']), $_REQUEST['suggesttags']))); }
        if ($a_edit) { $rawtags[1] = array_unique(array_map('trim', explode(chr($modSettings['smftags_set_delimiter']), $_REQUEST['tags']))); }

        // attempt to identify tags by name
        foreach ($rawtags as $i => $array) {
            if (!empty($rawtags[$i])) {
                foreach ($array as $j => $tag) {
                    if (empty($tag)) {
                        // ignore null tags
                        unset($rawtags[$i][$j]);
                    }
                    else if (isset($tagnames[strtolower($tag)])) {
                        // tag is already known
                        $j = $tagnames[strtolower($tag)];
                        $addtags[$i][$j] = $tag;
                        // just check we're not trying to approve and suggest the same tag
                        if (isset($addtags[1-$i][$j])) { unset($addtags[0][$j]); }
                    }
                    else if ($createtype > -1) {
                        // tag probably needs creating
                        $j = strtolower($tag);
                        $createtags[$i][$j] = $tag;
                        if (isset($createtags[1-$i][$j])) { unset($createtags[0][$j]); }
                    }
                }
            }
        }
        if (!empty($createtags[0]) || !empty($createtags[1])) {
            // see if these tags have already been added or not, we do not care if they are approved or not
            $dbresult = db_query("
                SELECT DISTINCT tag AS tag, ID_TAG
                FROM {$db_prefix}tags
                WHERE tag IN (\"" . implode('", "', array_merge($createtags[0], $createtags[1])) . '")
                ORDER BY `ID_TAG` DESC', __FILE__, __LINE__);

            while($row = mysql_fetch_assoc($dbresult))
            {
                $j = 0;
                $ltag = strtolower($row['tag']);
                // remove any occurances from the create lists
                if (isset($createtags[0][$ltag])) {
                    unset($createtags[0][$ltag]);
                }
                if (isset($createtags[1][$ltag])) {
                    unset($createtags[1][$ltag]);
                    $j = 1;
                }
                // move tag to the new list
                $reusetags[$j][$row['ID_TAG']] = 1;
            }
            mysql_free_result($dbresult);
        }
    }
    else if ($type == 2) {
        // do list tagging only
        // submissions will always be the highest permission available to the user ($a_edit)
        foreach ($_REQUEST as $key => $value) {
            if (substr($key,0,3) == "tag" && is_numeric(substr($key,3))) {
                $addtags[$a_edit][substr($key,3)] = 1;
            }
        }
    }

    // discard any tags that do not meet our size constraints - silently
	// this is intentionally done after checking for existing tags to allow for grandfathered tags outside the current constraints
    foreach ($createtags as $i => $array) {
        foreach ($array as $j => $tag) {
            if (strlen($tag) < $modSettings['smftags_set_mintaglength'] || strlen($tag) > $modSettings['smftags_set_maxtaglength']) {
                unset($createtags[$i][$j]);
            }
        }
    }

    // check if need to prune some tags - for now will be discarded silently
    foreach (array(0,1) as $i) {
        while ($modSettings['smftags_set_maxtags'] < (count($addtags[$i]) + count($createtags[$i]) + count($reusetags[$i]) - count($oldtags[$i]))) {
            if (!empty($addtags)) { array_pop($addtags[$i]); }
            else if (!empty($createtags)) { array_pop($reusetags[$i]); }
            else if (!empty($reuseetags)) { array_pop($createtags[$i]); }
        }
    }

    // action arrays
    $add = $create = $delete = array();

    // if topic tags were applied to new but existing tags
    if (!empty($reusetags[0]) || !empty($reusetags[1])) {
        foreach ($reusetags as $i => $array) {
            foreach ($array as $key => $j) {
                $add[] = "($key,$topic,$ID_MEMBER,$i)";
            }
        }
    }
    unset ($reusetags);

    // firstly delete tags that have been unticked or removed
    // suggested tags are not deletable with list tags, by design
    // (recall that disabled form elements will not be submitted)
    foreach (($type == 2 ? array(1) : array(0,1)) as $i) {
        foreach (array_diff_key($oldtags[$i],$addtags[$i]) as $j => $tag) {
            $delete[] = (int) $j;
        }
    }
    if (!empty($delete)) {
        $q = db_query("DELETE FROM {$db_prefix}tags_log WHERE `ID_TOPIC` = $topic AND `ID_TAG` IN (".implode(',',$delete).")", __FILE__, __LINE__);
    }
    unset($delete);

    // secondly we create any new tags
    foreach ($createtags as $i => $j) {
        foreach ($j as $tag) {
            // this first query has to be done individually for last_insert_id() support
            $q = db_query("INSERT INTO {$db_prefix}tags (`TAG`, `approved`) VALUES (\"".mysql_real_escape_string($tag)."\", $createtype)", __FILE__, __LINE__);
            // but the rest we store for later
            $add[] = "(".db_insert_id().", $topic, $ID_MEMBER, $i)";
        }
    }
    unset($createtags);

    // thirdly, replace the topic tag entries
    foreach (array(0,1) as $i) {
        foreach (array_diff_key($addtags[$i],$oldtags[$i]) as $j => $tag) {
            $add[] = "($j, $topic, $ID_MEMBER, $i)";
        }
    }
    if (!empty($add)) {
        $q = db_query("REPLACE INTO {$db_prefix}tags_log (`ID_TAG`,`ID_TOPIC`,`ID_MEMBER`,`approved`) VALUES ".implode(',', $add), __FILE__, __LINE__);
    }
    unset($add);

    // finally, if the 'remove suggestions' option has been ticked, clear out any remaining suggestions
    if (isset($_REQUEST['tags_rmsuggest']) && allowedTo('smftags_manage')) {
        $q = db_query("DELETE FROM {$db_prefix}tags_log WHERE `approved` = 0 AND `ID_TOPIC` = $topic", __FILE__, __LINE__);
    }

    //Redirect back to the topic
    redirectexit('topic=' . $topic);
}

function ApproveTopic()
{
    global $db_prefix, $ID_MEMBER, $txt;
    
    $id = (int) $_REQUEST['id'];
    //Check permission
    $a_approve = allowedTo('smftags_manage');

    if (!$a_approve)
        fatal_error($txt['cannot_smftags_approve'],false);
    
    // get some more details
    $dbresult = db_query("
        SELECT ID_MEMBER,ID_TOPIC,ID_TAG
        FROM {$db_prefix}tags_log
        WHERE ID = '$id'
        LIMIT 1
        ", __FILE__, __LINE__);

    $row = mysql_fetch_assoc($dbresult);
    mysql_free_result($dbresult);

    // Approve the taggings
    db_query("
        UPDATE {$db_prefix}tags_log
        SET `approved` = 1
        WHERE ID = '$id'
        LIMIT 1
    ", __FILE__, __LINE__);

    // Redirect back to the topic
    redirectexit('topic='.$row['ID_TOPIC']);
}

function DeleteTopic()
{
    global $db_prefix, $ID_MEMBER, $txt;
    
    $id = (int) $_REQUEST['id'];
    //Check permission
    $a_edit = allowedTo('smftags_edit_any');

    // if we have a chance of having rights
    if ($a_edit || allowedTo('smftags_edit_own')) {
        // get some more details
        $dbresult = db_query("
            SELECT ID_MEMBER,ID_TOPIC,ID_TAG
            FROM {$db_prefix}tags_log
            WHERE ID = '$id'
            LIMIT 1
        ", __FILE__, __LINE__);

        $row = mysql_fetch_assoc($dbresult);
        mysql_free_result($dbresult);

        // check owner permissions
        if (!$a_edit && $row['ID_MEMBER'] == $ID_MEMBER)
            $a_edit = true;
    }

    if (!$a_edit)
        fatal_error($txt['cannot_smftags_edit'],false);
    
    // Delete the tagging
    db_query("
        DELETE FROM {$db_prefix}tags_log
        WHERE ID = '$id'
        LIMIT 1
    ", __FILE__, __LINE__);

    // Redirect back to the topic
    redirectexit('topic='.$row['ID_TOPIC']);
}

function RenameTag()
{
    global $db_prefix, $txt;
    
    //Check permission
    $a_manage = allowedTo('smftags_manage');
    if (!$a_manage)
        fatal_error($txt['cannot_smftags_manage'],false);

    $ida = array();
    foreach ($_REQUEST as $key => $value) {
        if (preg_match('/^rename[0-9]+$/', $key)) { $ida[substr($key,6)] = $value; }
    }

    // get these tag names
    $dbresult = db_query("
        SELECT ID_TAG, tag
        FROM {$db_prefix}tags
        WHERE ID_TAG IN(".implode(',', array_keys($ida)).")
        ORDER BY tag ASC", __FILE__, __LINE__);

    while ($row = mysql_fetch_array($dbresult)) {
        if ($row['tag'] != $ida[$row['ID_TAG']]) {
            $id = (int) $row['ID_TAG'];
            // get some more details
            db_query("
                UPDATE {$db_prefix}tags
                SET tag = '".mysql_real_escape_string($ida[$row['ID_TAG']])."'
                WHERE ID_TAG = '$id'
                LIMIT 1
                ", __FILE__, __LINE__);
        } 
    }
    mysql_free_result($dbresult);
    redirectexit('action=tags;id='.implode(',', array_keys($ida)));
}

function TagsSettings()
{
    global $context, $txt, $mbname;
    adminIndex('tags_settings');
    // Check permission
    $a_admin = allowedTo('smftags_admin');
    if ($a_admin == false)
        fatal_error($txt['cannot_smftags_admin'],false);
    
    $context['sub_template']  = 'admin_settings';
    $context['page_title'] = $mbname . ' - ' . $txt['smftags_settings'];
}

function TagsSettings2()
{
    global $txt, $db_prefix, $modSettings;
    // Check permission
    $a_admin = allowedTo('smftags_admin');
    if ($a_admin == false)
        fatal_error($txt['cannot_smftags_admin'],false);
    
    // Get the settings
    $smftags_set_mintaglength = (int) max(1,$_REQUEST['smftags_set_mintaglength']);
    $smftags_set_maxtaglength =  (int) min(64,$_REQUEST['smftags_set_maxtaglength']);
    $smftags_set_maxtags = (int) $_REQUEST['smftags_set_maxtags'];

    $smftags_set_manualtags = (int) isset($_REQUEST['smftags_set_manualtags']);
    $smftags_set_delimiter = (int) ord($_REQUEST['smftags_set_delimiter']);

    $dbresult = db_query("
        SELECT COUNT(*)
        FROM {$db_prefix}tags
        WHERE `tag` LIKE '%" . chr(ord($_REQUEST['smftags_set_delimiter'])) . "%'
    ", __FILE__, __LINE__);

    $delresult = mysql_fetch_row($dbresult);
    $smftags_set_delimiter = (int) ($delresult[0] == 0 ? ord($_REQUEST['smftags_set_delimiter']) : $modSettings['smftags_set_delimiter']);

    $smftags_set_listtags = (int) isset($_REQUEST['smftags_set_listtags']);
    $smftags_set_listcols = (int) $_REQUEST['smftags_set_listcols'];

    $smftags_set_display_top = (int) isset($_REQUEST['smftags_set_display_top']);
    $smftags_set_display_bottom = (int) isset($_REQUEST['smftags_set_display_bottom']);
    $smftags_set_display_messageindex = (int) isset($_REQUEST['smftags_set_display_messageindex']);

    $smftags_set_latest_limit = (int) $_REQUEST['smftags_set_latest_limit'];
    
    $smftags_set_cloud_tags_to_show = (int) $_REQUEST['smftags_set_cloud_tags_to_show'];
    // precent [sic] variables
    $smftags_set_cloud_max_font_size_precent = (int) $_REQUEST['smftags_set_cloud_max_font_size_precent'];
    $smftags_set_cloud_min_font_size_precent = (int) $_REQUEST['smftags_set_cloud_min_font_size_precent'];
    $smftags_set_cloud_sort = (in_array($_REQUEST['smftags_set_cloud_sort'],array('alpha','count','random'))) ? $_REQUEST['smftags_set_cloud_sort'] : $modSettings['smftags_set_cloud_sort'];

    $smftags_set_taggable = '';
    foreach (split(" ", $_REQUEST['smftags_set_taggable']) as $item)
        if (is_numeric($item)) { $smftags_set_taggable .= " $item"; }


    // Save the setting information
    updateSettings(
    array('smftags_set_maxtags' => $smftags_set_maxtags,
    'smftags_set_mintaglength' => $smftags_set_mintaglength,
    'smftags_set_maxtaglength' => $smftags_set_maxtaglength,
    'smftags_set_manualtags' => $smftags_set_manualtags,
    'smftags_set_delimiter' => $smftags_set_delimiter,
    'smftags_set_listtags' => $smftags_set_listtags,
    'smftags_set_listcols' => $smftags_set_listcols,
    'smftags_set_display_top' => $smftags_set_display_top,
    'smftags_set_display_bottom' => $smftags_set_display_bottom,
    'smftags_set_display_messageindex' => $smftags_set_display_messageindex,
    'smftags_set_latest_limit' => $smftags_set_latest_limit,
    'smftags_set_taggable' => trim($smftags_set_taggable),
    'smftags_set_cloud_sort' => $smftags_set_cloud_sort,
    'smftags_set_cloud_tags_to_show' => $smftags_set_cloud_tags_to_show,
    // precent [sic] variables
    'smftags_set_cloud_max_font_size_precent' => $smftags_set_cloud_max_font_size_precent,
    'smftags_set_cloud_min_font_size_precent' => $smftags_set_cloud_min_font_size_precent,
    ));

    // check if we need to complain about the tag delimiter
    if ($delresult[0] > 0) { fatal_error(sprintf($txt['smftags_err_delimiterused'], $delresult[0])); }
    
    // Redirect to the admin section
    redirectexit('action=tags;sa=admin');
}

// delete only empty tags, not sure why?
function TagCleanUp($ID_TAG)
{
    global $db_prefix, $txt;
    if (!allowedTo('smftags_admin'))
    	fatal_error($txt['cannot_smftags_admin']);

    db_query("
        SELECT ID_TAG 
        FROM {$db_prefix}tags_log 
        WHERE ID_TAG = " . $ID_TAG
        , __FILE__, __LINE__);

    if (db_affected_rows() == 0) 
        db_query("DELETE FROM {$db_prefix}tags WHERE ID_TAG = " . $ID_TAG, __FILE__, __LINE__);
    
    //Redirect to the admin section - not any more
    //redirectexit('action=tags;sa=admin');
}

function SuggestTopic()
{
    global $context, $txt, $mbname, $db_prefix, $ID_MEMBER;
    // Check permission
    // Get the Topic (id)
    $topic = (int) $_REQUEST['topic'];

    if (empty($topic))
        fatal_error($txt['smftags_err_notopic'],false);

    // Check permission
    $a_suggest = allowedTo('smftags_suggest_any');

    if (!$a_suggest && allowedTo('smftags_suggest_own')) {
        $dbresult = db_query("
            SELECT m.ID_MEMBER
            FROM {$db_prefix}topics as t, {$db_prefix}messages as m
            WHERE t.ID_FIRST_MSG = m.ID_MSG AND t.ID_TOPIC = $topic
            LIMIT 1
        ", __FILE__, __LINE__);

        $row = mysql_fetch_assoc($dbresult);
        mysql_free_result($dbresult);

        if ($ID_MEMBER == $row['ID_MEMBER'])
            $a_suggest = true;
    }

    if (!$a_suggest)
        fatal_error($txt['cannot_smftags_suggest'],false);

    $context['tags_topic'] = $topic;

    filltags($topic);

    // never allow editing of existing topic tags
    $context['smftags_flag'] = 0;
    $context['smftags_act'] = 'suggest';

    // Load the subtemplate
    $context['sub_template']  = 'edittopic';
    $context['page_title'] = $mbname . ' - ' . $txt['smftags_suggest'];
}

function SuggestTopic2()
{
    global $db_prefix, $txt, $ID_MEMBER;
    $topic = (int) $_REQUEST['topic'];

    if (empty($topic))
        fatal_error($txt['smftags_err_notopic'],false);

    // Check permission
    $a_suggest = allowedTo('smftags_suggest_any');

    if (!$a_suggest && allowedTo('smftags_suggest_own')) {
        $dbresult = db_query("
            SELECT m.ID_MEMBER
            FROM {$db_prefix}topics as t, {$db_prefix}messages as m
            WHERE t.ID_FIRST_MSG = m.ID_MSG AND t.ID_TOPIC = $topic
            LIMIT 1
        ", __FILE__, __LINE__);

        $row = mysql_fetch_assoc($dbresult);
        mysql_free_result($dbresult);

        if ($ID_MEMBER == $row['ID_MEMBER'])
            $a_suggest = true;
    }

    if (!$a_suggest)
        fatal_error($txt['cannot_smftags_suggest'],false);

    foreach ($_REQUEST as $key => $value) {
        if (substr($key,0,3) == "tag" && is_numeric(substr($key,3)) && !isset($old[substr($key,3)])) {
            $k = (int) substr($key,3);
            $new[substr($key,3)] = "($k,$topic,$ID_MEMBER,0)";
        }
    }

    $q = db_query("
        INSERT {$db_prefix}tags_log (`ID_TAG`,`ID_TOPIC`,`ID_MEMBER`,`approved`)
        VALUES " . implode(',', $new),
        __FILE__, __LINE__);

    //Redirect back to the topic
    redirectexit('topic=' . $topic);
}

?>
