<?php
/**
 *
 * PWA Enhancer. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Vinny <https://github.com/vinny>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\pwa\controller;

use phpbb\controller\helper;
use phpbb\language\language;
use phpbb\template\template;
use Symfony\Component\HttpFoundation\Response;

class offline
{
	/** @var helper */
	protected $helper;

	/** @var language */
	protected $language;

	/** @var template */
	protected $template;

	public function __construct(
		helper $helper,
		language $language,
		template $template
	) {
		$this->helper = $helper;
		$this->language = $language;
		$this->template = $template;
	}

	/**
	 * @return Response
	 */
	public function display()
	{
		$this->language->add_lang('pwa', 'vinny/pwa');
		$this->template->assign_var('U_PWA_BOARD', generate_board_url());

		return $this->helper->render('@vinny_pwa/pwa_offline.html', $this->language->lang('PWA_OFFLINE_TITLE'));
	}
}
