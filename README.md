# Commander
A simple CLI tool for Drupal.

## Installation
```
composer require chi-teck/commander
```

## Usage
```
vendor/bin/commander
```

## Command Authoring
1. Create a command according to [symfony/console](https://symfony.com/doc/3.4/components/console.html) documentation.
2. Optionally, implement `Commander/DrupalAwareInterface` to indicate that the command needs fully bootstrapped Drupal installation.
3. Register the command in _composer.json_.
```
"extra": {
    "commands": ["Foo\\Command\\BarCommand"]
}
```

Command discovery is performed for all packages in _vendor_ directory, enabled modules and themes.

Dependencies can be injected via factory `create` method. For modules it is possible to register a command as service like follows.
```
services:
  Drupal\foo\Command\BarCommand:
    arguments: ['@entity_type.manager']
```

## Links
[#2242947: Integrate Symfony Console component to natively support command line operations](https://www.drupal.org/project/drupal/issues/2242947)

## License
GNU General Public License, version 2 or later.
