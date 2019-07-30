<?php

namespace Commander\Command;

use Commander\DrupalAwareInterface;
use Drupal\Core\CronInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements run-cron console command.
 */
class RunCronCommand extends Command implements DrupalAwareInterface, ContainerInjectionInterface {

  /**
   * The Drupal Cron service.
   *
   * @var \Drupal\Core\CronInterface
   */
  protected $cron;

  /**
   * Constructs RunCronDrupalCommand object.
   *
   * @param \Drupal\Core\CronInterface $cron
   *   The Drupal Cron service.
   */
  public function __construct(CronInterface $cron) {
    parent::__construct();
    $this->cron = $cron;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('cron'));
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('run-cron')
      ->setAliases(['cron'])
      ->setDescription('Run cron.');
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $io = new SymfonyStyle($input, $output);
    if ($this->cron->run()) {
      $io->success('Cron run completed.');
    }
    else {
      $io->getErrorStyle()->error('Cron run failed.');
      return 1;
    }
  }

}
