<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\event_registration\Service\EventManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for creating and managing events.
 */
class EventConfigForm extends FormBase
{

  /**
   * The event manager service.
   *
   * @var \Drupal\event_registration\Service\EventManager
   */
  protected $eventManager;

  /**
   * Constructs an EventConfigForm object.
   *
   * @param \Drupal\event_registration\Service\EventManager $event_manager
   *   The event manager service.
   */
  public function __construct(EventManager $event_manager)
  {
    $this->eventManager = $event_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('event_registration.event_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'event_registration_event_config';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $form['event_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Event Name'),
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    $form['category'] = [
      '#type' => 'select',
      '#title' => $this->t('Category'),
      '#required' => TRUE,
      '#options' => [
        '' => $this->t('- Select Category -'),
        'Technical' => $this->t('Technical'),
        'Cultural' => $this->t('Cultural'),
        'Sports' => $this->t('Sports'),
        'Workshop' => $this->t('Workshop'),
        'Seminar' => $this->t('Seminar'),
      ],
    ];

    $form['event_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Event Date'),
      '#required' => TRUE,
    ];

    $form['reg_start'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Registration Start'),
      '#required' => TRUE,
      '#description' => $this->t('When registration opens for this event.'),
    ];

    $form['reg_end'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Registration End'),
      '#required' => TRUE,
      '#description' => $this->t('When registration closes for this event.'),
    ];

    $form['status'] = [
      '#type' => 'radios',
      '#title' => $this->t('Status'),
      '#options' => [
        1 => $this->t('Active'),
        0 => $this->t('Inactive'),
      ],
      '#default_value' => 1,
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save Event'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    $event_date = $form_state->getValue('event_date');
    $reg_start = $form_state->getValue('reg_start');
    $reg_end = $form_state->getValue('reg_end');

    // Convert datetime objects to timestamps.
    $reg_start_timestamp = $reg_start instanceof \Drupal\Core\Datetime\DrupalDateTime
      ? $reg_start->getTimestamp()
      : strtotime($reg_start ?? '');

    $reg_end_timestamp = $reg_end instanceof \Drupal\Core\Datetime\DrupalDateTime
      ? $reg_end->getTimestamp()
      : strtotime($reg_end ?? '');

    // Validate dates using EventManager.
    $errors = $this->eventManager->validateEventDates(
      $event_date,
      $reg_start_timestamp,
      $reg_end_timestamp
    );

    foreach ($errors as $error) {
      $form_state->setErrorByName('event_date', $this->t($error));
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $reg_start = $form_state->getValue('reg_start');
    $reg_end = $form_state->getValue('reg_end');

    // Convert datetime objects to timestamps.
    $reg_start_timestamp = $reg_start instanceof \Drupal\Core\Datetime\DrupalDateTime
      ? $reg_start->getTimestamp()
      : strtotime($reg_start ?? '');

    $reg_end_timestamp = $reg_end instanceof \Drupal\Core\Datetime\DrupalDateTime
      ? $reg_end->getTimestamp()
      : strtotime($reg_end ?? '');

    $event_data = [
      'event_name' => $form_state->getValue('event_name'),
      'category' => $form_state->getValue('category'),
      'event_date' => $form_state->getValue('event_date'),
      'reg_start' => $reg_start_timestamp,
      'reg_end' => $reg_end_timestamp,
      'status' => $form_state->getValue('status'),
    ];

    $result = $this->eventManager->createEvent($event_data);

    if ($result) {
      $this->messenger()->addStatus($this->t('Event created successfully.'));
    } else {
      $this->messenger()->addError($this->t('Failed to create event. Please try again.'));
    }
  }

}
