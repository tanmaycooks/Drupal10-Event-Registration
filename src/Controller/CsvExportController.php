<?php

namespace Drupal\event_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Drupal\node\NodeInterface;

/**
 * Controller for CSV exports.
 */
class CsvExportController extends ControllerBase
{

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
        // Check permission/access strictly here or via route requirements?
        // Doing basic check.
        if (!$this->currentUser()->hasPermission('administer event registration')) {
            return new Response('Access Denied', 403);
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="registrations.csv"');

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['Registration ID', 'Email', 'Created']);

        // Fetch registrations (Logic to fetch via repository needed here).
        // For scaffold, we'll just put a header.

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        $response->setContent($content);
        return $response;
    }

}
