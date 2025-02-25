<?php

namespace App\Http\Controllers\Jobs;

use App\Http\Controllers\Common\PHPController as Controller;
use App\Http\Requests\Queue\QueueRequest;
use App\Model\Mailjob\FaveoQueue;
use App\Model\Mailjob\QueueService;
use Form;
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
                throw new Exception('Sorry we can not find your request');
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
                throw new Exception('Sorry we can not find your request');
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

            return redirect()->back()->with('success', 'Updated');
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
                return redirect()->back()->with('fails', "To activate $queue->name , Please configure it first");
            }
            if ($activeQueue) {
                $activeQueue->status = 0;
                $activeQueue->save();
            }
            $queue->status = 1;
            $queue->save();
            // $this->updateSnapShotJob($queue);
            $result = $queue->name.' '.'Activated successfully';

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
                    $form .= $this->form($short, 'Driver', 'driver', 'col-md-6 form-group', 'beanstalkd');
                    $form .= $this->form($short, 'Host', 'host', 'col-md-6 form-group', 'localhost');
                    $form .= $this->form($short, 'Queue', 'queue', 'col-md-6 form-group', 'default');
                    $form .= '</div>';

                    return $form;
                case 'sqs':
                    $form .= "<div class='row'>";
                    $form .= $this->form($short, 'Driver', 'driver', 'col-md-6 form-group', 'sqs');
                    $form .= $this->form($short, 'Key', 'key', 'col-md-6 form-group', 'your-public-key');
                    $form .= $this->form($short, 'Secret', 'secret', 'col-md-6 form-group', 'your-queue-url');
                    $form .= $this->form($short, 'Region', 'region', 'col-md-6 form-group', 'us-east-1');
                    $form .= '</div>';

                    return $form;
                case 'iron':
                    $form .= "<div class='row'>";
                    $form .= $this->form($short, 'Driver', 'driver', 'col-md-6 form-group', 'iron');
                    $form .= $this->form($short, 'Host', 'host', 'col-md-6 form-group', 'mq-aws-us-east-1.iron.io');
                    $form .= $this->form($short, 'Token', 'token', 'col-md-6 form-group', 'your-token');
                    $form .= $this->form($short, 'Project', 'project', 'col-md-6 form-group', 'your-project-id');
                    $form .= $this->form($short, 'Queue', 'queue', 'col-md-6 form-group', 'your-queue-name');
                    $form .= '</div>';

                    return $form;
                case 'redis':
                    if (! extension_loaded('redis')) {
                        return errorResponse(\Lang::get('message.extension_required_error', ['extension' => 'redis']), 500);
                    }
                    $form .= "<div class='row'>";
                    $form .= $this->form($short, 'Driver', 'driver', 'col-md-6 form-group', 'redis');
                    $form .= $this->form($short, 'Queue', 'queue', 'col-md-6 form-group', 'default');
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
            $form = "<div class='".$class."'>".Form::label($name, $label)."<span class='text-red'> *</span>".
                    Form::text($name, $queue->getExtraField($name), ['class' => 'form-control', 'placeholder' => $placeholder]).'</div>';
        } else {
            $form = "<div class='".$class."'>".Form::label($name, $label)."<span class='text-red'> *</span>".
                    Form::text($name, null, ['class' => 'form-control', 'placeholder' => $placeholder]).'</div>';
        }

        return $form;
    }
}
