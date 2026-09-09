<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Support\Clockwork;

class Converter
{
    /**
     * Convert the phpdebugbar data to Clockwork format.
     *
     * @param array $data
     *
     */
    public function convert($data): array
    {
        $meta = $data['__meta'];

        // Default output
        $output = [
            'id' => $meta['id'],
            'method' => $meta['method'],
            'uri' => $meta['uri'],
            'time' => $meta['utime'],
            'headers' => [],
            'cookies' => [],
            'emailsData' => [],
            'getData' => [],
            'log' => [],
            'postData' => [],
            'sessionData' => [],
            'timelineData' => [],
            'viewsData' => [],
            'controller' => null,
            'responseTime' => null,
            'responseStatus' => null,
            'responseDuration' => 0,
        ];

        if (isset($data['clockwork'])) {
            $output = array_merge($output, $data['clockwork']);
        }

        if (isset($data['memory']['peak_usage'])) {
            $output['memoryUsage'] = $data['memory']['peak_usage'];
        }

        if (isset($data['time']['measures'])) {
            $time = $data['time'];
            $output['time'] = $time['start'];
            $output['responseTime'] = $time['end'];
            $output['responseDuration'] = $time['duration'] * 1000;
            foreach ($time['measures'] as $measure) {
                $output['timelineData'][] = [
                    'data' => [],
                    'description' => $measure['label'],
                    'duration' => $measure['duration'] * 1000,
                    'end' => $measure['end'],
                    'start' => $measure['start'],
                    'relative_start' => $measure['start'] - $time['start'],
                ];
            }
        }

        if (isset($data['route'])) {
            $route = $data['route'];

            $output['controller'] = null;
            if (isset($route['controller']['value'])) {
                $output['controller'] = $route['controller']['value'];
            } elseif (isset($route['uses'])) {
                $output['controller'] = $route['uses'];
            }

            [$method, $uri] = explode(' ', $route['uri'], 2);

            $output['routes'][] = [
                'action' => $output['controller'],
                'after' => $route['after'] ?? null,
                'before' => $route['before'] ?? null,
                'method' => $method,
                'name' => $route['as'] ?? null,
                'uri' => $uri,
            ];
        }

        if (isset($data['messages']['messages'])) {
            foreach ($data['messages']['messages'] as $message) {
                $output['log'][] = [
                    'message' => $message['message'],
                    'time' => $message['time'],
                    'level' => $message['label'],
                ];
            }
        }

        if (isset($data['exceptions']['exceptions'])) {
            foreach ($data['exceptions']['exceptions'] as $message) {
                $output['log'][] = [
                    'message' => $message['message'] . "\n" . ($message['stack_trace'] ?? ''),
                    'time' => $message['time'] ?? null,
                    'level' => str_starts_with($message['type'], 'E_') || $message['type'] === 'UNKNOWN' ? 'warning' : 'error',
                ];
            }
        }

        if (isset($data['logs']['messages'])) {
            foreach ($data['logs']['messages'] as $message) {
                $output['log'][] = [
                    'message' => $message['message'],
                    'time' => strtotime($message['time']),
                    'level' => $message['label'],
                ];
            }
        }

        if (isset($data['queries']['statements'])) {
            $queries = $data['queries'];
            foreach ($queries['statements'] as $statement) {
                if ($statement['type'] === 'explain' || $statement['type'] === 'info') {
                    continue;
                }
                $output['databaseQueries'][] = [
                    'query' => $statement['sql'],
                    'bindings' => $statement['params'] ?? [],
                    'duration' => ($statement['duration'] ?? 0) * 1000,
                    'time' => $statement['start'] ?? null,
                    'connection' => $statement['connection'] ?? null,
                    'model' => $statement['filename'] ?? null,
                ];
            }

            $output['databaseDuration'] = $queries['accumulated_duration'] * 1000;
        }

        if (isset($data['models']['data'])) {
            $output['modelsActions'] = [];
            $output['modelsCreated'] = [];
            $output['modelsUpdated'] = [];
            $output['modelsDeleted'] = [];
            $output['modelsRetrieved'] = [];

            foreach ($data['models']['data'] as $model => $value) {
                foreach ($value as $event => $count) {
                    $eventKey = 'models' . ucfirst($event);
                    if (isset($output[$eventKey])) {
                        $output[$eventKey][$model] = $count;
                    }
                }
            }
        }

        if (isset($data['views']['templates'])) {
            foreach ($data['views']['templates'] as $view) {
                $output['viewsData'][] = [
                    'description' => 'Rendering a view',
                    'duration' => 0,
                    'end' => 0,
                    'start' => $view['start'] ?? 0,
                    'data' => [
                        'name' => $view['name'],
                        'data' => $view['params'],
                    ],
                ];
            }
        }

        if (isset($data['event']['measures'])) {
            foreach ($data['event']['measures'] as $event) {
                $event['data'] = [];
                $event['listeners'] = [];
                foreach ($event['params'] ?? [] as $key => $param) {
                    $event[is_numeric($key) ? 'data' : 'listeners'] = $param;
                }
                $output['events'][] = [
                    'event' => ['event' => $event['label']],
                    'data' => $event['data'],
                    'time' => $event['start'],
                    'duration' => $event['duration'] * 1000,
                    'listeners' => $event['listeners'],
                ];
            }
        }

        if (isset($data['symfonymailer_mails']['mails'])) {
            foreach ($data['symfonymailer_mails']['mails'] as $mail) {
                $output['emailsData'][] = [
                    'data' => [
                        'to' => implode(', ', $mail['to']),
                        'subject' => $mail['subject'],
                        'headers' => isset($mail['headers']) ? explode("\n", $mail['headers']) : null,
                    ],
                ];
            }
        }

        return $output;
    }
}
