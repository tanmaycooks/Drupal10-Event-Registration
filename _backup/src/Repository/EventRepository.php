<?php

namespace Drupal\event_registration\Repository;

use Drupal\Core\Database\Connection;

/**
 * Repository for event configuration data access.
 */
class EventRepository {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs an EventRepository object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * Creates a new event.
   *
   * @param array $event_data
   *   Event data array with required keys.
   *
   * @return int
   *   The event ID.
   *
   * @throws \InvalidArgumentException
   *   If required fields are missing.
   */
  public function create(array $event_data): int {
    $required_fields = ['event_name', 'category', 'event_date', 'reg_start', 'reg_end', 'status', 'created'];
    foreach ($required_fields as $field) {
      if (!isset($event_data[$field])) {
        throw new \InvalidArgumentException("Missing required field: $field");
      }
    }

    return $this->database->insert('event_config')
      ->fields($event_data)
      ->execute();
  }

  /**
   * Updates an existing event.
   *
   * @param int $event_id
   *   The event ID.
   * @param array $event_data
   *   Event data array.
   *
   * @return int
   *   Number of rows affected.
   */
  public function update($event_id, array $event_data) {
    return $this->database->update('event_config')
      ->fields($event_data)
      ->condition('id', $event_id)
      ->execute();
  }

  /**
   * Deletes an event.
   *
   * @param int $event_id
   *   The event ID.
   *
   * @return int
   *   Number of rows affected.
   */
  public function delete($event_id) {
    return $this->database->delete('event_config')
      ->condition('id', $event_id)
      ->execute();
  }

  /**
   * Loads an event by ID.
   *
   * @param int $event_id
   *   The event ID.
   *
   * @return object|false
   *   Event object or FALSE if not found.
   */
  public function load($event_id) {
    $result = $this->database->select('event_config', 'e')
      ->fields('e')
      ->condition('id', $event_id)
      ->execute()
      ->fetchObject();

    return $result ?: FALSE;
  }

  /**
   * Gets all active events.
   *
   * @return array
   *   Array of event objects.
   */
  public function getActiveEvents() {
    return $this->database->select('event_config', 'e')
      ->fields('e')
      ->condition('status', 1)
      ->orderBy('event_date', 'ASC')
      ->execute()
      ->fetchAll();
  }

  /**
   * Gets all events (active and inactive).
   *
   * @return array
   *   Array of event objects.
   */
  public function getAllEvents() {
    return $this->database->select('event_config', 'e')
      ->fields('e')
      ->orderBy('created', 'DESC')
      ->execute()
      ->fetchAll();
  }

  /**
   * Gets unique categories from active events.
   *
   * @return array
   *   Array of category strings.
   */
  public function getCategories() {
    $results = $this->database->select('event_config', 'e')
      ->fields('e', ['category'])
      ->condition('status', 1)
      ->distinct()
      ->orderBy('category', 'ASC')
      ->execute()
      ->fetchAll();

    return array_map(function ($row) {
      return $row->category;
    }, $results);
  }

  /**
   * Gets event dates by category.
   *
   * @param string $category
   *   The category.
   *
   * @return array
   *   Array of event date strings.
   */
  public function getEventDatesByCategory($category) {
    $results = $this->database->select('event_config', 'e')
      ->fields('e', ['event_date'])
      ->condition('category', $category)
      ->condition('status', 1)
      ->distinct()
      ->orderBy('event_date', 'ASC')
      ->execute()
      ->fetchAll();

    return array_map(function ($row) {
      return $row->event_date;
    }, $results);
  }

  /**
   * Gets events by category and date.
   *
   * @param string $category
   *   The category.
   * @param string $event_date
   *   The event date.
   *
   * @return array
   *   Array of event objects.
   */
  public function getEventsByCategoryAndDate($category, $event_date) {
    return $this->database->select('event_config', 'e')
      ->fields('e')
      ->condition('category', $category)
      ->condition('event_date', $event_date)
      ->condition('status', 1)
      ->orderBy('event_name', 'ASC')
      ->execute()
      ->fetchAll();
  }

  /**
   * Checks if registration is currently open for an event.
   *
   * @param int $event_id
   *   The event ID.
   *
   * @return bool
   *   TRUE if registration is open, FALSE otherwise.
   */
  public function isRegistrationOpen($event_id) {
    $current_time = \Drupal::time()->getRequestTime();
    
    $result = $this->database->select('event_config', 'e')
      ->fields('e', ['id'])
      ->condition('id', $event_id)
      ->condition('status', 1)
      ->condition('reg_start', $current_time, '<=')
      ->condition('reg_end', $current_time, '>=')
      ->execute()
      ->fetchField();

    return (bool) $result;
  }

}
