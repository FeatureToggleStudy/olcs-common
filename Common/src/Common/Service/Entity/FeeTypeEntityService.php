<?php

/**
 * Fee Type Entity Service
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Common\Service\Entity;

/**
 * Fee Type Entity Service
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FeeTypeEntityService extends AbstractEntityService
{
    const FEE_TYPE_CONTINUATION = 'CONT';

    /**
     * Define entity for default behaviour
     *
     * @var string
     */
    protected $entity = 'FeeType';
}
