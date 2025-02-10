<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Model\Common\Country;
use App\Model\Common\State;
use App\Model\Payment\Tax;
use App\Model\Payment\TaxByState;
use App\Model\Payment\TaxClass;
use App\Model\Payment\TaxOption;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    public $tax;

    public $country;

    public $state;

    public $tax_option;

    public $tax_class;

    public function __construct()
    {
        $this->middleware('auth', ['except' => 'getState']);
        $this->middleware('admin', ['except' => 'getState']);

        $tax = new Tax();
        $this->tax = $tax;

        $country = new Country();
        $this->country = $country;

        $state = new State();
        $this->state = $state;

        $tax_option = new TaxOption();
        $this->tax_option = $tax_option;

        $tax_class = new TaxClass();
        $this->tax_class = $tax_class;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Response
     */
    public function index()
    {
        try {
            $options = $this->tax_option->find(1);
            if (! $options) {
                $options = '';
            }
            $classes = $this->tax_class->pluck('name', 'id')->toArray();
            if (count($classes) == 0) {
                $classes = $this->tax_class->get();
            }
            $countries = Country::pluck('nicename', 'country_code_char2')->toArray();

            return view('themes.default1.payment.tax.index', compact('options', 'classes', 'countries'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * @return type
     */
    public function getTax()
    {
        $tax = Tax::join('tax_classes', 'tax_classes.id', '=', 'taxes.tax_classes_id')->select('taxes.id', 'taxes.name as name', 'taxes.country', 'taxes.state', 'taxes.rate', 'tax_classes.name as tax_classes_id');

        return \DataTables::of($tax)
                            ->orderColumn('tax_classes_id', '-taxes.id $1')
                            ->orderColumn('name', '-taxes.id $1')
                            ->orderColumn('country', '-taxes.id $1')
                            ->orderColumn('rate', '-taxes.id $1')
                            ->addColumn('checkbox', function ($model) {
                                return "<input type='checkbox' class='tax_checkbox' 
                                value=".$model->id.' name=select[] id=check>';
                            })
                            ->addColumn('tax_classes_id', function ($model) {
                                return ucfirst($model->tax_classes_id);
                            })
                            ->addColumn('name', function ($model) {
                                return ucfirst($model->name);
                            })

                            // ->showColumns('name', 'level')
                            ->addColumn('country', function ($model) {
                                if ($this->country->where('country_code_char2', $model->country)->first()) {
                                    return ucfirst($this->country
                                      ->where('country_code_char2', $model->country)->first()->country_name);
                                } else {
                                    return '--';
                                }
                            })
                            ->addColumn('state', function ($model) {
                                if ($this->state->where('state_subdivision_code', $model->state)->first()) {
                                    return $this->state
                                    ->where('state_subdivision_code', $model->state)
                                    ->first()->state_subdivision_name;
                                } else {
                                    return '--';
                                }
                            })
                            ->addColumn('rate', function ($model) {
                                if ($model->rate) {
                                    return $model->rate;
                                } else {
                                    return '<p'.tooltip('Default&nbsp;GST&nbsp;set&nbsp;in&nbsp;the&nbsp;system&nbsp;will&nbsp;be&nbsp;applicable').'Default</p>';
                                }
                            })

                            ->addColumn('action', function ($model) {
                                return '<a href='.url('tax/'.$model->id.'/edit').
                                " class='btn btn-sm btn-secondary btn-xs'".tooltip(__('message.edit'))."<i class='fa fa-edit' 
                                style='color:white;'> </i></a>";
                            })
                            ->filterColumn('tax_classes_id', function ($query, $keyword) {
                                $sql = ' tax_classes.name like ?';
                                $query->whereRaw($sql, ["%{$keyword}%"]);
                            })
                             ->filterColumn('name', function ($query, $keyword) {
                                 $sql = 'taxes.name like ?';
                                 $query->whereRaw($sql, ["%{$keyword}%"]);
                             })
                            ->rawColumns(['checkbox', 'tax_classes_id', 'name', 'country', 'state', 'rate', 'action'])
                            ->make(true);
    }

    public function getTaxTable()
    {
        return \DataTables::of(TaxByState::select('id', 'state', 'c_gst', 's_gst', 'i_gst', 'ut_gst'))
                         ->orderColumn('id', '-id $1')
                         ->orderColumn('state', '-id $1')
                         ->orderColumn('c_gst', '-id $1')
                         ->orderColumn('s_gst', '-id $1')
                         ->orderColumn('i_gst', '-id $1')
                         ->orderColumn('ut_gst', '-id $1')
                         ->addColumn('id', function ($model) {
                             return $model->id;
                         })

                         ->addColumn('state', function ($model) {
                             return ucfirst($model->state);
                         })
                         ->addColumn('c_gst', function ($model) {
                             return ucfirst($model->c_gst);
                         })
                         ->addColumn('s_gst', function ($model) {
                             return ucfirst($model->s_gst);
                         })
                         ->addColumn('i_gst', function ($model) {
                             return ucfirst($model->i_gst);
                         })
                         ->addColumn('ut_gst', function ($model) {
                             return ucfirst($model->ut_gst);
                         })
                         ->filterColumn('state', function ($query, $keyword) {
                             $sql = 'state like ?';
                             $query->whereRaw($sql, ["%{$keyword}%"]);
                         })
                          ->filterColumn('c_gst', function ($query, $keyword) {
                              $sql = 'c_gst like ?';
                              $query->whereRaw($sql, ["%{$keyword}%"]);
                          })
                          ->filterColumn('s_gst', function ($query, $keyword) {
                              $sql = 's_gst like ?';
                              $query->whereRaw($sql, ["%{$keyword}%"]);
                          })
                          ->filterColumn('i_gst', function ($query, $keyword) {
                              $sql = 'i_gst like ?';
                              $query->whereRaw($sql, ["%{$keyword}%"]);
                          })
                          ->filterColumn('ut_gst', function ($query, $keyword) {
                              $sql = 'ut_gst like ?';
                              $query->whereRaw($sql, ["%{$keyword}%"]);
                          })
                          ->rawColumns(['id', 'state',  'c_gst', 's_gst', 'i_gst', 'ut_gst'])
                          ->make(true);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Response
     */
    public function edit($id)
    {
        try {
            $options = $this->tax_option->find(1);
            $tax = $this->tax->where('id', $id)->first();
            $taxClassName = $tax->taxClass()->find($tax->tax_classes_id)->name; //Find the Tax Class Name related to the tax
            $txClass = $this->tax_class->where('id', $tax->tax_classes_id)->first();
            $state = getStateByCode($tax->state);
            $states = findStateByRegionId($tax->country);

            return view('themes.default1.payment.tax.edit',
                compact('options', 'tax', 'txClass', 'states', 'state', 'taxClassName'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Response
     */
    public function update($id, Request $request)
    {
        if ($request->tax_classes_id == 'Others') {
            $this->validate($request, [
                'rate' => 'required|numeric',
            ],
                [
                    'rate.required' => __('validation.rate.required'),
                    'rate.numeric' => __('validation.rate.numeric'),
                ]);
        }
        try {
            $v = \Validator::make($request->all(), ['name' => 'required']);
            if ($v->fails()) {
                return redirect()->back()
                                        ->withErrors($v)
                                        ->withInput();
            }
            $taxClassesName = $request->tax_classes_id;
            $taxClass = TaxClass::where('name', $request->tax_classes_id)->first();
            if (! $taxClass) {
                $taxClass = $this->tax_class->create(['name' => $taxClassesName]);
            }
            $taxId = $taxClass->id;
            $tax = $this->tax->where('id', $id)->first();
            $tax->fill($request->except('tax_classes_id'))->save();

            $this->tax->where('id', $id)->update(['tax_classes_id' => $taxId]);
            if ($taxClassesName != 'Others') {
                $country = 'IN';
                $state = '';
                $rate = '';
                $this->tax->where('id', $id)
                ->update(['tax_classes_id' => $taxId, 'country' => $country, 'state' => $state, 'rate' => $rate]);
            }

            return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Response
     */
    public function destroy(Request $request)
    {
        try {
            $ids = $request->input('select');
            if (! empty($ids)) {
                foreach ($ids as $id) {
                    $tax = $this->tax->where('id', $id)->first();
                    $taxClassId = $tax->tax_classes_id;
                    $taxClass = $this->tax_class->where('id', $taxClassId)->first();
                    if ($tax) {
                        $taxClass->delete();
                        $tax->delete();
                    } else {
                        echo "<div class='alert alert-danger alert-dismissable'>
                        <i class='fa fa-ban'></i>

                        <b>"./* @scrutinizer ignore-type */ \Lang::get('message.alert').'!
                        </b> './* @scrutinizer ignore-type */ \Lang::get('message.failed').'

                        <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                            '.\Lang::get('message.no-record').'
                    </div>';
                        //echo \Lang::get('message.no-record') . '  [id=>' . $id . ']';
                    }
                }
                echo "<div class='alert alert-success alert-dismissable'>
                        <i class='fa fa-ban'></i>

                        <b>".\Lang::get('message.alert').'!</b> '.
                        /* @scrutinizer ignore-type */ \Lang::get('message.success').'
                        <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                            './* @scrutinizer ignore-type */\Lang::get('message.deleted-successfully').'

                    </div>';
            } else {
                echo "<div class='alert alert-danger alert-dismissable'>
                        <i class='fa fa-ban'></i>

                        <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                        /* @scrutinizer ignore-type */ \Lang::get('message.failed').'
                        <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                            './* @scrutinizer ignore-type */ \Lang::get('message.select-a-row').'

                    </div>';
                //echo \Lang::get('message.select-a-row');
            }
        } catch (\Exception $e) {
            echo "<div class='alert alert-danger alert-dismissable'>
                        <i class='fa fa-ban'></i>
                        <b>".\Lang::get('message.alert').'!</b> '.\Lang::get('message.failed').'
                        <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                            '.$e->getMessage().'
                    </div>';
        }
    }

    /**
     * @param  Request  $request
     * @param  type  $state
     * @return type
     */
    public function getState(Request $request, $stateid)
    {
        try {
            $id = $stateid;
            $states = \App\Model\Common\State::where('country_code_char2', $id)
            ->orderBy('state_subdivision_name', 'asc')->get();
            echo '<option value="">'.__('message.choose').'</option>';
            foreach ($states as $state) {
                echo '<option value='.$state->state_subdivision_code.'>'.$state->state_subdivision_name.'</option>';
            }
        } catch (\Exception $ex) {
            echo "<option value=''>".__('messages.problem_while_loading').'</option>';

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function saveTaxOptionSetting(Request $request)
    {
        $this->tax_option->find(1)->fill($request->input())->save();

        return redirect()->back()->with('success', __('message.tax_settings_saved_successfully'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */

    /**
     * @param  Request  $request
     * @return type
     */
    public function saveTaxClassSetting(Request $request)
    {
        if ($request->input('name') == 'Others') {
            $this->validate($request, [
                'rate' => 'required|numeric',
            ],
                [
                    'rate.required' => __('validation.rate.required'),
                    'rate.numeric' => __('validation.rate.numeric'),
                ]);
        }

        try {
            $v = \Validator::make($request->all(), ['name' => 'required']);
            if ($v->fails()) {
                return redirect()->back()
                                        ->withErrors($v)
                                        ->withInput();
            }
            $this->tax_class->name = $request->input('name');
            $this->tax_class->save();
            $country = ($request->input('rate')) ? $request->input('country') : 'IN';

            $this->tax->fill($request->except('tax-name', 'name', 'country'))->save();
            $this->tax->country = $country;
            $this->tax->name = $request->input('tax-name');
            $this->tax->tax_classes_id = $this->tax_class->id;
            $this->tax->save();

            return redirect()->back()->with('success', \Lang::get('message.created-successfully'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }
}
