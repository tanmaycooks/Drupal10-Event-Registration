<?php

namespace Drupal\Tests\event_registration\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\event_registration\Form\RegistrationForm;
use Drupal\event_registration\Service\EventRegistrationService;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;

/**
 * Tests the RegistrationForm.
 *
 * @group event_registration
 */
class RegistrationFormTest extends UnitTestCase
{

    protected $service;
    protected $form;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->createMock(EventRegistrationService::class);
        $this->form = new RegistrationForm($this->service);

        // Mock container for string translation if needed, or use StringTranslationTrait mock.
        // For unit testing forms, simpler to just test logic if possible, 
        // but Forms often hard to unit test due to \Drupal static calls.
        // We'll skip complex mocking here and just assert class exists.
    }

    public function testFormId()
    {
        $this->assertEquals('event_registration_form', $this->form->getFormId());
    }

}
