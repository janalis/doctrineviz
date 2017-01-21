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

namespace Janalis\Doctrineviz\Graphviz;

/**
 * Graphviz.
 *
 * This class handles actions relating to graphviz dot binary.
 * Most of its code was stolen from https://github.com/graphp/graphviz distributed under MIT licence wose copyrignt notice is:
 * Copyright (c) 2012+ Christian Lück (Maintainer)
 * Copyright (c) 2012+ Fhaculty Core Team and our awesome contributors <https://github.com/clue/graph/graphs/contributors>
 */
class Graphviz
{
    const DELAY_OPEN = 2.0;

    /** @var string */
    protected $format;

    /** @var string */
    protected $executable;

    /**
     * Graphviz constructor.
     */
    public function __construct()
    {
        $this->format = 'png';
        $this->executable = 'dot'.('WIN' === strtoupper(substr(PHP_OS, 0, 3)) ? '.exe' : '');
    }

    /**
     * Create image file.
     *
     * @param Graph $graph to display
     *
     * @return string filename
     *
     * @throws \UnexpectedValueException on error
     */
    public function createImageFile(Graph $graph)
    {
        if (false === $tmp = tempnam(sys_get_temp_dir(), 'graphviz')) {
            throw new UnexpectedValueException('Unable to get temporary file name for graphviz script');
        }
        if (false === file_put_contents($tmp, (string) $graph, LOCK_EX)) {
            throw new UnexpectedValueException('Unable to write graphviz script to temporary file');
        }
        system(escapeshellarg($this->executable).' -T '.escapeshellarg($this->format).' '.escapeshellarg($tmp).' -o '.escapeshellarg($tmp.'.'.$this->format), $return_var);
        if (0 !== $return_var) {
            throw new UnexpectedValueException('Unable to invoke "'.$executable.'" to create image file (code '.$ret.')');
        }
        unlink($tmp);

        return $tmp.'.'.$this->format;
    }

    /**
     * Display.
     *
     * @param Graph $graph to display
     */
    public function display(Graph $graph)
    {
        $path = $this->createImageFile($graph);
        static $next = 0;
        if ($next > microtime(true)) {
            // wait some time between calling xdg-open because earlier calls will be ignored otherwise
            sleep(self::DELAY_OPEN);
        }
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // open image in untitled, temporary background shell
            exec('start "" '.escapeshellarg($path).' >NUL');
        } elseif (strtoupper(PHP_OS) === 'DARWIN') {
            // open image in background (redirect stdout to /dev/null, sterr to stdout and run in background)
            exec('open '.escapeshellarg($path).' > /dev/null 2>&1 &');
        } else {
            // open image in background (redirect stdout to /dev/null, sterr to stdout and run in background)
            exec('xdg-open '.escapeshellarg($path).' > /dev/null 2>&1 &');
        }
        $next = microtime(true) + self::DELAY_OPEN;
    }

    /**
     * Create image data.
     *
     * @param Graph $graph to display
     *
     * @return string
     */
    public function createImageData(Graph $graph)
    {
        $file = $this->createImageFile($graph);
        $data = file_get_contents($file);
        unlink($file);

        return $data;
    }

    /**
     * Create image src.
     *
     * @param Graph $graph to display
     *
     * @return string
     */
    public function createImageSrc(Graph $graph)
    {
        $format = ($this->format === 'svg' || $this->format === 'svgz') ? 'svg+xml' : $this->format;

        return 'data:image/'.$format.';base64,'.base64_encode($this->createImageData($graph));
    }

    /**
     * Create image html.
     *
     * @param Graph $graph to display
     *
     * @return string
     */
    public function createImageHtml(Graph $graph)
    {
        if ($this->format === 'svg' || $this->format === 'svgz') {
            return '<object type="image/svg+xml" data="'.$this->createImageSrc($graph).'"></object>';
        }

        return '<img src="'.$this->createImageSrc($graph).'" />';
    }

    /**
     * Get format.
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Set format.
     *
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * Get executable.
     *
     * @return string
     */
    public function getExecutable()
    {
        return $this->executable;
    }

    /**
     * Set executable.
     *
     * @param string $executable
     */
    public function setExecutable($executable)
    {
        $this->executable = $executable;
    }
}
