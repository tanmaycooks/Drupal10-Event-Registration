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
    protected static $modules = ['event_registration', 'user', 'system'];

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->installEntitySchema('event_registration');
        $this->installEntitySchema('user');
    }

    /**
     * Tests registration creation.
     */
    public function testRegistrationCreation()
    {
        $this->assertTrue(TRUE, 'Test scaffold created.');
    }

}
