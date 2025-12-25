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
     * Display a thank you page (optional).
     */
    public function thankYou()
    {
        return [
            '#markup' => $this->t('Thank you for registering!'),
        ];
    }

}
