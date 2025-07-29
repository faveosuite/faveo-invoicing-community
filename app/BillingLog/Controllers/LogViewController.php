<?php

namespace App\BillingLog\Controllers;

use App\BillingLog\Model\ExceptionLog;
use App\BillingLog\Model\LogCategory;
use DataTables;
use Illuminate\Http\Request;

class LogViewController
{
    public function getSystemLogs()
    {
        return view('log::system-log');
    }

    public function getLogs($type, Request $request)
    {
        switch($type) {
            case 'exception':
                return $this->getExceptionLogs($request);
            default:
                return successResponse('');
        }
    }

    public function getExceptionLogs(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $date = $request->input('date');

        $query = ExceptionLog::whereDate('created_at', $date)->with('category');

        return DataTables::of($query)
            ->editColumn('created_at', function ($row) {
                return $row->created_at->format('Y-m-d H:i:s');
            })
            ->make(true);
    }

    public function getLogCategoryList(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $date = $request->input('date');

        $categories = LogCategory::whereHas('exceptions', function ($query) use ($date) {
            $query->whereDate('created_at', $date);
        })
            ->withCount(['exceptions' => function ($query) use ($date) {
                $query->whereDate('created_at', $date);
            }])
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => \Str::ucfirst($category->name),
                    'exceptionCount' => $category->exceptions_count,
                ];
            });

        return successResponse('', $categories);
    }
}
