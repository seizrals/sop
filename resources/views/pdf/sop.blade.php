<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <title>{{ $document->title }}</title>
        <style>
            @page {
                margin: 10mm;
                size: A4 landscape;
            }

            html,
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            body {
                font-family: Arial, sans-serif;
                font-size: 10px;
                color: #000000;
                margin: 0;
                width: 277mm;
            }

            * {
                box-sizing: border-box;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                page-break-inside: auto;
            }

            thead {
                display: table-header-group;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
                break-inside: avoid;
                break-after: auto;
            }

            .activity-table tbody tr {
                min-height: 52px;
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .activity-table tbody tr:nth-child(1),
            .activity-table tbody tr:nth-child(2) {
                page-break-inside: auto;
                break-inside: auto;
            }

            td,
            th {
                border: 1px solid #000000;
                padding: 6px;
                vertical-align: top;
                color: #000000;
            }

            .page-break {
                page-break-after: always;
                break-after: page;
                height: 0;
                line-height: 0;
                font-size: 0;
                margin: 0;
                padding: 0;
                border: 0;
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
                border: 1px solid #000000;
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
                color: #000000;
            }

            .identity-label {
                width: 140px;
                background: #f3f4f6;
                font-weight: 700;
                text-transform: uppercase;
                font-size: 9px;
                vertical-align: middle;
                padding: 6px 8px;
                color: #000000;
            }

            .identity-value {
                font-size: 10px;
                vertical-align: middle;
                padding: 6px 10px;
                color: #000000;
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
                color: #000000;
            }

            .sop-title {
                font-size: 11px;
                font-weight: 700;
                line-height: 1.45;
                color: #000000;
            }

            .section-heading {
                background: #f3f4f6;
                font-weight: 700;
                text-transform: uppercase;
                font-size: 9px;
                letter-spacing: 0.2px;
                color: #000000;
            }

            .section-body {
                min-height: 78px;
                line-height: 1.55;
                font-size: 9.5px;
                color: #000000;
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
                color: #000000;
            }

            .activity-table thead tr {
                height: 24px;
            }

            .activity-table tbody td {
                padding: 2px 4px;
                vertical-align: middle;
                color: #000000;
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
                color: #000000;
            }

            .activity-flow {
                font-size: 8.5px;
                line-height: 1.45;
                color: #000000;
            }

            .activity-flow-label {
                font-weight: 700;
            }

            .executor-cell {
                text-align: center;
                padding: 0;
                min-height: 52px;
                vertical-align: middle;
                overflow: visible;
                position: relative;
                page-break-inside: auto;
                break-inside: auto;
            }

            .flow-svg {
                display: block;
                width: calc(100% + 20px);
                height: 68px;
                position: absolute;
                left: -10px;
                top: 50%;
                transform: translateY(-50%);
                z-index: 2;
                pointer-events: none;
            }

            .text-cell {
                font-size: 8.8px;
                line-height: 1.5;
                vertical-align: middle;
                color: #000000;
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
                color: #000000;
                page-break-inside: auto;
                break-inside: auto;
            }

            .duration-cell.group-start {
                vertical-align: top;
                padding-top: 10px;
            }

            .duration-cell.group-continue {
                border-top: 0 !important;
                padding-top: 0;
                padding-bottom: 0;
            }

            .empty-node {
                color: #9ca3af;
                font-size: 8px;
            }

            @media print {
                table {
                    page-break-inside: auto;
                }

                tr {
                    page-break-inside: avoid;
                    break-inside: avoid;
                    page-break-after: auto;
                    break-after: auto;
                }

                .activity-table {
                    page-break-inside: auto;
                }

                .activity-table tbody tr {
                    page-break-inside: avoid;
                    break-inside: avoid;
                }

                .activity-table tbody tr:nth-child(1),
                .activity-table tbody tr:nth-child(2) {
                    page-break-inside: auto !important;
                    break-inside: auto !important;
                }

                .activity-table tbody td {
                    page-break-inside: auto;
                    break-inside: auto;
                }

                .duration-cell,
                .identity-table,
                .detail-table {
                    page-break-inside: auto;
                    break-inside: auto;
                }

                .duration-cell.group-continue {
                    border-top: 0 !important;
                }

                thead {
                    display: table-header-group !important;
                }

                .page-break {
                    page-break-after: always !important;
                    break-after: page !important;
                }

                .executor-cell {
                    page-break-inside: auto;
                    break-inside: auto;
                }

                .flow-svg {
                    overflow: visible;
                }
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

            $usedExecutorKeys = $activities
                ->flatMap(fn ($activity) => collect(data_get($activity, 'flow_nodes', []))
                    ->map(fn ($node) => (string) data_get($node, 'executor_key'))
                    ->filter(fn ($key) => filled($key)))
                ->unique()
                ->values()
                ->all();

            if (count($usedExecutorKeys) > 0) {
                $executorByKey = [];
                foreach ($executors as $executor) {
                    $executorByKey[(string) data_get($executor, 'key')] = $executor;
                }
                $filteredExecutors = collect();
                foreach ($usedExecutorKeys as $key) {
                    if (isset($executorByKey[$key])) {
                        $filteredExecutors->push($executorByKey[$key]);
                    } else {
                        $filteredExecutors->push(['key' => (string) $key, 'label' => (string) $key]);
                    }
                }
                $executors = $filteredExecutors->values();
            }

            $executorCount = max($executors->count(), 1);
            $executorTotalWidthPercent = 42;
            $executorWidthPercent = $executorTotalWidthPercent / $executorCount;

            $lineStroke = 0.9;
            $shapeStroke = 1.0;
            $arrowPenetrate = 0.3;
            $arrowStroke = 0.9;
            $shapeFill = '#D3D3D3';
            $shapeText = '#000000';

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
            global $pendingCrossRowEntries;
            $pendingCrossRowEntries = [];

            $firstExecutorKey = function (array $row, array $executorKeys): ?string {
                $orderedNodes = collect(data_get($row, 'flow_nodes', []))
                    ->filter(fn ($node) => filled(data_get($node, 'type')) && filled(data_get($node, 'executor_key')) && in_array((string) data_get($node, 'executor_key'), $executorKeys, true))
                    ->values()
                    ->all();

                if ($orderedNodes === []) {
                    $performers = data_get($row, 'performers', []);
                    if (is_array($performers)) {
                        foreach ($executorKeys as $executorKey) {
                            $performer = data_get($performers, $executorKey);
                            if (is_array($performer) && filled(data_get($performer, 'type'))) {
                                return (string) $executorKey;
                            }
                        }
                    }
                    return null;
                }

                return (string) $orderedNodes[0]['executor_key'];
            };

            $lastExecutorKey = function (array $row, array $executorKeys): ?string {
                $orderedNodes = collect(data_get($row, 'flow_nodes', []))
                    ->filter(fn ($node) => filled(data_get($node, 'type')) && filled(data_get($node, 'executor_key')) && in_array((string) data_get($node, 'executor_key'), $executorKeys, true))
                    ->values()
                    ->all();

                if ($orderedNodes === []) {
                    $performers = data_get($row, 'performers', []);
                    if (is_array($performers)) {
                        $found = null;
                        foreach ($executorKeys as $executorKey) {
                            $performer = data_get($performers, $executorKey);
                            if (is_array($performer) && filled(data_get($performer, 'type'))) {
                                $found = (string) $executorKey;
                            }
                        }
                        return $found;
                    }
                    return null;
                }

                return (string) $orderedNodes[count($orderedNodes) - 1]['executor_key'];
            };

            $executorKeysForConnector = $executors->pluck('key')->all();

            $buildRowFlowSvgs = function (array $row, int $rowIndex, bool $isFirstRow, bool $isLastRow, ?string $prevRowLastKey = null, ?string $nextRowFirstKey = null) use (
                $executors,
                $wrapSvgText,
                $svgDataUri,
                $lineStroke,
                $shapeStroke,
                $arrowPenetrate,
                $arrowStroke,
                $shapeFill,
                $shapeText,
                $activities
            ): array {
                global $pendingCrossRowEntries;
                if (! is_array($pendingCrossRowEntries)) { $pendingCrossRowEntries = []; }
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
                $bleedX = 10;
                $bleedTop = 8;
                $canvasHeight = $viewHeight + ($bleedTop * 2);
                $centerY = 26;
                $crossRowExitY = 46;
                $crossRowEntryY = 6;
                $branchLabelY = 14;

                $cells = array_fill(0, max(count($executorKeys), 1), [
                    'lines' => [],
                    'shapes' => [],
                    'labels' => [],
                ]);

                $edge = function (string $type): array {
                    return match (trim($type)) {
                        'decision' => ['l' => 22, 'r' => 78, 't' => 10, 'b' => 42],
                        'start', 'end' => ['l' => 34, 'r' => 66, 't' => 18, 'b' => 34],
                        default => ['l' => 28, 'r' => 72, 't' => 17, 'b' => 35],
                    };
                };

                $arrow = function (string $dir, float $x, float $y) use ($arrowPenetrate, $arrowStroke): string {
                    $tipX = $x;
                    $tipY = $y;
                    $tipOffset = $arrowPenetrate;
                    $sw = $arrowStroke;
                    $s = 2.2;
                    $t = 1.5;

                    if ($dir === 'right') {
                        $tipX = $x + $tipOffset;
                        $tailX = $tipX - $s;
                        $topY = $tipY - $t;
                        $botY = $tipY + $t;
                        return '<polygon points="' . $tipX . ',' . $tipY . '  ' . $tailX . ',' . $topY . '  ' . $tailX . ',' . $botY . '" fill="#000000" stroke="#000000" stroke-width="' . $sw . '" stroke-linejoin="miter"/>';
                    }

                    if ($dir === 'left') {
                        $tipX = $x - $tipOffset;
                        $tailX = $tipX + $s;
                        $topY = $tipY - $t;
                        $botY = $tipY + $t;
                        return '<polygon points="' . $tipX . ',' . $tipY . '  ' . $tailX . ',' . $topY . '  ' . $tailX . ',' . $botY . '" fill="#000000" stroke="#000000" stroke-width="' . $sw . '" stroke-linejoin="miter"/>';
                    }

                    if ($dir === 'down') {
                        $tipY = $y + $tipOffset;
                        $tailY = $tipY - $s;
                        $leftX = $tipX - $t;
                        $rightX = $tipX + $t;
                        return '<polygon points="' . $tipX . ',' . $tipY . '  ' . $leftX . ',' . $tailY . '  ' . $rightX . ',' . $tailY . '" fill="#000000" stroke="#000000" stroke-width="' . $sw . '" stroke-linejoin="miter"/>';
                    }

                    $tipY = $y - $tipOffset;
                    $tailY = $tipY + $s;
                    $leftX = $tipX - $t;
                    $rightX = $tipX + $t;
                    return '<polygon points="' . $tipX . ',' . $tipY . '  ' . $leftX . ',' . $tailY . '  ' . $rightX . ',' . $tailY . '" fill="#000000" stroke="#000000" stroke-width="' . $sw . '" stroke-linejoin="miter"/>';
                };

                $shapeSvg = function (string $type, string $label) use ($wrapSvgText, $shapeStroke, $shapeFill, $shapeText): string {
                    $type = trim($type);
                    $label = trim($label);
                    $sw = $shapeStroke;
                    $fill = $shapeFill;
                    $txt = $shapeText;

                    if ($type === 'decision') {
                        $lines = $wrapSvgText($label !== '' ? $label : 'Keputusan', 12, 3);
                        $lineCount = count($lines);
                        $lineHeight = 7;
                        $startDy = -((($lineCount - 1) / 2) * $lineHeight) + 0.5;
                        $tspans = '';
                        foreach ($lines as $i => $line) {
                            $dy = $i === 0 ? $startDy : $lineHeight;
                            $tspans .= '<tspan x="50" dy="' . $dy . '">' . htmlspecialchars($line, ENT_QUOTES | ENT_XML1, 'UTF-8') . '</tspan>';
                        }

                        return '<polygon points="50,7 82,26 50,45 18,26" fill="' . $fill . '" stroke="#000000" stroke-width="' . $sw . '"/>'
                            . '<text x="50" y="26" font-family="Arial, sans-serif" font-size="8" font-weight="700" fill="#000000" text-anchor="middle" dominant-baseline="middle">'
                            . $tspans
                            . '</text>';
                    }

                    if ($type === 'start' || $type === 'end') {
                        $text = $type === 'start' ? 'Start' : 'End';
                        return '<rect x="30" y="17" width="40" height="18" rx="9" ry="9" fill="' . $fill . '" stroke="#000000" stroke-width="' . $sw . '"/>'
                            . '<text x="50" y="26" font-family="Arial, sans-serif" font-size="7.5" font-weight="700" fill="' . $txt . '" text-anchor="middle" dominant-baseline="middle">' . $text . '</text>';
                    }

                    return '<rect x="22" y="16" width="56" height="20" fill="' . $fill . '" stroke="#000000" stroke-width="' . $sw . '"/>';
                };

                $orderedNodes = collect($nodes)
                    ->filter(fn ($node) => array_key_exists((string) data_get($node, 'executor_key'), $indexByKey))
                    ->values()
                    ->all();

                $nodeByCol = [];
                foreach ($orderedNodes as $node) {
                    $col = $indexByKey[$node['executor_key']] ?? null;
                    if ($col === null) {
                        continue;
                    }
                    $cells[$col]['shapes'][] = $shapeSvg($node['type'], $node['label']);
                    $nodeByCol[$col] = $node;
                }

                $colCount = max(count($executorKeys), 1);
                $slotTracker = array_fill(0, $colCount, [
                    'top' => ['count' => 0, 'positions' => ['mid' => false, 'upper' => false, 'lower' => false]],
                    'right' => ['count' => 0, 'positions' => ['mid' => false, 'upper' => false, 'lower' => false]],
                    'bottom' => ['count' => 0, 'positions' => ['mid' => false, 'upper' => false, 'lower' => false]],
                    'left' => ['count' => 0, 'positions' => ['mid' => false, 'upper' => false, 'lower' => false]],
                ]);

                $acquireSlot = function (int $col, string $side) use (&$slotTracker): string {
                    $t = &$slotTracker[$col][$side];
                    $t['count']++;
                    if ($t['count'] === 1 || ! $t['positions']['mid']) {
                        $t['positions']['mid'] = true;
                        return 'mid';
                    }
                    if (! $t['positions']['upper']) {
                        $t['positions']['upper'] = true;
                        return 'upper';
                    }
                    $t['positions']['lower'] = true;
                    return 'lower';
                };

                $getPointOnSide = function (string $side, string $slot, array $e) use ($centerY): array {
                    $delta = 8.5;
                    if ($side === 'right') {
                        $y = $centerY;
                        if ($slot === 'upper') $y = $centerY - $delta;
                        if ($slot === 'lower') $y = $centerY + $delta;
                        return ['x' => $e['r'], 'y' => $y];
                    }
                    if ($side === 'left') {
                        $y = $centerY;
                        if ($slot === 'upper') $y = $centerY - $delta;
                        if ($slot === 'lower') $y = $centerY + $delta;
                        return ['x' => $e['l'], 'y' => $y];
                    }
                    if ($side === 'top') {
                        $x = 50;
                        if ($slot === 'upper') $x = 50 - $delta;
                        if ($slot === 'lower') $x = 50 + $delta;
                        return ['x' => $x, 'y' => $e['t']];
                    }
                    $x = 50;
                    if ($slot === 'upper') $x = 50 - $delta;
                    if ($slot === 'lower') $x = 50 + $delta;
                    return ['x' => $x, 'y' => $e['b']];
                };

                $drawHorizontalLineRange = function (int $fromCol, int $toCol, float $y) use (&$cells, $lineStroke, $bleedX): void {
                    if ($fromCol === $toCol) {
                        return;
                    }
                    if ($fromCol < $toCol) {
                        $cells[$fromCol]['lines'][] = '<line x1="' . (100 + $bleedX) . '" y1="' . $y . '" x2="-' . $bleedX . '" y2="' . $y . '" stroke="#000000" stroke-width="' . $lineStroke . '"/>';
                        for ($c = $fromCol + 1; $c <= $toCol - 1; $c++) {
                            $cells[$c]['lines'][] = '<line x1="-' . $bleedX . '" y1="' . $y . '" x2="' . (100 + $bleedX) . '" y2="' . $y . '" stroke="#000000" stroke-width="' . $lineStroke . '"/>';
                        }
                        $cells[$toCol]['lines'][] = '<line x1="-' . $bleedX . '" y1="' . $y . '" x2="' . (100 + $bleedX) . '" y2="' . $y . '" stroke="#000000" stroke-width="' . $lineStroke . '"/>';
                    } else {
                        for ($c = $toCol; $c <= $fromCol; $c++) {
                            $cells[$c]['lines'][] = '<line x1="' . (100 + $bleedX) . '" y1="' . $y . '" x2="-' . $bleedX . '" y2="' . $y . '" stroke="#000000" stroke-width="' . $lineStroke . '"/>';
                        }
                    }
                };

                $drawExplicitSource = function (
                    int $fromCol, int $targetCol,
                    string $exitSide, string $exitSlot,
                    array $fromEdge,
                    int $targetActivityIdx,
                    ?string $label,
                    string $targetEntrySide = 'top'
                ) use (
                    &$cells, &$pendingCrossRowEntries, $acquireSlot, $getPointOnSide,
                    $bleedX, $lineStroke, $centerY, $canvasHeight
                ): void {
                    $exSlot = $acquireSlot($fromCol, $exitSide);
                    $exitP = $getPointOnSide($exitSide, $exSlot, $fromEdge);
                    $straightStep = 8.0;
                    $isVExit = ($exitSide === 'top' || $exitSide === 'bottom');
                    $stepX = $exitP['x'] + ($isVExit ? 0 : (($exitSide === 'right') ? 1 : -1) * $straightStep);
                    $stepY = $exitP['y'] + ($isVExit ? (($exitSide === 'bottom') ? 1 : -1) * $straightStep : 0);
                    // SNAKE: Pastikan stepX/stepY TIDAK JAUH kurang dari 4 unit dari border cell.
                    // Zona LARANGAN: kanan [96..104], kiri [-4..4], bawah [48..56], atas [-4..4] (tempat garis tabel cell)
                    if (!$isVExit && $exitSide === 'right' && $stepX >= 96 && $stepX <= (100 + $bleedX - 2)) { $stepX = 106; }
                    if (!$isVExit && $exitSide === 'left'  && $stepX <= 4  && $stepX >= (-$bleedX + 2)) { $stepX = -6; }
                    if ($isVExit  && $exitSide === 'bottom' && $stepY >= 48 && $stepY <= ($canvasHeight + 2)) { $stepY = 56; }
                    if ($isVExit  && $exitSide === 'top'    && $stepY <= 4  && $stepY >= (-2)) { $stepY = -6; }

                    $pushSeg = function ($x1, $y1, $x2, $y2, int $col) use (&$cells, $lineStroke): void {
                        if (abs($x1 - $x2) < 0.005 && abs($y1 - $y2) < 0.005) return;
                        $cells[$col]['lines'][] = '<line x1="' . $x1 . '" y1="' . $y1 . '" x2="' . $x2 . '" y2="' . $y2 . '" stroke="#000000" stroke-width="' . $lineStroke . '"/>';
                    };

                    $laneX = 50;
                    $laneY = $centerY;

                    if (! $isVExit) {
                        $laneY = $exitP['y'];
                        $pushSeg($exitP['x'], $laneY, $stepX, $laneY, $fromCol);
                        $startC = min($fromCol, $targetCol);
                        $endC = max($fromCol, $targetCol);
                        for ($c = $startC; $c <= $endC; $c++) {
                            if ($c === $fromCol) {
                                $lx1 = $stepX;
                                $lx2 = ($targetCol >= $fromCol) ? (100 + $bleedX) : -$bleedX;
                                $pushSeg($lx1, $laneY, $lx2, $laneY, $c);
                            } elseif ($c === $targetCol) {
                                $lx1 = ($targetCol >= $fromCol) ? -$bleedX : (100 + $bleedX);
                                $lx2 = 50;
                                $pushSeg($lx1, $laneY, $lx2, $laneY, $c);
                            } else {
                                $pushSeg(-$bleedX, $laneY, (100 + $bleedX), $laneY, $c);
                            }
                        }
                        $laneX = 50;
                        $pushSeg($laneX, $laneY, $laneX, $canvasHeight, $targetCol);
                    } else {
                        $laneX = $stepX;
                        $laneY = $stepY;
                        $pushSeg($exitP['x'], $exitP['y'], $stepX, $stepY, $fromCol);
                        $startC = min($fromCol, $targetCol);
                        $endC = max($fromCol, $targetCol);
                        for ($c = $startC; $c <= $endC; $c++) {
                            if ($c === $fromCol) {
                                $lx1 = $laneX;
                                $lx2 = ($targetCol >= $fromCol) ? (100 + $bleedX) : -$bleedX;
                                $pushSeg($lx1, $laneY, $lx2, $laneY, $c);
                            } elseif ($c === $targetCol) {
                                $lx1 = ($targetCol >= $fromCol) ? -$bleedX : (100 + $bleedX);
                                $lx2 = 50;
                                $pushSeg($lx1, $laneY, $lx2, $laneY, $c);
                            } else {
                                $pushSeg(-$bleedX, $laneY, (100 + $bleedX), $laneY, $c);
                            }
                        }
                        $laneX = 50;
                        $pushSeg($laneX, $laneY, $laneX, $canvasHeight, $targetCol);
                    }

                    if ($label !== null) {
                        $lx = 50; $labelY = $centerY + 3;
                        if ($exitSide === 'right') {
                            $lx = $exitP['x'] + 20.0;
                            if ($lx > 85) $lx = 85;
                            $labelY = $exitP['y'] + 7.5;
                        } elseif ($exitSide === 'left') {
                            $lx = $exitP['x'] - 20.0;
                            if ($lx < 15) $lx = 15;
                            $labelY = $exitP['y'] + 7.5;
                        } elseif ($exitSide === 'bottom') {
                            $lx = $exitP['x'] + 9.0;
                            $labelY = $exitP['y'] + 5.0;
                            if ($labelY > 50) $labelY = 50;
                        } else {
                            $lx = $exitP['x'] + 9.0;
                            $labelY = ($exitP['y'] + $stepY) / 2;
                        }
                        $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_XML1, 'UTF-8');
                        $cells[$fromCol]['labels'][] = '<text x="' . $lx . '" y="' . $labelY . '" font-family="Arial, sans-serif" font-size="9.5" font-weight="700" fill="#000000">' . $safeLabel . '</text>';
                    }

                    if (! isset($pendingCrossRowEntries[$targetActivityIdx])) {
                        $pendingCrossRowEntries[$targetActivityIdx] = [];
                    }
                    $pendingCrossRowEntries[$targetActivityIdx][] = [
                        'col' => $targetCol,
                        'entryX' => $laneX,
                        'entrySide' => $targetEntrySide,
                    ];
                };

                $drawElbow = function (
                    int $fromCol, int $toCol,
                    string $exitSide, string $entrySide,
                    string $exitSlot, string $entrySlot,
                    array $fromEdge, array $toEdge,
                    ?string $label = null
                ) use (
                    &$cells, $acquireSlot, $getPointOnSide, $bleedX, $lineStroke, $arrow, $centerY
                ): void {
                    $exSlot = $acquireSlot($fromCol, $exitSide);
                    $enSlot = $acquireSlot($toCol, $entrySide);
                    $exitP = $getPointOnSide($exitSide, $exSlot, $fromEdge);
                    $entryP = $getPointOnSide($entrySide, $enSlot, $toEdge);
                    $isVExit = $exitSide === 'top' || $exitSide === 'bottom';
                    $isVEntry = $entrySide === 'top' || $entrySide === 'bottom';
                    $isShortStepCase = (! $isVExit && $isVEntry) || ($isVExit && ! $isVEntry);
                    $straightStep = $isShortStepCase ? 5.2 : 8.0;
                    $approach = $isShortStepCase ? 5.5 : 7.5;
                    $dirSign = ['right' => 1, 'left' => -1, 'top' => -1, 'bottom' => 1];
                    $stepX = $exitP['x'] + ($isVExit ? 0 : $dirSign[$exitSide] * $straightStep);
                    $stepY = $exitP['y'] + ($isVExit ? $dirSign[$exitSide] * $straightStep : 0);
                    // Pre-shape (jarak sebelum target shape): di sumbu axis entry, mundur approach
                    //   left/right entry: x di approach posisi, y SAMA DENGAN entryP.y (agar horizontal terakhir)
                    //   top/bottom entry: y di approach posisi, x SAMA DENGAN entryP.x (agar vertikal terakhir)
                    $preShapeX = $entryP['x'];
                    $preShapeY = $entryP['y'];
                    if ($entrySide === 'left') { $preShapeX = $entryP['x'] - $approach; $preShapeY = $entryP['y']; }
                    if ($entrySide === 'right') { $preShapeX = $entryP['x'] + $approach; $preShapeY = $entryP['y']; }
                    if ($entrySide === 'top') { $preShapeY = $entryP['y'] - $approach; $preShapeX = $entryP['x']; }
                    if ($entrySide === 'bottom') { $preShapeY = $entryP['y'] + $approach; $preShapeX = $entryP['x']; }
                    // SNAKE: Pastikan stepX/stepY dan preShapeX/Y TIDAK dekat dengan border cell
                    // Zona LARANGAN ELBOW: kanan [96..104], kiri [-4..4], bawah [48..56], atas [-4..4] (tempat garis tabel cell)
                    if (!$isVExit && $exitSide === 'right' && $stepX >= 96 && $stepX <= (100 + $bleedX - 2)) { $stepX = 106; }
                    if (!$isVExit && $exitSide === 'left'  && $stepX <= 4  && $stepX >= (-$bleedX + 2)) { $stepX = -6; }
                    if ($isVExit  && $exitSide === 'bottom' && $stepY >= 48 && $stepY <= 56) { $stepY = 56; }
                    if ($isVExit  && $exitSide === 'top'    && $stepY <= 4  && $stepY >= -4) { $stepY = -6; }
                    if (!$isVEntry && $entrySide === 'right' && $preShapeX >= 96) { $preShapeX = 106; }
                    if (!$isVEntry && $entrySide === 'left'  && $preShapeX <= 4)  { $preShapeX = -6; }
                    if ($isVEntry  && $entrySide === 'bottom' && $preShapeY >= 48) { $preShapeY = 56; }
                    if ($isVEntry  && $entrySide === 'top'    && $preShapeY <= 4)  { $preShapeY = -6; }

                    $lineSegs = [];
                    $pushSeg = function ($x1, $y1, $x2, $y2, int $col) use (&$lineSegs, $lineStroke) {
                        if (abs($x1 - $x2) < 0.01 && abs($y1 - $y2) < 0.01) return;
                        // PASTIKAN HORIZONTAL ATAU VERTICAL SAJA (tidak pernah diagonal)
                        if (abs($x1 - $x2) > 0.01 && abs($y1 - $y2) > 0.01) {
                            // Jika ada 2 sumbu beda, split: horizontal dulu baru vertikal (agar tidak diagonal)
                            $lineSegs[$col][] = '<line x1="' . $x1 . '" y1="' . $y1 . '" x2="' . $x2 . '" y2="' . $y1 . '" stroke="#000000" stroke-width="' . $lineStroke . '"/>';
                            $lineSegs[$col][] = '<line x1="' . $x2 . '" y1="' . $y1 . '" x2="' . $x2 . '" y2="' . $y2 . '" stroke="#000000" stroke-width="' . $lineStroke . '"/>';
                            return;
                        }
                        $lineSegs[$col][] = '<line x1="' . $x1 . '" y1="' . $y1 . '" x2="' . $x2 . '" y2="' . $y2 . '" stroke="#000000" stroke-width="' . $lineStroke . '"/>';
                    };

                    if ($fromCol === $toCol) {
                        $pushSeg($exitP['x'], $exitP['y'], $stepX, $stepY, $fromCol);
                        // Belok ke y / x match entry approach point
                        if (! $isVExit && ! $isVEntry) {
                            // HH: entry left/right → approach horizontal terakhir: vertikal dulu ke entryP.y → horizontal ke preShapeX → horizontal pendek ke entry
                            $pushSeg($stepX, $stepY, $stepX, $entryP['y'], $fromCol);
                            $pushSeg($stepX, $entryP['y'], $preShapeX, $entryP['y'], $fromCol);
                            $pushSeg($preShapeX, $entryP['y'], $entryP['x'], $entryP['y'], $toCol);
                        } elseif ($isVExit && $isVEntry) {
                            // VV: entry top/bottom → approach vertikal terakhir: horizontal dulu ke entryP.x → vertikal ke preShapeY → vertikal pendek ke entry
                            $pushSeg($stepX, $stepY, $entryP['x'], $stepY, $fromCol);
                            $pushSeg($entryP['x'], $stepY, $entryP['x'], $preShapeY, $fromCol);
                            $pushSeg($entryP['x'], $preShapeY, $entryP['x'], $entryP['y'], $toCol);
                        } elseif (! $isVExit && $isVEntry) {
                            // HV: exit left/right → entry top/bottom (misal: exit kanan Process, entry atas Decision)
                            // VERTIKAL DULU ke preShapeY, baru HORIZONTAL ke entryP.x, lalu vertikal terakhir ke entry
                            $pushSeg($stepX, $stepY, $stepX, $preShapeY, $fromCol);
                            if (abs($stepX - $entryP['x']) > 0.01) {
                                $pushSeg($stepX, $preShapeY, $entryP['x'], $preShapeY, $fromCol);
                            }
                            $pushSeg($entryP['x'], $preShapeY, $entryP['x'], $entryP['y'], $toCol);
                        } else {
                            // VH: exit top/bottom → entry left/right
                            // HORIZONTAL DULU ke preShapeX, baru VERTIKAL ke entryP.y, lalu horizontal terakhir ke entry
                            $pushSeg($stepX, $stepY, $preShapeX, $stepY, $fromCol);
                            if (abs($stepY - $entryP['y']) > 0.01) {
                                $pushSeg($preShapeX, $stepY, $preShapeX, $entryP['y'], $fromCol);
                            }
                            $pushSeg($preShapeX, $entryP['y'], $entryP['x'], $entryP['y'], $toCol);
                        }
                    } elseif (! $isVExit && ! $isVEntry) {
                        // HH: exit left/right → entry left/right
                        $laneY = $exitP['y'];
                        // Lurus horizontal dari exit sampai batas kolom dari (exit lane tetap laneY=exitP.y)
                        $pushSeg($exitP['x'], $exitP['y'], (100 + $bleedX), $laneY, $fromCol);
                        if ($fromCol < $toCol) {
                            for ($c = $fromCol + 1; $c <= $toCol - 1; $c++) {
                                $pushSeg(-$bleedX, $laneY, (100 + $bleedX), $laneY, $c);
                            }
                            // Sampai di kolom target: masih horizontal laneY sampai x=preShapeX (approach x), lalu BELONG VERTICAL ke y=entryP.y (pada preShapeX) → HORIZONTAL PENDEK TERAKHIR ke entry (entryP.y sama)
                            $pushSeg(-$bleedX, $laneY, $preShapeX, $laneY, $toCol);
                            $pushSeg($preShapeX, $laneY, $preShapeX, $entryP['y'], $toCol); // vertikal ke y entry (pada approach X, tepat di belakang shape)
                            $pushSeg($preShapeX, $entryP['y'], $entryP['x'], $entryP['y'], $toCol); // horizontal pendek terakhir menuju shape edge left/right
                        } else {
                            for ($c = $toCol + 1; $c <= $fromCol - 1; $c++) {
                                $pushSeg(-$bleedX, $laneY, (100 + $bleedX), $laneY, $c);
                            }
                            $pushSeg(-$bleedX, $laneY, $preShapeX, $laneY, $toCol);
                            $pushSeg($preShapeX, $laneY, $preShapeX, $entryP['y'], $toCol);
                            $pushSeg($preShapeX, $entryP['y'], $entryP['x'], $entryP['y'], $toCol);
                        }
                    } elseif ($isVExit && ! $isVEntry) {
                        // VH: exit top/bottom → entry left/right (contoh: dari process bawah keluar bottom, masuk kiri shape lain)
                        $laneY = $stepY;
                        $pushSeg($exitP['x'], $exitP['y'], $stepX, $stepY, $fromCol);
                        $pushSeg($stepX, $stepY, $stepX, $laneY, $fromCol);
                        $laneX2 = $stepX;
                        if ($fromCol < $toCol) {
                            $pushSeg($laneX2, $laneY, (100 + $bleedX), $laneY, $fromCol);
                            for ($c = $fromCol + 1; $c <= $toCol - 1; $c++) {
                                $pushSeg(-$bleedX, $laneY, (100 + $bleedX), $laneY, $c);
                            }
                            // Target col: horizontal laneY → sampai preShapeX → vertikal ke y=entryP.y (pada approach X) → horizontal terakhir ke edge
                            $pushSeg(-$bleedX, $laneY, $preShapeX, $laneY, $toCol);
                            $pushSeg($preShapeX, $laneY, $preShapeX, $entryP['y'], $toCol);
                            $pushSeg($preShapeX, $entryP['y'], $entryP['x'], $entryP['y'], $toCol);
                        } else {
                            $pushSeg($laneX2, $laneY, -$bleedX, $laneY, $fromCol);
                            for ($c = $toCol + 1; $c <= $fromCol - 1; $c++) {
                                $pushSeg(-$bleedX, $laneY, (100 + $bleedX), $laneY, $c);
                            }
                            $pushSeg(-$bleedX, $laneY, $preShapeX, $laneY, $toCol);
                            $pushSeg($preShapeX, $laneY, $preShapeX, $entryP['y'], $toCol);
                            $pushSeg($preShapeX, $entryP['y'], $entryP['x'], $entryP['y'], $toCol);
                        }
                    } elseif (! $isVExit && $isVEntry) {
                        // HV: exit left/right → entry top/bottom (contoh: exit kanan process, masuk atas decision)
                        $laneX = $stepX;
                        $pushSeg($exitP['x'], $exitP['y'], $laneX, $exitP['y'], $fromCol);
                        // Turun / naik vertikal dulu ke preShapeY (approach Y, y entry - approach)
                        $pushSeg($laneX, $exitP['y'], $laneX, $preShapeY, $fromCol);
                        if ($fromCol < $toCol) {
                            // Horizontal lintas kolom SAMPAI preShapeY tetap (sudah di belakang shape), lalu horizontal ke entryP.x, VERTIKAL TERAKHIR PENDEK
                            $pushSeg($laneX, $preShapeY, (100 + $bleedX), $preShapeY, $fromCol);
                            for ($c = $fromCol + 1; $c <= $toCol - 1; $c++) {
                                $pushSeg(-$bleedX, $preShapeY, (100 + $bleedX), $preShapeY, $c);
                            }
                            $pushSeg(-$bleedX, $preShapeY, $entryP['x'], $preShapeY, $toCol);
                        } else {
                            $pushSeg($laneX, $preShapeY, -$bleedX, $preShapeY, $fromCol);
                            for ($c = $toCol + 1; $c <= $fromCol - 1; $c++) {
                                $pushSeg(-$bleedX, $preShapeY, (100 + $bleedX), $preShapeY, $c);
                            }
                            $pushSeg(-$bleedX, $preShapeY, $entryP['x'], $preShapeY, $toCol);
                        }
                        // Vertikal pendek terakhir ke shape edge top/bottom
                        $pushSeg($entryP['x'], $preShapeY, $entryP['x'], $entryP['y'], $toCol);
                    } else {
                        // VV: exit top/bottom → entry top/bottom
                        $laneY = $stepY;
                        $pushSeg($exitP['x'], $exitP['y'], $stepX, $stepY, $fromCol);
                        if ($fromCol < $toCol) {
                            $pushSeg($stepX, $laneY, (100 + $bleedX), $laneY, $fromCol);
                            for ($c = $fromCol + 1; $c <= $toCol - 1; $c++) {
                                $pushSeg(-$bleedX, $laneY, (100 + $bleedX), $laneY, $c);
                            }
                            $pushSeg(-$bleedX, $laneY, $entryP['x'], $laneY, $toCol);
                        } else {
                            $pushSeg($stepX, $laneY, -$bleedX, $laneY, $fromCol);
                            for ($c = $toCol + 1; $c <= $fromCol - 1; $c++) {
                                $pushSeg(-$bleedX, $laneY, (100 + $bleedX), $laneY, $c);
                            }
                            $pushSeg(-$bleedX, $laneY, $entryP['x'], $laneY, $toCol);
                        }
                        // Dari laneY vertikal ke preShapeY approach, lalu pendek terakhir ke entry (VV → approach sebelum shape)
                        $pushSeg($entryP['x'], $laneY, $entryP['x'], $preShapeY, $toCol);
                        $pushSeg($entryP['x'], $preShapeY, $entryP['x'], $entryP['y'], $toCol);
                    }

                    foreach ($lineSegs as $col => $segs) {
                        foreach ($segs as $s) {
                            $cells[$col]['lines'][] = $s;
                        }
                    }

                    $arr = $arrowDirFromEntrySide = match ($entrySide) {
                        'left' => 'right', 'right' => 'left', 'top' => 'down', 'bottom' => 'up', default => 'down',
                    };
                    if ($entrySide === 'top') { $cells[$toCol]['lines'][] = $arrow('down', $entryP['x'], $entryP['y']); }
                    elseif ($entrySide === 'bottom') { $cells[$toCol]['lines'][] = $arrow('up', $entryP['x'], $entryP['y']); }
                    elseif ($entrySide === 'left') { $cells[$toCol]['lines'][] = $arrow('right', $entryP['x'], $entryP['y']); }
                    else { $cells[$toCol]['lines'][] = $arrow('left', $entryP['x'], $entryP['y']); }

                    if ($label !== null) {
                        $labelY = $centerY + 3;
                        $lx = 50;
                        if ($exitSide === 'right') {
                            $lx = $exitP['x'] + 20.0;
                            if ($lx > 85) $lx = 85;
                            $labelY = $exitP['y'] + 7.5;
                        } elseif ($exitSide === 'left') {
                            $lx = $exitP['x'] - 20.0;
                            if ($lx < 15) $lx = 15;
                            $labelY = $exitP['y'] + 7.5;
                        } elseif ($exitSide === 'bottom') {
                            $lx = $exitP['x'] + 9.0;
                            $labelY = $exitP['y'] + 5.0;
                            if ($labelY > 50) $labelY = 50;
                        } else {
                            $lx = $exitP['x'] + 9.0;
                            $labelY = ($exitP['y'] + $stepY) / 2;
                        }
                        $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_XML1, 'UTF-8');
                        $cells[$fromCol]['labels'][] = '<text x="' . $lx . '" y="' . $labelY . '" font-family="Arial, sans-serif" font-size="9.5" font-weight="700" fill="#000000">' . $safeLabel . '</text>';
                    }
                };

                if ($orderedNodes !== []) {
                    $firstNode = $orderedNodes[0];
                    $firstCol = $indexByKey[$firstNode['executor_key']];
                    $firstEdge = $edge($firstNode['type']);
                    $prevRowLastCol = ($prevRowLastKey !== null && array_key_exists($prevRowLastKey, $indexByKey)) ? $indexByKey[$prevRowLastKey] : null;

                    if (! $isFirstRow) {
                        if ($prevRowLastCol === null || $prevRowLastCol === $firstCol) {
                            $slotTracker[$firstCol]['top']['count']++;
                            $slotTracker[$firstCol]['top']['positions']['mid'] = true;
                            $approachY = $firstEdge['t'] - 6.5;
                            $arrowYArg = ($firstEdge['t'] + 0.4) - $arrowPenetrate;
                            $cells[$firstCol]['lines'][] = '<line x1="50" y1="-' . $bleedTop . '" x2="50" y2="' . $approachY . '" stroke="#000000" stroke-width="' . $lineStroke . '"/>'
                                . '<line x1="50" y1="' . $approachY . '" x2="50" y2="' . ($firstEdge['t'] + 0.2) . '" stroke="#000000" stroke-width="' . $lineStroke . '"/>'
                                . $arrow('down', 50, $arrowYArg);
                        } else {
                            $slotTracker[$prevRowLastCol]['bottom']['count']++;
                            $slotTracker[$prevRowLastCol]['bottom']['positions']['mid'] = true;
                            $slotTracker[$firstCol]['top']['count']++;
                            $slotTracker[$firstCol]['top']['positions']['mid'] = true;
                            $approachY = $firstEdge['t'] - 6.5;
                            $arrowYArg = ($firstEdge['t'] + 0.4) - $arrowPenetrate;
                            $cells[$prevRowLastCol]['lines'][] = '<line x1="50" y1="-' . $bleedTop . '" x2="50" y2="' . $crossRowEntryY . '" stroke="#000000" stroke-width="' . $lineStroke . '"/>';
                            $drawHorizontalLineRange(min($prevRowLastCol, $firstCol), max($prevRowLastCol, $firstCol), $crossRowEntryY);
                            $cells[$firstCol]['lines'][] = '<line x1="50" y1="' . $crossRowEntryY . '" x2="50" y2="' . $approachY . '" stroke="#000000" stroke-width="' . $lineStroke . '"/>'
                                . '<line x1="50" y1="' . $approachY . '" x2="50" y2="' . ($firstEdge['t'] + 0.2) . '" stroke="#000000" stroke-width="' . $lineStroke . '"/>'
                                . $arrow('down', 50, $arrowYArg);
                        }
                    }

                    $lastNode = $orderedNodes[count($orderedNodes) - 1];
                    $lastCol = $indexByKey[$lastNode['executor_key']];
                    $lastEdge = $edge($lastNode['type']);
                    $nextRowFirstCol = ($nextRowFirstKey !== null && array_key_exists($nextRowFirstKey, $indexByKey)) ? $indexByKey[$nextRowFirstKey] : null;

                    if (! $isLastRow) {
                        if ($nextRowFirstCol === null || $nextRowFirstCol === $lastCol) {
                            $slotTracker[$lastCol]['bottom']['count']++;
                            $slotTracker[$lastCol]['bottom']['positions']['mid'] = true;
                            $cells[$lastCol]['lines'][] = '<line x1="50" y1="' . ($lastEdge['b']) . '" x2="50" y2="' . ($viewHeight + $bleedTop) . '" stroke="#000000" stroke-width="' . $lineStroke . '"/>';
                        } else {
                            $slotTracker[$lastCol]['bottom']['count']++;
                            $slotTracker[$lastCol]['bottom']['positions']['mid'] = true;
                            $slotTracker[$nextRowFirstCol]['top']['count']++;
                            $slotTracker[$nextRowFirstCol]['top']['positions']['mid'] = true;
                            $cells[$lastCol]['lines'][] = '<line x1="50" y1="' . ($lastEdge['b']) . '" x2="50" y2="' . $crossRowExitY . '" stroke="#000000" stroke-width="' . $lineStroke . '"/>';
                            $drawHorizontalLineRange(min($lastCol, $nextRowFirstCol), max($lastCol, $nextRowFirstCol), $crossRowExitY);
                            $cells[$nextRowFirstCol]['lines'][] = '<line x1="50" y1="' . $crossRowExitY . '" x2="50" y2="' . ($viewHeight + $bleedTop) . '" stroke="#000000" stroke-width="' . $lineStroke . '"/>';
                        }
                    }

                    // ===== DRAW PENDING CROSS-ROW EXPLICIT ENTRIES (from previous rows Y/T decisions) =====
                    if (isset($pendingCrossRowEntries[$rowIndex]) && is_array($pendingCrossRowEntries[$rowIndex])) {
                        $usedTopX = [];
                        foreach ($pendingCrossRowEntries[$rowIndex] as $entry) {
                            $targetCol = (int) ($entry['col'] ?? -1);
                            if ($targetCol < 0 || $targetCol >= count($cells)) continue;
                            $entryXBase = (float) ($entry['entryX'] ?? 50);
                            $entrySideForSlot = (string) ($entry['entrySide'] ?? 'top');

                            $slot = $acquireSlot($targetCol, $entrySideForSlot);
                            $slotDelta = 8.5;
                            $entryX = $entryXBase;
                            if ($slot === 'upper') { $entryX = $entryXBase - $slotDelta; }
                            elseif ($slot === 'lower') { $entryX = $entryXBase + $slotDelta; }

                            $targetNode = $nodeByCol[$targetCol] ?? null;
                            $entryTopY = 26;
                            if ($targetNode !== null) {
                                $te = $edge($targetNode['type']);
                                $entryTopY = $te['t'];
                            }
                            $approachDist = 6.5;   // Jarak pendek (sebelum shape), turun 6.5 unit sebelum shape
                            $preShapeY = $entryTopY - $approachDist;   // Jarak aman di atas shape, sebelum mengarah ke shape

                            // 1. Vertical from TOP SVG sampai ke preShapeY (jarak sebelum shape)
                            $cells[$targetCol]['lines'][] = '<line x1="' . $entryX . '" y1="-' . $bleedTop . '" x2="' . $entryX . '" y2="' . $preShapeY . '" stroke="#000000" stroke-width="' . $lineStroke . '"/>';
                            // 2. Turun vertikal PENDEK TERAKHIR dari preShapeY (jarak aman) sampai ke boundary LUAR shape (tidak menembus)
                            $lastLineEndY = $entryTopY + 0.2;  // cuma 0.2 unit lewat boundary luar (sambung ke arrow ujung)
                            $cells[$targetCol]['lines'][] = '<line x1="' . $entryX . '" y1="' . $preShapeY . '" x2="' . $entryX . '" y2="' . $lastLineEndY . '" stroke="#000000" stroke-width="' . $lineStroke . '"/>';
                            // 3. Arrow TEPAT DI ATAS shape:
                            //    Kita mau TIP arrow (puncak segitiga) HANYA menembus 0.4 unit ke dalam shape.
                            //    Function arrow('down', $x, $yArg) menghasilkan tip di y = $yArg + $arrowPenetrate.
                            //    Jadi yArg = (entryTopY + 0.4) - $arrowPenetrate → agar base segitiga TETAP DI ATAS shape (tidak tabrak fill).
                            $arrowYArg = ($entryTopY + 0.4) - $arrowPenetrate;
                            $cells[$targetCol]['lines'][] = $arrow('down', $entryX, $arrowYArg);
                        }
                        unset($pendingCrossRowEntries[$rowIndex]);
                    }
                }

                $drawnBranches = [];

                $nodeCountThisRow = count($orderedNodes);
                $processNodesThisRow = [];
                $decisionNodesThisRow = [];
                $processColsThisRow = [];
                $decisionColsThisRow = [];
                $processExecKeysThisRow = [];
                $decisionExecKeysThisRow = [];
                foreach ($orderedNodes as $n) {
                    $tt = trim($n['type']);
                    $cc = $indexByKey[$n['executor_key']] ?? null;
                    $kk = (string) $n['executor_key'];
                    if ($tt === 'process' || $tt === 'start' || $tt === 'end') {
                        $processNodesThisRow[] = $n;
                        if ($cc !== null) $processColsThisRow[] = $cc;
                        $processExecKeysThisRow[] = $kk;
                    }
                    if ($tt === 'decision') {
                        $decisionNodesThisRow[] = $n;
                        if ($cc !== null) $decisionColsThisRow[] = $cc;
                        $decisionExecKeysThisRow[] = $kk;
                    }
                }
                $rowHasProcessAndDecision = count($processNodesThisRow) > 0 && count($decisionNodesThisRow) > 0;

                $findDecisionPartner = function (string $decisionExecKey) use ($decisionNodesThisRow, $processNodesThisRow, $indexByKey): ?array {
                    $decisionNode = null;
                    foreach ($decisionNodesThisRow as $d) {
                        if ((string) $d['executor_key'] === $decisionExecKey) { $decisionNode = $d; break; }
                    }
                    if ($decisionNode === null) return null;
                    $decisionCol = $indexByKey[$decisionExecKey] ?? null;
                    if ($decisionCol === null) return null;

                    $nearestProcess = null;
                    $nearestDist = null;
                    $nearestCol = null;
                    foreach ($processNodesThisRow as $p) {
                        $pc = $indexByKey[$p['executor_key']] ?? null;
                        if ($pc === null) continue;
                        $d = abs($pc - $decisionCol);
                        if ($nearestDist === null || $d < $nearestDist) {
                            $nearestDist = $d;
                            $nearestProcess = $p;
                            $nearestCol = $pc;
                        }
                    }
                    if ($nearestProcess === null) return null;
                    $processLeftOfDecision = $nearestCol < $decisionCol;
                    return [
                        'process' => $nearestProcess,
                        'processExecKey' => (string) $nearestProcess['executor_key'],
                        'processCol' => $nearestCol,
                        'processLeftOfDecision' => $processLeftOfDecision,
                        'decisionCol' => $decisionCol,
                    ];
                };

                for ($i = 0; $i < count($orderedNodes) - 1; $i++) {
                    $from = $orderedNodes[$i];
                    $to = $orderedNodes[$i + 1];
                    $fromCol = $indexByKey[$from['executor_key']];
                    $toCol = $indexByKey[$to['executor_key']];
                    $fromEdge = $edge($from['type']);
                    $toEdge = $edge($to['type']);
                    $fromType = trim($from['type']);
                    $toType = trim($to['type']);
                    $isDecision = $fromType === 'decision';
                    $hasExplicitNo = filled($from['no_target']);
                    $hasExplicitYes = filled($from['yes_target']);

                    $sameRowConnector = $fromCol !== $toCol;
                    if (! $sameRowConnector) {
                        continue;
                    }

                    $rule = 'sameRow';
                    $exitSide = 'right';
                    $entrySide = 'left';

                    if ($rule === 'sameRow') {
                        if ($toType === 'decision') {
                            $exitSide = ($toCol > $fromCol) ? 'right' : 'left';
                            $entrySide = 'top';
                        } elseif ($fromType === 'decision') {
                            if ($toCol > $fromCol) { $exitSide = 'right'; $entrySide = 'left'; }
                            elseif ($toCol < $fromCol) { $exitSide = 'left'; $entrySide = 'right'; }
                            else { $exitSide = 'bottom'; $entrySide = 'top'; }
                        } else {
                            if ($toCol > $fromCol) { $exitSide = 'right'; $entrySide = 'left'; }
                            else { $exitSide = 'left'; $entrySide = 'right'; }
                        }
                    }

                    if ($isDecision && $hasExplicitYes) {
                        $partner = $rowHasProcessAndDecision ? $findDecisionPartner((string) $from['executor_key']) : null;
                        $processLeft = ($partner !== null && $partner['processLeftOfDecision'] === true);
                        $processRight = ($partner !== null && $partner['processLeftOfDecision'] === false);
                        $tIdx = $hasExplicitNo ? ((int) $from['no_target'] - 1) : null;
                        $tExecKey = $hasExplicitNo ? (string) $from['no_target_executor_key'] : null;
                        $tIsSameActivity = ($tIdx !== null && $tIdx === $rowIndex);
                        $tBackToPartnerProcess = false;
                        if ($partner !== null && $tIsSameActivity && $tExecKey === $partner['processExecKey']) {
                            $tBackToPartnerProcess = true;
                        }
                        $tToPriorActivity = ($tIdx !== null && ! $tIsSameActivity && $tIdx < $rowIndex);
                        $yesIdx = (int) $from['yes_target'] - 1;
                        $yesToNextActivity = ($yesIdx > $rowIndex);
                        $tToNextActivity = ($tIdx !== null && ! $tIsSameActivity && $tIdx > $rowIndex);

                        // ==== ROUTING KEPUTUSAN Y / T (ATURAN 5 POINT USER) ====
                        if ($rowHasProcessAndDecision && $partner !== null) {
                            // Point 2 & 4: T KEMBALI KE PROCESS SAMA (partner process)
                            // Point 3 & 5: T KE KEGIATAN SEBELUMNYA (LUAR)
                            if ($tBackToPartnerProcess) {
                                // POINT 2 (Process LEFT) + POINT 4 (Process RIGHT)
                                if ($processLeft) {
                                    $yExit = 'right';
                                    $yEntry = (in_array($toCol, $processColsThisRow, true)) ? 'bottom' : ($toType === 'decision' ? 'top' : ($toCol > $fromCol ? 'left' : 'right'));
                                } else {
                                    // POINT 4 simetris: Process RIGHT
                                    $yExit = 'left';
                                    $yEntry = (in_array($toCol, $processColsThisRow, true)) ? 'bottom' : ($toType === 'decision' ? 'top' : ($toCol > $fromCol ? 'left' : 'right'));
                                }
                                $tExit = 'bottom';
                                $tEntry = 'bottom';
                            } elseif ($tToPriorActivity) {
                                // POINT 3 (Process LEFT) + POINT 5 (Process RIGHT)
                                if ($processLeft) {
                                    $tExit = 'right';
                                } else {
                                    // POINT 5 simetris: Process RIGHT
                                    $tExit = 'left';
                                }
                                $tEntry = 'top';
                                $yExit = 'bottom';
                                $yEntry = 'top';
                            } else {
                                // T ke kegiatan BERIKUTNYA (bukan partner / bukan sebelumnya) → default + Y ke luar
                                if ($processLeft) {
                                    $tExit = 'left';
                                } else {
                                    $tExit = 'right';
                                }
                                $tEntry = 'top';
                                $yExit = 'bottom';
                                $yEntry = 'top';
                            }

                            if ($yesToNextActivity) { $yEntry = 'top'; }
                            if ($tToNextActivity) { $tEntry = 'top'; }

                            $ySameRow = ($yesIdx === $rowIndex);
                            if (! $ySameRow) {
                                $drawExplicitSource($fromCol, $toCol, $yExit, 'mid', $fromEdge, $yesIdx, 'Y', 'top');
                            } else {
                                $drawElbow($fromCol, $toCol, $yExit, $yEntry, 'mid', 'mid', $fromEdge, $toEdge, 'Y');
                            }

                            if ($hasExplicitNo) {
                                $noExecKey = (string) $from['no_target_executor_key'];
                                $noCol = null;
                                $noNode = null;
                                $noActivityIdx = (int) $from['no_target'] - 1;
                                $noInSameActivity = ($noActivityIdx === $rowIndex);
                                if ($noInSameActivity) {
                                    foreach ($orderedNodes as $n) {
                                        if ((string) $n['executor_key'] === $noExecKey) {
                                            $noCol = $indexByKey[$n['executor_key']] ?? null;
                                            $noNode = $n;
                                            break;
                                        }
                                    }
                                }
                                $tSameRow = ($noActivityIdx === $rowIndex);
                                if ($noCol !== null && $noNode !== null && ! isset($drawnBranches[$i.'-T'])) {
                                    $noEdge = $edge($noNode['type']);
                                    if (! $tSameRow) {
                                        $drawExplicitSource($fromCol, $noCol, $tExit, 'mid', $fromEdge, $noActivityIdx, 'T', 'top');
                                    } else {
                                        $drawElbow($fromCol, $noCol, $tExit, $tEntry, 'mid', 'mid', $fromEdge, $noEdge, 'T');
                                    }
                                    $drawnBranches[$i.'-T'] = true;
                                } elseif (! $noInSameActivity && $tToPriorActivity && ! isset($drawnBranches[$i.'-T'])) {
                                    if ($tIdx !== null) {
                                        $drawExplicitSource($fromCol, $fromCol, $tExit, 'mid', $fromEdge, $tIdx, 'T', 'top');
                                    }
                                    $drawnBranches[$i.'-T'] = true;
                                }
                            }
                        } else {
                            // ===== BUKAN SAME-ACTIVITY (Process & Decision BERADA DI KEGIATAN BERBEDA) =====
                            $yesIdx2 = (int) $from['yes_target'] - 1;
                            $yToNext2 = ($yesIdx2 > $rowIndex);
                            $ySameRow2 = ($yesIdx2 === $rowIndex);
                            $toRule = function ($toType2, $fromCol2, $toCol2, $fromType2, $yToNext, $tIdx = null, $rowIdx = null) {
                                $tToNext = ($tIdx !== null && $rowIdx !== null && $tIdx > $rowIdx);
                                $ret = null;
                                if ($toType2 === 'decision') {
                                    $ret = [($toCol2 > $fromCol2 ? 'right' : 'left'), 'top'];
                                } elseif ($fromType2 === 'decision') {
                                    $ret = [($toCol2 > $fromCol2 ? 'right' : ($toCol2 < $fromCol2 ? 'left' : 'bottom')),
                                            ($toCol2 > $fromCol2 ? 'left' : ($toCol2 < $fromCol2 ? 'right' : 'top'))];
                                } elseif ($toCol2 > $fromCol2) {
                                    $ret = ['right', 'left'];
                                } else {
                                    $ret = ['left', 'right'];
                                }
                                if ($yToNext || $tToNext) {
                                    $ret[1] = 'top';
                                }
                                return $ret;
                            };
                            [$yExit, $yEntry] = $toRule($toType, $fromCol, $toCol, $fromType, $yToNext2);
                            if ($yToNext2 || $yesIdx2 !== $rowIndex) { $yEntry = 'top'; }
                            if (! $ySameRow2) {
                                $drawExplicitSource($fromCol, $toCol, $yExit, 'mid', $fromEdge, $yesIdx2, 'Y', 'top');
                            } else {
                                $drawElbow($fromCol, $toCol, $yExit, $yEntry, 'mid', 'mid', $fromEdge, $toEdge, 'Y');
                            }

                            if ($hasExplicitNo) {
                                $noExecKey = (string) $from['no_target_executor_key'];
                                $noCol = null;
                                $noNode = null;
                                $noActivityIdx = (int) $from['no_target'] - 1;
                                $noInSameActivity = ($noActivityIdx === $rowIndex);
                                if ($noInSameActivity) {
                                    foreach ($orderedNodes as $n) {
                                        if ((string) $n['executor_key'] === $noExecKey) {
                                            $noCol = $indexByKey[$n['executor_key']] ?? null;
                                            $noNode = $n;
                                            break;
                                        }
                                    }
                                }
                                if ($noCol !== null && $noNode !== null && ! isset($drawnBranches[$i.'-T'])) {
                                    $noEdge = $edge($noNode['type']);
                                    $noType = trim($noNode['type']);
                                    [$tExit, $tEntry] = $toRule($noType, $fromCol, $noCol, $fromType, false, $noActivityIdx, $rowIndex);
                                    if ($noActivityIdx > $rowIndex) { $tEntry = 'top'; }
                                    if ($tExit === $yExit) {
                                        $alternatives = ['bottom', 'left', 'right', 'top'];
                                        foreach ($alternatives as $alt) {
                                            if ($alt !== $yExit) { $tExit = $alt; break; }
                                        }
                                    }
                                    if ($noActivityIdx !== $rowIndex) {
                                        $drawExplicitSource($fromCol, $noCol, $tExit, 'mid', $fromEdge, $noActivityIdx, 'T', 'top');
                                    } else {
                                        $drawElbow($fromCol, $noCol, $tExit, $tEntry, 'mid', 'mid', $fromEdge, $noEdge, 'T');
                                    }
                                    $drawnBranches[$i.'-T'] = true;
                                }
                            }
                        }
                    } elseif ($isDecision) {
                        // Decision TANPA explicit yes → default rule (aturan standar routing)
                        $toRule2 = function ($toType2, $fromCol2, $toCol2, $fromType2) use ($rowHasProcessAndDecision, $processColsThisRow) {
                            if ($toType2 === 'decision') return [($toCol2 > $fromCol2 ? 'right' : 'left'), 'top'];
                            if ($rowHasProcessAndDecision && $fromType2 === 'decision' && in_array($toCol2, $processColsThisRow, true)) {
                                if ($toCol2 > $fromCol2) return ['right', 'bottom'];
                                if ($toCol2 < $fromCol2) return ['left', 'bottom'];
                                return ['bottom', 'top'];
                            }
                            if ($fromType2 === 'decision') {
                                return [($toCol2 > $fromCol2 ? 'right' : ($toCol2 < $fromCol2 ? 'left' : 'bottom')),
                                        ($toCol2 > $fromCol2 ? 'left' : ($toCol2 < $fromCol2 ? 'right' : 'top'))];
                            }
                            if ($toCol2 > $fromCol2) return ['right', 'left'];
                            return ['left', 'right'];
                        };
                        [$yExit, $yEntry] = $toRule2($toType, $fromCol, $toCol, $fromType);
                        $drawElbow($fromCol, $toCol, $yExit, $yEntry, 'mid', 'mid', $fromEdge, $toEdge, 'Y');
                    } else {
                        // Biasa (bukan decision) = aturan kiri/kanan
                        if ($toCol > $fromCol) { $exitSide = 'right'; $entrySide = ($toType === 'decision') ? 'top' : 'left'; }
                        else { $exitSide = 'left'; $entrySide = ($toType === 'decision') ? 'top' : 'right'; }
                        $drawElbow($fromCol, $toCol, $exitSide, $entrySide, 'mid', 'mid', $fromEdge, $toEdge, null);
                    }
                }

                $svgs = [];
                for ($c = 0; $c < max(count($executorKeys), 1); $c++) {
                    $content = implode('', array_merge($cells[$c]['shapes'], $cells[$c]['lines'], $cells[$c]['labels']));
                    $svgId = 'flow-row-' . $rowIndex . '-col-' . $c;
                    $svg = '<svg xmlns="http://www.w3.org/2000/svg" id="' . $svgId . '" width="' . (100 + ($bleedX * 2)) . '" height="' . $canvasHeight . '" viewBox="-' . $bleedX . ' -' . $bleedTop . ' ' . (100 + ($bleedX * 2)) . ' ' . $canvasHeight . '">'
                        . '<desc>Flow SVG row ' . $rowIndex . ' column ' . $c . ' - ' . $svgId . ' - ' . uniqid('svg_', true) . '</desc>'
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
            $MAX_SPAN = 3;
            $currentSpanCount = 0;

            foreach ($activities as $activityIndex => $activityRow) {
                $duration = trim((string) data_get($activityRow, 'duration', ''));
                $durationKey = mb_strtolower($duration);

                if ($durationKey === '') {
                    $durationSpans[$activityIndex] = 1;
                    $currentDurationKey = null;
                    $currentSpanStart = null;
                    $currentSpanCount = 0;
                    continue;
                }

                $isSameGroup = ($durationKey === $currentDurationKey && $currentSpanStart !== null);
                $isSpanRoom = ($isSameGroup && $currentSpanCount < $MAX_SPAN);

                if ($isSpanRoom) {
                    $durationSpans[$currentSpanStart] = ($durationSpans[$currentSpanStart] ?? 1) + 1;
                    $durationSpans[$activityIndex] = 0;
                    $currentSpanCount++;
                    continue;
                }

                $currentDurationKey = $durationKey;
                $currentSpanStart = $activityIndex;
                $durationSpans[$activityIndex] = 1;
                $currentSpanCount = 1;
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
                    <th rowspan="2" class="header-merged" style="width: 22%;">Kegiatan</th>
                    <th colspan="{{ $executorCount }}" style="width: {{ $executorTotalWidthPercent }}%;">Pelaksana</th>
                    <th colspan="3" style="width: 27%;">Mutu Baku</th>
                    <th rowspan="2" class="header-merged" style="width: 6%;">Keterangan</th>
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
                        $prevData = $index > 0 ? (is_array($activities[$index - 1]) ? $activities[$index - 1] : (array) $activities[$index - 1]) : null;
                        $nextData = $index < ($activities->count() - 1) ? (is_array($activities[$index + 1]) ? $activities[$index + 1] : (array) $activities[$index + 1]) : null;
                        $prevLastExecutor = $prevData !== null ? ($lastExecutorKey)($prevData, $executorKeysForConnector) : null;
                        $nextFirstExecutor = $nextData !== null ? ($firstExecutorKey)($nextData, $executorKeysForConnector) : null;
                    @endphp
                    <tr>
                        <td class="activity-no">{{ $index + 1 }}</td>
                        <td>
                            <div class="activity-name">{{ data_get($row, 'name', '-') }}</div>
                        </td>
                        @php
                            $rowSvgs = $buildRowFlowSvgs($row, $index, $index === 0, $index === ($activities->count() - 1), $prevLastExecutor, $nextFirstExecutor);
                        @endphp
                        @forelse ($executors as $executor)
                            <td class="executor-cell">
                                @if (!empty($rowSvgs[$executor['key']]))
                                    <img class="flow-svg" src="{{ $rowSvgs[$executor['key']] }}" alt="Flow {{ $executor['label'] }} {{ $index + 1 }}">
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
                            <td class="duration-cell" rowspan="{{ $span }}" style="vertical-align: middle;">
                                {{ data_get($row, 'duration') ?: '-' }}
                            </td>
                        @endif
                        <td class="text-cell" style="border-right: 1px solid #000000;">{!! $renderListHtml(data_get($row, 'outputs', []), true) !!}</td>
                        <td class="text-cell" style="border-left: 1px solid #000000;">
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
