<?php
/**
 *
 * @package vinny/pwa
 * @copyright (c) 2026 Vinny
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace vinny\pwa\acp;

use phpbb\filesystem\exception\filesystem_exception;
use phpbb\filesystem\filesystem_interface;

class pwa_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	public function main($id, $mode)
	{
		global $user, $template, $request, $db, $config;

		$user->add_lang_ext('vinny/pwa', 'info_acp_pwa');
		$user->add_lang_ext('vinny/pwa', 'pwa');

		$this->tpl_name = 'acp_pwa';
		$this->page_title = $user->lang('ACP_PWA_SETTINGS');

		add_form_key('vinny_pwa');

		$error = [];

		if ($request->is_set_post('purge_cache'))
		{
			if (!check_form_key('vinny_pwa'))
			{
				$error[] = $user->lang('FORM_INVALID');
			}

			if (!count($error))
			{
				$current_version = (int) $config['pwa_cache_version'];
				$config->set('pwa_cache_version', $current_version + 1);
				trigger_error($user->lang('PWA_CACHE_PURGED') . adm_back_link($this->u_action));
			}
		}
		else if ($request->is_set_post('submit'))
		{
			if (!check_form_key('vinny_pwa'))
			{
				$error[] = $user->lang('FORM_INVALID');
			}

			if (!count($error))
			{
				$config->set('pwa_enabled', $request->variable('pwa_enabled', 0));
				$config->set('pwa_mobile_style_id', $request->variable('pwa_mobile_style_id', 0));
				$config->set('pwa_force_on_mobile', $request->variable('pwa_force_on_mobile', 0));
				$config->set('pwa_force_on_pwa', $request->variable('pwa_force_on_pwa', 0));
				$config->set('pwa_theme_colour', $this->normalise_colour($request->variable('pwa_theme_colour', '#000000'), '#000000'));
				$config->set('pwa_background_colour', $this->normalise_colour($request->variable('pwa_background_colour', '#ffffff'), '#ffffff'));
				$config->set('pwa_show_install_banner', $request->variable('pwa_show_install_banner', 1));

				$this->handle_icon_upload('pwa_icon_512', 512, $error);

				if (!count($error))
				{
					trigger_error($user->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
				}
			}
		}

		$sql = 'SELECT style_id, style_name
			FROM ' . STYLES_TABLE . '
			WHERE style_active = 1
			ORDER BY style_name ASC';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('pwa_styles', [
				'STYLE_ID'		=> (int) $row['style_id'],
				'STYLE_NAME'	=> $row['style_name'],
				'S_SELECTED'	=> (int) $config['pwa_mobile_style_id'] === (int) $row['style_id'],
			]);
		}
		$db->sql_freeresult($result);

		$template->assign_vars([
			'PWA_ENABLED'				=> $config['pwa_enabled'],
			'PWA_FORCE_ON_MOBILE'		=> $config['pwa_force_on_mobile'],
			'PWA_FORCE_ON_PWA'			=> $config['pwa_force_on_pwa'],
			'PWA_THEME_COLOUR'			=> $config['pwa_theme_colour'],
			'PWA_BACKGROUND_COLOUR'		=> $config['pwa_background_colour'],
			'PWA_SHOW_INSTALL_BANNER'	=> $config['pwa_show_install_banner'],
			'PWA_ICON_512'				=> $config['pwa_icon_512'] ? generate_board_url() . '/images/pwa_icons/' . $config['pwa_icon_512'] : '',
			'U_ACTION'					=> $this->u_action,
		]);

		if (count($error))
		{
			$template->assign_vars([
				'S_ERROR'	=> true,
				'ERROR_MSG'	=> implode('<br>', $error),
			]);
		}
	}

	private function handle_icon_upload($input_name, $required_size, &$error)
	{
		global $request, $user, $phpbb_root_path, $config, $phpbb_container;

		$files = $request->get_super_global(\phpbb\request\request_interface::FILES);
		$file = isset($files[$input_name]) ? $files[$input_name] : null;

		if (!$file || empty($file['name']) || $file['error'] === UPLOAD_ERR_NO_FILE)
		{
			return;
		}

		if ($file['error'] !== UPLOAD_ERR_OK)
		{
			$error[] = $user->lang('PWA_UPLOAD_ERROR') . ' (Code: ' . $file['error'] . ')';
			return;
		}

		$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
		$allowed_exts = ['png'];
		$allowed_mimes = ['image/png'];

		$mime = '';
		$finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : false;
		if ($finfo)
		{
			$mime = (string) finfo_file($finfo, $file['tmp_name']);
			finfo_close($finfo);
		}

		if (!in_array($ext, $allowed_exts, true) || (!empty($mime) && !in_array($mime, $allowed_mimes, true)))
		{
			$error[] = $user->lang('PWA_INVALID_FILE_TYPE');
			return;
		}

		set_error_handler(static function () {
			return true;
		});

		try
		{
			$img_size = getimagesize($file['tmp_name']);
		}
		finally
		{
			restore_error_handler();
		}

		if ($img_size === false || $img_size[0] !== $required_size || $img_size[1] !== $required_size)
		{
			$error[] = sprintf($user->lang('PWA_INVALID_IMAGE_DIMENSIONS'), $required_size, $required_size);
			return;
		}

		$filename = 'pwa_icon.' . $ext;
		$destination_dir = $phpbb_root_path . 'images/pwa_icons/';
		$destination = $destination_dir . $filename;
		$filesystem = $phpbb_container->get('filesystem');

		try
		{
			if (!$filesystem->exists($destination_dir))
			{
				$filesystem->mkdir($destination_dir, 0755);
				$filesystem->touch($destination_dir . 'index.htm');
			}
		}
		catch (filesystem_exception $e)
		{
			$error[] = $user->lang('PWA_UPLOAD_ERROR');
			return;
		}

		if (!move_uploaded_file($file['tmp_name'], $destination))
		{
			$error[] = $user->lang('PWA_UPLOAD_ERROR');
			return;
		}

		try
		{
			$filesystem->phpbb_chmod($destination, filesystem_interface::CHMOD_READ | filesystem_interface::CHMOD_WRITE);
		}
		catch (filesystem_exception $e)
		{
			$error[] = $user->lang('PWA_UPLOAD_ERROR');
			return;
		}

		$config->set($input_name, $filename);
	}

	private function normalise_colour($colour, $default)
	{
		$colour = strtolower(trim((string) $colour));

		return preg_match('/^#[a-f0-9]{6}$/', $colour) ? $colour : $default;
	}
}
