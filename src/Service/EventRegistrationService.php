<?php

namespace Drupal\event_registration\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\NodeInterface;

/**
 * Service for handling event registrations.
 */
class EventRegistrationService
{

    /**
     * The entity type manager.
     *
     * @var \Drupal\Core\Entity\EntityTypeManagerInterface
     */
    protected $entityTypeManager;

    /**
     * The capacity manager.
     *
     * @var \Drupal\event_registration\Service\CapacityManager
     */
    protected $capacityManager;

    /**
     * The email manager.
     *
     * @var \Drupal\event_registration\Service\EmailNotificationManager
     */
    protected $emailManager;

    /**
     * The current user.
     *
     * @var \Drupal\Core\Session\AccountProxyInterface
     */
    protected $currentUser;

    /**
     * Constructor.
     *
     * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
     *   The entity type manager.
     * @param \Drupal\event_registration\Service\CapacityManager $capacity_manager
     *   The capacity manager.
     * @param \Drupal\Core\Session\AccountProxyInterface $current_user
     *   The current user.
     * @param \Drupal\event_registration\Service\EmailNotificationManager $email_manager
     *   The email manager.
     */
    public function __construct(EntityTypeManagerInterface $entity_type_manager, CapacityManager $capacity_manager, AccountProxyInterface $current_user, EmailNotificationManager $email_manager)
    {
        $this->entityTypeManager = $entity_type_manager;
        $this->capacityManager = $capacity_manager;
        $this->currentUser = $current_user;
        $this->emailManager = $email_manager;
    }

    /**
     * Validates if a user can register for an event.
     *
     * @param \Drupal\node\NodeInterface $event
     *   The event node.
     * @param \Drupal\Core\Session\AccountProxyInterface $account
     *   The user account (optional).
     *
     * @return bool
     *   TRUE if valid.
     */
    public function validateRegistration(NodeInterface $event, AccountProxyInterface $account = NULL)
    {
        // Check if event is open.
        if (!$event->get('field_registration_open')->value) {
            return FALSE;
        }

        // Check capacity.
        if (!$this->capacityManager->hasSpace($event)) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Registers a user for an event.
     *
     * @param \Drupal\node\NodeInterface $event
     *   The event node.
     * @param \Drupal\Core\Session\AccountProxyInterface $account
     *   The user account.
     *
     * @return \Drupal\event_registration\Entity\RegistrationInterface
     *   The registration entity.
     *
     * @throws \Drupal\event_registration\Exception\RegistrationException
     *   If registration fails for any reason.
     */
    public function register(NodeInterface $event, AccountProxyInterface $account)
    {
        if (!$this->validateRegistration($event, $account)) {
            throw new RegistrationException("Registration validation failed.");
        }

        try {
            $storage = $this->entityTypeManager->getStorage('event_registration');

            // Create registration.
            $registration = $storage->create([
                'eid' => $event->id(),
                'uid' => $account->id(),
                'email' => $account->getEmail(),
            ]);

            $registration->save();

            // Send confirmation.
            $this->emailManager->sendConfirmation($account->getEmail(), ['node' => $event]);

            \Drupal::logger('event_registration')->notice('User @uid registered for event @eid', [
                '@uid' => $account->id(),
                '@eid' => $event->id(),
            ]);

            return $registration;
        } catch (\Exception $e) {
            \Drupal::logger('event_registration')->error($e->getMessage());
            throw new RegistrationException("System error during registration.", 0, $e);
        }
    }

    /**
     * Cancels a registration.
     *
     * @param \Drupal\event_registration\Entity\RegistrationInterface $registration
     *   The registration to cancel.
     */
    public function cancelRegistration($registration)
    {
        \Drupal::logger('event_registration')->notice('Canceling registration @id', ['@id' => $registration->id()]);
        $registration->delete();
    }

}
