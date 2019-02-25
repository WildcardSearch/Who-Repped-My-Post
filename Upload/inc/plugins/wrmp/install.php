<?php
/*
 * Plugin Name: Who Repped My Post? for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * the installation routines
 */

/*
 * wrmp_info()
 *
 * Information about the plugin used by MyBB for display as well as to connect with updates
 *
 * @return: (array) the plugin info
 */
function wrmp_info()
{
	global $mybb, $lang, $cp_style;

	if(!$lang->wrmp)
	{
		$lang->load('wrmp');
	}

	$extra_links = '<br />';
	$settings_link = wrmp_build_settings_link();
	if($settings_link)
	{
		$extra_links = <<<EOF
<ul>
	<li style="list-style-image: url(styles/{$cp_style}/images/wrmp/settings.gif)">
		{$settings_link}
	</li>
</ul>
EOF;

		$button_pic = "styles/{$cp_style}/images/wrmp/donate.gif";
		$border_pic = "styles/{$cp_style}/images/wrmp/pixel.gif";
		$wrmp_description = <<<EOF
<table width="100%">
	<tbody>
		<tr>
			<td>
				{$lang->wrmp_description}{$extra_links}
			</td>
			<td style="text-align: center;">
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
					<input type="hidden" name="cmd" value="_s-xclick">
					<input type="hidden" name="hosted_button_id" value="VA5RFLBUC4XM4">
					<input type="image" src="{$button_pic}" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
					<img alt="" border="0" src="{$border_pic}" width="1" height="1">
				</form>
			</td>
		</tr>
	</tbody>
</table>
EOF;
	} else {
		$wrmp_description = $lang->wrmp_description;
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
		"description" => $wrmp_description,
		"website" => 'https://github.com/WildcardSearch/Who-Repped-My-Post',
		"author" => $author,
		"authorsite" => 'http://www.rantcentralforums.com',
		"version" => '2.0',
		"compatibility" => '18*',
		"guid" => '6a9a29dcb7af694de01c60a56d66c9b0',
	);
}

/*
 * wrmp_is_installed()
 *
 * check to see if the plugin's settings group is installed-- assume the plugin is installed if so
 *
 * @return: (bool) true if installed, false if not
 */
function wrmp_is_installed()
{
	return wrmp_get_settingsgroup();
}

/*
 * wrmp_install()
 *
 * install settings
 *
 * @return: n/a
 */
function wrmp_install()
{
	global $lang;

	if(!$lang->wrmp)
	{
		$lang->load('wrmp');
	}

	if(!class_exists('WildcardPluginInstaller'))
	{
		require_once MYBB_ROOT . 'inc/plugins/wrmp/classes/installer.php';
	}
	$installer = new WildcardPluginInstaller(MYBB_ROOT . 'inc/plugins/wrmp/install_data.php');
	$installer->install();
}

/*
 * wrmp_activate()
 *
 * handle version control (a la pavemen), upgrade if necessary and
 * change permissions for ASB
 *
 * @return: n/a
 */
function wrmp_activate()
{
	require_once MYBB_ROOT . '/inc/adminfunctions_templates.php';
	find_replace_templatesets('postbit', "#" . preg_quote('<td class="smalltext post_author_info"') . "#i", '{$post[\'wrmp_postbit\']}<td class="smalltext post_author_info"');
	find_replace_templatesets('postbit_classic', "#" . preg_quote('{$post[\'user_details\']}') . "#i", '{$post[\'user_details\']}{$post[\'wrmp_postbit\']}');

	find_replace_templatesets('postbit', "#" . preg_quote('{$post[\'button_rep\']}') . "#i", '{$post[\'button_rep\']}{$post[\'wrmp_below\']}');

	find_replace_templatesets('footer', '#^(.*?)$#s', '$1{$wrmpJs}');

	// if we just upgraded . . .
	$old_version = wrmp_get_cache_version();
	$info = wrmp_info();
	if(version_compare($old_version, $info['version'], '<'))
	{
		wrmp_install();
	}
	wrmp_set_cache_version();
}

/*
 * wrmp_deactivate()
 *
 *
 *
 * @return: n/a
 */
