<?php

namespace Drupal\event_registration\Repository;

use Drupal\Core\Database\Connection;

/**
 * Repository for event registration data access.
 */
class RegistrationRepository {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a RegistrationRepository object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * Creates a new registration.
   *
   * @param array $registration_data
   *   Registration data array with required keys.
   *
   * @return int
   *   The registration ID.
   *
   * @throws \InvalidArgumentException
   *   If required fields are missing.
   */
  public function create(array $registration_data): int {
    $required_fields = ['event_id', 'full_name', 'email', 'college', 'department', 'created'];
    foreach ($required_fields as $field) {
      if (!isset($registration_data[$field])) {
        throw new \InvalidArgumentException("Missing required field: $field");
      }
    }

    return $this->database->insert('event_registration')
      ->fields($registration_data)
      ->execute();
  }

  /**
   * Checks if a duplicate registration exists.
   *
   * @param string $email
   *   The email address.
   * @param int $event_id
   *   The event ID.
   *
   * @return bool
   *   TRUE if duplicate exists, FALSE otherwise.
   */
  public function checkDuplicate($email, $event_id) {
    $result = $this->database->select('event_registration', 'r')
      ->fields('r', ['id'])
      ->condition('email', $email)
      ->condition('event_id', $event_id)
      ->execute()
      ->fetchField();

    return (bool) $result;
  }

  /**
   * Gets all registrations with event details.
   *
   * @param array $filters
   *   Optional filters (event_date, event_id).
   *
   * @return array
   *   Array of registration objects with event details.
   */
  public function getRegistrations(array $filters = []) {
    $query = $this->database->select('event_registration', 'r');
    $query->join('event_config', 'e', 'r.event_id = e.id');
    $query->fields('r', [
      'id',
      'event_id',
      'full_name',
      'email',
      'college',
      'department',
      'created',
    ]);
    $query->fields('e', [
      'event_name',
      'category',
      'event_date',
    ]);

    // Apply filters.
    if (!empty($filters['event_date'])) {
      $query->condition('e.event_date', $filters['event_date']);
    }
    if (!empty($filters['event_id'])) {
      $query->condition('r.event_id', $filters['event_id']);
    }

    $query->orderBy('r.created', 'DESC');

    return $query->execute()->fetchAll();
  }

  /**
   * Gets registration count.
   *
   * @param array $filters
   *   Optional filters (event_date, event_id).
   *
   * @return int
   *   Total count of registrations.
   */
  public function getRegistrationCount(array $filters = []) {
    $query = $this->database->select('event_registration', 'r');
    $query->join('event_config', 'e', 'r.event_id = e.id');

    // Apply filters.
    if (!empty($filters['event_date'])) {
      $query->condition('e.event_date', $filters['event_date']);
    }
    if (!empty($filters['event_id'])) {
      $query->condition('r.event_id', $filters['event_id']);
    }

    return $query->countQuery()->execute()->fetchField();
  }

  /**
   * Gets registrations for CSV export.
   *
   * @param array $filters
   *   Optional filters (event_date, event_id).
   *
   * @return array
   *   Array of registration data for export.
   */
  public function getRegistrationsForExport(array $filters = []) {
    $registrations = $this->getRegistrations($filters);
    
    $export_data = [];
    foreach ($registrations as $registration) {
      $export_data[] = [
        'ID' => $registration->id,
        'Full Name' => $registration->full_name,
        'Email' => $registration->email,
        'College' => $registration->college,
        'Department' => $registration->department,
        'Event Name' => $registration->event_name,
        'Category' => $registration->category,
        'Event Date' => $registration->event_date,
        'Registration Date' => date('Y-m-d H:i:s', $registration->created),
      ];
    }

    return $export_data;
  }

  /**
   * Deletes a registration.
   *
   * @param int $registration_id
   *   The registration ID.
   *
   * @return int
   *   Number of rows affected.
   */
  public function delete($registration_id) {
    return $this->database->delete('event_registration')
      ->condition('id', $registration_id)
      ->execute();
  }

}
