<?php

namespace Commander\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Implements site-status console command.
 */
class SiteStatusCommand extends Command implements ContainerAwareInterface {

  use ContainerAwareTrait;

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('site-status')
      ->setDescription('An overview of the site environment.')
      ->setAliases(['st']);
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $rows = [
      ['Drupal version', \Drupal::VERSION],
      ['Drupal root', DRUPAL_ROOT],
    ];

    if ($this->container) {
      $config = $this->container->get('config.factory');

      $rows[] = ['Site name', $config->get('system.site')->get('name')];

      $db_options = $this->container->get('database')->getConnectionOptions();
      $rows[] = ['DB name', $db_options['database']];
      $rows[] = ['DB user', $db_options['username']];
      $rows[] = ['DB host', $db_options['host']];
      $rows[] = ['DB driver', $db_options['driver']];

      $rows[] = ['Default theme', $config->get('system.theme')->get('default')];
      $rows[] = ['Admin theme', $config->get('system.theme')->get('admin')];
    }

    $rows[] = ['PHP binary', PHP_BINARY];
    $rows[] = ['PHP OS', PHP_OS];
    $rows[] = ['PHP version', PHP_VERSION];
    $rows[] = ['PHP config', php_ini_loaded_file()];

    $io = new SymfonyStyle($input, $output);
    $io->table([], $rows);
  }

}
