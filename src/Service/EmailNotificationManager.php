<?php

namespace Drupal\event_registration\Service;

use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Manages email notifications for event registration.
 */
class EmailNotificationManager
{

    use StringTranslationTrait;

    /**
     * The mail manager.
     *
     * @var \Drupal\Core\Mail\MailManagerInterface
     */
    protected $mailManager;

    /**
     * Constructor.
     *
     * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
     *   The mail manager.
     */
    public function __construct(MailManagerInterface $mail_manager)
    {
        $this->mailManager = $mail_manager;
    }

    /**
     * Sends a confirmation email.
     *
     * @param string $to
     *   The recipient email.
     * @param array $params
     *   Email parameters.
     */
    public function sendConfirmation($to, array $params = [])
    {
        $langcode = \Drupal::currentUser()->getPreferredLangcode();
        $result = $this->mailManager->mail('event_registration', 'confirmation', $to, $langcode, $params, NULL, TRUE);
        return $result['result'];
    }

}
