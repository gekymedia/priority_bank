<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class QueueJobsController extends Controller
{
    public function index()
    {
        $queueConnection = config('queue.default');
        $queueConfig = config('queue.connections.' . $queueConnection);
        $queueDriver = $queueConfig['driver'] ?? 'unknown';

        // Get table name from config (defaults to 'jobs' if not specified)
        $jobsTable = $queueConfig['table'] ?? 'jobs';

        // Initialize counts
        $pendingCount = 0;
        $failedCount = 0;
        $pendingJobs = collect();
        $failedJobs = collect();

        try {
            // Check if queue driver is database
            if ($queueDriver === 'database') {
                // Get pending jobs from database
                if (DB::getSchemaBuilder()->hasTable($jobsTable)) {
                    $pendingJobs = DB::table($jobsTable)
                        ->select('id', 'queue', 'payload', 'attempts', 'reserved_at', 'available_at', 'created_at')
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->map(function ($job) {
                            try {
                                $payload = json_decode($job->payload, true);
                                return [
                                    'id' => $job->id,
                                    'queue' => $job->queue ?? 'default',
                                    'displayName' => $payload['displayName'] ?? ($payload['displayName'] ?? 'Unknown Job'),
                                    'job' => $payload['job'] ?? null,
                                    'attempts' => $job->attempts,
                                    'reserved_at' => $job->reserved_at ? date('Y-m-d H:i:s', $job->reserved_at) : null,
                                    'available_at' => date('Y-m-d H:i:s', $job->available_at),
                                    'created_at' => date('Y-m-d H:i:s', strtotime($job->created_at)),
                                ];
                            } catch (\Exception $e) {
                                return [
                                    'id' => $job->id,
                                    'queue' => $job->queue ?? 'default',
                                    'displayName' => 'Error parsing job',
                                    'job' => null,
                                    'attempts' => $job->attempts ?? 0,
                                    'reserved_at' => null,
                                    'available_at' => date('Y-m-d H:i:s', $job->available_at ?? time()),
                                    'created_at' => date('Y-m-d H:i:s', strtotime($job->created_at ?? now())),
                                ];
                            }
                        });
                    $pendingCount = $pendingJobs->count();
                }
            } elseif ($queueDriver === 'redis') {
                $pendingJobs = collect();
                $pendingCount = 0;
            }

            // Failed jobs are always stored in the failed_jobs table regardless of queue driver
            if (DB::getSchemaBuilder()->hasTable('failed_jobs')) {
                $failedJobs = DB::table('failed_jobs')
                    ->select('id', 'uuid', 'connection', 'queue', 'payload', 'exception', 'failed_at')
                    ->orderBy('failed_at', 'desc')
                    ->get()
                    ->map(function ($job) {
                        try {
                            $payload = json_decode($job->payload, true);
                            return [
                                'id' => $job->id,
                                'uuid' => $job->uuid,
                                'connection' => $job->connection,
                                'queue' => $job->queue,
                                'displayName' => $payload['displayName'] ?? 'Unknown Job',
                                'exception' => substr($job->exception, 0, 200) . '...',
                                'failed_at' => date('Y-m-d H:i:s', strtotime($job->failed_at)),
                            ];
                        } catch (\Exception $e) {
                            return [
                                'id' => $job->id,
                                'uuid' => $job->uuid ?? '',
                                'connection' => $job->connection ?? 'unknown',
                                'queue' => $job->queue ?? 'default',
                                'displayName' => 'Error parsing job',
                                'exception' => substr($job->exception ?? 'No exception data', 0, 200),
                                'failed_at' => date('Y-m-d H:i:s', strtotime($job->failed_at ?? now())),
                            ];
                        }
                    });
                $failedCount = $failedJobs->count();
            }
        } catch (\Exception $e) {
            \Log::error('Queue Jobs Controller Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'queue_driver' => $queueDriver,
                'queue_connection' => $queueConnection,
            ]);
        }

        // Debug information
        $debugInfo = [
            'queue_driver' => $queueDriver,
            'queue_connection' => $queueConnection,
            'jobs_table_exists' => Schema::hasTable($jobsTable),
            'failed_jobs_table_exists' => Schema::hasTable('failed_jobs'),
            'jobs_table_name' => $jobsTable,
            'env_queue_connection' => env('QUEUE_CONNECTION', 'database'),
        ];

        try {
            if (Schema::hasTable($jobsTable)) {
                $debugInfo['direct_pending_count'] = DB::table($jobsTable)->count();
            }
            if (Schema::hasTable('failed_jobs')) {
                $debugInfo['direct_failed_count'] = DB::table('failed_jobs')->count();
            }
        } catch (\Exception $e) {
            $debugInfo['database_error'] = $e->getMessage();
        }

        return view('admin.queue-jobs.index', compact(
            'pendingJobs',
            'failedJobs',
            'pendingCount',
            'failedCount',
            'queueDriver',
            'queueConnection',
            'debugInfo'
        ));
    }

    public function clear(Request $request)
    {
        $type = $request->input('type', 'pending');
        $queueConnection = config('queue.default');
        $queueConfig = config('queue.connections.' . $queueConnection);
        $jobsTable = $queueConfig['table'] ?? 'jobs';

        try {
            if ($type === 'pending') {
                if (Schema::hasTable($jobsTable)) {
                    DB::table($jobsTable)->truncate();
                }
                \Illuminate\Support\Facades\Cache::forget('queue:pending_count');
                return back()->with('success', 'All pending jobs have been cleared.');
            } elseif ($type === 'failed') {
                Artisan::call('queue:flush');
                \Illuminate\Support\Facades\Cache::forget('queue:failed_count');
                return back()->with('success', 'All failed jobs have been cleared.');
            } elseif ($type === 'all') {
                if (Schema::hasTable($jobsTable)) {
                    DB::table($jobsTable)->truncate();
                }
                Artisan::call('queue:flush');
                \Illuminate\Support\Facades\Cache::forget('queue:pending_count');
                \Illuminate\Support\Facades\Cache::forget('queue:failed_count');
                return back()->with('success', 'All pending and failed jobs have been cleared.');
            }

            return back()->with('error', 'Invalid job type specified.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to clear jobs: ' . $e->getMessage());
        }
    }

    public function retry($id)
    {
        try {
            $failedJob = DB::table('failed_jobs')->where('id', $id)->first();

            if (!$failedJob) {
                return back()->with('error', 'Failed job not found.');
            }

            Artisan::call('queue:retry', ['id' => $failedJob->uuid]);

            \Illuminate\Support\Facades\Cache::forget('queue:failed_count');

            return back()->with('success', 'Job queued for retry successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to retry job: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $deleted = DB::table('failed_jobs')->where('id', $id)->delete();

            if ($deleted) {
                \Illuminate\Support\Facades\Cache::forget('queue:failed_count');
                return back()->with('success', 'Failed job deleted successfully.');
            }

            return back()->with('error', 'Failed job not found.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete job: ' . $e->getMessage());
        }
    }

    public function retryAll()
    {
        try {
            Artisan::call('queue:retry', ['id' => 'all']);
            \Illuminate\Support\Facades\Cache::forget('queue:failed_count');
            return back()->with('success', 'All failed jobs have been queued for retry.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to retry all jobs: ' . $e->getMessage());
        }
    }
}
