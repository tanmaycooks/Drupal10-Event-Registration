<?php

namespace Drupal\event_registration\Repository;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Repository for Registration entities.
 */
class RegistrationRepository
{

    /**
     * The entity type manager.
     *
     * @var \Drupal\Core\Entity\EntityTypeManagerInterface
     */
    protected $entityTypeManager;

    /**
     * Application constructor.
     *
     * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
     *   The entity type manager.
     */
    public function __construct(EntityTypeManagerInterface $entity_type_manager)
    {
        $this->entityTypeManager = $entity_type_manager;
    }

    /**
     * Get registration storage.
     *
     * @return \Drupal\Core\Entity\EntityStorageInterface
     *   The registration storage.
     */
    protected function getStorage()
    {
        return $this->entityTypeManager->getStorage('event_registration');
    }

}