function wrmp_deactivate()
{
	require_once MYBB_ROOT . '/inc/adminfunctions_templates.php';
	find_replace_templatesets('postbit', "#" . preg_quote('{$post[\'wrmp_postbit\']}') . "#i", '');
	find_replace_templatesets('postbit_classic', "#" . preg_quote('{$post[\'wrmp_postbit\']}') . "#i", '');
	find_replace_templatesets('postbit', "#" . preg_quote('{$post[\'wrmp_below\']}') . "#i", '');

	find_replace_templatesets('footer', "#" . preg_quote('{$wrmpJs}') . "#i", '');

	wrmp_unset_cache_version();
}

/*
 * wrmp_uninstall()
 *
 * drop the table added to the DB and the column added to
 * the mybb_users table (show_sidebox),
 * delete the plugin settings, templates and style sheets
 *
 * @return: n/a
 */
function wrmp_uninstall()
{
	if(!class_exists('WildcardPluginInstaller'))
	{
		require_once MYBB_ROOT . 'inc/plugins/wrmp/classes/installer.php';
	}
	$installer = new WildcardPluginInstaller(MYBB_ROOT . 'inc/plugins/wrmp/install_data.php');
	$installer->uninstall();

	// delete our cached version
	wrmp_unset_cache_version();
}

/*
 * settings
 */

/*
 * wrmp_get_settingsgroup()
 *
 * retrieves the plugin's settings group gid if it exists
 * attempts to cache repeat calls
 *
 * @return: (int) the setting groups id
 */
function wrmp_get_settingsgroup()
{
	static $wrmp_settings_gid;

	// if we have already stored the value
	if(isset($wrmp_settings_gid))
	{
		// don't waste a query
		$gid = (int) $wrmp_settings_gid;
	}
	else
	{
		global $db;

		// otherwise we will have to query the db
		$query = $db->simple_select('settinggroups', 'gid', "name='wrmp_settings'");
		$gid = (int) $db->fetch_field($query, 'gid');
	}
	return $gid;
}

/*
 * wrmp_build_settings_url()
 *
 * builds the url to modify plugin settings if given valid info
 *
 * @param - $gid is an integer representing a valid settings group id
 * @return: (string) the URL to view the setting group
 */
function wrmp_build_settings_url($gid)
{
	if($gid)
	{
		return 'index.php?module=config-settings&amp;action=change&amp;gid=' . $gid;
	}
}

/*
 * wrmp_build_settings_link()
 *
 * builds a link to modify plugin settings if it exists
 *
 * @return: (string) an HTML anchor element pointing to the plugin settings
 */
function wrmp_build_settings_link()
{
	global $lang;

	if(!$lang->wrmp)
	{
		$lang->load('wrmp');
	}

	$gid = wrmp_get_settingsgroup();

	// does the group exist?
	if($gid)
	{
		// if so build the URL
		$url = wrmp_build_settings_url($gid);

		// did we get a URL?
		if($url)
		{
			// if so build the link
			return "<a href=\"{$url}\" title=\"{$lang->wrmp_plugin_settings}\">{$lang->wrmp_plugin_settings}</a>";
		}
	}
	return false;
}

/*
 * wrmp_get_cache_version()
 *
 * check cached version info
 * derived from the work of pavemen in MyBB Publisher
 *
 * @return: (int) the currently installed version
 */
function wrmp_get_cache_version()
{
	global $cache;

	// get currently installed version, if there is one
	$wrmp = $cache->read('wrmp');
	if($wrmp['version'])
	{
        return $wrmp['version'];
	}
    return 0;
}

/*
 * wrmp_set_cache_version()
 *
 * set cached version info
 * derived from the work of pavemen in MyBB Publisher
 *
 * @return: (bool) true on success
 */
function wrmp_set_cache_version()
{
	global $cache;

	// get version from this plugin file
	$wrmp_info = wrmp_info();

	// update version cache to latest
	$wrmp = $cache->read('wrmp');
	$wrmp['version'] = $wrmp_info['version'];
	$cache->update('wrmp', $wrmp);
    return true;
}

/*
 * wrmp_unset_cache_version()
 *
 * remove cached version info
 * derived from the work of pavemen in MyBB Publisher
 *
 * @return: (bool) true on success
 */
function wrmp_unset_cache_version()
{
	global $cache;

	$wrmp = $cache->read('wrmp');
	$wrmp = null;
	$cache->update('wrmp', $wrmp);
    return true;
}

?>
