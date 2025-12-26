<?php

namespace Drupal\event_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Drupal\event_registration\Repository\RegistrationRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\NodeInterface;

/**
 * Controller for CSV exports.
 */
class CsvExportController extends ControllerBase
{

    /**
     * The registration repository.
     *
     * @var \Drupal\event_registration\Repository\RegistrationRepository
     */
    protected $repository;

    /**
     * Constructor.
     *
     * @param \Drupal\event_registration\Repository\RegistrationRepository $repository
     *   The registration repository.
     */
    public function __construct(RegistrationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('event_registration.repository')
        );
    }

    /**
     * Export registrations for an event.
     *
     * @param \Drupal\node\NodeInterface $event
     *   The event node.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *   The CSV response.
     */
    public function export(NodeInterface $event)
    {
        if (!$this->currentUser()->hasPermission('export event registrations')) {
            return new Response('Access Denied', 403);
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="registrations_' . $event->id() . '.csv"');

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['Registration ID', 'Email', 'Created', 'User ID']);

        $registrations = $this->repository->getRegistrations($event->id());
        foreach ($registrations as $registration) {
            fputcsv($handle, [
                $registration->id(),
                $registration->getEmail(),
                date('Y-m-d H:i:s', $registration->getCreatedTime()),
                $registration->getOwnerId(),
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        $response->setContent($content);
        return $response;
    }

}
