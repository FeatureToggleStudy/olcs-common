<?php

namespace Common\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("hoursOfWeekContent")
 * @Form\Type("Zend\Form\Fieldset")
 */
class HoursOfWeekContentRequired
{
    /**
     * @Form\Type("Text")
     * @Form\Filter({"name":"Common\Filter\NullToFloat"})
     * @Form\Attributes({
     *     "class": "short",
     *     "pattern": "\d(\.)*",
     *     "data-container-class": "inline-text"
     * })
     * @Form\Options({
     *     "label": "days-of-week-short-mon",
     *     "label_attributes": {
     *         "aria-label": "Enter your working hours, Monday"
     *     }
     * })
     * @Form\Validator({"name":"Zend\I18n\Validator\IsFloat", "options": {
     *     "messages": {
     *          "notFloat": "Only numbers are allowed"
     *     }
     * }})
     * @Form\Validator({"name":"Between", "options": {
     *     "min": 0,
     *     "max": 24,
     *     "messages": {
     *         "notBetween": "Mon must be between '%min%' and '%max%', inclusively"
     *     }
     * }})
     * @Form\Validator({"name": "Common\Form\Elements\Validators\SumContext", "options": {
     *     "min": 0.1,
     *     "messages": {
     *         "belowMin": "transport-manager-hours-per-week-validation-message"
     *     }
     * }})
     */
    public $hoursMon = null;

    /**
     * @Form\Filter({"name":"Common\Filter\NullToFloat"})
     * @Form\Attributes({
     *     "class": "short",
     *     "pattern": "\d(\.)*",
     *     "data-container-class": "inline-text"
     * })
     * @Form\Options({
     *     "label": "days-of-week-short-tue",
     *     "label_attributes": {
     *         "aria-label": "Tuesday"
     *     }
     * })
     * @Form\Validator({"name":"Zend\I18n\Validator\IsFloat", "options": {
     *     "messages": {
     *          "notFloat": "Only numbers are allowed"
     *     }
     * }})
     * @Form\Validator({"name":"Between", "options": {
     *     "min": 0,
     *     "max": 24,
     *     "messages": {
     *         "notBetween": "Tue must be between '%min%' and '%max%', inclusively"
     *     }
     * }})
     */
    public $hoursTue = null;

    /**
     * @Form\Filter({"name":"Common\Filter\NullToFloat"})
     * @Form\Attributes({
     *     "class": "short",
     *     "pattern": "\d(\.)*",
     *     "data-container-class": "inline-text"
     * })
     * @Form\Options({
     *     "label": "days-of-week-short-wed",
     *     "label_attributes": {
     *         "aria-label": "Wednesday"
     *     }
     * })
     * @Form\Validator({"name":"Zend\I18n\Validator\IsFloat", "options": {
     *     "messages": {
     *          "notFloat": "Only numbers are allowed"
     *     }
     * }})
     * @Form\Validator({"name":"Between", "options": {
     *     "min": 0,
     *     "max": 24,
     *     "messages": {
     *         "notBetween": "Wed must be between '%min%' and '%max%', inclusively"
     *     }
     * }})
     */
    public $hoursWed = null;

    /**
     * @Form\Filter({"name":"Common\Filter\NullToFloat"})
     * @Form\Attributes({
     *     "class": "short",
     *     "pattern": "\d(\.)*",
     *     "data-container-class": "inline-text"
     * })
     * @Form\Options({
     *     "label": "days-of-week-short-thu",
     *     "label_attributes": {
     *         "aria-label": "Thursday"
     *     }
     * })
     * @Form\Validator({"name":"Zend\I18n\Validator\IsFloat", "options": {
     *     "messages": {
     *          "notFloat": "Only numbers are allowed"
     *     }
     * }})
     * @Form\Validator({"name":"Between", "options": {
     *     "min": 0,
     *     "max": 24,
     *     "messages": {
     *         "notBetween": "Thu must be between '%min%' and '%max%', inclusively"
     *     }
     * }})
     */
    public $hoursThu = null;

    /**
     * @Form\Filter({"name":"Common\Filter\NullToFloat"})
     * @Form\Attributes({
     *     "class": "short",
     *     "pattern": "\d(\.)*",
     *     "data-container-class": "inline-text"
     * })
     * @Form\Options({
     *     "label": "days-of-week-short-fri",
     *     "label_attributes": {
     *         "aria-label": "Friday"
     *     }
     * })
     * @Form\Validator({"name":"Zend\I18n\Validator\IsFloat", "options": {
     *     "messages": {
     *          "notFloat": "Only numbers are allowed"
     *     }
     * }})
     * @Form\Validator({"name":"Between", "options": {
     *     "min": 0,
     *     "max": 24,
     *     "messages": {
     *         "notBetween": "Fri must be between '%min%' and '%max%', inclusively"
     *     }
     * }})
     */
    public $hoursFri = null;

    /**
     * @Form\Filter({"name":"Common\Filter\NullToFloat"})
     * @Form\Attributes({
     *     "class": "short",
     *     "pattern": "\d(\.)*",
     *     "data-container-class": "inline-text"
     * })
     * @Form\Options({
     *     "label": "days-of-week-short-sat",
     *     "label_attributes": {
     *         "aria-label": "Saturday"
     *     }
     * })
     * @Form\Validator({"name":"Zend\I18n\Validator\IsFloat", "options": {
     *     "messages": {
     *          "notFloat": "Only numbers are allowed"
     *     }
     * }})
     * @Form\Validator({"name":"Between", "options": {
     *     "min": 0,
     *     "max": 24,
     *     "messages": {
     *         "notBetween": "Sat must be between '%min%' and '%max%', inclusively"
     *     }
     * }})
     */
    public $hoursSat = null;

    /**
     * @Form\Filter({"name":"Common\Filter\NullToFloat"})
     * @Form\Attributes({
     *     "class": "short",
     *     "pattern": "\d(\.)*",
     *     "data-container-class": "inline-text"
     * })
     * @Form\Options({
     *     "label": "days-of-week-short-sun",
     *     "label_attributes": {
     *         "aria-label": "Sunday"
     *     }
     * })
     * @Form\Validator({"name":"Zend\I18n\Validator\IsFloat", "options": {
     *     "messages": {
     *          "notFloat": "Only numbers are allowed"
     *     }
     * }})
     * @Form\Validator({"name":"Between", "options": {
     *     "min": 0,
     *     "max": 24,
     *     "messages": {
     *         "notBetween": "Sun must be between '%min%' and '%max%', inclusively"
     *     }
     * }})
     */
    public $hoursSun = null;
}
