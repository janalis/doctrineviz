# doctrineviz

Render a graphical representation of a Doctrine2 mapped database.

## To do

- [ ] Add phpunit tests
- [ ] Add mongoDB support

## Installation

Require package through composer:
```bash
$ composer require janalis/doctrineviz
```

In order to be able to use the graph drawing feature you'll have to install GraphViz (dot executable).

### Mac OS

```bash
$ brew install graphviz
```

### Ubuntu

```bash
$ sudo apt install -y graphviz
```

### Windows

Windows users may [download GraphViZ for Windows](http://www.graphviz.org/Download_windows.php).

## Use

Into your symfony project:
```bash
$ php app/console doctrine:generate:viz
```

## Credits

- [globalcitizen/mysqlviz](https://github.com/globalcitizen/mysqlviz)
- [graphp/graphviz](https://github.com/graphp/graphviz)