<?php

/**
 * Write email to a tmp file rather than send
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace Common\Service\Email;

use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * Write email to a tmp file rather than send
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class EmailToTmpFile implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function sendEmail($from, $to, $subject, $body)
    {
        $txt = "To : {$to}\n"
        . "Subject : {$subject}\n"
        . "Body : {$body}";

        file_put_contents(tempnam(sys_get_temp_dir(), str_replace(' ', '_', $subject).'-'), $txt);
    }
}