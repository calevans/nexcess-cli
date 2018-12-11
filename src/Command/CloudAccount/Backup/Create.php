<?php
/**
 * @package Nexcess-CLI
 * @license https://opensource.org/licenses/MIT
 * @copyright 2018 Nexcess.net, LLC
 */

declare(strict_types = 1);

namespace Nexcess\Sdk\Cli\Command\CloudAccount\Backup;

use Nexcess\Sdk\ {
  Resource\CloudAccount\Backup,
  Resource\CloudAccount\Endpoint,
  Resource\CloudAccount\CloudAccount,
  Resource\Promise,
  Util\Config,
  Util\Util
};

use Nexcess\Sdk\Cli\ {
  Command\CloudAccount\GetsCloudAccountChoices,
  Command\InputCommand,
  Console
};

use Symfony\Component\Console\ {
  Input\InputInterface as Input,
  Input\InputOption as Opt,
  Output\OutputInterface as Output
};

/**
 * Creates a new Cloud Account.
 */
class Create extends InputCommand {
  use GetsCloudAccountChoices;

  /** {@inheritDoc} */
  const ENDPOINT = Endpoint::class;

  /** {@inheritDoc} */
  const SUMMARY_KEYS = ['filename', 'complete'];

  /** {@inheritDoc} */
  const INPUTS = ['cloud_account_id' => Util::FILTER_INT];

  /** {@inheritDoc} */
  const NAME = 'cloud-account:backup:create';

  /** {@inheritDoc} */
  const OPTS = [
    'cloud-account-id|c' => [OPT::VALUE_REQUIRED],
    'download|d' => [OPT::VALUE_REQUIRED],
  ];

  /** {@inheritDoc} */
  const RESTRICT_TO = [Config::COMPANY_NEXCESS];

  /**
   * {@inheritDoc}
   */
  public function execute(Input $input, Output $output) {
    // create backup
    $app = $this->getConsole();
    $app->say($this->getPhrase('starting_backup'));

    $endpoint = $this->_getEndpoint();
    assert($endpoint instanceof Endpoint);

    $cloud_account_id = $this->getInput('cloud_account_id', false);
    $cloud = $endpoint->retrieve($cloud_account_id);
    assert($cloud instanceof CloudAccount);

    $backup = $endpoint->createBackup($cloud);
    $this->_saySummary($backup->toArray(), $input->getOption('json'));

    // wait for backup to complete and then download it?
    $download_path = $input->getOption('download');
    if (isset($download_path)) {
      $this->_downloadWhenComplete($backup, $download_path);
      return Console::EXIT_SUCCESS;
    }

    // wait for backup to complete?
    if ($input->getOption('wait')) {
      $this->_waitUntilComplete($backup);
      return Console::EXIT_SUCCESS;
    }

    // not waiting
    $app->say(
      $this->getPhrase(
        'backup_started',
        [
          'filename' => $backup->get('filename'),
          'cloud_account_id' => $cloud_account_id
        ]
      )
    );
    return Console::EXIT_SUCCESS;
  }

  /**
   * Waits for backup to complete and downloads the file.
   *
   * @param Backup $backup The backup to wait for
   * @param string $download_path The target download directory
   */
  protected function _downloadWhenComplete(
    Backup $backup,
    string $download_path
  ) : void {
    $app = $this->getConsole();
    $app->say($this->getPhrase('downloading'));

    $backup->whenComplete([Promise::OPT_TIMEOUT => 0])
      ->then(function ($backup) use ($download_path) {
        $backup->download($download_path);
      })
      ->wait();

    $app->say(
       $this->getPhrase(
         'download_complete',
         ['filename' => "{$download_path}/{$backup->get('filename')}"]
       )
    );

    $app->say($this->getPhrase('done'));
  }

  /**
   * {@inheritDoc}
   */
  protected function _getChoices(string $name, bool $format = true) : array {
    switch ($name) {
      case 'cloud_account_id':
        return $this->_getCloudAccountChoices($format);
      default:
        return parent::_getChoices($name, $format);
    }
  }

  /**
   * Waits for backup to complete.
   *
   * @param Backup $backup The backup to wait for
   */
  protected function _waitUntilComplete(Backup $backup) : void {
    $app = $this->getConsole();
    $app->say($this->getPhrase('waiting'));

    $backup->whenComplete([Promise::OPT_TIMEOUT => 0])->wait();

    $app->say(
      $this->getPhrase(
        'backup_complete',
        [
          'filename' => $backup->get('filename'),
          'cloud_account_id' => $backup->getCloudAccount()->getId()
        ]
      )
    );
  }
}
