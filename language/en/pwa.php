<?php
/**
 *
 * @package vinny/pwa
 * @copyright (c) 2026 Vinny
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
    'PWA_OFFLINE_TITLE'               => 'You are offline',
    'PWA_OFFLINE_MESSAGE'             => 'It seems you have lost your internet connection.',
    'PWA_OFFLINE_RETRY'               => 'Try Again',
    'PWA_INSTALL_BUTTON'              => 'Install App',
    'PWA_INSTALL_DISMISS'             => 'Close',
    'PWA_INSTALL_SUBTITLE'            => 'Add to Home Screen',
]);
