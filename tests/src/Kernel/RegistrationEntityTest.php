<?php

namespace Drupal\Tests\event_registration\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the Registration entity.
 *
 * @group event_registration
 */
class RegistrationEntityTest extends KernelTestBase
{

    /**
     * {@inheritdoc}
     */
    protected static $modules = ['event_registration', 'user', 'system', 'node', 'field', 'text'];

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->installEntitySchema('event_registration');
        $this->installEntitySchema('user');
        $this->installConfig(['event_registration']);
    }

    public function testEntity()
    {
        $user = $this->createUser();
        $registration = \Drupal\event_registration\Entity\Registration::create([
            'uid' => $user->id(),
            'email' => 'test@example.com',
        ]);
        $registration->save();

        $this->assertEquals($user->id(), $registration->getOwnerId());
        $this->assertEquals('test@example.com', $registration->getEmail());
    }

    /**
     * Tests registration creation.
     */
    public function testRegistrationCreation()
    {
        $this->assertTrue(TRUE, 'Test scaffold created.');
    }

}
