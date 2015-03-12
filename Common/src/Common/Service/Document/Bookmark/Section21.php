<?php

namespace Common\Service\Document\Bookmark;

/**
 * SECTION_2_1 bookmark
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class Section21 extends AbstractPublicationLinkSection
{
    //section ids differ based on the publication type
    protected $pubTypeSection = [
        'N&P' => 1,
        'A&D' => 4
    ];
}
