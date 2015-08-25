<?php

/**
 * Letter Generation Document Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace CommonTest\Data\Mapper;

use PHPUnit_Framework_TestCase;
use Common\Data\Mapper\LetterGenerationDocument;

/**
 * Letter Generation Document Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LetterGenerationDocumentTest extends PHPUnit_Framework_TestCase
{
    public function testMapFromResult()
    {
        $data = [
            'metadata' => '{"bookmarks":[1,2,3],"details":{"documentTemplate":"Foo"}}',
            'category' => [
                'id' => 123
            ],
            'subCategory' => [
                'id' => 321
            ]
        ];
        $expected = [
            'data' => [
                'metadata' => '{"bookmarks":[1,2,3],"details":{"documentTemplate":"Foo"}}',
                'category' => [
                    'id' => 123
                ],
                'subCategory' => [
                    'id' => 321
                ]
            ],
            'details' => [
                'category' => 123,
                'documentSubCategory' => 321,
                'documentTemplate' => 'Foo',
                'bookmarks' => [
                    1,
                    2,
                    3
                ]
            ],
            'bookmarks' => [
                1,
                2,
                3
            ]
        ];

        $this->assertEquals($expected, LetterGenerationDocument::mapFromResult($data));
    }
}