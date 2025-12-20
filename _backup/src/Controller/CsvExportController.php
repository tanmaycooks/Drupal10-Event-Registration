<?php

namespace Drupal\event_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\event_registration\Repository\RegistrationRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller for CSV export functionality.
 */
class CsvExportController extends ControllerBase
{

  /**
   * The registration repository.
   *
   * @var \Drupal\event_registration\Repository\RegistrationRepository
   */
  protected $registrationRepository;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a CsvExportController object.
   *
   * @param \Drupal\event_registration\Repository\RegistrationRepository $registration_repository
   *   The registration repository.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(
    RegistrationRepository $registration_repository,
    RequestStack $request_stack
  ) {
    $this->registrationRepository = $registration_repository;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('event_registration.registration_repository'),
      $container->get('request_stack')
    );
  }

  /**
   * Exports registrations to CSV.
   *
   * @return \Symfony\Component\HttpFoundation\StreamedResponse
   *   CSV file download response.
   */
  public function export()
  {
    // Get filters from query parameters.
    $filters = [];
    $request = $this->requestStack->getCurrentRequest();

    if ($request->query->has('event_date')) {
      $filters['event_date'] = $request->query->get('event_date');
    }
    if ($request->query->has('event_id')) {
      $filters['event_id'] = $request->query->get('event_id');
    }

    // Get registration data for export.
    $export_data = $this->registrationRepository->getRegistrationsForExport($filters);

    // Create streamed response for memory efficiency.
    $response = new StreamedResponse(function () use ($export_data) {
      $handle = fopen('php://output', 'w');

      try {
        // Add UTF-8 BOM for Excel compatibility.
        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Write header row.
        if (!empty($export_data)) {
          fputcsv($handle, array_keys($export_data[0]));
        }

        // Write data rows.
        foreach ($export_data as $row) {
          fputcsv($handle, $row);
        }
      } finally {
        // Ensure file handle is always closed.
        if (is_resource($handle)) {
          fclose($handle);
        }
      }
    });

    // Set headers for CSV download.
    $filename = 'event_registrations_' . date('Y-m-d_H-i-s') . '.csv';
    $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
    $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

    // Security headers (defense-in-depth).
    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $response->headers->set('X-Frame-Options', 'DENY');
    $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');

    return $response;
  }

}
