<?php

namespace Drupal\event_registration\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\event_registration\Repository\RegistrationRepository;
use Drupal\event_registration\CapacityManagerInterface;

/**
 * Manages event capacity logic.
 */
class CapacityManager implements CapacityManagerInterface
{

    /**
     * The registration repository.
     *
     * @var \Drupal\event_registration\Repository\RegistrationRepository
     */
    protected $repository;

    /**
     * The entity type manager.
     *
     * @var \Drupal\Core\Entity\EntityTypeManagerInterface
     */
    protected $entityTypeManager;

    /**
     * Application constructor.
     *
     * @param \Drupal\event_registration\Repository\RegistrationRepository $repository
     *   The registration repository.
     * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
     *   The entity type manager.
     */
    public function __construct(RegistrationRepository $repository, EntityTypeManagerInterface $entity_type_manager)
    {
        $this->repository = $repository;
        $this->entityTypeManager = $entity_type_manager;
    }

    /**
     * Check if an event has space.
     *
     * @param \Drupal\node\NodeInterface $event
     *   The event node.
     *
     * @return bool
     *   TRUE if space is available, FALSE otherwise.
     */
    public function hasSpace($event)
    {
        $capacity = $event->get('field_capacity')->value;
        // Unlimited capacity if 0 or null? Let's assume 0 means unlimited for now? 
        // Or strictly check value.
        if (empty($capacity)) {
            return TRUE;
        }

        $count = $this->repository->countRegistrations($event->id());
        return $count < $capacity;
    }

    /**
     * Get remaining spots.
     *
     * @param \Drupal\node\NodeInterface $event
     *   The event node.
     *
     * @return int
     *   Number of remaining spots.
     */
    public function getRemainingSpots($event)
    {
        $capacity = $event->get('field_capacity')->value;
        if (empty($capacity)) {
            return 9999;
        }
        $count = $this->repository->countRegistrations($event->id());
        return max(0, $capacity - $count);
    }

}
