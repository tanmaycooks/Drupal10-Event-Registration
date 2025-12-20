<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\event_registration\Repository\EventRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Filter form for admin registration listing.
 */
class RegistrationFilterForm extends FormBase {

  /**
   * The event repository.
   *
   * @var \Drupal\event_registration\Repository\EventRepository
   */
  protected $eventRepository;

  /**
   * Constructs a RegistrationFilterForm object.
   *
   * @param \Drupal\event_registration\Repository\EventRepository $event_repository
   *   The event repository.
   */
  public function __construct(EventRepository $event_repository) {
    $this->eventRepository = $event_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('event_registration.event_repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'event_registration_filter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get unique event dates.
    $events = $this->eventRepository->getAllEvents();
    $date_options = ['' => $this->t('- All Dates -')];
    $dates_seen = [];
    
    foreach ($events as $event) {
      // Use array key for O(1) lookup instead of in_array O(n).
      if (!isset($dates_seen[$event->event_date])) {
        $date_options[$event->event_date] = $event->event_date;
        $dates_seen[$event->event_date] = TRUE;
      }
    }

    $form['event_date'] = [
      '#type' => 'select',
      '#title' => $this->t('Event Date'),
      '#options' => $date_options,
      '#ajax' => [
        'callback' => '::updateEventNameOptions',
        'wrapper' => 'event-name-wrapper',
        'event' => 'change',
      ],
    ];

    // Event name dropdown (filtered by date).
    $form['event_name_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'event-name-wrapper'],
    ];

    $selected_date = $form_state->getValue('event_date');
    $event_options = ['' => $this->t('- All Events -')];
    
    if (!empty($selected_date)) {
      $filtered_events = array_filter($events, function ($event) use ($selected_date) {
        return $event->event_date === $selected_date;
      });
      
      foreach ($filtered_events as $event) {
        $event_options[$event->id] = $event->event_name;
      }
    }
    else {
      foreach ($events as $event) {
        $event_options[$event->id] = $event->event_name . ' (' . $event->event_date . ')';
      }
    }

    $form['event_name_wrapper']['event_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Event Name'),
      '#options' => $event_options,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Filter'),
      '#button_type' => 'primary',
    ];

    $form['actions']['reset'] = [
      '#type' => 'submit',
      '#value' => $this->t('Reset'),
      '#submit' => ['::resetForm'],
    ];

    return $form;
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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Form submission is handled by the controller.
    $form_state->setRebuild(TRUE);
  }

  /**
   * Reset form handler.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function resetForm(array &$form, FormStateInterface $form_state) {
    // Clear all form values.
    $form_state->setValues([]);
    // Clear user input to reset form completely.
    $form_state->setUserInput([]);
    // Clear any stored data.
    $form_state->setStorage([]);
    // Rebuild the form with cleared values.
    $form_state->setRebuild(TRUE);
  }

}
