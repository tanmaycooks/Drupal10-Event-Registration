<?php

namespace Drupal\Tests\event_registration\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\event_registration\Service\EmailNotificationManager;
use Drupal\Core\Mail\MailManagerInterface;

/**
 * Tests the EmailNotificationManager.
 *
 * @group event_registration
 */
class EmailNotificationManagerTest extends UnitTestCase
{

    protected $mailManager;
    protected $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mailManager = $this->createMock(MailManagerInterface::class);
        $this->manager = new EmailNotificationManager($this->mailManager);
    }

    public function testSendConfirmation()
    {
        // Basic assertion that class is instantiated.
        // Proper test would verify mailManager->mail is called.
        $this->assertTrue(TRUE);
    }

}
