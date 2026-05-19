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
	'ACP_PWA'                         => 'PWA Enhancer',
	'ACP_PWA_SETTINGS'                => 'PWA Settings',
	'ACP_PWA_SETTINGS_EXPLAIN'        => 'Here you can configure the Progressive Web App parameters and mobile detection for your board.',
	'ACP_PWA_SETTINGS_COLOURS'        => 'Colours',
	'ACP_PWA_SETTINGS_ICON'           => 'Icon',

	'PWA_ENABLED'                     => 'Enable PWA Enhancer',

	'PWA_FORCE_ON_MOBILE'             => 'Force style on mobile devices',
	'PWA_FORCE_ON_MOBILE_EXPLAIN'     => 'If enabled, the selected mobile style will be forced when a mobile device is detected.',

	'PWA_FORCE_ON_PWA'                => 'Force style on PWA mode',
	'PWA_FORCE_ON_PWA_EXPLAIN'        => 'If enabled, the selected mobile style will be forced when the board is launched as a PWA.',

	'PWA_MOBILE_STYLE'                => 'Mobile Style',
	'PWA_MOBILE_STYLE_EXPLAIN'        => 'Select the style that should be loaded when forcing is triggered.',

	'PWA_THEME_COLOUR'                => 'Theme Colour',
	'PWA_BACKGROUND_COLOUR'           => 'Background Colour',

	'PWA_ICON'                        => 'Icon',
	'PWA_ICON_EXPLAIN'                => 'Upload an app icon with exactly 512x512 pixels. Only PNG is supported.',

	'PWA_INVALID_FILE_TYPE'           => 'Invalid file type uploaded. Only PNG format is allowed.',
	'PWA_INVALID_IMAGE_DIMENSIONS'    => 'Invalid image dimensions. The uploaded image must be exactly %dx%d pixels.',
	'PWA_UPLOAD_ERROR'                => 'An error occurred while uploading the file.',
	'PWA_UPLOAD_ERROR_CODE'           => 'An error occurred while uploading the file. Upload error code: %d.',

	'PWA_SHOW_INSTALL_BANNER'         => 'Show Install Banner',
	'PWA_SHOW_INSTALL_BANNER_EXPLAIN' => 'Display a polite custom prompt inviting the user to add the PWA to their home screen on mobile browsers.',
	'PWA_PURGE_CACHE'                 => 'Purge PWA Cache',
	'PWA_PURGE_CACHE_EXPLAIN'         => 'Increment the internal cache version. This forces all user devices to download fresh assets for the App in the background.',
	'PWA_CACHE_PURGED'                => 'The PWA cache version has been successfully incremented.',

	'LOG_PWA_ICON_DIR_CREATE_FAIL'    => 'PWA Enhancer could not create the icon storage directory: %s',
	'LOG_PWA_ICON_DIR_REMOVE_FAIL'    => 'PWA Enhancer could not remove the icon storage directory: %s',

	'NONE'                            => 'None',
]);
