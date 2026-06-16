<?php

namespace App\Http\Controllers\Common\Dependency;

use Lang;
use Exception;
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
                return errorResponse(Lang::get('lang.fails'));
            }

            return successResponse('', $data);
        } catch (Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function handleDependencies($type)
    {
        $this->dependencyKey = $type;

        return match ($type) {
            'time-zones' => $this->timeZones(),
            'languages' => $this->languages(),
            'countries' => $this->countries(),
            'states' => $this->states(),
            default => $this->handleNonPublicDependencies($type),
        };
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

        return $this->get('time_zones', $baseQuery, fn($element) => (object) ['id' => $element->id, 'name' => $element->timezone_name]);
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
        $this->sortField = 'country_name';
        $this->sortOrder = 'asc';

        $baseQuery = $this->baseQuery(new Country)
            ->where('country_name', 'LIKE', '%'.$this->searchQuery.'%')
            ->select('country_id', 'country_name', 'country_code_char2', 'phonecode');

        return $this->get('countries', $baseQuery, fn($element) => (object) [
            'id' => $element->country_id,
            'name' => $element->country_name,
            'code' => $element->country_code_char2,
        ]);
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

        $iso = $this->request->input('country') ?: Setting::find(1)->country;

        $baseQuery = $this->baseQuery(new State)
            ->where('state_subdivision_name', 'LIKE', '%'.$this->searchQuery.'%')
            ->where('country_code', strtoupper((string) $iso))
            ->select('state_subdivision_name', 'state_subdivision_id', 'iso2');

        return $this->get('states', $baseQuery, fn($element) => (object) [
            'id' => $element->state_subdivision_id,
            'name' => $element->state_subdivision_name,
            'iso2' => $element->iso2,
        ]);
    }
}
