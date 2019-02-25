<?php
/*
 * Plugin Name: Who Repped My Post? for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * the forum-side routines
 */

// add only the necessary hooks, cache templates
wrmp_initialize();

/**
 * wrmp_postbit()
 *
 * display post reps info where applicable
 *
 * @param - &$post - (array) the current post info
 * @return: void
 */
function wrmp_postbit(&$post)
{
	global $db, $mybb, $templates, $lang;
	static $wrmp;

	if(!$lang->wrmp)
	{
		$lang->load('wrmp');
	}

	if(!isset($wrmp))
	{
		wrmp_build_cache($wrmp);
	}

	$pid = (int) $post['pid'];

	if(!isset($wrmp[$pid]))
	{
		return;
	}

	switch($mybb->settings['wrmp_position']) {
	case 'postbit':
		if($mybb->settings['postlayout'] == "classic")
		{
			eval("\$post['wrmp_postbit'] = \"" . $templates->get('wrmp_postbit_classic') . "\";");
		}
		else
		{
			eval("\$post['wrmp_postbit'] = \"" . $templates->get('wrmp_postbit') . "\";");
		}
		break;
	case 'post':
		eval("\$post['message'] .= \"" . $templates->get('wrmp_post') . "\";");
		break;
	default:
		eval("\$post['wrmp_below'] = \"" . $templates->get('wrmp_below') . "\";");
		break;
	}
}

/**
 * wrmp_build_cache()
 *
 * build the rep info for all the posts on this page
 *
 * @param - &$wrmp - (array) a reference to the post rep cache
 * @return - void
 */
function wrmp_build_cache(&$wrmp)
{
	global $db, $pids, $mybb, $templates, $lang, $theme;

	$wrmp = array();

	// build the WHERE clause
	$where = "p.{$pids}";
	if($mybb->input['mode'] == 'threaded')
	{
		$where = "p.pid={$mybb->input['pid']}";
	}

	$query_string = <<<EOF
SELECT
	r.pid, r.comments, r.reputation AS repvalue,
	u.uid, u.username, u.usergroup, u.displaygroup
FROM
	{$db->table_prefix}reputation r
LEFT JOIN
	{$db->table_prefix}users u ON (r.adduid=u.uid)
LEFT JOIN
	{$db->table_prefix}posts p ON (p.pid=r.pid)
WHERE
	{$where}
ORDER BY
	r.dateline DESC;
EOF;
	$query = $db->query($query_string);
	if($db->num_rows($query) == 0)
	{
		return;
	}

	$all_reps = array();
	while($user = $db->fetch_array($query))
	{
		$all_reps[$user['pid']][$user['uid']] = $user;
	}

	foreach($all_reps as $pid => $users)
	{
		$other_reps = '';
		$too_many_names = $too_many = $rep_count = $reppers = array();

		$did_something = false;
		foreach($users as $uid => $user)
		{
			switch(true) {
			case $user['repvalue'] < 0:
				$rep_value = 'negative';
				break;
			case $user['repvalue'] == 0:
				$rep_value = 'neutral';
				break;
			case $user['repvalue'] > 0:
				$rep_value = 'positive';
				$user['repvalue'] = '+' . $user['repvalue'];
				break;
			}

			// if admin isn't showing this rep type, skip it
			$setting_name = "wrmp_show_{$rep_value}";
			if(!$mybb->settings[$setting_name])
			{
				continue;
			}

			// if we have reached the max for this type of rep . . .
			++$rep_count[$rep_value];
			if($rep_count[$rep_value] > $mybb->settings['wrmp_max_' . $rep_value])
			{
				// add it to our overage list and skip
				$too_many_names[$rep_value][] = $user['username'];
				$too_many[$rep_value] = true;
				continue;
			}

			$comments = '';
			if($user['comments'])
			{
				$comments = ': ' . nl2br(htmlspecialchars_uni($user['comments']));
			}

			// build the name link
			$user_name = format_name($user['username'], $user['usergroup'], $user['displaygroup']);

			$user_name = str_replace('style="', 'style="background-image: none; padding-left: 0px; ', $user_name);

			$user_link = get_profile_link($user['uid']);
			eval("\$reppers[\$rep_value][] = \"" . $templates->get('wrmp_user_link', 1, 0) . "\";");
			$did_something = true;
		}

		$sep = '';
		foreach($reppers as $rep_value => $repper)
		{
			/*
			 * if there are more reps than admin has allowed to show
			 * display the overage in the title text
			 */
			if($too_many[$rep_value])
			{
				$other = $lang->wrmp_other;
				$over_count = $rep_count[$rep_value] - $mybb->settings['wrmp_max_' . $rep_value];
				if($over_count > 1)
				{
					$other = $lang->wrmp_others;
				}

				// pop the last name off the stack
				$last_repper = array_pop($too_many_names[$rep_value]);

				// comma-separate the rest (if any)
				$others_list = implode("{$lang->comma}", $too_many_names[$rep_value]);

				// if there were others, use 'and'
				if($others_list)
				{
					$others_list .= " {$lang->and} {$last_repper}";
				}
				else
				{
					// if not, just list the single name
					$others_list = $last_repper;
				}
				eval("\$other_reps = \"" . $templates->get('wrmp_other_reps') . "\";");
			}

			// pop the last name off the stack
			$last_repper = array_pop($repper);

			// comma-separate the rest (if any)
			$who_repped_me = implode("{$lang->comma}", (array) $repper);

			// if there were other names . . .
			if($who_repped_me)
			{
				// if there was an overage . . .
				if($other_reps)
				{
					// use comma sep (and is coming from overage text)
					$who_repped_me .= "{$lang->comma} {$last_repper}";
				}
				else
				{
					// use and
					$who_repped_me .= " {$lang->and} {$last_repper}";
				}
			}
			else
			{
				// just list the single name
				$who_repped_me = $last_repper;
			}

			eval("\$wrmp[\$pid] .= \"" . $templates->get("wrmp_reps_{$rep_value}") . "\";");

			// below post doesn't need line breaks
			if($mybb->settings['wrmp_position'] != 'below')
			{
				$sep = '<br />';
			}
		}
	}
}

/**
 * wrmp_initialize()
 *
 * hook into the postbit and cache templates if applicable
 *
 * @return: void
 */
function wrmp_initialize()
{
	global $mybb;

	/*
	 * only hook if we are in showthread and at least one rep power
	 * is enabled for display
	 */
	if(THIS_SCRIPT != 'showthread.php' ||
	   (!$mybb->settings['wrmp_show_negative'] &&
	    !$mybb->settings['wrmp_show_neutral'] &&
	    !$mybb->settings['wrmp_show_positive']) ||
	   (!$mybb->settings['wrmp_max_negative'] &&
	    !$mybb->settings['wrmp_max_neutral'] &&
		!$mybb->settings['wrmp_show_positive']))
	{
		return;
	}

	global $templatelist, $plugins;
	$templatelist .= ',wrmp_reps_negative,wrmp_reps_neutral,wrmp_reps_positive,wrmp_postbit,wrmp_post,wrmp_below';
	$plugins->add_hook('postbit', 'wrmp_postbit');
	$plugins->add_hook('global_intermediate', 'wrmp_add_js');
}

/**
 * add JS to override standard post-rep behavior
 *
 * @return: void
 */
function wrmp_add_js()
{
	global $mybb, $wrmpJs;

	$wrmpJs = <<<EOF
<script type="text/javascript" src="{$mybb->asset_url}/jscripts/wrmp/reputation.js"></script>
EOF;
}

?>
