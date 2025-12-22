<?php

namespace Drupal\event_registration\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Registration entities.
 *
 * @ingroup event_registration
 */
interface RegistrationInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface
{

    /**
     * Gets the registration creation timestamp.
     *
     * @return int
     *   Creation timestamp of the registration.
     */
    public function getCreatedTime();

    /**
     * Sets the registration creation timestamp.
     *
     * @param int $timestamp
     *   The registration creation timestamp.
     *
     * @return \Drupal\event_registration\Entity\RegistrationInterface
     *   The called Registration entity.
     */
    public function setCreatedTime($timestamp);

    /**
     * Gets the registrant email.
     *
     * @return string
     *   The registrant email.
     */
    public function getEmail();

    /**
     * Sets the registrant email.
     *
     * @param string $email
     *   The registrant email.
     *
     * @return \Drupal\event_registration\Entity\RegistrationInterface
     *   The called Registration entity.
     */
    public function setEmail($email);

    /**
     * Gets the event ID.
     *
     * @return int
     *   The event ID.
     */
    public function getEventId();

    /**
     * Sets the event ID.
     *
     * @param int $eid
     *   The event ID.
     *
     * @return \Drupal\event_registration\Entity\RegistrationInterface
     *   The called Registration entity.
     */
    public function setEventId($eid);

}
