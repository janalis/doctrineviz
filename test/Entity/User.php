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

namespace Janalis\Doctrineviz\Test\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="user")
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $firstName;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $lastName;

    /**
     * @ORM\Column(name="email_id", type="integer")
     */
    protected $idEmail;

    /**
     * @ORM\OneToOne(targetEntity="Email")
     * @ORM\JoinColumn(name="email_id", nullable=false, referencedColumnName="id")
     */
    protected $email;

    /**
     * @ORM\ManyToOne(targetEntity="Address", inversedBy="users")
     */
    protected $address;

    /**
     * @ORM\ManyToMany(targetEntity="Group", mappedBy="users")
     */
    protected $groups;
}
