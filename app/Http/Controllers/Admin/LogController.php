<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log as SystemLog;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $logFiles = $this->getLogFiles();
        $selectedLogFile = $request->get('log_file', 'latest');

        $logs = [];
        $logStats = [
            'size' => '0 MB',
            'last_modified' => 'Never',
            'counts' => [
                'emergency' => 0,
                'alert' => 0,
                'critical' => 0,
                'error' => 0,
                'warning' => 0,
                'notice' => 0,
                'info' => 0,
                'debug' => 0,
            ]
        ];

        $pagination = [
            'current_page' => 1,
            'per_page' => 300,
            'total' => 0,
            'from' => 0,
            'to' => 0,
            'last_page' => 1,
        ];

        $logFilePath = $this->getLogFilePath($selectedLogFile, $logFiles);

        if (File::exists($logFilePath)) {
            $logStats['size'] = $this->formatBytes(File::size($logFilePath));
            $logStats['last_modified'] = File::lastModified($logFilePath)
                ? date('Y-m-d H:i:s', File::lastModified($logFilePath))
                : 'Never';

            $logContent = File::get($logFilePath);
            $logs = $this->parseLogFile($logContent);

            foreach ($logs as $log) {
                if (isset($logStats['counts'][$log['level']])) {
                    $logStats['counts'][$log['level']]++;
                }
            }

            $logs = array_reverse($logs);

            $perPage = 300;
            $currentPage = $request->get('page', 1);
            $offset = ($currentPage - 1) * $perPage;
            $paginatedLogs = array_slice($logs, $offset, $perPage);

            $pagination = [
                'current_page' => (int) $currentPage,
                'per_page' => $perPage,
                'total' => count($logs),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, count($logs)),
                'last_page' => (int) ceil(count($logs) / $perPage),
            ];

            $logs = $paginatedLogs;
        }

        $logFilesWithSize = [];
        foreach ($logFiles as $logFile) {
            $path = storage_path('logs/' . $logFile);
            $logFilesWithSize[] = [
                'name' => $logFile,
                'size' => File::exists($path) ? $this->formatBytes(File::size($path)) : '0 B',
            ];
        }

        return view('admin.logs.index', [
            'logs' => $logs,
            'logStats' => $logStats,
            'pagination' => $pagination,
            'logFiles' => $logFiles,
            'logFilesWithSize' => $logFilesWithSize,
            'selectedLogFile' => $selectedLogFile,
        ]);
    }

    public function refresh(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Logs refreshed successfully',
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    public function clear(Request $request)
    {
        try {
            $logFiles = $this->getLogFiles();
            $selectedLogFile = $request->get('log_file', 'latest');
            $logFilePath = $this->getLogFilePath($selectedLogFile, $logFiles);

            if (File::exists($logFilePath)) {
                File::put($logFilePath, '');
                SystemLog::info('Log file cleared by system administrator: ' . basename($logFilePath));
            }

            return response()->json([
                'success' => true,
                'message' => 'Logs cleared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear logs: ' . $e->getMessage()
            ], 500);
        }
    }

    public function download(Request $request)
    {
        $logFiles = $this->getLogFiles();
        $selectedLogFile = $request->get('log_file', 'latest');
        $logFilePath = $this->getLogFilePath($selectedLogFile, $logFiles);

        if (!File::exists($logFilePath)) {
            return redirect()->back()->with('error', 'Log file not found');
        }

        $filename = basename($logFilePath);
        return response()->download($logFilePath, $filename);
    }

    public function clearAll(Request $request)
    {
        try {
            $logFiles = $this->getLogFiles();
            $clearedFiles = 0;

            foreach ($logFiles as $logFile) {
                $logFilePath = storage_path('logs/' . $logFile);
                if (File::exists($logFilePath)) {
                    File::put($logFilePath, '');
                    $clearedFiles++;
                }
            }

            SystemLog::info("All log files ({$clearedFiles} files) cleared by system administrator");

            return response()->json([
                'success' => true,
                'message' => "All log files cleared successfully ({$clearedFiles} files)"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear all logs: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadAll(Request $request)
    {
        try {
            $logFiles = $this->getLogFiles();
            $zipFileName = 'laravel-logs-' . date('Y-m-d-His') . '.zip';
            $zipPath = storage_path('app/temp/' . $zipFileName);

            if (!File::exists(dirname($zipPath))) {
                File::makeDirectory(dirname($zipPath), 0755, true);
            }

            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE) === true) {
                foreach ($logFiles as $logFile) {
                    $logFilePath = storage_path('logs/' . $logFile);
                    if (File::exists($logFilePath) && File::size($logFilePath) > 0) {
                        $zip->addFile($logFilePath, $logFile);
                    }
                }
                $zip->close();

                return response()->download($zipPath)->deleteFileAfterSend(true);
            }

            return redirect()->back()->with('error', 'Failed to create zip file');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to download all logs: ' . $e->getMessage());
        }
    }

    private function getLogFiles(): array
    {
        $logPath = storage_path('logs');
        $files = [];

        if (File::exists($logPath)) {
            $allFiles = File::files($logPath);

            foreach ($allFiles as $file) {
                $filename = $file->getFilename();
                if (preg_match('/^laravel(-\d{4}-\d{2}-\d{2})?\.log$/', $filename)) {
                    $files[] = $filename;
                }
            }

            usort($files, function ($a, $b) use ($logPath) {
                return File::lastModified($logPath . '/' . $b) - File::lastModified($logPath . '/' . $a);
            });
        }

        return $files;
    }

    private function getLogFilePath(string $selectedLogFile, array $logFiles): string
    {
        if ($selectedLogFile === 'latest' && !empty($logFiles)) {
            return storage_path('logs/' . $logFiles[0]);
        }

        if (in_array($selectedLogFile, $logFiles)) {
            return storage_path('logs/' . $selectedLogFile);
        }

        return storage_path('logs/laravel.log');
    }

    private function parseLogFile(string $content): array
    {
        $logs = [];

        if (empty(trim($content))) {
            return $logs;
        }

        $pattern = '/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+): (.*?)(?=\n\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]|\n*$)/s';

        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $log = [
                'timestamp' => $match[1],
                'date' => substr($match[1], 0, 10),
                'env' => $match[2],
                'level' => strtolower($match[3]),
                'message' => trim($match[4]),
                'context' => [],
                'stack_trace' => null,
                'extra' => []
            ];

            $this->parseLogDetails($log, $match[4]);

            $logs[] = $log;
        }

        return $logs;
    }

    private function parseLogDetails(array &$log, string $message): void
    {
        if (preg_match('/\{(?:[^{}]|(?R))*\}/x', $message, $jsonMatches)) {
            foreach ($jsonMatches as $json) {
                try {
                    $data = json_decode($json, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        if (isset($data['context'])) {
                            $log['context'] = $data['context'];
                        }
                        if (isset($data['exception'])) {
                            $log['stack_trace'] = $data['exception'];
                        }
                        if (isset($data['user_id'])) {
                            $log['user_id'] = $data['user_id'];
                        }
                        if (isset($data['ip'])) {
                            $log['ip'] = $data['ip'];
                        }
                        $log['extra'] = array_diff_key($data, array_flip(['context', 'exception', 'user_id', 'ip']));
                    }
                } catch (\Exception $e) {
                    //
                }
            }
        }

        if (str_contains($message, 'Stack trace:')) {
            $parts = explode('Stack trace:', $message, 2);
            $log['message'] = trim($parts[0]);
            if (count($parts) > 1) {
                $log['stack_trace'] = 'Stack trace:' . $parts[1];
            }
        }

        if (preg_match('/ in (.*?):(\d+)$/', $message, $fileMatches)) {
            $log['file'] = $fileMatches[1];
            $log['line'] = $fileMatches[2];
            $log['message'] = str_replace($fileMatches[0], '', $log['message']);
        }
    }

    public function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
