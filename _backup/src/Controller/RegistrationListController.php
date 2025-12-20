<?php

namespace Drupal\event_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\event_registration\Repository\RegistrationRepository;
use Drupal\event_registration\Form\RegistrationFilterForm;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Controller for registration listing page.
 */
class RegistrationListController extends ControllerBase {

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
   * Constructs a RegistrationListController object.
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
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('event_registration.registration_repository'),
      $container->get('request_stack')
    );
  }

  /**
   * Displays the registration listing page.
   *
   * @return array
   *   Render array.
   */
  public function listRegistrations() {
    $build = [];

    // Add filter form.
    $filter_form = \Drupal::formBuilder()->getForm(RegistrationFilterForm::class);
    $build['filter_form'] = $filter_form;

    // Get filters from form state or session.
    $filters = [];
    $request = $this->requestStack->getCurrentRequest();
    
    if ($request->query->has('event_date')) {
      $filters['event_date'] = $request->query->get('event_date');
    }
    if ($request->query->has('event_id')) {
      $filters['event_id'] = $request->query->get('event_id');
    }

    // Get registrations.
    $registrations = $this->registrationRepository->getRegistrations($filters);
    $total_count = $this->registrationRepository->getRegistrationCount($filters);

    // Build table.
    $header = [
      $this->t('ID'),
      $this->t('Full Name'),
      $this->t('Email'),
      $this->t('College'),
      $this->t('Department'),
      $this->t('Event Name'),
      $this->t('Category'),
      $this->t('Event Date'),
      $this->t('Registration Date'),
    ];

    $rows = [];
    foreach ($registrations as $registration) {
      $rows[] = [
        $registration->id ?? '',
        $registration->full_name ?? '',
        $registration->email ?? '',
        $registration->college ?? '',
        $registration->department ?? '',
        $registration->event_name ?? '',
        $registration->category ?? '',
        $registration->event_date ?? '',
        isset($registration->created) ? date('Y-m-d H:i:s', $registration->created) : '',
      ];
    }

    $build['summary'] = [
      '#markup' => '<div class="registration-summary"><strong>' . 
        $this->t('Total Participants: @count', ['@count' => $total_count]) . 
        '</strong></div>',
    ];

    $build['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No registrations found.'),
      '#attributes' => ['class' => ['registration-listing-table']],
    ];

    // Add export link.
    $export_url = \Drupal\Core\Url::fromRoute('event_registration.csv_export', [], [
      'query' => $filters,
    ]);
    
    $build['export'] = [
      '#type' => 'link',
      '#title' => $this->t('Export to CSV'),
      '#url' => $export_url,
      '#attributes' => [
        'class' => ['button', 'button--primary'],
      ],
    ];

    return $build;
  }

}
