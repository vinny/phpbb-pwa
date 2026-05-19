<?php
/**
 *
 * PWA Enhancer. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Vinny <https://github.com/vinny>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\pwa\migrations;

class v100_initial extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['pwa_enabled']);
	}

	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v330\v330'];
	}

	public function update_data()
	{
		return [
			['module.remove', [
				'acp',
				'ACP_PWA',
				[
					'module_basename'	=> '\vinny\pwa\acp\pwa_module',
					'modes'				=> ['settings'],
				],
			]],
			['module.remove', [
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_PWA',
			]],
			['config.add', ['pwa_enabled', '1']],
			['config.add', ['pwa_mobile_style_id', '0']],
			['config.add', ['pwa_force_on_mobile', '0']],
			['config.add', ['pwa_force_on_pwa', '1']],
			['config.add', ['pwa_theme_colour', '#000000']],
			['config.add', ['pwa_background_colour', '#ffffff']],
			['config.add', ['pwa_show_install_banner', '1']],
			['config.add', ['pwa_cache_version', '1']],
			['config.add', ['pwa_icon_512', '']],
			['module.add', [
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_PWA',
			]],
			['module.add', [
				'acp',
				'ACP_PWA',
				[
					'module_basename'	=> '\vinny\pwa\acp\pwa_module',
					'modes'				=> ['settings'],
				],
			]],
		];
	}

	public function revert_data()
	{
		return [
			['module.remove', [
				'acp',
				'ACP_PWA',
				[
					'module_basename'	=> '\vinny\pwa\acp\pwa_module',
					'modes'				=> ['settings'],
				],
			]],
			['module.remove', [
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_PWA',
			]],
			['config.remove', ['pwa_enabled']],
			['config.remove', ['pwa_mobile_style_id']],
			['config.remove', ['pwa_force_on_mobile']],
			['config.remove', ['pwa_force_on_pwa']],
			['config.remove', ['pwa_theme_colour']],
			['config.remove', ['pwa_background_colour']],
			['config.remove', ['pwa_show_install_banner']],
			['config.remove', ['pwa_cache_version']],
			['config.remove', ['pwa_icon_512']],
		];
	}
}
