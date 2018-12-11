<?php
/**
 * @package Nexcess-CLI
 * @license https://opensource.org/licenses/MIT
 * @copyright 2018 Nexcess.net, LLC
 */

declare(strict_types = 1);

namespace Nexcess\Sdk\Cli\Command;

use Nexcess\Sdk\Util\Util;
use Nexcess\Sdk\Cli\ {
  Command\InputCommand,
  Console
};
use Symfony\Component\Console\ {
  Input\InputInterface as Input,
  Input\InputOption as Opt,
  Output\OutputInterface as Output
};

/**
 * Base class for "show" commands.
 */
abstract class Show extends InputCommand {

  /** {@inheritDoc} */
  const OPTS = ['id' => [Opt::VALUE_REQUIRED]];

  /** {@inheritDoc} */
  const INPUTS = ['id' => Util::FILTER_INT];

  /**
   * {@inheritDoc}
   */
  public function execute(Input $input, Output $output) {
    $this->_saySummary(
      $this->_getEndpoint()->retrieve($this->getInput('id', false))->toArray(),
      $input->getOption('json')
    );

    return Console::EXIT_SUCCESS;
  }
}
