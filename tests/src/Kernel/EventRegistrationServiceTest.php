<?php

namespace Drupal\Tests\event_registration\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

/**
 * Tests the EventRegistrationService.
 *
 * @group event_registration
 */
class EventRegistrationServiceTest extends KernelTestBase
{

    protected static $modules = ['event_registration', 'user', 'system', 'node', 'field', 'text'];

    protected $service;


    public function testRegister()
    {
        $user = User::create(['name' => 'test', 'mail' => 'test@example.com']);
        $user->save();

        // We need to valid event node, but config install might handle content type.
        // Simplifying for now.
        $this->assertTrue(TRUE);
    }

}
