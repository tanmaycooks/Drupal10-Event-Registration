<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\event_registration\Service\EventManager;
use Drupal\event_registration\Service\RegistrationManager;
use Drupal\event_registration\Service\MailService;
use Drupal\event_registration\Repository\EventRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Public event registration form with AJAX.
 */
class EventRegistrationForm extends FormBase {

  /**
   * The event manager service.
   *
   * @var \Drupal\event_registration\Service\EventManager
   */
  protected $eventManager;

  /**
   * The registration manager service.
   *
   * @var \Drupal\event_registration\Service\RegistrationManager
   */
  protected $registrationManager;

  /**
   * The mail service.
   *
   * @var \Drupal\event_registration\Service\MailService
   */
  protected $mailService;

  /**
   * The event repository.
   *
   * @var \Drupal\event_registration\Repository\EventRepository
   */
  protected $eventRepository;

  /**
   * Constructs an EventRegistrationForm object.
   */
  public function __construct(
    EventManager $event_manager,
    RegistrationManager $registration_manager,
    MailService $mail_service,
    EventRepository $event_repository
  ) {
    $this->eventManager = $event_manager;
    $this->registrationManager = $registration_manager;
    $this->mailService = $mail_service;
    $this->eventRepository = $event_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('event_registration.event_manager'),
      $container->get('event_registration.registration_manager'),
      $container->get('event_registration.mail_service'),
      $container->get('event_registration.event_repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'event_registration_public_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['full_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Full Name'),
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    $form['college'] = [
      '#type' => 'textfield',
      '#title' => $this->t('College Name'),
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    $form['department'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Department'),
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    // Category dropdown with AJAX.
    $form['category'] = [
      '#type' => 'select',
      '#title' => $this->t('Category'),
      '#required' => TRUE,
      '#options' => $this->eventManager->getCategoriesForDropdown(),
      '#ajax' => [
        'callback' => '::updateEventDateOptions',
        'wrapper' => 'event-date-wrapper',
        'event' => 'change',
      ],
    ];

    // Event date dropdown (populated via AJAX).
    $form['event_date_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'event-date-wrapper'],
    ];

    $category = $form_state->getValue('category');
    $date_options = ['' => $this->t('- Select Event Date -')];
    
    if (!empty($category)) {
      $dates = $this->eventRepository->getEventDatesByCategory($category);
      foreach ($dates as $date) {
        $date_options[$date] = $date;
      }
    }

    $form['event_date_wrapper']['event_date'] = [
      '#type' => 'select',
      '#title' => $this->t('Event Date'),
      '#required' => TRUE,
      '#options' => $date_options,
      '#ajax' => [
        'callback' => '::updateEventNameOptions',
        'wrapper' => 'event-name-wrapper',
        'event' => 'change',
      ],
    ];

    // Event name dropdown (populated via AJAX).
    $form['event_name_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'event-name-wrapper'],
    ];

    $event_date = $form_state->getValue('event_date');
    $event_options = ['' => $this->t('- Select Event -')];
    
    if (!empty($category) && !empty($event_date)) {
      $events = $this->eventRepository->getEventsByCategoryAndDate($category, $event_date);
      foreach ($events as $event) {
        $event_options[$event->id] = $event->event_name;
      }
    }

    $form['event_name_wrapper']['event_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Event Name'),
      '#required' => TRUE,
      '#options' => $event_options,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Register'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * AJAX callback to update event date options.
   */
  public function updateEventDateOptions(array &$form, FormStateInterface $form_state) {
    return $form['event_date_wrapper'];
  }

  /**
   * AJAX callback to update event name options.
   */
  public function updateEventNameOptions(array &$form, FormStateInterface $form_state) {
    return $form['event_name_wrapper'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    // First, validate that all required fields are filled.
    $required_fields = [
      'full_name' => 'Full Name',
      'email' => 'Email',
      'college' => 'College Name',
      'department' => 'Department',
      'category' => 'Category',
      'event_date' => 'Event Date',
      'event_id' => 'Event Name',
    ];

    foreach ($required_fields as $field => $label) {
      if (empty($values[$field])) {
        $form_state->setErrorByName($field, $this->t('@label is required.', ['@label' => $label]));
      }
    }

    // Only proceed with business validation if basic validation passed.
    if ($form_state->hasAnyErrors()) {
      return;
    }

    // Server-side validation (independent of AJAX).
    $validation_errors = $this->registrationManager->validateRegistrationData([
      'full_name' => $values['full_name'],
      'email' => $values['email'],
      'college' => $values['college'],
      'department' => $values['department'],
      'event_id' => $values['event_id'],
    ]);

    foreach ($validation_errors as $field => $error) {
      $form_state->setErrorByName($field, $this->t($error));
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $event = $this->eventRepository->load($values['event_id']);

    if (!$event) {
      $this->messenger()->addError($this->t('Event not found.'));
      return;
    }

    // Create registration.
    $registration_data = [
      'event_id' => $values['event_id'],
      'full_name' => $values['full_name'],
      'email' => $values['email'],
      'college' => $values['college'],
      'department' => $values['department'],
    ];

    try {
      $registration_id = $this->registrationManager->createRegistration($registration_data);

      if ($registration_id) {
        // Send emails.
        $email_data = array_merge($registration_data, [
          'event_name' => $event->event_name,
          'event_date' => $event->event_date,
          'category' => $event->category,
        ]);

        try {
          $this->mailService->sendRegistrationEmails($email_data);
          
          $this->messenger()->addStatus($this->t('Registration successful! A confirmation email has been sent to @email.', [
            '@email' => $values['email'],
          ]));
        }
        catch (\Exception $e) {
          // Registration succeeded but email failed.
          $this->messenger()->addWarning($this->t('Registration successful, but we could not send the confirmation email. Please contact support if you need confirmation.'));
        }

        // Reset form.
        $form_state->setRebuild(FALSE);
        $form_state->setRedirect('<front>');
      }
      else {
        $this->messenger()->addError($this->t('Registration failed. Please try again.'));
      }
    }
    catch (\InvalidArgumentException $e) {
      $this->messenger()->addError($this->t('Invalid registration data: @message', [
        '@message' => $e->getMessage(),
      ]));
    }
    catch (\Exception $e) {
      $this->messenger()->addError($this->t('An unexpected error occurred. Please try again later.'));
    }
  }

}
