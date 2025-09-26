<?php

namespace App\Http\Controllers\Common\Dependency;

use App\Http\Controllers\Controller;
use Auth;
use Closure;
use Illuminate\Http\Request;

class BaseDependencyController extends Controller
{
    /**
     * @var string Search String that is required to be searched
     */
    protected $searchQuery;

    /**
     * if search should match the exact searchQuery
     *
     * @var bool
     */
    protected $strictSearch = false;

    /**
     * @var int     Maximum number of rows that are required
     */
    protected $limit;

    /**
     * What should be the order of the result
     *
     * @var string
     */
    protected $sortOrder;

    /**
     * The field which has to be sorted
     *
     * @var string
     */
    protected $sortField;

    /**
     * @var string      Role of the user (admin, user)
     *                  It should be initialized in a way that if user is not logged in, its value should be 'user'
     */
    protected $userRole;

    /**
     * @var bool     In most of the cases only few columns like 'id' and 'name' is required in the response,
     *                  but when more information is required other than 'id' and 'name', meta must be initialized as true
     *                  for eg. in case of ticket status, sometimes 'id' and 'name' of the status is enough but sometimes we need 'icon' and 'icon_color' also.
     *                  In that case $meta must be initialized as true, else false
     */
    protected $meta;

    /**
     * @var bool     There are certain differences in data that is required in agent panel (for display purpose)  and in admin panel (for config purpose).
     *                  For eg. In ticket status, status like 'unapproved' is not required in agent panel but required in admin panel irrespective of
     *                  user being an admin. In that case $config must initialized as true, else false.
     *                  Also, when meta is passed as true, all the fields in the DB will be returned irrespective of value of $meta
     *                  because it is required in admin panel for configuration purpose
     */
    protected $config;

    /**
     *@var array        When we need to call the methods avaible in EnhancedDependency trait to update or modify list data based on specail conditions
     */
    protected $supplements;

    /**
     * the ids the dependencies that we need to fetch
     *
     * @var array
     */
    protected $ids = [];

    /**
     * Dependency key which will be used to see which dependency to call
     *
     * @var string
     */
    protected $dependencyKey;

    /**
     * If passed as true, it will paginate the dependency
     *
     * @var bool
     */
    protected $paginate = false;

    /**
     * Reauest
     *
     * @var \Request
     */
    protected $request;

    /**
     * check by which method data should be paginated i.e ( simplePaginate() or paginate() )
     */
    protected $simplePaginate = true;


    protected $limitByStatus = true;


    /**
     * Populates class variables to handle addition params in the request . For eg. search-query, limit, meta, config, so that
     * it can be used throughout the class to give user relevant information according to the parameters passed and userType
     *
     * @param  object  $request
     */
    public function initializeParameterValues($request)
    {
        $this->request = $request;

        $this->searchQuery = $request->input('search-query') ?: '';

        $this->limit = $request->input('limit') ?: 10;

        if ($request->input('limit') == 'all') {
            // making it a big number on the assumption that number of records will be
            // less than 10000. In case of dependencies, this limit is legit
            $this->limit = 10000;
        }

        $this->userRole = Auth::check() ? Auth::user()->role : 'user';

        $this->ids = $request->input('ids') ?: [];

        //only admin can set config as true
        // or can be set through code when $ids are non empty
        $this->config = ($this->userRole == 'admin' || count($this->ids)) ? (bool) $request->input('config') : false;

        //Config will be true if it is accessed from admin panel, in that case all the data & columns in the table will be returned
        //So meta don't have to be true
        $this->meta = $request->input('meta') ?: false;

        $this->supplements = $request->input('supplements') ?: [];

        $this->sortField = $request->input('sort-field') ?: 'name';

        $this->sortOrder = $request->input('sort-order') ?: 'asc';

        $this->strictSearch = (bool) $request->input('strict-search');

        $this->paginate = (bool) $request->input('paginate');

        $this->limitByStatus = $request->input('limit-by-status', true);

        $this->request->client_visibility = (bool) $request->input('client_visibility', false);

        $this->request->purpose_of_status = (bool) $request->input('purpose_of_status', false);

        $this->request->purpose_of_status_id = $request->input('purpose_of_status_id') ?: null;

        $this->request->deactivate_id = $request->input('deactivate_id') ?: null;
    }

    protected function baseQuery($model)
    {
        $baseQuery = $model->query();

        /*
         * We do not have proper DB structure for all dependencies, so some workarounds are added
         */
        if (count($this->ids)) {
            $primaryKey = 'id';
            $baseQuery = $baseQuery->whereIn($model->getTable().'.'.$primaryKey, $this->ids);
        }

        // if strict search is on, it should not give any other result but just the search query exact match
        if ($this->strictSearch) {
            $keysWithAndWithoutNameAttributeArray = [
                ['dependency_type' => $this->dependencyKey, 'attribute_name' => 'name'],
            ];

            $arrayIndex = array_search($this->dependencyKey, array_column($keysWithAndWithoutNameAttributeArray, 'dependency_type'));

            $baseQuery = $baseQuery->where($model->getTable().'.'.$keysWithAndWithoutNameAttributeArray[$arrayIndex]['attribute_name'], $this->searchQuery);
        }

        return $baseQuery;
    }


    /**
     * Gets dependency record in required format
     *
     * @return array
     */
    protected function get($dependencyName, $baseQuery, Closure $callback = null)
    {
        if ($this->config) {
            $baseQuery->addSelect($baseQuery->getModel()->getTable().'.*');
        }

        if ($this->sortField && $this->sortOrder) {
            $baseQuery->orderBy($this->sortField, $this->sortOrder);
        }

        if ($this->paginate) {
            $paginationMethod = $this->simplePaginate ? 'simplePaginate' : 'paginate';
            $result = $baseQuery->$paginationMethod($this->limit);

            if ($callback) {
                $result->getCollection()->transform($callback);
            }

            return $result;
        }

        $result = $baseQuery->take($this->limit)->get();
        if ($callback) {
            $result = $result->transform($callback);
        }

        return [$dependencyName => $result];
    }
}
