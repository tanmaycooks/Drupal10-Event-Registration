<?php

namespace Drupal\event_registration\Service;

use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Render\RendererInterface;
use Psr\Log\LoggerInterface;

/**
 * Service for sending email notifications.
 */
class MailService {

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a MailService object.
   *
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   The mail manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.
   */
  public function __construct(
    MailManagerInterface $mail_manager,
    ConfigFactoryInterface $config_factory,
    RendererInterface $renderer,
    LoggerInterface $logger
  ) {
    $this->mailManager = $mail_manager;
    $this->configFactory = $config_factory;
    $this->renderer = $renderer;
    $this->logger = $logger;
  }

  /**
   * Sends registration confirmation emails.
   *
   * @param array $registration_data
   *   Registration data including email, full_name, event details.
   *
   * @return bool
   *   TRUE if emails sent successfully, FALSE otherwise.
   *
   * @throws \Exception
   *   Throws exception if user confirmation email fails (critical).
   */
  public function sendRegistrationEmails(array $registration_data) {
    $config = $this->configFactory->get('event_registration.settings');

    // Prepare email parameters.
    $params = [
      'full_name' => $registration_data['full_name'],
      'email' => $registration_data['email'],
      'event_name' => $registration_data['event_name'],
      'event_date' => $registration_data['event_date'],
      'category' => $registration_data['category'],
      'college' => $registration_data['college'] ?? '',
      'department' => $registration_data['department'] ?? '',
    ];

    // Send user confirmation email (CRITICAL - must succeed).
    $user_result = $this->mailManager->mail(
      'event_registration',
      'user_confirmation',
      $registration_data['email'],
      \Drupal::languageManager()->getCurrentLanguage()->getId(),
      $params,
      NULL,
      TRUE
    );

    if (!$user_result['result']) {
      $this->logger->error('CRITICAL: Failed to send user confirmation email to @email', [
        '@email' => $registration_data['email'],
      ]);
      // User confirmation is critical - throw exception.
      throw new \Exception('Failed to send confirmation email. Please contact support.');
    }

    $this->logger->info('User confirmation email sent to @email', [
      '@email' => $registration_data['email'],
    ]);

    // Send admin notification if enabled (non-critical).
    if ($config->get('admin_notification_enabled')) {
      $admin_email = $config->get('admin_email');
      
      if (!empty($admin_email)) {
        $admin_result = $this->mailManager->mail(
          'event_registration',
          'admin_notification',
          $admin_email,
          \Drupal::languageManager()->getCurrentLanguage()->getId(),
          $params,
          NULL,
          TRUE
        );

        if (!$admin_result['result']) {
          // Admin notification failure is non-critical - log but don't fail.
          $this->logger->warning('Failed to send admin notification email to @email', [
            '@email' => $admin_email,
          ]);
        }
        else {
          $this->logger->info('Admin notification email sent to @email', [
            '@email' => $admin_email,
          ]);
        }
      }
    }

    return TRUE;
  }

}
