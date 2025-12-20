<?php

namespace Drupal\event_registration\Service;

use Drupal\event_registration\Repository\RegistrationRepository;
use Drupal\event_registration\Repository\EventRepository;
use Drupal\Component\Utility\EmailValidatorInterface;
use Psr\Log\LoggerInterface;

/**
 * Service for registration management business logic.
 */
class RegistrationManager {

  /**
   * The registration repository.
   *
   * @var \Drupal\event_registration\Repository\RegistrationRepository
   */
  protected $registrationRepository;

  /**
   * The event repository.
   *
   * @var \Drupal\event_registration\Repository\EventRepository
   */
  protected $eventRepository;

  /**
   * The email validator.
   *
   * @var \Drupal\Component\Utility\EmailValidatorInterface
   */
  protected $emailValidator;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a RegistrationManager object.
   *
   * @param \Drupal\event_registration\Repository\RegistrationRepository $registration_repository
   *   The registration repository.
   * @param \Drupal\event_registration\Repository\EventRepository $event_repository
   *   The event repository.
   * @param \Drupal\Component\Utility\EmailValidatorInterface $email_validator
   *   The email validator.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.
   */
  public function __construct(
    RegistrationRepository $registration_repository,
    EventRepository $event_repository,
    EmailValidatorInterface $email_validator,
    LoggerInterface $logger
  ) {
    $this->registrationRepository = $registration_repository;
    $this->eventRepository = $event_repository;
    $this->emailValidator = $email_validator;
    $this->logger = $logger;
  }

  /**
   * Validates text fields (name, college, department).
   *
   * @param string $value
   *   The value to validate.
   * @param string $field_name
   *   The field name for error messages.
   *
   * @return string|null
   *   Error message or NULL if valid.
   */
  public function validateTextField($value, $field_name) {
    // Trim whitespace.
    $value = trim($value);

    // Check minimum length.
    if (mb_strlen($value) < 2) {
      return "$field_name must be at least 2 characters long.";
    }

    // Check maximum length.
    if (mb_strlen($value) > 255) {
      return "$field_name must not exceed 255 characters.";
    }

    // Allow letters (including Unicode), spaces, hyphens, apostrophes, and periods.
    // This supports international names like "O'Brien", "Jean-Paul", "José García".
    if (!preg_match("/^[\p{L}\s\-'.]+$/u", $value)) {
      return "$field_name must contain only letters, spaces, hyphens, apostrophes, and periods.";
    }

    return NULL;
  }

  /**
   * Validates email address.
   *
   * @param string $email
   *   The email address.
   *
   * @return bool
   *   TRUE if valid, FALSE otherwise.
   */
  public function validateEmail($email) {
    return $this->emailValidator->isValid($email);
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
  public function isDuplicate($email, $event_id) {
    return $this->registrationRepository->checkDuplicate($email, $event_id);
  }

  /**
   * Creates a new registration.
   *
   * @param array $registration_data
   *   Registration data array.
   *
   * @return int|bool
   *   Registration ID on success, FALSE on failure.
   *
   * @throws \InvalidArgumentException
   *   If required registration data is missing.
   * @throws \Drupal\Core\Database\DatabaseExceptionWrapper
   *   If database operation fails.
   */
  public function createRegistration(array $registration_data) {
    // Validate required fields.
    $required_fields = ['event_id', 'full_name', 'email', 'college', 'department'];
    foreach ($required_fields as $field) {
      if (empty($registration_data[$field])) {
        throw new \InvalidArgumentException("Missing required field: $field");
      }
    }

    try {
      $registration_data['created'] = \Drupal::time()->getRequestTime();
      $registration_id = $this->registrationRepository->create($registration_data);
      
      $this->logger->info('Registration created: @email for event @event_id (ID: @id)', [
        '@email' => $registration_data['email'],
        '@event_id' => $registration_data['event_id'],
        '@id' => $registration_id,
      ]);

      return $registration_id;
    }
    catch (\Drupal\Core\Database\DatabaseExceptionWrapper $e) {
      $this->logger->error('Database error creating registration: @message', [
        '@message' => $e->getMessage(),
      ]);
      throw $e;
    }
    catch (\Exception $e) {
      $this->logger->error('Unexpected error creating registration: @message', [
        '@message' => $e->getMessage(),
      ]);
      return FALSE;
    }
  }

  /**
   * Validates registration data.
   *
   * @param array $data
   *   Registration data.
   *
   * @return array
   *   Array of validation errors (empty if valid).
   */
  public function validateRegistrationData(array $data): array {
    $errors = [];

    // Validate text fields.
    $text_fields = [
      'full_name' => 'Full Name',
      'college' => 'College Name',
      'department' => 'Department',
    ];

    foreach ($text_fields as $field => $label) {
      if (!empty($data[$field])) {
        $error = $this->validateTextField($data[$field], $label);
        if ($error) {
          $errors[$field] = $error;
        }
      }
    }

    // Validate email.
    if (!empty($data['email']) && !$this->validateEmail($data['email'])) {
      $errors['email'] = 'Please enter a valid email address.';
    }

    // Check for duplicate.
    if (!empty($data['email']) && !empty($data['event_id'])) {
      if ($this->isDuplicate($data['email'], $data['event_id'])) {
        $errors['email'] = 'You have already registered for this event.';
      }
    }

    // Verify registration window.
    if (!empty($data['event_id'])) {
      if (!$this->eventRepository->isRegistrationOpen($data['event_id'])) {
        $errors['event_id'] = 'Registration is not currently open for this event.';
      }
    }

    return $errors;
  }

}
