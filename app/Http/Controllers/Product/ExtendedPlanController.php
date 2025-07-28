<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Model\Payment\Period;
use Illuminate\Http\Request;

/////
//Handles Operations for Setting up Product Plans //
/////
class ExtendedPlanController extends Controller
{
    /**
     * Insert Period Into Database.
     *
     * @author Ashutosh Pathak <ashutosh.pathak@ladybirdweb.com>
     *
     * @date   2019-01-08T02:27:37+0530
     *
     * @param  Request  $request  Get Name And Days as Parameter
     * @return json Suucess or Failure
     */
    public function postInsertPeriod(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|integer',
            'days' => 'required|numeric',
        ], [
            'name.required' => __('message.please_enter_period'),
            'days.required' => __('message.days_required'),
            'days.numeric' => __('message.days_numeric'),
        ]);

        try {
            if ($request->ajax()) {
                $data = [
                    'name' => $request->input('name').' '.$request->input('select-period'),
                    'days' => $request->input('days'),
                ];

                return response(Period::create($data));
            }
        } catch (\Exception $ex) {
            $result = [$ex->getMessage()];

            return response()->json(compact('result'), 500);
        }
    }
}
