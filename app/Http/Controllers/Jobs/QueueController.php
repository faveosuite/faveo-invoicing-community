<?php

namespace App\Http\Controllers\Jobs;

use App\Http\Controllers\Common\PHPController as Controller;
use App\Http\Requests\Queue\QueueRequest;
use App\Model\Mailjob\FaveoQueue;
use App\Model\Mailjob\QueueService;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    private $queue;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');

        $this->queue = new QueueService();
    }

    public function index()
    {
        try {
            $cronPath = base_path('artisan');
            $queue = new QueueService();
            $activeQueue = $queue->where('status', 1)->first();
            $paths = $this->getPHPBinPath();

            return view('themes.default1.queue.index', compact('activeQueue', 'paths', 'cronPath'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    // public function monitorQueues()
    // {

    // }

    public function getQueues()
    {
        try {
            $allQueues = $this->queue->select('id', 'name', 'status');

            return \DataTables::of($allQueues)
            ->orderColumn('name', '-id $1')
            ->orderColumn('status', '-id $1')
        ->addColumn('name', function ($model) {
            return $model->getName();
        })
        ->addColumn('status', function ($model) {
            return $model->getStatus();
        })
        ->addColumn('action', function ($model) {
            return $model->getAction();
        })
          ->filterColumn('name', function ($query, $keyword) {
              $sql = 'name like ?';
              $query->whereRaw($sql, ["%{$keyword}%"]);
          })
        ->rawColumns(['checkbox', 'name', 'status', 'action'])
        ->make(true);
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $queues = new QueueService();
            $queue = $queues->find($id);
            if (! $queue) {
                throw new Exception( __('message.sorry_cannot_find_request'));
            }

            return view('themes.default1.queue.edit', compact('queue'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function update($id, QueueRequest $request)
    {
        try {
            $values = $request->except('_token');
            $queues = new QueueService();
            $queue = $queues->find($id);

            if (! $queue) {
                throw new Exception( __('message.sorry_cannot_find_request'));
            }
            $setting = new FaveoQueue();
            $settings = $setting->where('service_id', $id)->get();
            if ($settings->count() > 0) {
                foreach ($settings as $set) {
                    $set->delete();
                }
            }
            if (count($values) > 0) {
                foreach ($values as $key => $value) {
                    $setting->create([
                        'service_id' => $id,
                        'key' => $key,
                        'value' => $value,
                    ]);
                }
            }

            return redirect()->back()->with('success', __('message.updated'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function getForm(Request $request)
    {
        $queueid = $request->input('queueid');
        $form = $this->getFormById($queueid);

        return $form;
    }

    public function activate(Request $request, QueueService $queue)
    {
        try {
            $activeQueue = QueueService::where('status', 1)->first();

            if ($queue->isActivate() == false && $queue->id != 1 && $queue->id != 2) {
                return redirect()->back()->with('fails', __('message.activate_configure_first', ['name' => $queue->name]));
            }
            if ($activeQueue) {
                $activeQueue->status = 0;
                $activeQueue->save();
            }
            $queue->status = 1;
            $queue->save();
            // $this->updateSnapShotJob($queue);
            $result = __('message.activated_successfully', ['name' => $queue->name]);

            return redirect()->back()->with('success', $result);
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function getShortNameById($queueid)
    {
        $short = '';
        $queues = new QueueService();
        $queue = $queues->find($queueid);
        if ($queue) {
            $short = $queue->short_name;
        }

        return $short;
    }

    public function getIdByShortName($short)
    {
        $id = '';
        $queues = new QueueService();
        $queue = $queues->where('short_name', $short)->first();
        if ($queue) {
            $id = $queue->id;
        }

        return $id;
    }

    public function getFormById($id)
    {
        $errors = session('errors');
        $driverErrorMessage = $errors ? $errors->first('driver') : '';
        $hostErrorMessage = $errors ? $errors->first('host') : '';
        $queueErrorMessage = $errors ? $errors->first('queue') : '';
        try {
            $short = $this->getShortNameById($id);
            $form = '';
            switch ($short) {
                case 'beanstalkd':
                    $form .= "<div class='row'>";
                    $form .= $this->form($short, __('message.driver'), 'driver', 'col-md-6 form-group', 'beanstalkd');
                    $form .= $this->form($short, __('message.host'), 'host', 'col-md-6 form-group', 'localhost');
                    $form .= $this->form($short, __('message.queue'), 'queue', 'col-md-6 form-group', 'default');
                    $form .= '</div>';

                    return $form;
                case 'sqs':
                    $form .= "<div class='row'>";
                    $form .= $this->form($short, __('message.driver'), 'driver', 'col-md-6 form-group', 'sqs');
                    $form .= $this->form($short, __('message.db_key'), 'key', 'col-md-6 form-group', 'your-public-key');
                    $form .= $this->form($short, __('message.secret'), 'secret', 'col-md-6 form-group', 'your-queue-url');
                    $form .= $this->form($short, __('message.region'), 'region', 'col-md-6 form-group', 'us-east-1');
                    $form .= '</div>';

                    return $form;
                case 'iron':
                    $form .= "<div class='row'>";
                    $form .= $this->form($short, __('message.driver'), 'driver', 'col-md-6 form-group', 'iron');
                    $form .= $this->form($short, __('message.host'), 'host', 'col-md-6 form-group', 'mq-aws-us-east-1.iron.io');
                    $form .= $this->form($short, __('message.db_token'), 'token', 'col-md-6 form-group', 'your-token');
                    $form .= $this->form($short, __('message.db_project'), 'project', 'col-md-6 form-group', 'your-project-id');
                    $form .= $this->form($short, __('message.queue'), 'queue', 'col-md-6 form-group', 'your-queue-name');
                    $form .= '</div>';

                    return $form;
                case 'redis':
                    if (! extension_loaded('redis')) {
                        return errorResponse(\Lang::get('message.extension_required_error', ['extension' => 'redis']), 500);
                    }
                    $form .= "<div class='row'>";
                    $form .= $this->form($short, __('message.driver'), 'driver', 'col-md-6 form-group', __('message.redis_place'));
                    $form .= $this->form($short, __('message.queue'), 'queue', 'col-md-6 form-group', __('message.default_place'));
                    $form .= '</div>';

                    return $form;
                default:
                    return $form;
            }
        } catch (Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function form($short, $label, $name, $class, $placeholder = '')
    {
        $queueid = $this->getIdByShortName($short);
        $queues = new QueueService();
        $queue = $queues->find($queueid);
        if ($queue) {
            $form = "<div class='".$class."'>".html()->label($label)->for($name)."<span class='text-red'> *</span>".
                html()->text($name, $queue->getExtraField($name))->class('form-control')->placeholder($placeholder).'</div>';
        } else {
            $form = "<div class='".$class."'>".html()->label($label)->for($name)."<span class='text-red'> *</span>".
                html()->text($name)->class('form-control')->placeholder($placeholder).'</div>';
        }

        return $form;
    }
}
