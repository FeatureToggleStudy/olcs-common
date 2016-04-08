<?php

/**
 * Username Test
 */
namespace CommonTest\Form\Elements\Validators;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Form\Elements\Validators\Username;

/**
 * Username Test
 */
class UsernameTest extends MockeryTestCase
{
    private $validator;

    public function setUp()
    {
        $this->validator = new Username();
    }

    /**
     * @dataProvider provider
     */
    public function testValidator($input, $isValid)
    {
        $outcome = $this->validator->isValid($input);

        $this->assertEquals($isValid, $outcome);
    }

    public function provider()
    {
        return [
            ['0123456789', true],
            ['abcdefghijklmnoprstuvwxyz', true],
            ['ABCDEFGHIJKLMNOPRSTUVWXYZ', true],
            ['#$%\'+-/=?^_.@`|~",:;<>', true],
            ['a¬b', false],
            ['a!b', false],
            ['a£b', false],
            ['a&b', false],
            ['a*b', false],
            ['a(b', false],
            ['a)b', false],
            ['a b', false],
            ['0', false],
            ['01', true],
            ['0123456789012345678901234567890123456789', true],
            ['01234567890123456789012345678901234567890', false],
        ];
    }
}
