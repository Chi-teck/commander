<?php

namespace Commander;

use Composer\Autoload\ClassLoader;
use Drupal\Core\DrupalKernel;
use DrupalFinder\DrupalFinder;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Console commander.
 */
class Commander {

  /**
   * The class loader.
   *
   * @var \Composer\Autoload\ClassLoader
   */
  private $classLoader;

  /**
   * The Drupal finder.
   *
   * @var \DrupalFinder\DrupalFinder
   */
  private $drupalFinder;

  /**
   * Construct a BootstrapHandler object.
   */
  public function __construct(ClassLoader $class_loader, DrupalFinder $drupal_finder) {
    $this->classLoader = $class_loader;
    $this->drupalFinder = $drupal_finder;
  }

  /**
   * Runs the console application.
   */
  public function run(): void {
    $container = $this->bootstrap();
    $commands = $this->getCommands($container);

    $application = new Application('Drupal', \Drupal::VERSION);
    $application->addCommands($commands);
    $application->run();
  }

  /**
   * Bootstraps Drupal.
   *
   * @return \Symfony\Component\DependencyInjection\ContainerInterface|null
   *   Current service container or null if bootstrap failed.
   */
  private function bootstrap(): ?ContainerInterface {
    try {
      $request = Request::createFromGlobals();
      $kernel = DrupalKernel::createFromRequest($request, $this->classLoader, 'prod');
      $kernel->boot();
      $kernel->preHandle($request);
      return $kernel->getContainer();
    }
    catch (\Exception $exception) {
      return NULL;
    }
  }

  /**
   * Finds and instantiates console commands.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface|null $container
   *   The service container or null if Drupal is not booted.
   *
   * @return \Symfony\Component\Console\Command\Command[]
   *   Array of console commands.
   */
  private function getCommands(?ContainerInterface $container): array {
    $commands = [];

    foreach ($this->getComposerFiles($container) as $file) {
      $data_encoded = file_get_contents($file);
      $data = json_decode($data_encoded);
      if (!isset($data->extra->commands)) {
        continue;
      }

      foreach ($data->extra->commands as $command_class) {
        if ($container) {
          $commands[] = $container
            ->get('class_resolver')
            ->getInstanceFromDefinition($command_class);
        }
        elseif (!is_subclass_of($command_class, DrupalAwareInterface::class)) {
          $commands[] = new $command_class();
        }
      }
    }

    if ($container) {
      $container->get('module_handler')->alter('commands', $commands);
    }

    return $commands;
  }

  /**
   * Returns list of composer.json files to decode.
   */
  private function getComposerFiles(?ContainerInterface $container): array {

    if (!$this->drupalFinder->locateRoot(getcwd())) {
      throw new \RuntimeException('Could not locate Drupal root');
    }

    $composer_files[] = __DIR__ . '/../composer.json';
    $composer_files[] = $this->drupalFinder->getComposerRoot() . '/composer.json';

    $vendor_files = glob($this->drupalFinder->getVendorDIr() . '/*/*/composer.json');
    $composer_files = array_merge($composer_files, $vendor_files);

    if ($container) {
      $module_handler = $container->get('module_handler');
      foreach ($module_handler->getModuleDirectories() as $directory) {
        $composer_files[] = $directory . '/composer.json';
      }
      $theme_handler = $container->get('theme_handler');
      $drupal_root = $this->drupalFinder->getDrupalRoot();
      foreach ($theme_handler->listInfo() as $theme) {
        $composer_files[] = $drupal_root . '/' . $theme->getPath() . '/composer.json';
      }
    }

    return array_filter($composer_files, 'file_exists');
  }

}
