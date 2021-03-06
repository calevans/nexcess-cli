<?php
/**
 * @package Nexcess-CLI
 * @license https://opensource.org/licenses/MIT
 * @copyright 2018 Nexcess.net, LLC
 */

declare(strict_types = 1);

namespace Nexcess\Sdk\Cli\Command;

use Nexcess\Sdk\Exception;

class ChoiceException extends Exception {

  /** @var int No cloud accounts to choose from. */
  const NO_CLOUD_ACCOUNT_CHOICES = 1;

  /** @var int No cloud account backups to choose from. */
  const NO_BACKUP_CHOICES = 2;

  /** @var int No cloud account service packages to choose from. */
  const NO_CLOUD_ACCOUNT_PACKAGE_CHOICES = 3;

  /** {@inheritDoc} */
  const INFO = [
    self::NO_BACKUP_CHOICES =>
      ['message' => 'console.cloud_account.exception.no_backup_choices'],
    self::NO_CLOUD_ACCOUNT_CHOICES => [
      'message' => 'console.cloud_account.exception.no_cloud_account_choices'
    ],
    self::NO_CLOUD_ACCOUNT_PACKAGE_CHOICES => [
      'message' =>
        'console.cloud_account.choices.no_cloud_account_package_choices'
    ]
  ];
}
