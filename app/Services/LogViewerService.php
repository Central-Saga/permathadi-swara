<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class LogViewerService
{
    protected $logPath;

    public function __construct()
    {
        $this->logPath = storage_path('logs');
    }

    /**
     * Get all log files
     */
    public function getLogFiles(): array
    {
        $files = [];

        if (!File::exists($this->logPath)) {
            return $files;
        }

        $allFiles = File::files($this->logPath);

        foreach ($allFiles as $file) {
            if ($file->getExtension() === 'log') {
                $files[] = [
                    'name' => $file->getFilename(),
                    'path' => $file->getPathname(),
                    'size' => $file->getSize(),
                    'modified' => $file->getMTime(),
                ];
            }
        }

        // Sort by modified time (newest first)
        usort($files, function ($a, $b) {
            return $b['modified'] - $a['modified'];
        });

        return $files;
    }

    /**
     * Read log file with pagination
     */
    public function readLogFile(string $filename, int $page = 1, int $perPage = 50, ?string $level = null, ?string $search = null): array
    {
        $filePath = $this->logPath . '/' . $filename;

        if (!File::exists($filePath)) {
            return [
                'entries' => [],
                'total' => 0,
                'current_page' => $page,
                'per_page' => $perPage,
                'last_page' => 1,
            ];
        }

        $content = File::get($filePath);
        $entries = $this->parseLogEntries($content);

        // Filter by level
        if ($level) {
            $entries = array_filter($entries, function ($entry) use ($level) {
                return isset($entry['level']) && strtolower($entry['level']) === strtolower($level);
            });
        }

        // Filter by search
        if ($search) {
            $entries = array_filter($entries, function ($entry) use ($search) {
                $searchLower = strtolower($search);
                return str_contains(strtolower($entry['message'] ?? ''), $searchLower) ||
                    str_contains(strtolower($entry['context'] ?? ''), $searchLower);
            });
        }

        // Re-index array after filtering
        $entries = array_values($entries);

        $total = count($entries);
        $lastPage = (int) ceil($total / $perPage);

        // Paginate
        $offset = ($page - 1) * $perPage;
        $paginatedEntries = array_slice($entries, $offset, $perPage);

        return [
            'entries' => $paginatedEntries,
            'total' => $total,
            'current_page' => $page,
            'per_page' => $perPage,
            'last_page' => $lastPage,
        ];
    }

    /**
     * Parse log entries from Laravel/Monolog format
     */
    protected function parseLogEntries(string $content): array
    {
        $entries = [];
        $lines = explode("\n", $content);
        $currentEntry = null;

        foreach ($lines as $line) {
            // Check if line starts a new log entry (Laravel format: [YYYY-MM-DD HH:MM:SS] local.LEVEL: message)
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s+(\w+)\.(\w+):\s+(.+)$/', $line, $matches)) {
                // Save previous entry if exists
                if ($currentEntry) {
                    $entries[] = $currentEntry;
                }

                // Start new entry
                $currentEntry = [
                    'timestamp' => $matches[1],
                    'environment' => $matches[2],
                    'level' => $matches[3],
                    'message' => $matches[4],
                    'context' => '',
                    'stack_trace' => '',
                ];
            } elseif ($currentEntry) {
                // Check if it's a stack trace line
                if (preg_match('/^#\d+/', $line) || str_contains($line, 'Stack trace:') || str_contains($line, 'Exception:')) {
                    $currentEntry['stack_trace'] .= $line . "\n";
                } else {
                    // Continue message or context
                    if (trim($line)) {
                        $currentEntry['context'] .= $line . "\n";
                    }
                }
            }
        }

        // Add last entry
        if ($currentEntry) {
            $entries[] = $currentEntry;
        }

        // Reverse to show newest first
        return array_reverse($entries);
    }

    /**
     * Get log levels
     */
    public function getLogLevels(): array
    {
        return [
            'emergency',
            'alert',
            'critical',
            'error',
            'warning',
            'notice',
            'info',
            'debug',
        ];
    }

    /**
     * Clear log file
     */
    public function clearLogFile(string $filename): bool
    {
        $filePath = $this->logPath . '/' . $filename;

        if (File::exists($filePath)) {
            File::put($filePath, '');
            return true;
        }

        return false;
    }

    /**
     * Download log file
     */
    public function downloadLogFile(string $filename): ?string
    {
        $filePath = $this->logPath . '/' . $filename;

        if (File::exists($filePath)) {
            return $filePath;
        }

        return null;
    }
}
