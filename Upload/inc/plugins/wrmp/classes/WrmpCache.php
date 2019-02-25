<?php
/*
 * Plugin Name: Who Repped My Post? for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * wrapper to handle our plugin's cache
 */

class WrmpCache extends WildcardPluginCache010300
{
	/**
	 * @var  string cache key
	 */
	protected $cacheKey = 'wrmp';

	/**
	 * @var  string cache sub key
	 */
	protected $subKey = '';

	/**
	 * return an instance of the cache wrapper
	 *
	 * @return instance of the child class
	 */
	static public function getInstance()
	{
		static $instance;
		if (!isset($instance)) {
			$instance = new WrmpCache;
		}

		return $instance;
	}
}

?>
