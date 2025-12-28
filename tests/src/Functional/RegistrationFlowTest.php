<?php

namespace Drupal\Tests\event_registration\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the registration web flow.
 *
 * @group event_registration
 */
class RegistrationFlowTest extends BrowserTestBase
{

    protected $defaultTheme = 'stark';
    protected static $modules = ['event_registration', 'node', 'user'];

    public function testRegistration()
    {
        $user = $this->drupalCreateUser(['register for events']);
        $this->drupalLogin($user);

        // Create event.
        $event = $this->drupalCreateNode([
            'type' => 'event',
            'title' => 'Test Event',
        ]);

        // Go to registration page.
        $this->drupalGet('events/' . $event->id() . '/register');
        $this->assertSession()->statusCodeEquals(200);

        // Submit form.
        $this->submitForm([], 'Register');
        $this->assertSession()->pageTextContains('You have successfully registered');
    }

}
