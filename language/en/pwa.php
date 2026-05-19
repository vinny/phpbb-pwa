<?php
/**
 *
 * PWA Enhancer. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Vinny <https://github.com/vinny>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

$lang = array_merge($lang, [
	'PWA_PHP_VERSION_ERROR'           => 'PWA Enhancer requires PHP 8.2 or newer.',
	'PWA_OFFLINE_TITLE'               => 'You are offline',
	'PWA_OFFLINE_MESSAGE'             => 'It seems you have lost your internet connection.',
	'PWA_OFFLINE_FALLBACK'            => 'You are offline.',
	'PWA_OFFLINE_RETRY'               => 'Try Again',
	'PWA_INSTALL_BUTTON'              => 'Install App',
	'PWA_INSTALL_DISMISS'             => 'Close',
	'PWA_INSTALL_SUBTITLE'            => 'Add to Home Screen',
]);
