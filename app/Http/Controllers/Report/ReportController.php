<?php

namespace App\Http\Controllers\Report;

use App\ExportDetail;
use App\Http\Controllers\Controller;
use App\ReportSetting;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function viewReports()
    {
        return view('themes.default1.report.index');
    }

    public function getReports()
    {
        try {
            $allReports = ExportDetail::join('users', 'export_details.user_id', '=', 'users.id')
            ->where('export_details.user_id', '=', \Auth::user()->id)
            ->select('export_details.id', 'export_details.user_id', 'export_details.file_path', 'export_details.file', 'export_details.name', 'export_details.created_at', 'users.first_name', 'users.last_name');

            return \DataTables::of($allReports)
            ->orderColumn('file_name', '-export_details.created_at $1')
            ->orderColumn('created_at', '-export_details.created_at $1')
             ->orderColumn('format', '-export_details.created_at $1')
              ->orderColumn('type', '-export_details.created_at $1')
               ->orderColumn('contact', '-export_details.created_at $1')

            ->addColumn('checkbox', function ($model) {
                return "<input type='checkbox' class='type_checkbox' 
            value=".$model->id.' name=select[] id=check>';
            })
            ->addColumn('file_name', function ($model) {
                return $model->file;
            })
            ->addColumn('format', function ($model) {
                $fileType = pathinfo($model->file, PATHINFO_EXTENSION);

                return ! empty($fileType) ? strtoupper($fileType) : 'XLSX';
            })
             ->addColumn('type', function ($model) {
                 return $model->name.'-'.'report';
             })
              ->addColumn('contact', function ($model) {
                  $user = User::find($model->user_id);

                  return '<a href='.url('clients/'.$user->id).'>'.ucfirst($user->first_name).' '.ucfirst($user->last_name).'</a>';
              })
               ->addColumn('created_at', function ($model) {
                   return getDateHtml($model->created_at);
               })
             ->addColumn('action', function ($model) {
                 $downloadLink = route('download.exported.file', ['id' => $model->id]);

                 return '<a href="'.$downloadLink.'" class="btn btn-sm btn-secondary btn-xs" data-toggle="tooltip" style="font-weight:500;" data-placement="top" title="'.__('message.download').'" data-original-title="Download">
                <i class="fas fa-download" style="color:white;"></i>
            </a>';
             })
              ->filterColumn('file_name', function ($query, $keyword) {
                  $sql = 'file like ?';
                  $query->whereRaw($sql, ["%{$keyword}%"]);
              })
             ->filterColumn('contact', function ($query, $keyword) {
                 $sql = "CONCAT(first_name,' ',last_name)  like ?";
                 $sql2 = 'first_name like ?';
                 $query->whereRaw($sql, ["%{$keyword}%"])->orWhereRaw($sql2, ["%{$keyword}%"]);
             })

              ->filterColumn('type', function ($query, $keyword) {
                  $sql = 'name like ?';
                  $query->whereRaw($sql, ["%{$keyword}%"]);
              })
             ->rawColumns(['checkbox', 'file_name', 'format', 'type', 'contact', 'created_at', 'action'])
            ->make(true);
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function destroyReports(Request $request)
    {
        $ids = $request->input('select');
        if (! empty($ids)) {
            foreach ($ids as $id) {
                $report = ExportDetail::where('id', $id)->first();
                if ($report) {
                    if (file_exists($report->file_path)) {
                        $relativeFilePath = str_replace(storage_path('app/'), '', $report->file_path);
                        Storage::delete($relativeFilePath);
                    }
                    $report->delete();
                } else {
                    echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */\Lang::get('message.success').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.no-record').'
                </div>';
                    //echo \Lang::get('message.no-record') . '  [id=>' . $id . ']';
                }
            }
            echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */\Lang::get('message.success').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.deleted-successfully').'
                </div>';
        } else {
            echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */ \Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */ \Lang::get('message.success').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.select-a-row').'
                </div>';
        }
    }

    public function viewRecordsColumn()
    {
        $settings = ReportSetting::first();

        return view('themes.default1.report.records-per-col', compact('settings'));
    }

    public function addRecords(Request $request)
    {
        $request->validate([
            'records' => 'required|integer|min:1|max:3000',
        ]);
        $settings = ReportSetting::first();
        $settings->records = $request->records;
        $settings->save();

        return redirect()->back()->with('success', __('message.settings_updated_successfully'));
    }
}
