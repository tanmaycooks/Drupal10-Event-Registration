<?php

namespace Drupal\event_registration\Service;

use Drupal\event_registration\Repository\EventRepository;
use Drupal\Component\Datetime\TimeInterface;
use Psr\Log\LoggerInterface;

/**
 * Service for event management business logic.
 */
class EventManager {

  /**
   * Event status: Active.
   */
  const STATUS_ACTIVE = 1;

  /**
   * Event status: Inactive.
   */
  const STATUS_INACTIVE = 0;

  /**
   * The event repository.
   *
   * @var \Drupal\event_registration\Repository\EventRepository
   */
  protected $eventRepository;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs an EventManager object.
   *
   * @param \Drupal\event_registration\Repository\EventRepository $event_repository
   *   The event repository.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.
   */
  public function __construct(EventRepository $event_repository, TimeInterface $time, LoggerInterface $logger) {
    $this->eventRepository = $event_repository;
    $this->time = $time;
    $this->logger = $logger;
  }

  /**
   * Validates event dates.
   *
   * @param string $event_date
   *   The event date.
   * @param int $reg_start
   *   Registration start timestamp.
   * @param int $reg_end
   *   Registration end timestamp.
   *
   * @return array
   *   Array of validation errors (empty if valid).
   */
  public function validateEventDates($event_date, $reg_start, $reg_end) {
    $errors = [];

    // Registration end must be after registration start.
    if ($reg_end <= $reg_start) {
      $errors[] = 'Registration end date must be after registration start date.';
    }

    // Event date must be after registration end.
    $event_timestamp = strtotime($event_date);
    if ($event_timestamp <= $reg_end) {
      $errors[] = 'Event date must be after registration end date.';
    }

    return $errors;
  }

  /**
   * Checks if an event is currently accepting registrations.
   *
   * @param int $event_id
   *   The event ID.
   *
   * @return bool
   *   TRUE if registration is open, FALSE otherwise.
   */
  public function isRegistrationOpen($event_id) {
    return $this->eventRepository->isRegistrationOpen($event_id);
  }

  /**
   * Gets event details for registration form.
   *
   * @param int $event_id
   *   The event ID.
   *
   * @return object|null
   *   Event object or NULL if not found/inactive.
   */
  public function getEventForRegistration($event_id) {
    $event = $this->eventRepository->load($event_id);
    
    if (!$event || $event->status != self::STATUS_ACTIVE) {
      return NULL;
    }

    return $event;
  }

  /**
   * Creates a new event.
   *
   * @param array $event_data
   *   Event data array with required keys: event_name, category, event_date,
   *   reg_start, reg_end, status.
   *
   * @return int|bool
   *   Event ID on success, FALSE on failure.
   *
   * @throws \InvalidArgumentException
   *   If required event data is missing.
   * @throws \Drupal\Core\Database\DatabaseExceptionWrapper
   *   If database operation fails.
   */
  public function createEvent(array $event_data) {
    // Validate required fields.
    $required_fields = ['event_name', 'category', 'event_date', 'reg_start', 'reg_end', 'status'];
    foreach ($required_fields as $field) {
      if (!isset($event_data[$field])) {
        throw new \InvalidArgumentException("Missing required field: $field");
      }
    }

    try {
      $event_data['created'] = $this->time->getRequestTime();
      $event_id = $this->eventRepository->create($event_data);
      
      $this->logger->info('Event created: @name (ID: @id)', [
        '@name' => $event_data['event_name'],
        '@id' => $event_id,
      ]);

      return $event_id;
    }
    catch (\Drupal\Core\Database\DatabaseExceptionWrapper $e) {
      $this->logger->error('Database error creating event: @message', [
        '@message' => $e->getMessage(),
      ]);
      throw $e;
    }
    catch (\Exception $e) {
      $this->logger->error('Unexpected error creating event: @message', [
        '@message' => $e->getMessage(),
      ]);
      return FALSE;
    }
  }

  /**
   * Updates an existing event.
   *
   * @param int $event_id
   *   The event ID.
   * @param array $event_data
   *   Event data array.
   *
   * @return bool
   *   TRUE on success, FALSE on failure.
   *
   * @throws \InvalidArgumentException
   *   If event_id is invalid.
   * @throws \Drupal\Core\Database\DatabaseExceptionWrapper
   *   If database operation fails.
   */
  public function updateEvent($event_id, array $event_data): bool {
    if (!is_numeric($event_id) || $event_id <= 0) {
      throw new \InvalidArgumentException("Invalid event ID: $event_id");
    }

    try {
      $this->eventRepository->update($event_id, $event_data);
      
      $this->logger->info('Event updated: ID @id', [
        '@id' => $event_id,
      ]);

      return TRUE;
    }
    catch (\Drupal\Core\Database\DatabaseExceptionWrapper $e) {
      $this->logger->error('Database error updating event: @message', [
        '@message' => $e->getMessage(),
      ]);
      throw $e;
    }
    catch (\Exception $e) {
      $this->logger->error('Unexpected error updating event: @message', [
        '@message' => $e->getMessage(),
      ]);
      return FALSE;
    }
  }

  /**
   * Gets all categories for dropdown.
   *
   * @return array
   *   Array of categories keyed by category name.
   */
  public function getCategoriesForDropdown() {
    $categories = $this->eventRepository->getCategories();
    $options = ['' => '- Select Category -'];
    
    foreach ($categories as $category) {
      $options[$category] = $category;
    }

    return $options;
  }

}
