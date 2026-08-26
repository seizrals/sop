<?php

namespace App\Services;

use App\Models\SopDocument;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class SopPdfGenerator
{
    public function generate(SopDocument $document, Collection $executors, Collection $activities): string
    {
        $tempDir = storage_path('app/tmp/sop-pdf');
        File::ensureDirectoryExists($tempDir);

        $token = (string) Str::uuid();
        $inputPath = $tempDir . DIRECTORY_SEPARATOR . $token . '.json';
        $outputPath = $tempDir . DIRECTORY_SEPARATOR . $token . '.pdf';
        $scriptPath = base_path('scripts/generate-sop-pdf.mjs');

        File::put($inputPath, json_encode($this->payload($document, $executors, $activities), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $process = new Process(
            [
                'node',
                $scriptPath,
                $inputPath,
                $outputPath,
            ],
            base_path(),
            $this->processEnvironment(),
            null,
            120
        );

        $process->run();

        File::delete($inputPath);

        if (! $process->isSuccessful()) {
            File::delete($outputPath);

            throw new RuntimeException('Gagal membuat PDF SOP: ' . trim($process->getErrorOutput() ?: $process->getOutput()));
        }

        if (! File::exists($outputPath)) {
            throw new RuntimeException('Gagal membuat file PDF SOP.');
        }

        return $outputPath;
    }

    private function payload(SopDocument $document, Collection $executors, Collection $activities): array
    {
        $teamName = strtoupper((string) optional($document->team)->display_name);

        return [
            'title' => $document->title,
            'sop_number' => $document->sop_number,
            'creation_date' => optional($document->creation_date)->format('Y-m-d'),
            'revision_date' => optional($document->revision_date)->format('Y-m-d'),
            'effective_date' => optional($document->effective_date)->format('Y-m-d'),
            'approval_position' => $document->approval_position ?: 'Kepala Badan Pusat Statistik Kabupaten Gorontalo Utara',
            'approval_name' => $document->approval_name,
            'approval_nip' => $document->approval_nip,
            'legal_basis' => array_values($document->legal_basis ?? []),
            'executor_qualifications' => array_values($document->executor_qualifications ?? []),
            'related_documents' => array_values($document->related_documents ?? []),
            'equipment' => array_values($document->equipment ?? []),
            'warnings' => array_values($document->warnings ?? []),
            'recording' => array_values($document->recording ?? []),
            'agency_lines' => [
                'BADAN PUSAT STATISTIK',
                'KABUPATEN GORONTALO UTARA',
                'TIM STATISTIK ' . ($teamName !== '' ? $teamName : 'PRODUKSI'),
            ],
            'logo_path' => resource_path('img/logo-bps.png'),
            'executors' => $executors->values()->map(fn ($executor) => [
                'key' => (string) data_get($executor, 'key'),
                'label' => (string) data_get($executor, 'label'),
            ])->all(),
            'activities' => $activities->values()->map(fn ($row) => [
                'name' => (string) data_get($row, 'name'),
                'flow_nodes' => collect(data_get($row, 'flow_nodes', []))->map(fn ($node) => [
                    'executor_key' => (string) data_get($node, 'executor_key'),
                    'type' => (string) data_get($node, 'type'),
                    'label' => (string) data_get($node, 'label'),
                    'yes_target' => data_get($node, 'yes_target'),
                    'no_target' => data_get($node, 'no_target'),
                    'yes_target_executor_key' => (string) data_get($node, 'yes_target_executor_key'),
                    'no_target_executor_key' => (string) data_get($node, 'no_target_executor_key'),
                ])->values()->all(),
                'quality_requirements' => array_values(data_get($row, 'quality_requirements', [])),
                'duration' => (string) data_get($row, 'duration', ''),
                'outputs' => array_values(data_get($row, 'outputs', [])),
                'notes' => (string) data_get($row, 'notes', ''),
            ])->all(),
        ];
    }

    private function processEnvironment(): array
    {
        return array_filter([
            'SystemRoot' => env('SystemRoot', 'C:\\Windows'),
            'WINDIR' => env('WINDIR', 'C:\\Windows'),
            'PATH' => env('PATH') ?: getenv('PATH') ?: null,
            'TEMP' => env('TEMP') ?: getenv('TEMP') ?: storage_path('app/tmp'),
            'TMP' => env('TMP') ?: getenv('TMP') ?: storage_path('app/tmp'),
            'ComSpec' => env('ComSpec') ?: getenv('ComSpec') ?: 'C:\\Windows\\System32\\cmd.exe',
            'PATHEXT' => env('PATHEXT') ?: getenv('PATHEXT') ?: '.COM;.EXE;.BAT;.CMD',
        ], fn ($value) => filled($value));
    }
}
