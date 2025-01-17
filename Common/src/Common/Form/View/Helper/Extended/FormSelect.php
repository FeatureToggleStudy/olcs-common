<?php

/**
 * Here we extend the View helper to allow us to add attributes that aren't in ZF2's whitelist
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\Form\View\Helper\Extended;

/**
 * Here we extend the View helper to allow us to add attributes that aren't in ZF2's whitelist
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FormSelect extends \Zend\Form\View\Helper\FormSelect
{
    use PrepareAttributesTrait;
}
