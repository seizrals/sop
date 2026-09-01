<?php

namespace App\Http\Controllers;

use App\Models\MasterExecutor;
use App\Models\SopDocument;
use App\Models\SopTemplate;
use App\Models\Team;
use App\Models\TeamActivity;
use App\Models\User;
use App\Services\SopPdfGenerator;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SopController extends Controller
{
    public function __construct(
        private readonly SopPdfGenerator $sopPdfGenerator
    ) {
    }

    public function index(): View
    {
        return view('sop.index', [
            'pageTitle' => 'SOP',
            'teams' => Team::withCount(['activities', 'sops'])->orderBy('display_name')->get(),
        ]);
    }

    public function team(Team $team): View
    {
        return view('sop.team', [
            'pageTitle' => 'SOP',
            'team' => $team,
            'activities' => $team->activities()
                ->withCount('sopDocuments')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function storeActivity(Request $request, Team $team): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $team->activities()->create($validated);

        return redirect()
            ->route('sop.team', $team)
            ->with('success', 'Kegiatan berhasil ditambahkan.');
    }

    public function storeMasterExecutor(Request $request, Team $team): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        if (! Schema::hasTable('master_executors')) {
            return response()->json([
                'message' => 'Tabel master pelaksana belum tersedia. Jalankan migration terlebih dahulu.',
            ], 422);
        }

        $label = trim($validated['name']);
        $slug = $this->normalizeExecutorKey($label);

        $executor = MasterExecutor::query()->updateOrCreate(
            [
                'team_id' => $team->id,
                'slug' => $slug,
            ],
            [
                'name' => $label,
                'is_active' => true,
            ]
        );

        return response()->json([
            'message' => 'Pelaksana berhasil disimpan ke database.',
            'executor' => [
                'key' => $executor->slug,
                'label' => $executor->name,
            ],
        ]);
    }

    public function activity(Team $team, TeamActivity $activity): View
    {
        abort_unless((int) $activity->team_id === (int) $team->id, 404);

        return view('sop.activity', [
            'pageTitle' => 'SOP',
            'team' => $team,
            'activity' => $activity,
            'documents' => SopDocument::with(['creator', 'rootDocument'])
                ->where('team_id', $team->id)
                ->where('team_activity_id', $activity->id)
                ->orderByDesc('year')
                ->orderByDesc('revision_number')
                ->get(),
            'templates' => SopTemplate::where('team_id', $team->id)
                ->where(function ($query) use ($activity) {
                    $query->whereNull('team_activity_id')
                        ->orWhere('team_activity_id', $activity->id);
                })
                ->latest()
                ->get(),
        ]);
    }

    public function create(Request $request, Team $team, TeamActivity $activity): View
    {
        abort_unless((int) $activity->team_id === (int) $team->id, 404);

        $template = $request->filled('template')
            ? SopTemplate::findOrFail($request->integer('template'))
            : null;

        $payload = $template ? $template->template_payload : $this->defaultPayload($team, $activity);

        $document = new SopDocument([
            'team_id' => $team->id,
            'team_activity_id' => $activity->id,
            'template_id' => $template?->id,
            'title' => data_get($payload, 'title', ''),
            'sop_number' => data_get($payload, 'sop_number', ''),
            'year' => (int) data_get($payload, 'year', now()->year),
            'status' => 'draft',
            'creation_date' => data_get($payload, 'creation_date', now()->toDateString()),
            'revision_date' => data_get($payload, 'revision_date'),
            'effective_date' => data_get($payload, 'effective_date'),
            'approval_position' => data_get($payload, 'approval_position'),
            'approval_name' => data_get($payload, 'approval_name'),
            'approval_nip' => data_get($payload, 'approval_nip'),
            'legal_basis' => data_get($payload, 'legal_basis', []),
            'executor_qualifications' => data_get($payload, 'executor_qualifications', []),
            'related_documents' => data_get($payload, 'related_documents', []),
            'equipment' => data_get($payload, 'equipment', []),
            'warnings' => data_get($payload, 'warnings', []),
            'recording' => data_get($payload, 'recording', []),
            'executors' => data_get($payload, 'executors', $this->defaultExecutors()),
            'activities' => data_get($payload, 'activities', []),
            'notes' => data_get($payload, 'notes'),
        ]);

        return $this->editorView($document, $team, $activity, $template);
    }

    public function edit(SopDocument $document): View
    {
        $document->load(['team', 'activity']);

        return $this->editorView($document, $document->team, $document->activity);
    }

    public function store(Request $request, Team $team, TeamActivity $activity): RedirectResponse
    {
        abort_unless((int) $activity->team_id === (int) $team->id, 404);

        $document = new SopDocument([
            'team_id' => $team->id,
            'team_activity_id' => $activity->id,
        ]);

        return $this->persistDocument($request, $document, $team, $activity);
    }

    public function update(Request $request, SopDocument $document): RedirectResponse
    {
        $document->load(['team', 'activity']);

        return $this->persistDocument($request, $document, $document->team, $document->activity);
    }

    public function revise(Request $request, SopDocument $document): RedirectResponse
    {
        $document->load(['team', 'activity']);

        $newDocument = $document->replicate();
        $newDocument->parent_document_id = $document->id;
        $newDocument->root_document_id = $document->root_document_id ?: $document->id;
        $newDocument->revision_number = $document->revision_number + 1;
        $newDocument->year = (int) $request->input('revision_year', now()->year);
        $newDocument->status = 'revisi';
        $newDocument->revision_date = now()->toDateString();
        $newDocument->created_by_id = $this->defaultUserId();
        $newDocument->updated_by_id = $this->defaultUserId();
        $newDocument->push();

        return redirect()
            ->route('sop.edit', $newDocument)
            ->with('success', 'Draft revisi berhasil dibuat.');
    }

    public function destroy(SopDocument $document): RedirectResponse
    {
        $isAdmin = auth()->user()?->role === 'admin';

        if ($document->status === 'final' && !$isAdmin) {
            return back()->with('error', 'Hanya admin yang dapat menghapus SOP berstatus final.');
        }

        $document->load(['team', 'activity']);
        $team = $document->team;
        $activity = $document->activity;

        $document->delete();

        if ($team && $activity) {
            return redirect()->route('sop.activity', [$team, $activity])->with('success', 'SOP berhasil dihapus.');
        }

        return back()->with('success', 'SOP berhasil dihapus.');
    }

    public function updateActivity(Request $request, Team $team, TeamActivity $activity): RedirectResponse
    {
        abort_unless((int) $activity->team_id === (int) $team->id, 404);
        abort_unless(auth()->user()?->role === 'admin', 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $activity->update($validated);

        return redirect()->route('sop.team', $team)->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function destroyActivity(Team $team, TeamActivity $activity): RedirectResponse
    {
        abort_unless((int) $activity->team_id === (int) $team->id, 404);
        abort_unless(auth()->user()?->role === 'admin', 403);

        $activity->sopDocuments()->delete();
        $activity->delete();

        return redirect()->route('sop.team', $team)->with('success', 'Kegiatan dan seluruh SOP-nya berhasil dihapus.');
    }

    public function download(SopDocument $document)
    {
        $document->load(['team', 'activity']);
        $filePath = $this->sopPdfGenerator->generate(
            $document,
            $this->executorsForPdf($document),
            collect($document->activities ?? [])
        );

        return response()
            ->download($filePath, ($document->sop_number ?: Str::slug($document->title)) . '.pdf')
            ->deleteFileAfterSend(true);
    }

    public function preview(SopDocument $document)
    {
        $document->load(['team', 'activity']);
        $filePath = $this->sopPdfGenerator->generate(
            $document,
            $this->executorsForPdf($document),
            collect($document->activities ?? [])
        );

        return response()
            ->file($filePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . (($document->sop_number ?: Str::slug($document->title)) . '.pdf') . '"',
            ]);
    }

    public function previewDownload(Request $request, Team $team, TeamActivity $activity)
    {
        abort_unless((int) $activity->team_id === (int) $team->id, 404);

        $validated = $request->validate($this->documentValidationRules());
        $document = new SopDocument();
        $this->fillDocumentFromValidated($document, $validated, $team, $activity);
        $filePath = $this->sopPdfGenerator->generate(
            $document,
            $this->executorsForPdf($document),
            collect($document->activities ?? [])
        );

        return response()
            ->download($filePath, ($document->sop_number ?: Str::slug($document->title ?: 'preview-sop')) . '.pdf')
            ->deleteFileAfterSend(true);
    }

    public function saveTemplate(Request $request, SopDocument $document): RedirectResponse
    {
        $request->validate([
            'template_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        SopTemplate::create([
            'team_id' => $document->team_id,
            'team_activity_id' => $document->team_activity_id,
            'source_sop_id' => $document->id,
            'name' => $request->string('template_name'),
            'template_code' => 'TPL-' . now()->format('YmdHis'),
            'description' => $request->string('description'),
            'template_payload' => $this->templatePayload($document),
        ]);

        return back()->with('success', 'Template SOP berhasil disimpan.');
    }

    private function editorView(
        SopDocument $document,
        Team $team,
        TeamActivity $activity,
        ?SopTemplate $activeTemplate = null
    ): View {
        return view('sop.editor', [
            'pageTitle' => 'Editor SOP',
            'hidePageHeader' => true,
            'team' => $team,
            'activity' => $activity,
            'document' => $document,
            'activeTemplate' => $activeTemplate,
            'templates' => SopTemplate::where('team_id', $team->id)
                ->where(function ($query) use ($activity) {
                    $query->whereNull('team_activity_id')
                        ->orWhere('team_activity_id', $activity->id);
                })
                ->latest()
                ->get(),
            'editorPayload' => [
                'executors' => $this->availableExecutors($team, $document),
                'activities' => $document->activities ?: [],
            ],
        ]);
    }

    private function persistDocument(
        Request $request,
        SopDocument $document,
        Team $team,
        TeamActivity $activity
    ): RedirectResponse {
        $validated = $request->validate($this->documentValidationRules());
        $this->fillDocumentFromValidated($document, $validated, $team, $activity);

        $document->save();
        $this->syncMasterExecutors($team, $document->executors ?? []);

        if (! $document->root_document_id) {
            $document->forceFill(['root_document_id' => $document->id])->save();
        }

        $message = match ($document->status) {
            'final' => 'SOP berhasil difinalisasi.',
            'revisi' => 'Revisi SOP berhasil disimpan.',
            default => 'Draft SOP berhasil disimpan.',
        };

        if ($document->status === 'final') {
            return redirect()
                ->route('sop.activity', [$team, $activity])
                ->with('success', $message);
        }

        return redirect()
            ->route('sop.edit', $document)
            ->with('success', $message);
    }

    private function documentValidationRules(): array
    {
        return [
            'sop_number' => ['nullable', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'year' => ['required', 'digits:4'],
            'creation_date' => ['nullable', 'date'],
            'revision_date' => ['nullable', 'date'],
            'effective_date' => ['nullable', 'date'],
            'approval_position' => ['nullable', 'string', 'max:255'],
            'approval_name' => ['nullable', 'string', 'max:255'],
            'approval_nip' => ['nullable', 'string', 'max:255'],
            'legal_basis_text' => ['nullable', 'string'],
            'executor_qualifications_text' => ['nullable', 'string'],
            'related_documents_text' => ['nullable', 'string'],
            'equipment_text' => ['nullable', 'string'],
            'warnings_text' => ['nullable', 'string'],
            'recording_text' => ['nullable', 'string'],
            'executors_json' => ['nullable', 'string'],
            'activities_json' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'status_action' => ['required', 'in:draft,revisi,final'],
        ];
    }

    private function fillDocumentFromValidated(
        SopDocument $document,
        array $validated,
        Team $team,
        TeamActivity $activity
    ): void {
        $executors = $this->parseExecutors($validated['executors_json'] ?? null);
        $activities = $this->parseActivities($validated['activities_json'] ?? null, $executors);

        $document->fill([
            'team_id' => $team->id,
            'team_activity_id' => $activity->id,
            'created_by_id' => $document->created_by_id ?: $this->defaultUserId(),
            'updated_by_id' => $this->defaultUserId(),
            'sop_number' => $validated['sop_number'] ?: null,
            'title' => $validated['title'],
            'year' => (int) $validated['year'],
            'status' => $validated['status_action'],
            'creation_date' => $validated['creation_date'] ?: null,
            'revision_date' => $validated['revision_date'] ?: null,
            'effective_date' => $validated['effective_date'] ?: null,
            'approval_position' => $validated['approval_position'] ?: null,
            'approval_name' => $validated['approval_name'] ?: null,
            'approval_nip' => $validated['approval_nip'] ?: null,
            'legal_basis' => $this->parseLines($validated['legal_basis_text'] ?? null),
            'executor_qualifications' => $this->parseLines($validated['executor_qualifications_text'] ?? null),
            'related_documents' => $this->parseLines($validated['related_documents_text'] ?? null),
            'equipment' => $this->parseLines($validated['equipment_text'] ?? null),
            'warnings' => $this->parseLines($validated['warnings_text'] ?? null),
            'recording' => $this->parseLines($validated['recording_text'] ?? null),
            'executors' => $executors,
            'activities' => $activities,
            'notes' => $validated['notes'] ?: null,
        ]);
    }

    private function defaultPayload(Team $team, TeamActivity $activity): array
    {
        return [
            'sop_number' => '',
            'title' => '',
            'year' => now()->year,
            'creation_date' => now()->toDateString(),
            'revision_date' => null,
            'effective_date' => null,
            'approval_position' => 'Kepala Badan Pusat Statistik Kabupaten Gorontalo Utara',
            'approval_name' => 'Depit Rudianto, SST, M.Ec.Dev.',
            'approval_nip' => '198606302009121003',
            'legal_basis' => [
                'Peraturan Presiden No. 86 Tahun 2007 tentang Badan Pusat Statistik.',
            ],
            'executor_qualifications' => [
                'Memahami alur kerja kegiatan.',
                'Mampu menggunakan perangkat pengolahan data.',
            ],
            'related_documents' => [
                'SOP terkait kegiatan ' . $activity->name,
            ],
            'equipment' => [
                'Laptop/Komputer',
                'Dokumen pendukung kegiatan',
            ],
            'warnings' => [],
            'recording' => [],
            'executors' => $this->defaultExecutors(),
            'activities' => [],
            'notes' => 'Template awal SOP tim ' . $team->display_name,
        ];
    }

    private function defaultExecutors(): array
    {
        return [];
    }

    private function parseLines(?string $value): array
    {
        if (! $value) {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', $value))
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values()
            ->all();
    }

    private function parseExecutors(?string $json): array
    {
        $decoded = json_decode($json ?: '[]', true);

        if (! is_array($decoded) || $decoded === []) {
            return $this->defaultExecutors();
        }

        return collect($decoded)
            ->map(function ($item) {
                $label = trim((string) data_get($item, 'label', ''));
                $key = trim((string) data_get($item, 'key', ''));

                if ($label === '') {
                    return null;
                }

                return [
                    'key' => $this->normalizeExecutorKey($key !== '' ? $key : Str::slug($label, '_')),
                    'label' => $label,
                ];
            })
            ->filter()
            ->unique('key')
            ->values()
            ->all();
    }

    private function parseActivities(?string $json, array $executors): array
    {
        $decoded = json_decode($json ?: '[]', true);

        if (! is_array($decoded)) {
            return [];
        }

        $executorKeys = collect($executors)->pluck('key')->filter()->values();

        return collect($decoded)
            ->map(function ($row) use ($executorKeys) {
                $flowNodes = collect(data_get($row, 'flow_nodes', []))
                    ->map(function ($node) use ($executorKeys) {
                        $executorKey = $this->normalizeExecutorKey((string) data_get($node, 'executor_key', ''));

                        if ($executorKey === '' || ! $executorKeys->contains($executorKey)) {
                            return null;
                        }

                        return [
                            'executor_key' => $executorKey,
                            'type' => trim((string) data_get($node, 'type', '')),
                            'label' => trim((string) data_get($node, 'label', '')),
                            'yes_target' => data_get($node, 'type') === 'decision' ? data_get($node, 'yes_target') : null,
                            'no_target' => data_get($node, 'type') === 'decision' ? data_get($node, 'no_target') : null,
                            'yes_target_executor_key' => data_get($node, 'type') === 'decision' ? $this->normalizeExecutorKey((string) data_get($node, 'yes_target_executor_key', '')) : null,
                            'no_target_executor_key' => data_get($node, 'type') === 'decision' ? $this->normalizeExecutorKey((string) data_get($node, 'no_target_executor_key', '')) : null,
                        ];
                    })
                    ->filter(fn ($node) => filled($node['type'] ?? null))
                    ->values();

                if ($flowNodes->isEmpty()) {
                    $performers = collect(data_get($row, 'performers', []));
                    $flowNodes = $performers
                        ->map(function ($data, $key) use ($executorKeys) {
                            $executorKey = $this->normalizeExecutorKey((string) $key);

                            if ($executorKey === '' || ! $executorKeys->contains($executorKey)) {
                                return null;
                            }

                            return [
                                'executor_key' => $executorKey,
                                'type' => trim((string) data_get($data, 'type', '')),
                                'label' => trim((string) data_get($data, 'label', '')),
                                'yes_target' => data_get($data, 'type') === 'decision' ? data_get($data, 'yes_target') : null,
                                'no_target' => data_get($data, 'type') === 'decision' ? data_get($data, 'no_target') : null,
                                'yes_target_executor_key' => data_get($data, 'type') === 'decision' ? $this->normalizeExecutorKey((string) data_get($data, 'yes_target_executor_key', '')) : null,
                                'no_target_executor_key' => data_get($data, 'type') === 'decision' ? $this->normalizeExecutorKey((string) data_get($data, 'no_target_executor_key', '')) : null,
                            ];
                        })
                        ->filter(fn ($node) => filled($node['type'] ?? null))
                        ->values();
                }

                $selectedExecutorKeys = $flowNodes
                    ->pluck('executor_key')
                    ->unique()
                    ->values();

                $normalizedPerformers = $flowNodes
                    ->unique('executor_key')
                    ->mapWithKeys(fn ($node) => [
                        $node['executor_key'] => [
                            'type' => $node['type'],
                            'label' => $node['type'] === 'decision' ? $node['label'] : '',
                            'yes_target' => $node['type'] === 'decision' ? (is_numeric($node['yes_target'] ?? null) ? (int) $node['yes_target'] : null) : null,
                            'no_target' => $node['type'] === 'decision' ? (is_numeric($node['no_target'] ?? null) ? (int) $node['no_target'] : null) : null,
                            'yes_target_executor_key' => $node['type'] === 'decision' ? $this->normalizeExecutorKey((string) ($node['yes_target_executor_key'] ?? '')) : null,
                            'no_target_executor_key' => $node['type'] === 'decision' ? $this->normalizeExecutorKey((string) ($node['no_target_executor_key'] ?? '')) : null,
                        ],
                    ]);

                return [
                    'name' => trim((string) data_get($row, 'name', '')),
                    'selected_executor_keys' => $selectedExecutorKeys->all(),
                    'flow_nodes' => $flowNodes
                        ->map(fn ($node) => [
                            'executor_key' => $node['executor_key'],
                            'type' => $node['type'],
                            'label' => $node['type'] === 'decision' ? $node['label'] : '',
                            'yes_target' => $node['type'] === 'decision' ? (is_numeric($node['yes_target'] ?? null) ? (int) $node['yes_target'] : null) : null,
                            'no_target' => $node['type'] === 'decision' ? (is_numeric($node['no_target'] ?? null) ? (int) $node['no_target'] : null) : null,
                            'yes_target_executor_key' => $node['type'] === 'decision' ? $this->normalizeExecutorKey((string) ($node['yes_target_executor_key'] ?? '')) : null,
                            'no_target_executor_key' => $node['type'] === 'decision' ? $this->normalizeExecutorKey((string) ($node['no_target_executor_key'] ?? '')) : null,
                        ])
                        ->all(),
                    'performers' => $normalizedPerformers->all(),
                    'quality_requirements' => $this->linesFromArray(data_get($row, 'quality_requirements', [])),
                    'duration' => trim((string) data_get($row, 'duration', '')),
                    'outputs' => $this->linesFromArray(data_get($row, 'outputs', [])),
                    'notes' => trim((string) data_get($row, 'notes', '')),
                ];
            })
            ->filter(fn ($row) => $row['name'] !== '')
            ->values()
            ->all();
    }

    private function linesFromArray(mixed $value): array
    {
        if (is_string($value)) {
            return $this->parseLines($value);
        }

        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values()
            ->all();
    }

    private function templatePayload(SopDocument $document): array
    {
        return [
            'sop_number' => $document->sop_number,
            'title' => $document->title,
            'year' => $document->year,
            'creation_date' => optional($document->creation_date)->format('Y-m-d'),
            'revision_date' => optional($document->revision_date)->format('Y-m-d'),
            'effective_date' => optional($document->effective_date)->format('Y-m-d'),
            'approval_position' => $document->approval_position,
            'approval_name' => $document->approval_name,
            'approval_nip' => $document->approval_nip,
            'legal_basis' => $document->legal_basis ?? [],
            'executor_qualifications' => $document->executor_qualifications ?? [],
            'related_documents' => $document->related_documents ?? [],
            'equipment' => $document->equipment ?? [],
            'warnings' => $document->warnings ?? [],
            'recording' => $document->recording ?? [],
            'executors' => $document->executors ?? [],
            'activities' => $document->activities ?? [],
            'notes' => $document->notes,
        ];
    }

    private function defaultUserId(): ?int
    {
        return User::query()->where('role', 'admin')->value('id')
            ?: User::query()->value('id');
    }

    private function availableExecutors(Team $team, SopDocument $document): array
    {
        $documentExecutors = collect($document->executors ?? []);

        if (! Schema::hasTable('master_executors')) {
            return $documentExecutors->values()->all();
        }

        return MasterExecutor::query()
            ->where('is_active', true)
            ->where(function ($query) use ($team) {
                $query->whereNull('team_id')
                    ->orWhere('team_id', $team->id);
            })
            ->orderBy('name')
            ->get()
            ->map(fn (MasterExecutor $executor) => [
                'key' => $executor->slug,
                'label' => $executor->name,
            ])
            ->merge($documentExecutors)
            ->unique('key')
            ->values()
            ->all();
    }

    private function syncMasterExecutors(Team $team, array $executors): void
    {
        if (! Schema::hasTable('master_executors')) {
            return;
        }

        foreach ($executors as $executor) {
            $label = trim((string) data_get($executor, 'label', ''));
            $slug = $this->normalizeExecutorKey((string) data_get($executor, 'key', ''));

            if ($label === '' || $slug === '') {
                continue;
            }

            MasterExecutor::query()->updateOrCreate(
                [
                    'team_id' => $team->id,
                    'slug' => $slug,
                ],
                [
                    'name' => $label,
                    'is_active' => true,
                ]
            );
        }
    }

    private function normalizeExecutorKey(string $value): string
    {
        return trim(Str::slug($value, '_'));
    }

    private function executorsForPdf(SopDocument $document): Collection
    {
        $documentExecutors = collect($document->executors ?? []);

        $usedExecutorKeys = collect($document->activities ?? [])
            ->flatMap(function ($row) {
                $flowNodeKeys = collect(data_get($row, 'flow_nodes', []))
                    ->pluck('executor_key')
                    ->map(fn ($key) => trim((string) $key))
                    ->filter();

                if ($flowNodeKeys->isNotEmpty()) {
                    return $flowNodeKeys;
                }

                $selected = collect(data_get($row, 'selected_executor_keys', []))
                    ->map(fn ($key) => trim((string) $key))
                    ->filter();

                if ($selected->isNotEmpty()) {
                    return $selected;
                }

                return collect(data_get($row, 'performers', []))
                    ->keys()
                    ->map(fn ($key) => trim((string) $key))
                    ->filter();
            })
            ->unique()
            ->values();

        if ($usedExecutorKeys->isEmpty()) {
            return $documentExecutors->values();
        }

        return $usedExecutorKeys
            ->map(function ($key) use ($documentExecutors) {
                $executor = $documentExecutors->firstWhere('key', $key);

                return [
                    'key' => $key,
                    'label' => data_get($executor, 'label', Str::of($key)->replace('_', ' ')->title()->toString()),
                ];
            })
            ->values();
    }
}
