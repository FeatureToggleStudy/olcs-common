<?php

/**
 * Form Helper Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\Service\Helper;

use Zend\Http\Request;
use Zend\Form\Form;
use Zend\Form\Fieldset;
use Zend\Form\Element\Checkbox;
use Zend\InputFilter\InputFilter;
use Zend\Validator\ValidatorChain;
use Common\Form\Elements\Types\Address;
use Common\Service\Table\TableBuilder;
use Zend\Form\Element;
use Zend\Form\Element\DateSelect;
use Zend\InputFilter\Input;
use Zend\View\Model\ViewModel;
use Zend\I18n\Validator\Postcode as PostcodeValidator;

/**
 * Form Helper Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FormHelperService extends AbstractHelperService
{
    const ALTER_LABEL_RESET = 0;
    const ALTER_LABEL_APPEND = 1;
    const ALTER_LABEL_PREPEND = 2;

    const CSRF_TIMEOUT = 600;

    /**
     * Create a form
     *
     * @param string $formName
     * @param bool $addCsrf
     * @param bool $addContinue
     *
     * @return \Zend\Form\Form
     * @throws \Exception
     */
    public function createForm($formName, $addCsrf = true, $addContinue = true)
    {
        $class = $this->findForm($formName);

        $annotationBuilder = $this->getServiceLocator()->get('FormAnnotationBuilder');

        $form = $annotationBuilder->createForm($class);

        if ($addCsrf) {
            $config = array(
                'type' => 'Zend\Form\Element\Csrf',
                'name' => 'security',
                'attributes' => array(
                    'class' => 'js-csrf-token',
                ),
                'options' => array(
                    'csrf_options' => array(
                        'messageTemplates' => array(
                            'notSame' => 'csrf-message'
                        ),
                        'timeout' => self::CSRF_TIMEOUT
                    )
                )
            );
            $form->add($config);
        }

        if ($addContinue) {
            $config = array(
                'type' => '\Zend\Form\Element\Button',
                'name' => 'form-actions[continue]',
                'options' => array(
                    'label' => 'Continue'
                ),
                'attributes' => array(
                    'type' => 'submit',
                    'class' => 'visually-hidden'
                )
            );
            $form->add($config);
        }

        $authService = $this->getServiceLocator()->get('ZfcRbac\Service\AuthorizationService');

        if ($authService->isGranted('internal-user')) {
            if (!$authService->isGranted('internal-edit') && !$form->getOption('bypass_auth')) {
                $form->setOption('readonly', true);
            }
        }

        return $form;
    }

    public function setFormActionFromRequest($form, $request)
    {
        if (!$form->hasAttribute('action')) {

            $url = $request->getUri()->getPath();

            $query = $request->getUri()->getQuery();

            if ($query !== '') {
                $url .= '?' . $query;
            } else {
                // WARNING: As rubbish as this looks, do *not* remove
                // the trailing space. When rendering forms in modals,
                // IE strips quote marks off attributes wherever possible.
                // This means that an action of /foo/bar/baz/ will render
                // without quotes, and the trailing slash will self-close
                // and completely destroy the form
                $url .= ' ';
            }

            $form->setAttribute('action', $url);
        }
    }

    public function createFormWithRequest($formName, $request)
    {
        $form = $this->createForm($formName);

        $this->setFormActionFromRequest($form, $request);

        return $form;
    }

    /**
     * Find form
     *
     * @param string $formName
     * @return string
     * @throws \Exception
     */
    private function findForm($formName)
    {
        foreach (['Olcs', 'Common', 'Admin'] as $namespace) {
            $class = $namespace . '\Form\Model\Form\\' . $formName;

            if (class_exists($class)) {
                return $class;
            }
        }

        throw new \RuntimeException('Form does not exist: ' . $formName);
    }

    /**
     * Check for address lookups
     *  Returns true if an address search is present, false otherwise
     *
     * @param Form $form
     * @param Request $request
     * @return boolean
     */
    public function processAddressLookupForm(Form $form, Request $request)
    {
        $return = false;

        $post = (array)$request->getPost();

        $fieldsets = $form->getFieldsets();

        foreach ($fieldsets as $fieldset) {

            if ($fieldset instanceof Address && $this->processAddressLookupFieldset($fieldset, $post, $form)) {
                // @NOTE we can't just return true here, as any other address lookups need processing also
                $return = true;
            }
        }

        return $return;
    }

    /**
     * Process an address lookup fieldset
     *
     * @param Fieldset $fieldset
     * @param array $post
     * @return boolean
     */
    private function processAddressLookupFieldset($fieldset, $post, $form)
    {
        $name = $fieldset->getName();

        // If we have clicked the find address button
        if (isset($post[$name]['searchPostcode']['search']) && !empty($post[$name]['searchPostcode']['search'])) {

            $this->processPostcodeSearch($fieldset, $post, $name);
            return true;
        }

        // If we have selected an address
        if (isset($post[$name]['searchPostcode']['select']) && !empty($post[$name]['searchPostcode']['select'])) {

            $this->processAddressSelect($fieldset, $post, $name, $form);
            $this->removeAddressSelectFields($fieldset);

            return true;
        }

        $this->removeAddressSelectFields($fieldset);
        return false;
    }

    /**
     * Process postcode lookup
     *
     * @param \Zend\Form\Fieldset $fieldset
     * @param array $post
     * @param string $name
     * @return boolean
     */
    private function processPostcodeSearch($fieldset, $post, $name)
    {
        $postcode = trim($post[$name]['searchPostcode']['postcode']);

        // If we haven't entered a postcode
        if (empty($postcode)) {

            $this->removeAddressSelectFields($fieldset);

            $fieldset->get('searchPostcode')->setMessages(array('Please enter a postcode'));

            return false;
        }

        $addressList = $this->getServiceLocator()->get('Data\Address')->getAddressesForPostcode($postcode);

        // If we haven't found any addresses
        if (empty($addressList)) {

            $this->removeAddressSelectFields($fieldset);

            $fieldset->get('searchPostcode')->setMessages(array('No addresses found for postcode'));

            return false;
        }

        $fieldset->get('searchPostcode')->get('addresses')->setValueOptions(
            $this->getServiceLocator()->get('Helper\Address')->formatAddressesForSelect($addressList)
        );

        return true;
    }

    /**
     * Process address select
     *
     * @param \Zend\Form\Fieldset $fieldset
     * @param array $post
     * @param string $name
     */
    private function processAddressSelect($fieldset, $post, $name, $form)
    {
        $address = $this->getServiceLocator()->get('Data\Address')
            ->getAddressForUprn($post[$name]['searchPostcode']['addresses']);

        $addressDetails = $this->getServiceLocator()->get('Helper\Address')->formatPostalAddressFromBs7666($address);

        $data = $post;
        $data[$name] = $addressDetails;
        $form->setData($data);
    }

    /**
     * Remove address select fields
     *
     * @param \Zend\Form\Fieldset $fieldset
     */
    private function removeAddressSelectFields($fieldset)
    {
        $fieldset->get('searchPostcode')->remove('addresses');
        $fieldset->get('searchPostcode')->remove('select');
    }

    /**
     * Alter an elements label
     *
     * @param \Zend\Form\Element $element
     * @param string $label
     * @param int $type
     */
    public function alterElementLabel($element, $label, $type = self::ALTER_LABEL_RESET)
    {
        if (in_array($type, array(self::ALTER_LABEL_APPEND, self::ALTER_LABEL_PREPEND))) {
            $oldLabel = $element->getLabel();

            if ($type == self::ALTER_LABEL_APPEND) {
                $label = $oldLabel . $label;
            } else {
                $label = $label . $oldLabel;
            }
        }

        $element->setLabel($label);
    }

    /**
     * When passed something like
     * $form, 'data->registeredAddress', this method will remove the element from the form and input filter
     *
     * @param \Zend\Form\Form $form
     * @param string $elementReference
     */
    public function remove($form, $elementReference)
    {
        $filter = $form->getInputFilter();

        $this->removeElement($form, $filter, $elementReference);

        return $this;
    }

    private function removeElement($form, $filter, $elementReference)
    {
        list($form, $filter, $name) = $this->getElementAndInputParents($form, $filter, $elementReference);

        $form->remove($name);
        $filter->remove($name);
    }

    /**
     * Grab the parent input filter and fieldset from the top level form and input filter using the -> notation
     * i.e. data->field would return the data fieldset, data input filter and the string field
     */
    public function getElementAndInputParents($form, $filter, $elementReference)
    {
        if (strstr($elementReference, '->')) {
            list($container, $elementReference) = explode('->', $elementReference, 2);

            return $this->getElementAndInputParents(
                $form->get($container),
                $filter->get($container),
                $elementReference
            );
        }

        return array($form, $filter, $elementReference);
    }

    /**
     * Disable empty validation
     *
     * @param Fieldset $form
     * @param InputFilter $filter
     */
    public function disableEmptyValidation(Fieldset $form, InputFilter $filter = null)
    {
        if ($filter === null) {
            $filter = $form->getInputFilter();
        }

        foreach ($form->getElements() as $key => $element) {

            $value = $element->getValue();

            if (empty($value) || $element instanceof Checkbox) {

                $filter->get($key)->setAllowEmpty(true)
                    ->setRequired(false)
                    ->setValidatorChain(
                        new ValidatorChain()
                    );
            }
        }

        if ($form instanceof Fieldset) {
            foreach ($form->getFieldsets() as $fieldset) {

                $this->disableEmptyValidation($fieldset, $filter->get($fieldset->getName()));
            }
        }
    }

    /**
     * Populate form table
     *
     * @param \Zend\Form\Fieldset $fieldset
     * @param \Common\Service\Table\TableBuilder $table
     */
    public function populateFormTable(Fieldset $fieldset, TableBuilder $table, $tableFieldsetName = null)
    {
        $fieldset->get('table')->setTable($table, $tableFieldsetName);
        $fieldset->get('rows')->setValue(count($table->getRows()));
    }

    /**
     * Recurse through the form and the input filter to disable the final result
     *
     * @param \Zend\Form\Form $form
     * @param string $reference
     * @param \Zend\InputFilter\InputFilter $filter
     * @return null
     */
    public function disableElement($form, $reference, $filter = null)
    {
        if ($filter === null) {
            $filter = $form->getInputFilter();
        }

        if (strstr($reference, '->')) {
            list($index, $reference) = explode('->', $reference, 2);

            return $this->disableElement($form->get($index), $reference, $filter->get($index));
        }

        $element = $form->get($reference);

        if ($element instanceof DateSelect) {
            $this->disableDateElement($element);
        } else {
            $element->setAttribute('disabled', 'disabled');
        }

        $filter->get($reference)->setAllowEmpty(true);
        $filter->get($reference)->setRequired(false);
    }

    /**
     * Disable date element
     *
     * @param \Zend\Form\Element\DateSelect $element
     */
    public function disableDateElement($element)
    {
        $element->getDayElement()->setAttribute('disabled', 'disabled');
        $element->getMonthElement()->setAttribute('disabled', 'disabled');
        $element->getYearElement()->setAttribute('disabled', 'disabled');
    }

    /**
     * Disable all elements recursively
     *
     * @param \Zend\Form\Fieldset $elements
     * @return null
     */
    public function disableElements($elements)
    {
        if ($elements instanceof Fieldset) {
            foreach ($elements->getElements() as $element) {
                $this->disableElements($element);
            }

            foreach ($elements->getFieldsets() as $fieldset) {
                $this->disableElements($fieldset);
            }
            return;
        }

        if ($elements instanceof DateSelect) {
            $this->disableDateElement($elements);
            return;
        }

        if ($elements instanceof Element) {
            $elements->setAttribute('disabled', 'disabled');
        }
    }

    /**
     * Disable field validation
     *
     * @param \Zend\InputFilter\InputFilter $inputFilter
     * @return null
     */
    public function disableValidation($inputFilter)
    {
        if ($inputFilter instanceof InputFilter) {
            foreach ($inputFilter->getInputs() as $input) {
                $this->disableValidation($input);
            }
            return;
        }

        if ($inputFilter instanceof Input) {
            $inputFilter->setAllowEmpty(true);
            $inputFilter->setRequired(false);
            $inputFilter->setValidatorChain(new ValidatorChain());
        }
    }

    /**
     * Lock the element
     *
     * @param \Zend\Form\Element $element
     * @param string $message
     */
    public function lockElement(Element $element, $message)
    {
        $translator = $this->getServiceLocator()->get('Helper\Translation');

        $viewRenderer = $this->getServiceLocator()->get('ViewRenderer');

        $lockView = new ViewModel(
            array('message' => $translator->translate($message))
        );
        $lockView->setTemplate('partials/lock');

        $label = $translator->translate($element->getLabel());

        $element->setLabel($label . $viewRenderer->render($lockView));
        $element->setLabelOption('disable_html_escape', true);

        $attributes = $element->getLabelAttributes();

        if (!isset($attributes['class'])) {
            $attributes['class'] = '';
        }
        // @todo add this back in when the css has been tweaked
        //$attributes['class'] .= ' tooltip-grandparent';

        $element->setLabelAttributes($attributes);
    }

    /**
     * Remove a list of form fields
     *
     * @param \Zend\Form\Form $form
     * @param string $fieldset
     * @param array $fields
     */
    public function removeFieldList(Form $form, $fieldset, array $fields)
    {
        foreach ($fields as $field) {
            $this->remove($form, $fieldset . '->' . $field);
        }
    }

    /**
     * Check for company number lookups
     *
     * @NOTE Doesn't quite adhere to the same interface as the other process*LookupForm
     * methods as it already expects the presence of a company number field to have been
     * determined, and it expects an array of data rather than a request
     *
     * @param Form $form
     * @param array $data
     * @param string $detailsFieldset
     * @param string $addressFieldset
     * @return boolean
     */
    public function processCompanyNumberLookupForm(Form $form, $data, $detailsFieldset, $addressFieldset = null)
    {
        if (strlen(trim($data[$detailsFieldset]['companyNumber']['company_number'])) === 8) {

            try {
                $result = $this->getServiceLocator()
                    ->get('Data\CompaniesHouse')
                    ->search('companyDetails', $data[$detailsFieldset]['companyNumber']['company_number']);
            } catch (\Exception $e) {
                // ResponseHelper throws root-level exceptions so can't be more specific here :(
                $message = 'company_number.search_error.error';
            }

            if (isset($result) && $result['Count'] === 1) {

                $form->get($detailsFieldset)->get('name')->setValue($result['Results'][0]['CompanyName']);

                if ($addressFieldset && isset($result['Results'][0]['RegAddress']['AddressLine'])) {
                    $this->populateRegisteredAddressFieldset(
                        $form->get($addressFieldset),
                        $result['Results'][0]['RegAddress']['AddressLine']
                    );
                }

                return;
            }

            if (!isset($message)) {
                $message = 'company_number.search_no_results.error';
            }
        } else {
            $message = 'company_number.length.validation.error';
        }

        $translator = $this->getServiceLocator()->get('translator');

        $form->get($detailsFieldset)->get('companyNumber')->setMessages(
            array(
                'company_number' => array($translator->translate($message))
            )
        );
    }

    /**
     * Remove a value option from an element
     *
     * @param \Zend\Form\Element $element
     * @param string $index
     */
    public function removeOption($element, $index)
    {
        $options = $element->getValueOptions();

        if (isset($options[$index])) {
            unset($options[$index]);
            $element->setValueOptions($options);
        }
    }

    public function setCurrentOption($element, $index)
    {
        $options = $element->getValueOptions();

        if (isset($options[$index])) {

            $translator = $this->getServiceLocator()->get('Helper\Translation');

            $options[$index] .= ' ' . $translator->translate('current.option.suffix');

            $element->setValueOptions($options);
        }
    }

    public function removeValidator($form, $reference, $validatorClass)
    {
        list($fieldset, $filter, $field) = $this->getElementAndInputParents($form, $form->getInputFilter(), $reference);

        $validatorChain = $filter->get($field)->getValidatorChain();
        $newValidatorChain = new ValidatorChain();

        foreach ($validatorChain->getValidators() as $validator) {
            if (! ($validator['instance'] instanceof $validatorClass)) {
                $newValidatorChain->attach($validator['instance']);
            }
        }

        $filter->get($field)->setValidatorChain($newValidatorChain);
    }

    public function attachValidator($form, $reference, $validator)
    {
        list($fieldset, $filter, $field) = $this->getElementAndInputParents($form, $form->getInputFilter(), $reference);

        $validatorChain = $filter->get($field)->getValidatorChain();

        $validatorChain->attach($validator);
    }

    public function getValidator($form, $reference, $validatorClass)
    {
        list($fieldset, $filter, $field) = $this->getElementAndInputParents($form, $form->getInputFilter(), $reference);

        $validatorChain = $filter->get($field)->getValidatorChain();

        foreach ($validatorChain->getValidators() as $validator) {
            if ($validator['instance'] instanceof $validatorClass) {
                return $validator['instance'];
            }
        }
    }

    /**
     * Set appropriate default values on date fields
     *
     * @param Zend\Form\Element $field
     * @param DateTime $currentDate
     * @return Zend\Form\Element
     */
    public function setDefaultDate($field)
    {
        // default to the current date if it is not set
        $currentValue = $field->getValue();
        $currentValue = trim($currentValue, '-'); // date element returns '--' when empty!
        if (empty($currentValue)) {
            $today = $this->getServiceLocator()->get('Helper\Date')->getDateObject();
            $field->setValue($today);
        }

        return $field;
    }

    /**
     * Populate an address fieldset using Companies House address data
     *
     * @param Zend\Form\Fieldset $fieldset address fieldset
     * @param array $data Companies House 'AddressLine' data
     * @return Zend\Form\Fieldset
     */
    public function populateRegisteredAddressFieldset($fieldset, $data)
    {
        // parse out postcode from address data
        $postcode = '';
        $postcodeValidator = new PostcodeValidator(['locale' => 'en-GB']);
        foreach ($data as $key => $datum) {
            if ($postcodeValidator->isValid($datum)) {
                $postcode =  $datum;
                unset($data[$key]);
            }
        }

        // populate remaining fields in order
        $fields = ['addressLine1', 'addressLine2', 'addressLine3', 'addressLine4', 'town'];
        $data = array_pad($data, count($fields), '');
        $addressData = array_combine($fields, $data);

        $addressData['postcode'] = $postcode;

        foreach ($addressData as $field => $value) {
            $fieldset->get($field)->setValue($value);
        }

        return $fieldset;
    }
}
