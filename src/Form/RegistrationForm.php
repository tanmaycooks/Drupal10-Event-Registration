<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\event_registration\Service\EventRegistrationService;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\event_registration\Exception\RegistrationException;

/**
 * Form for event registration.
 */
class RegistrationForm extends FormBase
{

    /**
     * The registration service.
     *
     * @var \Drupal\event_registration\Service\EventRegistrationService
     */
    protected $registrationService;

    /**
     * Constructor.
     *
     * @param \Drupal\event_registration\Service\EventRegistrationService $registration_service
     *   The registration service.
     */
    public function __construct(EventRegistrationService $registration_service)
    {
        $this->registrationService = $registration_service;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('event_registration.service')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'event_registration_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, NodeInterface $event = NULL)
    {
        $form['event_id'] = [
            '#type' => 'value',
            '#value' => $event->id(),
        ];

        $form['info'] = [
            '#markup' => '<h3>' . $this->t('Register for @title', ['@title' => $event->label()]) . '</h3>',
        ];

        // Future: Add custom fields if entity has them required on registration.
        // For now, it's just a confirmation button essentially, as user data is from current user.

        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Register'),
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        $event = \Drupal::routeMatch()->getParameter('event');

        // Check if event is open.
        if (!$this->registrationService->isRegistrationOpen($event)) {
            $form_state->setError($form, $this->t('Registration is closed for this event.'));
        }

        // Check capacity.
        if (!$this->registrationService->validateRegistration($event, $this->currentUser())) {
            $form_state->setError($form, $this->t('Registration is not available (e.g., event full or duplicate).'));
            \Drupal::logger('event_registration')->warning('Registration validation failed for user @uid on event @eid', [
                '@uid' => $this->currentUser()->id(),
                '@eid' => $event->id(),
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $event = \Drupal::routeMatch()->getParameter('event');

        try {
            $this->registrationService->register($event, $this->currentUser());
            $this->messenger()->addStatus($this->t('You have successfully registered.'));
        } catch (\Drupal\event_registration\Exception\RegistrationException $e) {
            $this->messenger()->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messenger()->addError($this->t('An unexpected error occurred.'));
        }
    }

}
