<?php

namespace App\Http\Controllers\Jobs;

use App\Http\Controllers\Common\PHPController as Controller;
use App\Http\Requests\Queue\QueueRequest;
use App\Model\Mailjob\FaveoQueue;
use App\Model\Mailjob\QueueService;
use Exception;
use Throwable;

class QueueController extends Controller
{
    private \App\Model\Mailjob\QueueService $queue;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');

        $this->queue = new QueueService();
    }

    public function getQueueData(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $searchString = $request->input('search-query', '');
            $sortField = $request->input('sort-field', 'name');
            $sortOrder = $request->input('sort-order', 'asc');
            $limit = (int) $request->input('limit', 10);

            $cronPath = base_path('artisan');
            $paths = $this->getPHPBinPath();

            $queueService = new QueueService();
            $activeQueue = $queueService->where('status', 1)->first();

            $queueData = $this->queue
                ->select('id', 'name', 'status')
                ->when($searchString, function ($query, string $searchString): void {
                    $query->where('name', 'like', sprintf('%%%s%%', $searchString));
                })
                ->orderBy($sortField, $sortOrder)
                ->simplePaginate($limit);

            $queueData->getCollection()->transform(fn ($queue): array => [
                'id' => $queue->id,
                'QueueDetails' => $queue->getQueueDetails(),
            ]);

            $data = [
                'cron_path' => $cronPath,
                'php_paths' => $paths,
                'active_queue' => $activeQueue,
                'queues' => $queueData,
            ];

            return successResponse(__('message.queue_data_fetched_successfully'), $data);
        } catch (Exception) {
            return errorResponse(__('message.something_went_wrong_fetching_queue'));
        }
    }

    public function edit(int $id): \Illuminate\Http\JsonResponse
    {
        try {
            $queueIdData = $this->queue->find($id);

            if (! $queueIdData) {
                return errorResponse(__('message.no-record'), 404);
            }

            return successResponse('', $queueIdData);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function update(int $id, QueueRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $values = $request->except('_token');
            $queue = $this->queue->find($id);

            if (! $queue) {
                return errorResponse(__('message.sorry_cannot_find_request'), 404);
            }

            if (! empty($values)) {
                foreach ($values as $key => $value) {
                    FaveoQueue::updateOrCreate(
                        [
                            'service_id' => $id,
                            'key' => $key,
                        ],
                        [
                            'value' => $value,
                        ]
                    );
                }
            }

            return successResponse(__('message.updated-successfully'), [
                'service_id' => $id,
                'updated_fields' => $values,
            ]);
        } catch (Exception) {
            return errorResponse(__('message.something_went_wrong'), 500);
        }
    }

    public function getForm(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $queueid = $request->input('queueid');

        return $this->getFormById($queueid);
    }

    public function activate(\Illuminate\Http\Request $request, int $queue): \Illuminate\Http\JsonResponse
    {
        try {
            $queue = QueueService::findOrFail($queue);

            if (! $queue->isActivate() && ! in_array($queue->id, [1, 2])) {
                return errorResponse(__('message.activate_configure_first', ['name' => $queue->name]), 422);
            }

            $activeQueue = QueueService::where('status', 1)
                ->where('id', '!=', $queue->id)
                ->first();

            if ($activeQueue) {
                $activeQueue->update(['status' => 0]);
            }

            $queue->update(['status' => 1]);

            return successResponse(__('message.activated_successfully', ['name' => $queue->name]), [
                'activated_queue' => [
                    'id' => $queue->id,
                    'name' => $queue->name,
                    'status' => $queue->status,
                ],
            ]);
        } catch (Exception) {
            return errorResponse(__('message.something_went_wrong'), 500);
        }
    }

    public function getShortNameById(int $queueid): string
    {
        $short = '';
        $queues = new QueueService();
        $queue = $queues->find($queueid);
        if ($queue) {
            return $queue->short_name;
        }

        return $short;
    }

    public function getIdByShortName(string $short): ?int
    {
        $queue = QueueService::where('short_name', $short)->first();

        return $queue ? $queue->id : null;
    }

    public function getFormById(int $id): \Illuminate\Http\JsonResponse
    {
        try {
            $short = $this->getShortNameById($id);

            if ($short === '' || $short === '0') {
                return errorResponse(__('message.invalid_queue_id'), 404);
            }

            // Redis extension check
            if ($short === 'redis' && ! extension_loaded('redis')) {
                return errorResponse(
                    __('message.extension_required_error', ['extension' => 'redis']),
                    500
                );
            }

            // Build field structure based on queue type
            $fields = match ($short) {
                'beanstalkd' => [
                    $this->buildField($short, __('message.driver'), 'driver', __('message.placeholder_beanstalkd')),
                    $this->buildField($short, __('message.host'), 'host', __('message.placeholder_localhost')),
                    $this->buildField($short, __('message.queue'), 'queue', __('message.default_place')),
                ],

                'sqs' => [
                    $this->buildField($short, __('message.driver'), 'driver', __('message.placeholder_sqs')),
                    $this->buildField($short, __('message.db_key'), 'key', __('message.placeholder_your-public-key')),
                    $this->buildField($short, __('message.secret'), 'secret', __('message.placeholder_your-queue-url')),
                    $this->buildField($short, __('message.region'), 'region', __('message.placeholder_us-east-1')),
                ],

                'iron' => [
                    $this->buildField($short, __('message.driver'), 'driver', __('message.placeholder_iron')),
                    $this->buildField($short, __('message.host'), 'host', __('message.placeholder_mq_aws')),
                    $this->buildField($short, __('message.db_token'), 'token', __('message.placeholder_your-token')),
                    $this->buildField($short, __('message.db_project'), 'project', __('message.placeholder_your-project-id')),
                    $this->buildField($short, __('message.queue'), 'queue', __('message.placeholder_your-queue-name')),
                ],

                'redis' => [
                    $this->buildField($short, __('message.driver'), 'driver', __('message.redis_place')),
                    $this->buildField($short, __('message.queue'), 'queue', __('message.default_place')),
                ],

                default => [],
            };

            return successResponse(__('message.form_loaded_successfully'), [
                'queue_id' => $id,
                'driver' => $short,
                'fields' => $fields,
            ]);
        } catch (Throwable) {
            return errorResponse(__('message.something_went_wrong'), 500);
        }
    }

    public function buildField(string $short, string $label, string $name, string $placeholder = ''): array
    {
        $queueId = $this->getIdByShortName($short);
        $queue = QueueService::find($queueId);

        return [
            'label' => $label,
            'name' => $name,
            'required' => true,
            'placeholder' => $placeholder,
            'value' => $queue ? $queue->getExtraField($name) : '',
            'type' => 'text',
        ];
    }
}
