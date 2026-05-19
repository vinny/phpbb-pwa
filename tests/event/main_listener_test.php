<?php
/**
 *
 * PWA Enhancer. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Vinny <https://github.com/vinny>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\pwa\tests\event;

use Detection\MobileDetect;
use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\event\data;
use phpbb\extension\manager;
use phpbb\request\request_interface;
use phpbb\template\template;
use phpbb\test_case;
use phpbb\user;
use vinny\pwa\event\main_listener;

class main_listener_test extends test_case
{
	/** @var config */
	protected $config;

	/** @var user */
	protected $user;

	/** @var request_interface */
	protected $request;

	/** @var template */
	protected $template;

	/** @var helper */
	protected $helper;

	/** @var manager */
	protected $ext_manager;

	/** @var MobileDetect */
	protected $mobile_detect;

	/** @var main_listener */
	protected $listener;

	protected function setUp(): void
	{
		parent::setUp();

		$this->config = new config([
			'pwa_enabled'			=> '1',
			'pwa_mobile_style_id'	=> '2',
			'pwa_force_on_mobile'	=> '1',
			'pwa_force_on_pwa'		=> '1',
			'cookie_name'			=> 'phpbb3_pwa',
		]);

		$this->user = $this->createMock(user::class);
		$this->user->data = ['user_style' => 1, 'session_id' => '123456789'];

		$this->request = $this->createMock(request_interface::class);
		$this->template = $this->createMock(template::class);
		$this->helper = $this->createMock(helper::class);
		$this->ext_manager = $this->createMock(manager::class);
		$this->mobile_detect = $this->createMock(MobileDetect::class);

		$this->listener = new main_listener(
			$this->config,
			$this->user,
			$this->request,
			$this->template,
			$this->helper,
			$this->ext_manager
		);

		$reflection = new \ReflectionClass($this->listener);
		$property = $reflection->getProperty('mobile_detect');
		$property->setAccessible(true);
		$property->setValue($this->listener, $this->mobile_detect);
	}

	public function test_force_style_on_mobile()
	{
		$this->request->method('variable')->willReturn(0);
		$this->mobile_detect->method('isMobile')->willReturn(true);
		$this->mobile_detect->method('isTablet')->willReturn(false);

		$event = new data(['style_id' => 1]);

		$this->listener->user_setup($event);

		$this->assertEquals(2, $event['style_id']);
	}

	public function test_ignore_style_if_tablet()
	{
		$this->request->method('variable')->willReturn(0);
		$this->mobile_detect->method('isMobile')->willReturn(true);
		$this->mobile_detect->method('isTablet')->willReturn(true);

		$event = new data(['style_id' => 1]);

		$this->listener->user_setup($event);

		$this->assertEquals(1, $event['style_id']);
	}

	public function test_force_style_on_pwa_mode_but_not_mobile()
	{
		$this->request->method('variable')->will($this->returnValueMap([
			['pwa_mode', 0, false, request_interface::REQUEST, 1],
		]));

		$this->mobile_detect->method('isMobile')->willReturn(false);
		$this->user->expects($this->once())->method('set_cookie');

		$event = new data(['style_id' => 1]);

		$this->listener->user_setup($event);

		$this->assertEquals(1, $event['style_id']);
	}

	public function test_force_style_on_pwa_mode_and_mobile()
	{
		$this->request->method('variable')->will($this->returnValueMap([
			['pwa_mode', 0, false, request_interface::REQUEST, 1],
		]));

		$this->mobile_detect->method('isMobile')->willReturn(true);
		$this->mobile_detect->method('isTablet')->willReturn(false);
		$this->user->expects($this->once())->method('set_cookie');

		$event = new data(['style_id' => 1]);

		$this->listener->user_setup($event);

		$this->assertEquals(2, $event['style_id']);
	}
}
