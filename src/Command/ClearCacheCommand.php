<?php

namespace Commander\Command;

use Commander\DrupalAwareInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Implements clear-cache console command.
 */
class ClearCacheCommand extends Command implements DrupalAwareInterface {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('clear-cache')
      ->setDescription('Clear all caches.')
      ->setAliases(['cc']);
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    drupal_flush_all_caches();
    $io = new SymfonyStyle($input, $output);
    $io->success('Cache rebuild complete.');
  }

}
