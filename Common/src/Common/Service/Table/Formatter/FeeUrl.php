<?php

/**
 * Fee URL formatter
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Common\Service\Table\Formatter;

/**
 * Fee URL formatter
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FeeUrl implements FormatterInterface
{
    /**
     * Format a fee URL
     *
     * @param array $row
     * @param array $column
     * @param \Zend\ServiceManager\ServiceManager $serviceLocator
     * @return string
     */
    public static function format($row, $column = array(), $serviceLocator = null)
    {
        $router     = $serviceLocator->get('router');
        $request    = $serviceLocator->get('request');
        $urlHelper  = $serviceLocator->get('Helper\Url');
        $routeMatch = $router->match($request);
        $matchedRouteName = $routeMatch->getMatchedRouteName();

        switch ($matchedRouteName) {
            case 'operator/fees':
            case 'licence/bus-fees':
            case 'licence/fees':
            case 'lva-application/fees':
                $url = $urlHelper->fromRoute(
                    $matchedRouteName.'/fee_action',
                    ['fee' => $row['id'], 'action' => 'edit-fee'],
                    [],
                    true
                );
                break;
            case 'fees':
                $url = $urlHelper->fromRoute('fees/pay', ['fee' => $row['id']], [], true);
                break;
            default:
                $url = $urlHelper->fromRoute(
                    'admin-dashboard/admin-payment-processing/misc-fees/fee_action',
                    ['fee' => $row['id'], 'action' => 'edit-fee', 'controller' => 'Admin\PaymentProcessingController'],
                    [],
                    true
                );
                break;
        }
        return '<a href="'. $url . '">'. $row['description'] . '</a>';
    }
}
