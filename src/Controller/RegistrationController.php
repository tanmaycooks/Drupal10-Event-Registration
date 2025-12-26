<?php

namespace Drupal\event_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for event registration pages.
 */
class RegistrationController extends ControllerBase
{

    /**
     * Display a thank you page.
     */
    public function thankYou()
    {
        $event = \Drupal::routeMatch()->getParameter('event');
        return [
            '#theme' => 'event_registration_thank_you',
            '#event' => $event,
        ];
    }

}
