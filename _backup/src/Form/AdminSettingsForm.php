<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\EmailValidatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configuration form for Event Registration module settings.
 */
class AdminSettingsForm extends ConfigFormBase {

  /**
   * The email validator.
   *
   * @var \Drupal\Component\Utility\EmailValidatorInterface
   */
  protected $emailValidator;

  /**
   * Constructs an AdminSettingsForm object.
   *
   * @param \Drupal\Component\Utility\EmailValidatorInterface $email_validator
   *   The email validator.
   */
  public function __construct(EmailValidatorInterface $email_validator) {
    $this->emailValidator = $email_validator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('email.validator')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['event_registration.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'event_registration_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('event_registration.settings');

    $form['admin_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Admin notification email'),
      '#description' => $this->t('Email address to receive registration notifications.'),
      '#default_value' => $config->get('admin_email'),
    ];

    $form['admin_notification_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable admin notifications'),
      '#description' => $this->t('Send email notifications to admin when users register.'),
      '#default_value' => $config->get('admin_notification_enabled'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $admin_email = $form_state->getValue('admin_email');
    $enabled = $form_state->getValue('admin_notification_enabled');

    // Validate email if notifications are enabled.
    if ($enabled && !empty($admin_email)) {
      if (!$this->emailValidator->isValid($admin_email)) {
        $form_state->setErrorByName('admin_email', $this->t('Please enter a valid email address.'));
      }
    }

    // Require email if notifications are enabled.
    if ($enabled && empty($admin_email)) {
      $form_state->setErrorByName('admin_email', $this->t('Admin email is required when notifications are enabled.'));
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    try {
      $this->config('event_registration.settings')
        ->set('admin_email', $form_state->getValue('admin_email'))
        ->set('admin_notification_enabled', $form_state->getValue('admin_notification_enabled'))
        ->save();

      parent::submitForm($form, $form_state);
    }
    catch (\Exception $e) {
      $this->messenger()->addError($this->t('Failed to save configuration: @message', [
        '@message' => $e->getMessage(),
      ]));
      \Drupal::logger('event_registration')->error('Configuration save failed: @message', [
        '@message' => $e->getMessage(),
      ]);
    }
  }

}
