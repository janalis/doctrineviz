<?php

/*
 * This file is part of the doctrineviz package
 *
 * Copyright (c) 2017 Pierre Hennequart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Pierre Hennequart <pierre@janalis.com>
 */

declare(strict_types=1);

namespace Janalis\Doctrineviz\Graphviz;

use Symfony\Component\Process\Process;

/**
 * Graphviz.
 *
 * This class handles actions relating to graphviz dot binary.
 * Most of its code was stolen from https://github.com/graphp/graphviz distributed under MIT licence whose copyright notice is:
 * Copyright (c) 2012+ Christian Lück (Maintainer)
 * Copyright (c) 2012+ Fhaculty Core Team and our awesome contributors <https://github.com/clue/graph/graphs/contributors>
 */
class Graphviz
{
    /** @var string */
    protected $format;

    /** @var string */
    protected $binary;

    /**
     * Graphviz constructor.
     *
     * @param string $format
     * @param string $binary
     */
    public function __construct(string $format = 'png', string $binary = null)
    {
        $this->format = $format;
        $this->binary = $binary ?: 'dot'.(0 === stripos(PHP_OS, 'WIN') ? '.exe' : '');
    }

    /**
     * Create image file.
     *
     * @param GraphInterface $graph to display
     *
     * @throws \RuntimeException on error
     *
     * @return string filename
     */
    public function createImageFile(GraphInterface $graph): string
    {
        // @codeCoverageIgnoreStart
        if (false === $tmp = tempnam(sys_get_temp_dir(), 'doctrineviz')) {
            throw new \RuntimeException('Unable to get temporary file name for graphviz script');
        }
        if (false === file_put_contents($tmp, (string) $graph, LOCK_EX)) {
            throw new \RuntimeException('Unable to write graphviz script to temporary file');
        }
        $path = "$tmp.{$this->format}";
        $this->execute(
            '%s -T %s %s -o %s',
            $this->binary,
            $this->format,
            $tmp,
            $path
        );
        // @codeCoverageIgnoreEnd

        return $path;
    }

    /**
     * Display.
     *
     * @param GraphInterface $graph to display
     */
    public function display(GraphInterface $graph): void
    {
        // @codeCoverageIgnoreStart
        switch (true) {
            case 0 === stripos(PHP_OS, 'WIN'):
                $binary = 'start';

                break;
            case 'DARWIN' === strtoupper(PHP_OS):
                $binary = 'open';

                break;
            default:
                $binary = 'xdg-open';
        }
        $path = $this->createImageFile($graph);
        $this->execute(
            '%s %s',
            $binary,
            $path
        );
        // @codeCoverageIgnoreEnd
    }

    /**
     * Create image data.
     *
     * @param GraphInterface $graph to display
     *
     * @return string
     */
    public function createImageData(GraphInterface $graph): string
    {
        if ('dot' === $this->format) {
            return (string) $graph;
        }
        $path = $this->createImageFile($graph);
        $data = file_get_contents($path);
        unlink($path);

        return $data;
    }

    /**
     * Create image src.
     *
     * @param GraphInterface $graph to display
     *
     * @return string
     */
    public function createImageSrc(GraphInterface $graph): string
    {
        $format = ('svg' === $this->format || 'svgz' === $this->format) ? 'svg+xml' : $this->format;

        return 'data:image/'.$format.';base64,'.base64_encode($this->createImageData($graph));
    }

    /**
     * Create image html.
     *
     * @param GraphInterface $graph to display
     *
     * @return string
     */
    public function createImageHtml(GraphInterface $graph): string
    {
        if ('svg' === $this->format || 'svgz' === $this->format) {
            return '<object type="image/svg+xml" data="'.$this->createImageSrc($graph).'"></object>';
        }

        return '<img src="'.$this->createImageSrc($graph).'" />';
    }

    /**
     * Executes a command.
     *
     * @param string $format  of the command
     * @param array  ...$args to escape for shell
     *
     * @return Process
     */
    protected function execute(string $format, ...$args): Process
    {
        $process = new Process(sprintf($format, ...array_map(function ($arg, $index) use ($format) {
            return 0 === $index && 0 === strpos('%s', $format) ? escapeshellcmd($arg) : escapeshellarg($arg);
        }, $args, array_keys($args))));
        $process->mustRun();

        return $process;
    }

    /**
     * Get format.
     *
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * Set format.
     *
     * @param string $format
     */
    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    /**
     * Get binary.
     *
     * @return string
     */
    public function getBinary(): string
    {
        return $this->binary;
    }

    /**
     * Set binary.
     *
     * @param string $binary
     */
    public function setBinary($binary): void
    {
        $this->binary = $binary;
    }
}
