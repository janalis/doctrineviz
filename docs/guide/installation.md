# Installation

## Requirements

- PHP 8.4 or newer within PHP 8.x.
- Symfony 7.4 or 8.x, DoctrineBundle 3.3+, and Doctrine DBAL 4.5+.
- A configured Doctrine DBAL connection and its PHP database driver.
- Graphviz only when rendering DOT to an image or PDF.

Doctrine ORM is not required. The bundle reads schema metadata from the database;
it does not generate SQL migrations or create tables.

## Install and register the bundle

```sh
composer require --dev janalis/doctrineviz
```

Add this entry alongside your existing bundles in `config/bundles.php`:

```php
Janalis\Doctrineviz\DoctrinevizBundle::class => ['dev' => true, 'test' => true],
```

Keep DoctrineBundle enabled and configure connections in the host application.
No doctrineviz configuration section is needed. Use a database account that can
read schema metadata; doctrineviz does not query application rows or write schema changes.

```sh
php bin/console doctrine:schema:graphviz --help
```

## Install Graphviz

```sh
brew install graphviz        # macOS
sudo apt install graphviz    # Debian / Ubuntu
```

On other systems, use the [Graphviz downloads](https://graphviz.org/download/).
The `dot` executable is separate from the Composer package.
