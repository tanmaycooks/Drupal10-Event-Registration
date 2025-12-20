<?php

namespace Drupal\event_registration;

/**
 * Defines events for the Event Registration module.
 */
final class EventRegistrationEvents
{

    /**
     * Name of the event fired when a registration is created.
     *
     * @Event
     */
    const REGISTRATION_CREATED = 'event_registration.created';

}
