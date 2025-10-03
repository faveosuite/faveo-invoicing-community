<?php

namespace App\Http\Controllers\Common\Dependency;

use App\Model\Common\Country;
use App\Model\Common\Language;
use App\Model\Common\Setting;
use App\Model\Common\State;
use App\Model\Common\Timezone;
use Illuminate\Http\Request;

class DependencyController extends NonPublicDependencies
{
    public function handle($type, Request $request)
    {
        try {
            $this->initializeParameterValues($request);

            $data = $this->handleDependencies($type);

            if (! $data) {
                return errorResponse(\Lang::get('lang.fails'));
            }

            return successResponse('', $data);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function handleDependencies($type)
    {
        $this->dependencyKey = $type;

        switch ($type) {
            case 'time-zones':
                return $this->timeZones();
            case 'languages':
                return $this->languages();
            case 'countries':
                return $this->countries();
            case 'states':
                return $this->states();
        }
    }

    /**
     * gives array of time zones.
     *
     * @return array array of time zones
     */
    protected function timeZones()
    {
        $this->sortField = 'name';
        $this->sortOrder = 'asc';

        $baseQuery = $this->baseQuery(new Timezone)
            ->whereRaw("concat(location, ' ', name) LIKE ?", ['%'.$this->searchQuery.'%'])
            ->select('id', 'name', 'location');

        return $this->get('time_zones', $baseQuery, function ($element) {
            return (object) ['id' => $element->id, 'name' => $element->timezone_name];
        });
    }

    /**
     * gives array of languages.
     *
     * @return array array of languages
     */
    protected function languages()
    {
        $this->sortField = 'name';
        $this->sortOrder = 'asc';

        $baseQuery = $this->baseQuery(new Language)
            ->where('name', 'LIKE', '%'.$this->searchQuery.'%')
            ->select('id', 'name', 'locale');

        return $this->get('languages', $baseQuery);
    }

    /**
     * gives array of countries.
     *
     * @return array array of countries
     */
    protected function countries()
    {
        $this->sortField = 'nicename';
        $this->sortOrder = 'asc';

        $baseQuery = $this->baseQuery(new Country)
            ->where('nicename', 'LIKE', '%'.$this->searchQuery.'%')
            ->select('country_id', 'nicename', 'phonecode');

        return $this->get('countries', $baseQuery, function ($element) {
            return (object) ['id' => $element->country_id, 'name' => $element->nicename];
        });
    }

    /**
     * gives array of states.
     *
     * @return array array of states
     */
    protected function states()
    {
        $this->sortField = 'state_subdivision_name';
        $this->sortOrder = 'asc';

        $iso = Setting::find(1)->country;

        $baseQuery = $this->baseQuery(new State)
            ->where('state_subdivision_name', 'LIKE', '%'.$this->searchQuery.'%')
            ->where('country_code_char2', $iso)
            ->select('state_subdivision_name', 'state_subdivision_code', 'state_subdivision_id');

        return $this->get('states', $baseQuery);
    }
}
