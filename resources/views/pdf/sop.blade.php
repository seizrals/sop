<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <title>{{ $document->title }}</title>
        <style>
            @page {
                margin: 10mm;
            }

            body {
                font-family: Arial, sans-serif;
                font-size: 10px;
                color: #111827;
                margin: 0;
            }

            * {
                box-sizing: border-box;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            td,
            th {
                border: 1px solid #111827;
                padding: 6px;
                vertical-align: top;
            }

            .page-break {
                page-break-after: always;
            }

            .center {
                text-align: center;
            }

            .muted {
                color: #6b7280;
            }

            .identity-table td,
            .identity-table th,
            .detail-table td,
            .detail-table th,
            .activity-table td,
            .activity-table th {
                border: 1px solid #111827;
            }

            .identity-table {
                table-layout: fixed;
            }

            .detail-table {
                table-layout: fixed;
            }

            .identity-left {
                width: 50%;
                padding: 0;
                vertical-align: middle;
            }

            .identity-left-inner {
                min-height: 163px;
                text-align: center;
                padding: 0 16px;
            }

            .agency-title {
                font-size: 14px;
                font-weight: 700;
                letter-spacing: 0.3px;
                text-transform: uppercase;
                line-height: 1.45;
            }

            .identity-label {
                width: 140px;
                background: #f3f4f6;
                font-weight: 700;
                text-transform: uppercase;
                font-size: 9px;
                vertical-align: middle;
                padding: 6px 8px;
            }

            .identity-value {
                font-size: 10px;
                vertical-align: middle;
                padding: 6px 10px;
            }

            .approval-box {
                min-height: 142px;
                text-align: center;
                vertical-align: top;
            }

            .approval-position {
                line-height: 1.35;
            }

            .approval-space {
                height: 78px;
            }

            .approval-name {
                font-weight: 700;
                text-decoration: underline;
                margin-top: 4px;
            }

            .sop-title {
                font-size: 11px;
                font-weight: 700;
                line-height: 1.45;
            }

            .section-heading {
                background: #f3f4f6;
                font-weight: 700;
                text-transform: uppercase;
                font-size: 9px;
                letter-spacing: 0.2px;
            }

            .section-body {
                min-height: 78px;
                line-height: 1.55;
                font-size: 9.5px;
            }

            .section-body div {
                margin-bottom: 2px;
            }

            .activity-table {
                table-layout: auto;
                width: 100%;
            }

            .activity-table thead th {
                background: #f3f4f6;
                text-align: center;
                font-size: 8px;
                text-transform: uppercase;
                font-weight: 700;
                line-height: 1.25;
                padding: 4px 3px;
                vertical-align: middle;
            }

            .activity-table thead tr {
                height: 24px;
            }

            .activity-table tbody td {
                padding: 2px 4px;
                vertical-align: middle;
            }

            .activity-table tbody tr {
                height: 52px;
            }

            .activity-table .header-merged {
                border-bottom: 0;
            }

            .activity-table .header-empty {
                background: #f3f4f6;
                border-top: 0;
                padding: 0;
                height: 0;
                line-height: 0;
                font-size: 0;
            }

            .activity-no {
                text-align: center;
                font-weight: 700;
                font-size: 9px;
                padding-left: 1px;
                padding-right: 1px;
                vertical-align: middle;
            }

            .activity-name {
                font-size: 9.5px;
                font-weight: 700;
                line-height: 1.35;
                margin-bottom: 0;
            }

            .activity-flow {
                font-size: 8.5px;
                line-height: 1.45;
                color: #374151;
            }

            .activity-flow-label {
                font-weight: 700;
            }

            .executor-cell {
                text-align: center;
                padding: 0;
                height: 52px;
                vertical-align: middle;
                overflow: visible;
                position: relative;
            }

            .flow-svg {
                display: block;
                width: calc(100% + 10px);
                height: 64px;
                margin: -6px -5px;
                position: relative;
                z-index: 2;
            }

            .text-cell {
                font-size: 8.8px;
                line-height: 1.5;
                vertical-align: middle;
            }

            .text-cell div {
                margin-bottom: 2px;
            }

            .duration-cell {
                text-align: center;
                font-size: 8.8px;
                font-weight: 700;
                line-height: 1.45;
                vertical-align: middle;
            }

            .empty-node {
                color: #9ca3af;
                font-size: 8px;
            }

        </style>
    </head>
    <body>
        @php
            $logoPath = resource_path('img/logo-bps.png');
            $logoSrc = file_exists($logoPath)
                ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
                : null;

            $executors = $executors instanceof \Illuminate\Support\Collection ? $executors->values() : collect($executors ?? [])->values();
            $activities = $activities instanceof \Illuminate\Support\Collection ? $activities->values() : collect($activities ?? [])->values();

            $executorCount = max($executors->count(), 1);
            $executorTotalWidthPercent = 33;
            $executorWidthPercent = $executorTotalWidthPercent / $executorCount;

            $formatDate = function ($value): string {
                if (blank($value)) {
                    return '-';
                }

                try {
                    return \Carbon\Carbon::parse($value)->locale('id')->translatedFormat('d F Y');
                } catch (\Throwable $exception) {
                    return (string) $value;
                }
            };

            $normalizeLines = function ($items): \Illuminate\Support\Collection {
                return collect(is_array($items) ? $items : [])
                    ->map(fn ($item) => trim((string) $item))
                    ->filter()
                    ->values();
            };

            $renderListHtml = function ($items, bool $numbered = true) use ($normalizeLines): string {
                $lines = $normalizeLines($items);

                if ($lines->isEmpty()) {
                    return '<div class="muted">-</div>';
                }

                return $lines->map(function ($item, $index) use ($numbered) {
                    $prefix = $numbered ? ($index + 1) . '. ' : '&#8226; ';
                    return '<div>' . $prefix . e($item) . '</div>';
                })->implode('');
            };

            $wrapSvgText = function (string $text, int $maxChars = 10, int $maxLines = 3): array {
                $words = preg_split('/\s+/', trim($text)) ?: [];
                $lines = [];
                $current = '';

                foreach ($words as $word) {
                    $candidate = trim($current . ' ' . $word);

                    if ($current !== '' && mb_strlen($candidate) > $maxChars) {
                        $lines[] = $current;
                        $current = $word;
                    } else {
                        $current = $candidate;
                    }
                }

                if ($current !== '') {
                    $lines[] = $current;
                }

                $lines = array_values(array_filter($lines));

                if (count($lines) > $maxLines) {
                    $lines = array_slice($lines, 0, $maxLines);
                    $lines[$maxLines - 1] = rtrim(\Illuminate\Support\Str::limit($lines[$maxLines - 1], $maxChars, '...'));
                }

                return $lines ?: [''];
            };

            $svgDataUri = fn (string $svg) => 'data:image/svg+xml;base64,' . base64_encode($svg);

            $buildRowFlowSvgs = function (array $row, int $rowIndex, bool $isFirstRow, bool $isLastRow) use ($executors, $wrapSvgText, $svgDataUri): array {
                $executorKeys = $executors->pluck('key')->all();
                $indexByKey = array_flip($executorKeys);

                $rawNodes = collect(data_get($row, 'flow_nodes', []))
                    ->filter(fn ($node) => filled(data_get($node, 'type')) && filled(data_get($node, 'executor_key')))
                    ->values()
                    ->all();

                $nodes = [];
                $seen = [];

                foreach ($rawNodes as $node) {
                    $key = (string) data_get($node, 'executor_key');
                    if (! array_key_exists($key, $indexByKey)) {
                        continue;
                    }
                    if (isset($seen[$key])) {
                        continue;
                    }
                    $seen[$key] = true;
                    $nodes[] = [
                        'executor_key' => $key,
                        'type' => (string) data_get($node, 'type'),
                        'label' => (string) data_get($node, 'label'),
                        'yes_target' => data_get($node, 'yes_target'),
                        'no_target' => data_get($node, 'no_target'),
                    ];
                }

                if ($nodes === []) {
                    $performers = data_get($row, 'performers', []);
                    if (is_array($performers)) {
                        foreach ($executorKeys as $executorKey) {
                            $performer = data_get($performers, $executorKey);
                            if (! is_array($performer) || blank(data_get($performer, 'type'))) {
                                continue;
                            }
                            $nodes[] = [
                                'executor_key' => (string) $executorKey,
                                'type' => (string) data_get($performer, 'type'),
                                'label' => (string) data_get($performer, 'label'),
                                'yes_target' => data_get($performer, 'yes_target'),
                                'no_target' => data_get($performer, 'no_target'),
                            ];
                        }
                    }
                }

                $viewHeight = 52;
                $bleedX = 6;
                $bleedTop = 6;
                $canvasHeight = $viewHeight + ($bleedTop * 2);
                $centerY = 26;
                $stroke = 0.8;

                $cells = array_fill(0, max(count($executorKeys), 1), [
                    'lines' => [],
                    'shapes' => [],
                    'labels' => [],
                ]);

                $edge = function (string $type): array {
                    return match (trim($type)) {
                        'decision' => ['l' => 25, 'r' => 75, 't' => 9, 'b' => 43],
                        'start', 'end' => ['l' => 30, 'r' => 70, 't' => 16, 'b' => 36],
                        default => ['l' => 24, 'r' => 76, 't' => 15, 'b' => 37],
                    };
                };

                $arrow = function (string $dir, float $x, float $y): string {
                    return match ($dir) {
                        'right' => '<polygon points="' . $x . ',' . $y . ' ' . ($x - 4.2) . ',' . ($y - 2.9) . ' ' . ($x - 4.2) . ',' . ($y + 2.9) . '" fill="#111827"/>',
                        'left' => '<polygon points="' . $x . ',' . $y . ' ' . ($x + 4.2) . ',' . ($y - 2.9) . ' ' . ($x + 4.2) . ',' . ($y + 2.9) . '" fill="#111827"/>',
                        default => '<polygon points="' . $x . ',' . $y . ' ' . ($x - 2.9) . ',' . ($y - 4.2) . ' ' . ($x + 2.9) . ',' . ($y - 4.2) . '" fill="#111827"/>',
                    };
                };

                $shapeSvg = function (string $type, string $label) use ($wrapSvgText): string {
                    $type = trim($type);
                    $label = trim($label);

                    if ($type === 'decision') {
                        $lines = $wrapSvgText($label !== '' ? $label : 'Keputusan', 12, 3);
                        $lineCount = count($lines);
                        $lineHeight = 8;
                        $startDy = -((($lineCount - 1) / 2) * $lineHeight);
                        $tspans = '';
                        foreach ($lines as $i => $line) {
                            $dy = $i === 0 ? $startDy : $lineHeight;
                            $tspans .= '<tspan x="50" dy="' . $dy . '">' . htmlspecialchars($line, ENT_QUOTES | ENT_XML1, 'UTF-8') . '</tspan>';
                        }

                        return '<polygon points="50,9 75,26 50,43 25,26" fill="#ffffff" stroke="#111827" stroke-width="0.8"/>'
                            . '<text x="50" y="26" font-family="Arial, sans-serif" font-size="7.8" font-weight="400" fill="#111827" text-anchor="middle" dominant-baseline="middle">'
                            . $tspans
                            . '</text>';
                    }

                    if ($type === 'start' || $type === 'end') {
                        $text = $type === 'start' ? 'Start' : 'End';
                        return '<rect x="30" y="16" width="40" height="20" rx="10" ry="10" fill="#ffffff" stroke="#111827" stroke-width="0.8"/>'
                            . '<text x="50" y="26" font-family="Arial, sans-serif" font-size="7.8" font-weight="400" fill="#111827" text-anchor="middle" dominant-baseline="middle">' . $text . '</text>';
                    }

                    return '<rect x="24" y="15" width="52" height="22" fill="#ffffff" stroke="#111827" stroke-width="0.8"/>';
                };

                $orderedNodes = collect($nodes)
                    ->filter(fn ($node) => array_key_exists((string) data_get($node, 'executor_key'), $indexByKey))
                    ->values()
                    ->all();

                foreach ($orderedNodes as $node) {
                    $col = $indexByKey[$node['executor_key']] ?? null;
                    if ($col === null) {
                        continue;
                    }
                    $cells[$col]['shapes'][] = $shapeSvg($node['type'], $node['label']);
                }

                if ($orderedNodes !== []) {
                    $firstNode = $orderedNodes[0];
                    $firstCol = $indexByKey[$firstNode['executor_key']];
                    $firstEdge = $edge($firstNode['type']);
                    if (! $isFirstRow) {
                        $cells[$firstCol]['lines'][] = '<line x1="50" y1="-' . $bleedTop . '" x2="50" y2="' . ($firstEdge['t'] - 1) . '" stroke="#111827" stroke-width="' . $stroke . '"/>' . $arrow('down', 50, $firstEdge['t'] - 1);
                    }

                    $lastNode = $orderedNodes[count($orderedNodes) - 1];
                    $lastCol = $indexByKey[$lastNode['executor_key']];
                    $lastEdge = $edge($lastNode['type']);
                    if (! $isLastRow) {
                        $cells[$lastCol]['lines'][] = '<line x1="50" y1="' . ($lastEdge['b'] + 1) . '" x2="50" y2="' . ($viewHeight + $bleedTop) . '" stroke="#111827" stroke-width="' . $stroke . '"/>';
                    }
                }

                for ($i = 0; $i < count($orderedNodes) - 1; $i++) {
                    $from = $orderedNodes[$i];
                    $to = $orderedNodes[$i + 1];
                    $fromCol = $indexByKey[$from['executor_key']];
                    $toCol = $indexByKey[$to['executor_key']];
                    $fromEdge = $edge($from['type']);
                    $toEdge = $edge($to['type']);

                    if ($fromCol === $toCol) {
                        continue;
                    }

                    if ($fromCol < $toCol) {
                        $cells[$fromCol]['lines'][] = '<line x1="' . ($fromEdge['r'] + 1) . '" y1="' . $centerY . '" x2="' . (100 + $bleedX) . '" y2="' . $centerY . '" stroke="#111827" stroke-width="' . $stroke . '"/>';
                        if (trim($from['type']) === 'decision') {
                            $cells[$fromCol]['labels'][] = '<text x="' . ($fromEdge['r'] + 7) . '" y="16" font-family="Arial, sans-serif" font-size="7.6" font-weight="400" fill="#111827">Y</text>';
                            if (filled($from['no_target'])) {
                                $cells[$fromCol]['lines'][] = '<line x1="' . ($fromEdge['l'] - 1) . '" y1="' . $centerY . '" x2="-' . $bleedX . '" y2="' . $centerY . '" stroke="#111827" stroke-width="' . $stroke . '"/>' . $arrow('left', -$bleedX, $centerY);
                                $cells[$fromCol]['labels'][] = '<text x="6" y="16" font-family="Arial, sans-serif" font-size="7.6" font-weight="400" fill="#111827">T</text>';
                            }
                        }

                        for ($c = $fromCol + 1; $c <= $toCol - 1; $c++) {
                            $cells[$c]['lines'][] = '<line x1="-' . $bleedX . '" y1="' . $centerY . '" x2="' . (100 + $bleedX) . '" y2="' . $centerY . '" stroke="#111827" stroke-width="' . $stroke . '"/>';
                        }

                        $cells[$toCol]['lines'][] = '<line x1="-' . $bleedX . '" y1="' . $centerY . '" x2="' . ($toEdge['l'] - 1) . '" y2="' . $centerY . '" stroke="#111827" stroke-width="' . $stroke . '"/>' . $arrow('right', $toEdge['l'] - 1, $centerY);
                    } else {
                        $cells[$fromCol]['lines'][] = '<line x1="' . ($fromEdge['l'] - 1) . '" y1="' . $centerY . '" x2="-' . $bleedX . '" y2="' . $centerY . '" stroke="#111827" stroke-width="' . $stroke . '"/>';
                        if (trim($from['type']) === 'decision') {
                            $cells[$fromCol]['labels'][] = '<text x="6" y="16" font-family="Arial, sans-serif" font-size="7.6" font-weight="400" fill="#111827">Y</text>';
                            if (filled($from['no_target'])) {
                                $cells[$fromCol]['lines'][] = '<line x1="' . ($fromEdge['r'] + 1) . '" y1="' . $centerY . '" x2="' . (100 + $bleedX) . '" y2="' . $centerY . '" stroke="#111827" stroke-width="' . $stroke . '"/>' . $arrow('right', 100 + $bleedX, $centerY);
                                $cells[$fromCol]['labels'][] = '<text x="' . ($fromEdge['r'] + 7) . '" y="16" font-family="Arial, sans-serif" font-size="7.6" font-weight="400" fill="#111827">T</text>';
                            }
                        }

                        for ($c = $toCol + 1; $c <= $fromCol - 1; $c++) {
                            $cells[$c]['lines'][] = '<line x1="-' . $bleedX . '" y1="' . $centerY . '" x2="' . (100 + $bleedX) . '" y2="' . $centerY . '" stroke="#111827" stroke-width="' . $stroke . '"/>';
                        }

                        $cells[$toCol]['lines'][] = '<line x1="' . ($toEdge['r'] + 1) . '" y1="' . $centerY . '" x2="' . (100 + $bleedX) . '" y2="' . $centerY . '" stroke="#111827" stroke-width="' . $stroke . '"/>' . $arrow('left', $toEdge['r'] + 1, $centerY);
                    }
                }

                $svgs = [];
                for ($c = 0; $c < max(count($executorKeys), 1); $c++) {
                    $content = implode('', array_merge($cells[$c]['lines'], $cells[$c]['shapes'], $cells[$c]['labels']));
                    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . (100 + ($bleedX * 2)) . '" height="' . $canvasHeight . '" viewBox="-' . $bleedX . ' -' . $bleedTop . ' ' . (100 + ($bleedX * 2)) . ' ' . $canvasHeight . '">'
                        . $content
                        . '</svg>';
                    $svgs[$executorKeys[$c] ?? (string) $c] = $svgDataUri($svg);
                }

                return $svgs;
            };

            $flowSummary = function (array $row, \Illuminate\Support\Collection $executors): string {
                $flowNodes = collect(data_get($row, 'flow_nodes', []))->values();

                if ($flowNodes->isEmpty()) {
                    return '-';
                }

                return $flowNodes->map(function ($node, $index) use ($executors) {
                    $executorLabel = data_get($executors->firstWhere('key', data_get($node, 'executor_key')), 'label', data_get($node, 'executor_key'));
                    $shapeLabel = match (data_get($node, 'type')) {
                        'start' => 'Start',
                        'end' => 'End',
                        'decision' => 'Decision',
                        default => 'Proses',
                    };

                    return ($index + 1) . '. ' . $executorLabel . ' (' . $shapeLabel . ')';
                })->implode(' -> ');
            };

            $durationSpans = [];
            $currentDurationKey = null;
            $currentSpanStart = null;

            foreach ($activities as $activityIndex => $activityRow) {
                $duration = trim((string) data_get($activityRow, 'duration', ''));
                $durationKey = mb_strtolower($duration);

                if ($durationKey === '') {
                    $durationSpans[$activityIndex] = 1;
                    $currentDurationKey = null;
                    $currentSpanStart = null;
                    continue;
                }

                if ($durationKey === $currentDurationKey && $currentSpanStart !== null) {
                    $durationSpans[$currentSpanStart] = ($durationSpans[$currentSpanStart] ?? 1) + 1;
                    $durationSpans[$activityIndex] = 0;
                    continue;
                }

                $currentDurationKey = $durationKey;
                $currentSpanStart = $activityIndex;
                $durationSpans[$activityIndex] = 1;
            }
        @endphp

        <table class="identity-table">
            <colgroup>
                <col style="width: 50%;">
                <col style="width: 140px;">
                <col style="width: auto;">
            </colgroup>
            <tr>
                <td class="identity-left" rowspan="6">
                    <div class="identity-left-inner">
                        @if ($logoSrc)
                            <img src="{{ $logoSrc }}" alt="Logo BPS" style="height: 78px; width: auto; margin: 0 auto 16px;">
                        @endif
                        <div class="agency-title">Badan Pusat Statistik</div>
                        <div class="agency-title">Kabupaten Gorontalo Utara</div>
                        <div class="agency-title">Tim Statistik {{ strtoupper($document->team?->display_name ?? '-') }}</div>
                    </div>
                </td>
                <td class="identity-label">Nomor SOP</td>
                <td class="identity-value">{{ $document->sop_number ?: '-' }}</td>
            </tr>
            <tr>
                <td class="identity-label">Tgl. Pembuatan</td>
                <td class="identity-value">{{ $formatDate($document->creation_date) }}</td>
            </tr>
            <tr>
                <td class="identity-label">Tgl. Revisi</td>
                <td class="identity-value">{{ $formatDate($document->revision_date) }}</td>
            </tr>
            <tr>
                <td class="identity-label">Tgl. Efektif</td>
                <td class="identity-value">{{ $formatDate($document->effective_date) }}</td>
            </tr>
            <tr>
                <td class="identity-label">Disahkan Oleh</td>
                <td class="approval-box">
                    <div class="approval-position">{{ $document->approval_position ?: 'Kepala Badan Pusat Statistik Kabupaten Gorontalo Utara' }}</div>
                    <div class="approval-space"></div>
                    <div class="approval-name">{{ $document->approval_name ?: '-' }}</div>
                    <div>NIP. {{ $document->approval_nip ?: '-' }}</div>
                </td>
            </tr>
            <tr>
                <td class="identity-label">Nama SOP</td>
                <td class="sop-title">{{ $document->title ?: '-' }}</td>
            </tr>
        </table>

        <table class="detail-table">
            <colgroup>
                <col style="width: 50%;">
                <col style="width: 50%;">
            </colgroup>
            <tr>
                <td class="section-heading">Dasar Hukum</td>
                <td class="section-heading">Kualifikasi Pelaksana</td>
            </tr>
            <tr>
                <td class="section-body">{!! $renderListHtml($document->legal_basis ?? [], true) !!}</td>
                <td class="section-body">{!! $renderListHtml($document->executor_qualifications ?? [], false) !!}</td>
            </tr>
            <tr>
                <td class="section-heading">Keterkaitan</td>
                <td class="section-heading">Peralatan/Perlengkapan</td>
            </tr>
            <tr>
                <td class="section-body">{!! $renderListHtml($document->related_documents ?? [], true) !!}</td>
                <td class="section-body">{!! $renderListHtml($document->equipment ?? [], true) !!}</td>
            </tr>
            <tr>
                <td class="section-heading">Peringatan</td>
                <td class="section-heading">Pencatatan dan Pendataan</td>
            </tr>
            <tr>
                <td class="section-body">{!! $renderListHtml($document->warnings ?? [], true) !!}</td>
                <td class="section-body">{!! $renderListHtml($document->recording ?? [], true) !!}</td>
            </tr>
        </table>

        <div class="page-break"></div>

        <table class="activity-table">
            <thead>
                <tr>
                    <th rowspan="2" class="header-merged" style="width: 3%;">No</th>
                    <th rowspan="2" class="header-merged" style="width: 29%;">Kegiatan</th>
                    <th colspan="{{ $executorCount }}" style="width: {{ $executorTotalWidthPercent }}%;">Pelaksana</th>
                    <th colspan="3" style="width: 26%;">Mutu Baku</th>
                    <th rowspan="2" class="header-merged" style="width: 9%;">Keterangan</th>
                </tr>
                <tr>
                    @forelse ($executors as $executor)
                        <th style="width: {{ number_format($executorWidthPercent, 2, '.', '') }}%;">{{ $executor['label'] }}</th>
                    @empty
                        <th style="width: {{ $executorTotalWidthPercent }}%;">Pelaksana</th>
                    @endforelse
                    <th style="width: 10%;">Kelengkapan</th>
                    <th style="width: 6%;">Waktu</th>
                    <th style="width: 10%;">Output</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($activities as $index => $row)
                    @php
                        $row = is_array($row) ? $row : (array) $row;
                    @endphp
                    <tr>
                        <td class="activity-no">{{ $index + 1 }}</td>
                        <td>
                            <div class="activity-name">{{ data_get($row, 'name', '-') }}</div>
                        </td>
                        @php
                            $rowSvgs = $buildRowFlowSvgs($row, $index, $index === 0, $index === ($activities->count() - 1));
                        @endphp
                        @forelse ($executors as $executor)
                            <td class="executor-cell">
                                @if (!empty($rowSvgs[$executor['key']]))
                                    <img class="flow-svg" src="{{ $rowSvgs[$executor['key']] }}" alt="Flow {{ $executor['label'] }}">
                                @else
                                    <div class="empty-node">-</div>
                                @endif
                            </td>
                        @empty
                            <td class="executor-cell"><div class="empty-node">-</div></td>
                        @endforelse
                        <td class="text-cell">{!! $renderListHtml(data_get($row, 'quality_requirements', []), true) !!}</td>
                        @php
                            $span = $durationSpans[$index] ?? 1;
                        @endphp
                        @if ($span > 0)
                            <td class="duration-cell" rowspan="{{ $span }}">{{ data_get($row, 'duration') ?: '-' }}</td>
                        @endif
                        <td class="text-cell" style="border-right: 1px solid #111827;">{!! $renderListHtml(data_get($row, 'outputs', []), true) !!}</td>
                        <td class="text-cell" style="border-left: 1px solid #111827;">
                            @if (filled(data_get($row, 'notes')))
                                {{ data_get($row, 'notes') }}
                            @else
                                <span class="muted">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="activity-no">-</td>
                        <td colspan="{{ 6 + $executorCount }}" class="center muted">Belum ada uraian kegiatan yang diisi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </body>
</html>
