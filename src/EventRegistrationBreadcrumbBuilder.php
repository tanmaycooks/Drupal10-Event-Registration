<?php

namespace Drupal\event_registration;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides a breadcrumb builder for event registration pages.
 */
class EventRegistrationBreadcrumbBuilder implements BreadcrumbBuilderInterface
{

    use StringTranslationTrait;

    /**
     * {@inheritdoc}
     */
    public function applies(RouteMatchInterface $route_match)
    {
        return in_array($route_match->getRouteName(), ['event_registration.register', 'event_registration.export']);
    }

    /**
     * {@inheritdoc}
     */
    public function build(RouteMatchInterface $route_match)
    {
        $breadcrumb = new Breadcrumb();
        $breadcrumb->addLink(Link::createFromRoute($this->t('Home'), '<front>'));

        $event = $route_match->getParameter('event') ?? $route_match->getParameter('node');
        if ($event) {
            $breadcrumb->addLink($event->toLink());
        }

        return $breadcrumb;
    }

}
