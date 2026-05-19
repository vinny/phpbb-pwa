<?php
/**
 *
 * PWA Enhancer. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Vinny <https://github.com/vinny>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\pwa;

use phpbb\filesystem\exception\filesystem_exception;

class ext extends \phpbb\extension\base
{
	public const PHP_MIN_VERSION = '8.2';

	/**
	 * @var array An array of installation error messages
	 */
	protected $errors = [];

	/**
	 * {@inheritdoc}
	 */
	public function is_enableable()
	{
		return $this->check_php_version()
			->result();
	}

	protected function check_php_version()
	{
		if (phpbb_version_compare(PHP_VERSION_ID, '80200', '<'))
		{
			$this->errors[] = 'PWA_PHP_VERSION_ERROR';
		}

		return $this;
	}

	protected function result()
	{
		if (empty($this->errors))
		{
			return true;
		}

		$language = $this->container->get('language');
		$language->add_lang('pwa', 'vinny/pwa');

		return array_map(static function ($error) use ($language) {
			return call_user_func_array([$language, 'lang'], (array) $error);
		}, $this->errors);
	}

	public function enable_step($old_state)
	{
		if ($old_state === false)
		{
			$filesystem = $this->container->get('filesystem');
			$icon_dir = $this->container->getParameter('core.root_path') . 'images/pwa_icons';

			try
			{
				if (!$filesystem->exists($icon_dir))
				{
					$filesystem->mkdir($icon_dir, 0755);
					$filesystem->touch($icon_dir . '/index.htm');
				}
			}
			catch (filesystem_exception $e)
			{
				$user = $this->container->get('user');
				$this->container->get('language')->add_lang('info_acp_pwa', 'vinny/pwa');
				$this->container->get('log')->add('critical', $user->data['user_id'], $user->ip, 'LOG_PWA_ICON_DIR_CREATE_FAIL', false, [$e->getMessage()]);
			}

			return 'pwa_icons_dir';
		}

		return parent::enable_step($old_state);
	}

	public function purge_step($old_state)
	{
		if ($old_state === false)
		{
			$filesystem = $this->container->get('filesystem');
			$icon_dir = $this->container->getParameter('core.root_path') . 'images/pwa_icons';

			try
			{
				if ($filesystem->exists($icon_dir))
				{
					$filesystem->remove($icon_dir);
				}
			}
			catch (filesystem_exception $e)
			{
				$user = $this->container->get('user');
				$this->container->get('language')->add_lang('info_acp_pwa', 'vinny/pwa');
				$this->container->get('log')->add('critical', $user->data['user_id'], $user->ip, 'LOG_PWA_ICON_DIR_REMOVE_FAIL', false, [$e->getMessage()]);
			}

			return 'pwa_icons_dir';
		}

		return parent::purge_step($old_state);
	}
}
