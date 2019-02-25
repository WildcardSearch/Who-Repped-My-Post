<?php
/*
 * Plugin Name: Who Repped My Post? for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * the installation routines
 */

/**
 * plugin info
 *
 * @return array
 */
function wrmp_info()
{
	global $mybb, $lang, $cp_style;

	if (!$lang->wrmp) {
		$lang->load('wrmp');
	}

	$extraLinks = '<br />';
	$settingsLink = wrmpBuildSettingsLink();
	if ($settingsLink) {
		$extraLinks = <<<EOF
<ul>
	<li style="list-style-image: url(styles/{$cp_style}/images/wrmp/settings.gif)">
		{$settingsLink}
	</li>
</ul>
EOF;

		$wrmpDescription = <<<EOF
<table width="100%">
	<tbody>
		<tr>
			<td>
				{$lang->wrmp_description}{$extraLinks}
			</td>
			<td style="text-align: center;">
				<a href="https://paypal.me/wildcardsearch"><img src="styles/{$cp_style}/images/wrmp/donate.gif" style="outline: none; border: none;" /></a>
			</td>
		</tr>
	</tbody>
</table>
EOF;
	} else {
		$wrmpDescription = $lang->wrmp_description;
	}

	$name = <<<EOF
<span style="font-familiy: arial; font-size: 1.5em; color: #AD5F32; text-shadow: 2px 2px 2px #664B39;">{$lang->wrmp}</span>
EOF;
	$author = <<<EOF
</a></small></i><a href="http://www.rantcentralforums.com" title="Rant Central"><span style="font-family: Courier New; font-weight: bold; font-size: 1.2em; color: #0e7109;">Wildcard</span></a><i><small><a>
EOF;

	// this array returns information about the plugin, some of which was prefabricated above based on whether the plugin has been installed or not.
	return array(
		"name" => $name,
		"description" => $wrmpDescription,
		"website" => 'https://github.com/WildcardSearch/Who-Repped-My-Post',
		"author" => $author,
		"authorsite" => 'http://www.rantcentralforums.com',
		"version" => WRMP_VERSION,
		"compatibility" => '18*',
		'codename' => 'wrmp',
	);
}

/**
 * check to see if the plugin's settings group is installed-- assume the plugin is installed if so
 *
 * @return bool
 */
function wrmp_is_installed()
{
	return wrmpGetSettingsGroup();
}

/**
 * install settings
 *
 * @return void
 */
function wrmp_install()
{
	global $lang;

	if (!$lang->wrmp) {
		$lang->load('wrmp');
	}

	WrmpInstaller::getInstance()->install();
}

/**
 * templates/version control
 *
 * @return void
 */
function wrmp_activate()
{
	require_once MYBB_ROOT.'/inc/adminfunctions_templates.php';
	find_replace_templatesets('postbit', "#".preg_quote('<td class="smalltext post_author_info"')."#i", '{$post[\'wrmp_postbit\']}<td class="smalltext post_author_info"');
	find_replace_templatesets('postbit_classic', "#".preg_quote('{$post[\'user_details\']}')."#i", '{$post[\'user_details\']}{$post[\'wrmp_postbit\']}');

	find_replace_templatesets('postbit', "#".preg_quote('{$post[\'button_rep\']}')."#i", '{$post[\'button_rep\']}{$post[\'wrmp_below\']}');

	find_replace_templatesets('footer', '#^(.*?)$#s', '$1{$wrmpJs}');

	// if we just upgraded...
	$oldVersion = wrmpGetCacheVersion();

	if (version_compare($oldVersion, WRMP_VERSION, '<')) {
		wrmp_install();
	}

	wrmpSetCacheVersion();
}

/**
 * edit templates
 *
 * @return void
 */
function wrmp_deactivate()
{
	require_once MYBB_ROOT.'/inc/adminfunctions_templates.php';
	find_replace_templatesets('postbit', "#".preg_quote('{$post[\'wrmp_postbit\']}')."#i", '');
	find_replace_templatesets('postbit_classic', "#".preg_quote('{$post[\'wrmp_postbit\']}')."#i", '');
	find_replace_templatesets('postbit', "#".preg_quote('{$post[\'wrmp_below\']}')."#i", '');

	find_replace_templatesets('footer', "#".preg_quote('{$wrmpJs}')."#i", '');
}

/**
 * remove all changes
 *
 * @return: n/a
 */
function wrmp_uninstall()
{
	WrmpInstaller::getInstance()->uninstall();

	// delete our cached version
	wrmpClearCacheVersion();
}

/*
 * settings
 */

/**
 * retrieves the plugin's settings group gid if it exists
 * attempts to cache repeat calls
 *
 * @return int
 */
function wrmpGetSettingsGroup()
{
	static $gid;

	// if we have already stored the value
	if (!isset($gid)) {
		global $db;

		// otherwise we will have to query the db
		$query = $db->simple_select('settinggroups', 'gid', "name='wrmp_settings'");
		$gid = (int) $db->fetch_field($query, 'gid');
	}
	return $gid;
}

/**
 * builds the url to modify plugin settings if given valid info
 *
 * @param  int
 * @return string
 */
function wrmpBuildSettingsUrl($gid)
{
	if (!$gid) {
		return;
	}

	return 'index.php?module=config-settings&amp;action=change&amp;gid='.$gid;
}

/**
 * builds a link to modify plugin settings if it exists
 *
 * @retur string
 */
function wrmpBuildSettingsLink()
{
	global $lang;

	if (!$lang->wrmp) {
		$lang->load('wrmp');
	}

	$gid = wrmpGetSettingsGroup();

	// does the group exist?
	if (!$gid) {
		return false;
	}

	// if so build the URL
	$url = wrmpBuildSettingsUrl($gid);

	// did we get a URL?
	if (!$url) {
		return false;
	}

		// if so build the link
	return <<<EOF
<a href="{$url}" title="{$lang->wrmp_plugin_settings}">{$lang->wrmp_plugin_settings}</a>
EOF;
}

/**
 * check cached version info
 *
 * @return string|int
 */
function wrmpGetCacheVersion()
{
	global $cache;

	// get currently installed version, if there is one
	$wrmp = $cache->read('wrmp');

	if ($wrmp['version']) {
        return $wrmp['version'];
	}

    return 0;
}

/**
 * set cached version info
 *
 * @return bool
 */
function wrmpSetCacheVersion()
{
	global $cache;

	// update version cache to latest
	$wrmp = $cache->read('wrmp');
	$wrmp['version'] = WRMP_VERSION;
	$cache->update('wrmp', $wrmp);
    return true;
}

/**
 * remove cached version info
 * derived from the work of pavemen in MyBB Publisher
 *
 * @return bool
 */
function wrmpClearCacheVersion()
{
	global $cache;

	$cache->update('wrmp', null);
    return true;
}

?>
