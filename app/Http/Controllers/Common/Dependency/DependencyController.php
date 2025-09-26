<?php

namespace App\Http\Controllers\Common\Dependency;

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
}
