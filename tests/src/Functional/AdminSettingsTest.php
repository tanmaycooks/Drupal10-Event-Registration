<?php

namespace Drupal\Tests\event_registration\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the admin settings form.
 *
 * @group event_registration
 */
class AdminSettingsTest extends BrowserTestBase
{

    protected $defaultTheme = 'stark';
    protected static $modules = ['event_registration', 'node', 'user'];

    public function testAdminSettings()
    {
        $user = $this->drupalCreateUser(['administer event registration']);
        $this->drupalLogin($user);

        $this->drupalGet('admin/config/events/settings');
        $this->assertSession()->statusCodeEquals(200);

        $this->submitForm(['notifications_enabled' => TRUE], 'Save configuration');
        $this->assertSession()->pageTextContains('The configuration options have been saved.');
    }

}
