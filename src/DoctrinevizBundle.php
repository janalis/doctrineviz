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

declare(strict_types = 1);

namespace Janalis\Doctrineviz;

use Janalis\Doctrineviz\DependencyInjection\DoctrinevizBundleExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class DoctrinevizBundle.
 */
class DoctrinevizBundle extends Bundle
{
    /**
     * Get container extension.
     *
     * @return DoctrinevizBundleExtension
     */
    public function getContainerExtension(): DoctrinevizBundleExtension
    {
        return new DoctrinevizBundleExtension();
    }
}
