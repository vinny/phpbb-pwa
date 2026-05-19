<?php
/**
 *
 * PWA Enhancer. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Vinny <https://github.com/vinny>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\pwa\acp;

class pwa_info
{
	public function module()
	{
		return [
			'filename'	=> '\vinny\pwa\acp\pwa_module',
			'title'		=> 'ACP_PWA',
			'modes'		=> [
				'settings' => [
					'title'	=> 'ACP_PWA_SETTINGS',
					'auth'	=> 'ext_vinny/pwa && acl_a_board',
					'cat'	=> ['ACP_PWA'],
				],
			],
		];
	}
}
