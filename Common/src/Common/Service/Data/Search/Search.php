<?php

namespace Common\Service\Data\Search;

use Common\Service\Data\AbstractData;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\Http\Request as HttpRequest;

/**
 * Class Search
 * @package Olcs\Service\Data\Search
 */
class Search extends AbstractData implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    protected $serviceName = 'Search';

    /**
     * @var string
     */
    protected $index;

    /**
     * @var array
     */
    protected $search;

    /**
     * @var \ArrayObject
     */
    protected $query;

    /**
     * Key value pairs for filters. Key is name of filter and value is value of filter (funily enough)
     *
     * @var array
     */
    protected $filters;

    /**
     * @var HttpRequest
     */
    protected $request;

    /**
     * @TODO Remove the dependency on request. Set the object/data you need instead. e.g. $post or $post['filters']
     * @return HttpRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param HttpRequest $request
     */
    public function setRequest(HttpRequest $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @param mixed $index
     * @return $this
     */
    public function setIndex($index)
    {
        $this->index = $index;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @param mixed $search
     * @return $this
     */
    public function setSearch($search)
    {
        $this->search = $search;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * @param \ArrayObject $query
     * @return $this
     */
    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @return \ArrayObject
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return ($this->getQuery() === null || empty($this->getQuery()->limit))? 10 : $this->getQuery()->limit;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return ($this->getQuery() === null || empty($this->getQuery()->page))? 1 : $this->getQuery()->page;
    }

    /**
     * Returns the field portion from the string of the format <field>-<order>
     *
     * @return string
     */
    public function getSort()
    {
        $sortOrder = ($this->getQuery() === null || empty($this->getQuery()->sort['order'])) ?
            '' : $this->getQuery()->sort['order'];

        if (strpos($sortOrder, '-')) {
            return explode('-', $sortOrder)[0];
        }
        return '';
    }

    /**
     * Returns the order portion from the string of the format <field>-<order>
     *
     * @return string
     */
    public function getOrder()
    {
        $sortOrder = ($this->getQuery() === null || empty($this->getQuery()->sort['order'])) ?
            '' : $this->getQuery()->sort['order'];
        if (strpos($sortOrder, '-')) {
            return explode('-', $sortOrder)[1];
        }
        return '';
    }

    /**
     * @TODO needs to check RBAC for permission to access the specified search index
     * @return array
     */
    public function fetchResults()
    {
        if (is_null($this->getData('results'))) {

            // Update selected values of filters FIRST.
            $this->updateFilterValuesFromForm();
            $this->updateDateRangeValuesFromPost();

            $query = [
                'q' => $this->getSearch(),
                'limit' => $this->getLimit(),
                'page' => $this->getPage(),
                'filters' => $this->getFilterNames(),
                'dateRanges' => $this->getDateRangeKvp(),
                'sort' => $this->getSort(),
                'order' => $this->getOrder()
            ];

            $uri = sprintf(
                '%s?%s',
                $this->getDataClass()->getSearchIndices(),
                http_build_query($query)
            );
            $data = $this->getRestClient()->get($uri);

            $this->setData('results', $data);

            $this->setFilterOptionValues($data['Filters']);
            $this->populateFiltersFormOptions();
        }
        return $this->getData('results');
    }

    /**
     * @return mixed
     */
    public function fetchResultsTable()
    {
        $results = $this->fetchResults();
        // if results is not an array then something is wrong, so make it an empty array
        if (!is_array($results)) {
            $results = [];
        }

        $tableBuilder = $this->getServiceLocator()->get('Table');
        /* @var $tableBuilder \Common\Service\Table\TableBuilder */
        return $tableBuilder->buildTable(
            $this->getDataClass()->getTableConfig(),
            $results,
            [
                'query' => $this->getQuery(),
                'limit' => $this->getLimit(),
                'page' => $this->getPage()
            ],
            false
        );
    }

    public function fetchFiltersFormObject()
    {
        $form = $this->getServiceLocator()
             ->get('ViewHelperManager')
             ->get('placeholder')
             ->getContainer('searchFilter')
             ->getValue();

        return $form;
    }

    public function populateFiltersFormOptions()
    {
        /** @var \Common\Form\Form $form */
        $form = $this->fetchFiltersFormObject();

        /** @var \Common\Data\Object\Search\Aggregations\Terms\TermsAbstract $filterClass */
        foreach ($this->getFilters() as $filterClass) {

            $options = array_combine(
                $filterClass->getOptionsKvp(),
                $filterClass->getOptionsKvp()
            );

            $select = $form->get('filter')->get($filterClass->getKey());
            $select->setValueOptions($options);
            $select->setValue($filterClass->getValue());
        }

        /** @var \Common\Data\Object\Search\Aggregations\DateRange\DateRangeAbstract $dateRangeClass */
        foreach ($this->getDateRanges() as $dateRangeClass) {
            $field = $form->get('dateRanges')->get($dateRangeClass->getKey());

            // set to null for empty string as '' sets Zend\Form\Element\DateSelect to today's date
            $value = ($dateRangeClass->getValue() !== '') ? $dateRangeClass->getValue() : null;

            $field->setValue($value);
        }

        return $form;
    }

    /**
     * @return \Common\Data\Object\Search\InternalSearchAbstract
     */
    protected function getDataClass()
    {
        $manager = $this->getServiceLocator()->get(SearchTypeManager::class);
        return $manager->get($this->getIndex());
    }

    /**
     * Updates selected values of the selected filters.
     *
     * @return null
     */
    public function updateDateRangeValuesFromPost()
    {
        $post = array_merge(
            (array)$this->getRequest()->getPost(),
            (array)$this->getRequest()->getQuery()
        );

        foreach ($this->getDateRanges() as $filterClass) {

            /** @var \Common\Data\Object\Search\Aggregations\DateRange\DateRangeAbstract $filterClass */
            if (isset($post['dateRanges'][$filterClass->getKey()]) &&
                !empty($post['dateRanges'][$filterClass->getKey()])) {

                $filterClass->setValue($post['dateRanges'][$filterClass->getKey()]);
            }
        }
    }

    /**
     * @return array
     */
    public function getDateRangeKvp()
    {
        $output = [];

        foreach ($this->getDateRanges() as $filterClass) {

            $output[$filterClass->getKey()] = $filterClass->getValue();
        }

        return $output;
    }

    /**
     * Updates selected values of the selected filters.
     *
     * @return null
     */
    public function updateFilterValuesFromForm()
    {
        $post = array_merge(
            (array)$this->getRequest()->getPost(),
            (array)$this->getRequest()->getQuery()
        );

        foreach ($this->getFilters() as $filterClass) {

            /** @var \Common\Data\Object\Search\Aggregations\Terms\TermsAbstract $filterClass */
            if (isset($post['filter'][$filterClass->getKey()]) && !empty($post['filter'][$filterClass->getKey()])) {

                $filterClass->setValue($post['filter'][$filterClass->getKey()]);
            }
        }
    }

    /**
     * @return array
     */
    public function getFilterNames()
    {
        $output = [];

        foreach ($this->getFilters() as $filterClass) {

            $output[$filterClass->getKey()] = $filterClass->getValue();
        }

        return $output;
    }

    /**
     * Returns an array of filters relevant to this index.
     *
     * @return mixed
     */
    public function getFilters()
    {
        return $this->getDataClass()->getFilters();
    }

    /**
     * Returns an array of getDateRanges relevant to this index.
     *
     * @return mixed
     */
    public function getDateRanges()
    {
        return $this->getDataClass()->getDateRanges();
    }

    /**
     * Returns an array of order options relevant to this index.
     *
     * @return mixed
     */
    public function getOrderOptions()
    {
        return $this->getDataClass()->getOrderOptions();
    }

    /**
     * Sets the available filter values into the filters.
     *
     * @param array $filters
     */
    public function setFilterOptionValues(array $filterValues)
    {
        foreach ($this->getFilters() as $filterClass) {

            /** @var $filterClass \Common\Data\Object\Search\Aggregations\Terms\TermsAbstract */
            if (isset($filterValues[$filterClass->getKey()])) {

                $filterClass->setOptions($filterValues[$filterClass->getKey()]);
            }

        }
    }
}
