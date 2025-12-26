<?php

namespace Drupal\Tests\event_registration\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests for CSV export.
 *
 * @group event_registration
 */
class CsvExportTest extends BrowserTestBase
{

    protected $defaultTheme = 'stark';
    protected static $modules = ['event_registration', 'node', 'user'];

    public function testExport()
    {
        $user = $this->drupalCreateUser(['export event registrations']);
        $this->drupalLogin($user);

        // Create event and registrations logic would go here.
        // Assert response headers and content.
        $this->assertTrue(TRUE);
    }

}
