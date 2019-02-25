<?php
/*
 * Plugin Name: Who Repped My Post? for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * the forum-side routines
 */

// add only the necessary hooks, cache templates
wrmpInitialize();

/**
 * display post reps info where applicable
 *
 * @param  array post info
 * @return void
 */
function wrmpPostbit(&$post)
{
	global $db, $mybb, $templates, $lang;
	static $wrmp;

	if (!$lang->wrmp) {
		$lang->load('wrmp');
	}

	if (!isset($wrmp)) {
		wrmpBuildCache($wrmp);
	}

	$pid = (int) $post['pid'];

	if (!isset($wrmp[$pid])) {
		return;
	}

	switch ($mybb->settings['wrmp_position']) {
	case 'postbit':
		if ($mybb->settings['postlayout'] == 'classic') {
			eval("\$post['wrmp_postbit'] = \"{$templates->get('wrmp_postbit_classic')}\";");
		} else {
			eval("\$post['wrmp_postbit'] = \"{$templates->get('wrmp_postbit')}\";");
		}
		break;
	case 'post':
		eval("\$post['message'] .= \"{$templates->get('wrmp_post')}\";");
		break;
	default:
		eval("\$post['wrmp_below'] = \"{$templates->get('wrmp_below')}\";");
		break;
	}
}

/**
 * build the rep info for all the posts on this page
 *
 * @param  array a reference to the post rep cache
 * @return void
 */
function wrmpBuildCache(&$wrmp)
{
	global $db, $pids, $mybb, $templates, $lang, $theme;

	$wrmp = array();

	// build the WHERE clause
	$where = "p.{$pids}";
	if ($mybb->input['mode'] == 'threaded') {
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
	if ($db->num_rows($query) == 0) {
		return;
	}

	$allReps = array();
	while ($user = $db->fetch_array($query)) {
		$allReps[$user['pid']][$user['uid']] = $user;
	}

	foreach ($allReps as $pid => $users) {
		$otherReps = '';
		$tooManyNames = $tooMany = $repCount = $reppers = array();

		$didSomething = false;
		foreach ($users as $uid => $user) {
			switch (true) {
			case $user['repvalue'] < 0:
				$repValue = 'negative';
				break;
			case $user['repvalue'] == 0:
				$repValue = 'neutral';
				break;
			case $user['repvalue'] > 0:
				$repValue = 'positive';
				$user['repvalue'] = '+'.$user['repvalue'];
				break;
			}

			// if admin isn't showing this rep type, skip it
			if (!$mybb->settings["wrmp_show_{$repValue}"]) {
				continue;
			}

			// if we have reached the max for this type of rep...
			++$repCount[$repValue];
			if ($repCount[$repValue] > $mybb->settings['wrmp_max_'.$repValue]) {
				// add it to our overage list and skip
				$tooManyNames[$repValue][] = $user['username'];
				$tooMany[$repValue] = true;
				continue;
			}

			$comments = '';
			if ($user['comments']) {
				$comments = ': '.nl2br(htmlspecialchars_uni($user['comments']));
			}

			// build the name link
			$userName = format_name($user['username'], $user['usergroup'], $user['displaygroup']);

			$userName = str_replace('style="', 'style="background-image: none; padding-left: 0px; ', $userName);

			$userLink = get_profile_link($user['uid']);
			eval("\$reppers[\$repValue][] = \"{$templates->get('wrmp_user_link', 1, 0)}\";");
			$didSomething = true;
		}

		$sep = '';
		foreach ($reppers as $repValue => $repper) {
			/*
			 * if there are more reps than admin has allowed to show
			 * display the overage in the title text
			 */
			if ($tooMany[$repValue]) {
				$other = $lang->wrmp_other;
				$overCount = $repCount[$repValue] - $mybb->settings['wrmp_max_'.$repValue];
				if ($overCount > 1) {
					$other = $lang->wrmp_others;
				}

				// pop the last name off the stack
				$lastRepper = array_pop($tooManyNames[$repValue]);

				// comma-separate the rest (if any)
				$othersList = implode($lang->comma, $tooManyNames[$repValue]);

				// if there were others, use 'and'
				if ($othersList) {
					$othersList .= " {$lang->and} {$lastRepper}";
				} else {
					// if not, just list the single name
					$othersList = $lastRepper;
				}
				eval("\$otherReps = \"{$templates->get('wrmp_other_reps')}\";");
			}

			// pop the last name off the stack
			$lastRepper = array_pop($repper);

			// comma-separate the rest (if any)
			$whoReppedMe = implode($lang->comma, (array) $repper);

			// if there were other names...
			if ($whoReppedMe) {
				// if there was an overage...
				if ($otherReps) {
					// use comma sep (and is coming from overage text)
					$whoReppedMe .= "{$lang->comma} {$lastRepper}";
				} else {
					// use and
					$whoReppedMe .= " {$lang->and} {$lastRepper}";
				}
			} else {
				// just list the single name
				$whoReppedMe = $lastRepper;
			}

			eval("\$wrmp[\$pid] .= \"{$templates->get("wrmp_reps_{$repValue}")}\";");

			// below post doesn't need line breaks
			if ($mybb->settings['wrmp_position'] != 'below') {
				$sep = '<br />';
			}
		}
	}
}

/**
 * hook into the postbit and cache templates if applicable
 *
 * @return void
 */
function wrmpInitialize()
{
	global $mybb;

	/*
	 * only hook if we are in showthread and at least one rep power
	 * is enabled for display
	 */
	if (THIS_SCRIPT != 'showthread.php' ||
	   (!$mybb->settings['wrmp_show_negative'] &&
	    !$mybb->settings['wrmp_show_neutral'] &&
	    !$mybb->settings['wrmp_show_positive']) ||
	   (!$mybb->settings['wrmp_max_negative'] &&
	    !$mybb->settings['wrmp_max_neutral'] &&
		!$mybb->settings['wrmp_show_positive'])) {
		return;
	}

	global $templatelist, $plugins;
	$templatelist .= ',wrmp_reps_negative,wrmp_reps_neutral,wrmp_reps_positive,wrmp_postbit,wrmp_post,wrmp_below';
	$plugins->add_hook('postbit', 'wrmpPostbit');
	$plugins->add_hook('global_intermediate', 'wrmpAddJs');
}

/**
 * add JS to override standard post-rep behavior
 *
 * @return: void
 */
function wrmpAddJs()
{
	global $mybb, $wrmpJs;

	$wrmpJs = <<<EOF
<script type="text/javascript" src="{$mybb->asset_url}/jscripts/wrmp/reputation.js"></script>
EOF;
}

?>
