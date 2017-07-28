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

namespace Janalis\Doctrineviz\Test\Command;

use Janalis\Doctrineviz\Command\DoctrinevizCommand;
use Janalis\Doctrineviz\Test\DoctrinevizTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Doctrineviz command test.
 *
 * @coversDefaultClass \Janalis\Doctrineviz\Command\DoctrinevizCommand
 */
class DoctrinevizCommandTest extends DoctrinevizTestCase
{
    /**
     * Test configure.
     *
     * @covers ::configure
     * @group command
     */
    public function testConfigure()
    {
        $command = new DoctrinevizCommand();
        $this->assertEquals($command::NAME, $command->getName());
        $this->assertContains('pattern', array_keys($command->getDefinition()->getOptions()));
    }

    /**
     * Test execute: dot formatting.
     *
     * @covers ::execute
     * @group command
     */
    public function testExecuteDotFormatting()
    {
        $expected = 'digraph g {'.PHP_EOL.
            '  rankdir="LR"'.PHP_EOL.
            '  ranksep="3"'.PHP_EOL.
            '  address ['.PHP_EOL.
            '    shape="record"'.PHP_EOL.
            '    width="4"'.PHP_EOL.
            '    label="ADDRESS|<id> id : integer\\l"'.PHP_EOL.
            '  ]'.PHP_EOL.
            '  email ['.PHP_EOL.
            '    shape="record"'.PHP_EOL.
            '    width="4"'.PHP_EOL.
            '    label="EMAIL|<id> id : integer\\l|<address> address : string\\l"'.PHP_EOL.
            '  ]'.PHP_EOL.
            '  group ['.PHP_EOL.
            '    shape="record"'.PHP_EOL.
            '    width="4"'.PHP_EOL.
            '    label="GROUP|<id> id : integer\\l|<name> name : string\\l"'.PHP_EOL.
            '  ]'.PHP_EOL.
            '  user ['.PHP_EOL.
            '    shape="record"'.PHP_EOL.
            '    width="4"'.PHP_EOL.
            '    label="USER|<id> id : integer\\l|<firstName> firstName : string\\l|<lastName> lastName : string\\l|<email_id> email_id : integer\\l|<address_id> address_id : integer\\l"'.PHP_EOL.
            '  ]'.PHP_EOL.
            '  user_group ['.PHP_EOL.
            '    shape="record"'.PHP_EOL.
            '    width="4"'.PHP_EOL.
            '    label="USER_GROUP|<group_id> group_id : integer\\l|<user_id> user_id : integer\\l"'.PHP_EOL.
            '  ]'.PHP_EOL.
            '  user:address_id : integer -> address:id : integer ['.PHP_EOL.
            '    headlabel="0..1"'.PHP_EOL.
            '    taillabel="*"'.PHP_EOL.
            '  ];'.PHP_EOL.
            '  user:email_id : integer -> email:id : integer ['.PHP_EOL.
            '    headlabel="1"'.PHP_EOL.
            '    taillabel="1"'.PHP_EOL.
            '  ];'.PHP_EOL.
            '  user_group:group_id : integer -> group:id : integer ['.PHP_EOL.
            '    headlabel="0..1"'.PHP_EOL.
            '    taillabel="*"'.PHP_EOL.
            '  ];'.PHP_EOL.
            '  user_group:user_id : integer -> user:id : integer ['.PHP_EOL.
            '    headlabel="0..1"'.PHP_EOL.
            '    taillabel="*"'.PHP_EOL.
            '  ];'.PHP_EOL.
            '}'.PHP_EOL;
        $client = static::createClient();
        $command = new DoctrinevizCommand();
        $command->setContainer($client->getContainer());
        $options = array_combine(array_map(function ($option) {
            return '--'.$option;
        }, array_keys($command->getDefinition()->getOptionDefaults())), array_values($command->getDefinition()->getOptionDefaults()));
        $input = new ArrayInput(array_replace($options, [
            '--format' => 'dot',
        ]), $command->getDefinition());
        $output = new BufferedOutput();
        $command->execute($input, $output);
        $this->assertEquals($expected, $output->fetch());
    }

    /**
     * Test execute: update example.
     *
     * @covers ::execute
     * @group command
     */
    public function testExecuteUpdateExample()
    {
        $client = static::createClient();
        $command = new DoctrinevizCommand();
        $command->setContainer($client->getContainer());
        $options = array_combine(array_map(function ($option) {
            return '--'.$option;
        }, array_keys($command->getDefinition()->getOptionDefaults())), array_values($command->getDefinition()->getOptionDefaults()));
        $input = new ArrayInput(array_replace($options, [
            '--output-path' => __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'example.png',
            '--format' => 'png',
        ]), $command->getDefinition());
        $output = new BufferedOutput();
        $command->execute($input, $output);
        $this->assertNotNull($output);
    }

    /**
     * Test get field mapping display name.
     *
     * @covers ::getFieldMappingDisplayName
     * @group command
     */
    public function testGetFieldMappingDisplayName()
    {
        $name = 'foo';
        $command = new DoctrinevizCommand();
        // default key is columnName and default doctrine type is integer
        $this->assertEquals("$name : integer", $this->callProtectedMethod(
            $command,
            'getFieldMappingDisplayName',
            [
                'columnName' => $name,
            ]
        ));
        // key can be passed as a parameter
        $key = 'bar';
        $this->assertEquals("$name : integer", $this->callProtectedMethod(
            $command,
            'getFieldMappingDisplayName',
            [
                $key => $name,
            ],
            $key
        ));
        // declared doctrine type is returned
        $type = 'baz';
        $this->assertEquals("$name : $type", $this->callProtectedMethod(
            $command,
            'getFieldMappingDisplayName',
            [
                'columnName' => $name,
                'type' => $type,
            ]
        ));
    }
}
