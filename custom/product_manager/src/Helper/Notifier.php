<?php

namespace Drupal\product_manager\Helper;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Mail\MailFormatHelper;
use Psr\Log\LoggerInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Class Notifier for sending email notification
 *
 * @package Drupal\product_manager\Helper
 */
class Notifier {

  use StringTranslationTrait;

  // Mail body template
  const MESSAGE_SUBJECT = 'New Product created : @product_title by @user_name';
  // Mail subject template
  const MESSAGE_BODY = 'New product @product_title was added by @user_name. Here is the product\'s description :<br/> @description';

  /**
   * Config factory.
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The current user account.
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The mail manager service.
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * The translation service.
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $translator;

  /**
   * The logger service.
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\Core\Session\AccountInterface $account
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   * @param \Psr\Log\LoggerInterface $logger
   */
  public function __construct(ConfigFactoryInterface $config_factory, AccountInterface $account, MailManagerInterface $mail_manager, TranslationInterface $string_translation, LoggerInterface $logger) {
    $this->configFactory = $config_factory;
    $this->account       = $account;
    $this->mailManager   = $mail_manager;
    $this->translator    = $string_translation;
    $this->logger        = $logger;
  }

  /**
   * Sends notification by email when new Product is created
   *
   * @param EntityInterface $entity
   * @param string $action
   */
  public function notify(EntityInterface $entity, $action) {
    $params       = $this->getEmailParameters($entity);
    $to           = $params['to'];
    $productTitle = $params['product_title'];
    // Remove unnecessary keys from the array
    unset($params['to']);
    unset($params['product_title']);
    $module   = 'product_manager';
    $key      = 'product_created';
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send     = TRUE;
    // Try sending the email
    $result   = $this->mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    // Log a message if the message can't be sent
    if ($result['result'] !== TRUE) {
      $errorMessage = $this->translator->translate(
        'There was a problem sending notification message. Product title : @product_title',
        ['@product_title' =>$productTitle]
      );
      $this->logger->error($errorMessage);
    }
  }

  /**
   * Build email parameters like subject, body, etc
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return array
   */
  public function getEmailParameters(EntityInterface $entity) {
    $config         = $this->configFactory->get('product_manager.settings');
    $siteConfig     = $this->configFactory->get('system.site');
    $recipientEmail = $config->get('recipient_email');
    $from           = $siteConfig->get('mail');
    // If no email was set for the module, use the site admin email
    if (empty($recipientEmail)) {
      $recipientEmail = $from;
    }
    // Compose our array to transmit to mail function
    $to           = $recipientEmail;
    $productTitle = $entity->label();
    $username     = $this->account->getAccountName();
    // For security remove html tags
    $description  = MailFormatHelper::htmlToText($entity->get('field_product_description')->value);

    $bodyParams   = [
      '@product_title' => $productTitle,
      '@user_name'     => $username,
      '@description'   => $description,
    ];
    $subjectParams = [
      '@product_title' => $productTitle,
      '@user_name'     => $username,
    ];
    $params = [
      'message' => $this->translator->translate(Notifier::MESSAGE_BODY, $bodyParams),
      'subject' => $this->translator->translate(Notifier::MESSAGE_SUBJECT, $subjectParams),
      'from'    => $from,
      'to' => $to,
      'product_title' =>$productTitle,
    ];

    return $params;
  }
}
