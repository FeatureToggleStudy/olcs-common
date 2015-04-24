<?php
namespace Common\Service\Document\Bookmark;

use Common\Service\Document\Bookmark\Base\DynamicBookmark;
use Common\Service\Document\Bookmark\Formatter\Name as NameFormatter;
use Common\Service\Document\Bookmark\Formatter\Address as AddressFormatter;

/**
 * StatementNameBodyAddress bookmark
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class StatementNameBodyAddress extends DynamicBookmark
{
    public function getQuery(array $data)
    {
        return isset($data['statement']) ? [
            'service' => 'Statement',
            'data' => [
                'id' => $data['statement']
            ],
            'bundle' => [
                'children' => [
                    'requestorsContactDetails' => [
                        'children' => [
                            'person',
                            'address',
                        ]
                    ]
                ]
            ]
        ] : null;
    }

    public function render()
    {
        $person = $this->data['requestorsContactDetails']['person'];
        $address = isset($this->data['requestorsContactDetails']['address'])
                 ? $this->data['requestorsContactDetails']['address'] : [];

        $separator = "\n";

        $string = implode(
            $separator,
            array_filter(
                [
                    NameFormatter::format($person),
                    $this->data['requestorsBody'],
                    AddressFormatter::format($address)
                ]
            )
        );

        return $string;
    }
}
