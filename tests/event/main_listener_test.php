<?php
/**
 *
 * @package vinny/pwa
 * @copyright (c) 2026 Vinny
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace vinny\pwa\tests\event;

class main_listener_test extends \phpbb\test_case
{
    protected $config;
    protected $user;
    protected $request;
    protected $template;
    protected $mobile_detect;
    protected $listener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = new \phpbb\config\config([
            'pwa_enabled'         => '1',
            'pwa_mobile_style_id' => '2',
            'pwa_force_on_mobile' => '1',
            'pwa_force_on_pwa'    => '1',
            'cookie_name'         => 'phpbb3_pwa',
        ]);

        $this->user = $this->createMock('\phpbb\user');
        $this->user->data = ['user_style' => 1, 'session_id' => '123456789'];
        
        $this->request = $this->createMock('\phpbb\request\request_interface');
        $this->template = $this->createMock('\phpbb\template\template');
        $this->mobile_detect = $this->createMock('\Detection\MobileDetect');

        $this->listener = clone $this->createPartialMock('\vinny\pwa\event\main_listener', []);
        
        $constructor = new \ReflectionMethod('\vinny\pwa\event\main_listener', '__construct');
        $constructor->invoke($this->listener, $this->config, $this->user, $this->request, $this->template);

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

        $event = new \phpbb\event\data(['style_id' => 1]);
        
        $this->listener->user_setup($event);

        $this->assertEquals(2, $event['style_id']);
    }

    public function test_ignore_style_if_tablet()
    {
        $this->request->method('variable')->willReturn(0);
        $this->mobile_detect->method('isMobile')->willReturn(true);
        $this->mobile_detect->method('isTablet')->willReturn(true);

        $event = new \phpbb\event\data(['style_id' => 1]);
        
        $this->listener->user_setup($event);

        $this->assertEquals(1, $event['style_id']);
    }

    public function test_force_style_on_pwa_mode_but_not_mobile()
    {
        $this->request->method('variable')->will($this->returnValueMap([
            ['pwa_mode', 0, false, \phpbb\request\request_interface::REQUEST, 1]
        ]));

        $this->mobile_detect->method('isMobile')->willReturn(false);

        $this->user->expects($this->once())->method('set_cookie');

        $event = new \phpbb\event\data(['style_id' => 1]);
        
        $this->listener->user_setup($event);

        $this->assertEquals(1, $event['style_id']);
    }

    public function test_force_style_on_pwa_mode_and_mobile()
    {
        $this->request->method('variable')->will($this->returnValueMap([
            ['pwa_mode', 0, false, \phpbb\request\request_interface::REQUEST, 1]
        ]));

        $this->mobile_detect->method('isMobile')->willReturn(true);
        $this->mobile_detect->method('isTablet')->willReturn(false);

        $this->user->expects($this->once())->method('set_cookie');

        $event = new \phpbb\event\data(['style_id' => 1]);
        
        $this->listener->user_setup($event);

        $this->assertEquals(2, $event['style_id']);
    }
}
