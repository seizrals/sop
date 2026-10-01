import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { jsPDF } from 'jspdf';
import autoTable from 'jspdf-autotable';

const [, , inputPath, outputPath] = process.argv;

if (!inputPath || !outputPath) {
  console.error('Usage: node generate-sop-pdf.mjs <input.json> <output.pdf>');
  process.exit(1);
}

const payload = JSON.parse(fs.readFileSync(inputPath, 'utf8'));

const arialRegularPath = 'C:\\Windows\\Fonts\\arial.ttf';
const arialBoldPath = 'C:\\Windows\\Fonts\\arialbd.ttf';

const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
const pageWidth = doc.internal.pageSize.getWidth();
const pageHeight = doc.internal.pageSize.getHeight();

const PURE_BLACK = [0, 0, 0];
const SHAPE_FILL = [211, 211, 211];
const SHAPE_TEXT_WHITE = [0, 0, 0];
const LINE_WIDTH_MM = 0.32;
const SHAPE_STROKE_MM = 0.34;
const ARROW_PENETRATE_MM = 0.04;
const NODE_SIZE = 6.2;
const DIAMOND_SCALE = 1.3;
const DIAMOND_VSCALE = DIAMOND_SCALE * 0.8;

const hasArialRegular = fs.existsSync(arialRegularPath);
const hasArialBold = fs.existsSync(arialBoldPath);

if (hasArialRegular) {
  doc.addFileToVFS('arial.ttf', fs.readFileSync(arialRegularPath).toString('base64'));
  doc.addFont('arial.ttf', 'Arial', 'normal');
}

if (hasArialBold) {
  doc.addFileToVFS('arialbd.ttf', fs.readFileSync(arialBoldPath).toString('base64'));
  doc.addFont('arialbd.ttf', 'Arial', 'bold');
}

const regularFont = hasArialRegular ? 'Arial' : 'helvetica';
const boldFont = hasArialBold ? 'Arial' : 'helvetica';

const setRegular = () => doc.setFont(regularFont, 'normal');
const setBold = () => doc.setFont(boldFont, 'bold');

const formatDate = (value) => {
  if (!value) return '-';
  try {
    return new Intl.DateTimeFormat('id-ID', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    }).format(new Date(value));
  } catch {
    return String(value);
  }
};

const numberedList = (items = []) => {
  if (!Array.isArray(items) || items.length === 0) return '-';
  return items.map((item, index) => `${index + 1}. ${item}`).join('\n');
};

const bulletList = (items = []) => {
  if (!Array.isArray(items) || items.length === 0) return '-';
  return items.map((item) => `- ${item}`).join('\n');
};

const drawCenteredTextBlock = (lines, centerX, startY, lineHeight) => {
  lines.forEach((line, index) => {
    doc.text(line, centerX, startY + (index * lineHeight), { align: 'center' });
  });
};

const loadLogoDataUrl = () => {
  if (!payload.logo_path || !fs.existsSync(payload.logo_path)) return null;
  const ext = path.extname(payload.logo_path).slice(1).toUpperCase() || 'PNG';
  const mime = ext === 'JPG' ? 'JPEG' : ext;
  return {
    type: mime,
    data: `data:image/${mime.toLowerCase()};base64,${fs.readFileSync(payload.logo_path).toString('base64')}`,
  };
};

const shapeEdgeOffsets = (type) => {
  switch (type) {
    case 'decision':
      return {
        top: NODE_SIZE * DIAMOND_VSCALE,
        bottom: NODE_SIZE * DIAMOND_VSCALE,
        left: NODE_SIZE * DIAMOND_SCALE,
        right: NODE_SIZE * DIAMOND_SCALE,
      };
    case 'start':
    case 'end':
      return { top: NODE_SIZE / 2, bottom: NODE_SIZE / 2, left: NODE_SIZE, right: NODE_SIZE };
    default:
      return { top: NODE_SIZE / 2, bottom: NODE_SIZE / 2, left: NODE_SIZE, right: NODE_SIZE };
  }
};

const drawPageOne = () => {
  const startX = 10;
  const startY = 10;
  const outerW = pageWidth - 20;
  const midX = startX + (outerW / 2);
  const rightX = pageWidth - 10;
  const rowH = 7;

  doc.setDrawColor(...PURE_BLACK);
  doc.setLineWidth(SHAPE_STROKE_MM);

  const drawRightRow = (y, label, value, options = {}) => {
    const tall = options.tall === true;
    const isLast = options.isLast === true;
    const h = tall ? 42 : rowH;
    const labelSplitX = midX + 40;

    doc.line(labelSplitX, y, labelSplitX, y + h);

    setBold();
    doc.setFontSize(9.5);
    doc.text(label, midX + 2, y + (tall ? 5 : (h / 2) + 1));

    setRegular();
    doc.setFontSize(9.5);

    if (tall) {
      const valueCenterX = labelSplitX + ((rightX - labelSplitX) / 2);
      const positionLines = String(payload.approval_position || 'Kepala Badan Pusat Statistik Kabupaten Gorontalo Utara')
        .split(/\r?\n/)
        .flatMap((line) => doc.splitTextToSize(line, rightX - labelSplitX - 8));

      drawCenteredTextBlock(positionLines, valueCenterX, y + 5, 5);

      setBold();
      const approvalName = String(payload.approval_name || '-');
      doc.text(approvalName, valueCenterX, y + 33, { align: 'center' });
      const textWidth = doc.getTextWidth(approvalName);
      doc.setLineWidth(SHAPE_STROKE_MM * 1.4);
      doc.line(valueCenterX - (textWidth / 2), y + 34, valueCenterX + (textWidth / 2), y + 34);
      doc.setLineWidth(SHAPE_STROKE_MM);

      setRegular();
      doc.text(`NIP. ${payload.approval_nip || '-'}`, valueCenterX, y + 38, { align: 'center' });
    } else {
      doc.text(value, labelSplitX + 2, y + (h / 2) + 1);
    }

    if (!isLast) {
      doc.line(midX, y + h, rightX, y + h);
    }

    return y + h;
  };

  let currentY = startY;
  currentY = drawRightRow(currentY, 'NOMOR SOP', `: ${payload.sop_number || '-'}`);
  currentY = drawRightRow(currentY, 'TGL. PEMBUATAN', `: ${formatDate(payload.creation_date)}`);
  currentY = drawRightRow(currentY, 'TGL. REVISI', `: ${formatDate(payload.revision_date)}`);
  currentY = drawRightRow(currentY, 'TGL. EFEKTIF', `: ${formatDate(payload.effective_date)}`);
  currentY = drawRightRow(currentY, 'DISAHKAN OLEH', '', { tall: true, isLast: true });

  doc.line(midX, currentY, rightX, currentY);

  setRegular();
  doc.setFontSize(9.5);
  const namaSopWidth = rightX - (midX + 44);
  const namaSopLines = doc.splitTextToSize(`: ${payload.title || '-'}`, namaSopWidth);
  const namaSopHeight = Math.max(rowH, (namaSopLines.length * 5) + 2);
  doc.line(midX + 40, currentY, midX + 40, currentY + namaSopHeight);
  setBold();
  doc.text('NAMA SOP', midX + 2, currentY + (namaSopHeight / 2) + 1);
  setRegular();
  if (namaSopLines.length === 1) {
    doc.text(namaSopLines[0], midX + 42, currentY + (namaSopHeight / 2) + 1);
  } else {
    doc.text(namaSopLines, midX + 42, currentY + 5);
  }
  currentY += namaSopHeight;

  const finalHeaderHeight = currentY - startY;
  doc.rect(startX, startY, outerW, finalHeaderHeight);
  doc.line(midX, startY, midX, startY + finalHeaderHeight);

  const leftCenterX = (startX + midX) / 2;
  const leftCenterY = startY + (finalHeaderHeight / 2);
  const logo = loadLogoDataUrl();
  const agencyLines = Array.isArray(payload.agency_lines) ? payload.agency_lines : [];
  const maxLogoWidth = 30;
  const maxLogoHeight = 24;
  let logoWidth = maxLogoWidth;
  let logoHeight = maxLogoHeight;

  if (logo) {
    const logoProps = doc.getImageProperties(logo.data);
    const ratio = logoProps.width / logoProps.height;
    logoWidth = maxLogoWidth;
    logoHeight = logoWidth / ratio;

    if (logoHeight > maxLogoHeight) {
      logoHeight = maxLogoHeight;
      logoWidth = logoHeight * ratio;
    }
  }

  const logoTextGap = 10;
  const agencyLineHeight = 7;
  const agencyBlockHeight = agencyLines.length * agencyLineHeight;
  const totalLeftBlockHeight = (logo ? logoHeight : 0) + (logo ? logoTextGap : 0) + agencyBlockHeight;
  const groupStartY = leftCenterY - (totalLeftBlockHeight / 2);

  if (logo) {
    doc.addImage(logo.data, logo.type, leftCenterX - (logoWidth / 2), groupStartY, logoWidth, logoHeight);
  }

  setBold();
  doc.setFontSize(14);
  const agencyStartY = groupStartY + (logo ? logoHeight + logoTextGap : 0) + 3;
  drawCenteredTextBlock(agencyLines, leftCenterX, agencyStartY, agencyLineHeight);

  autoTable(doc, {
    startY: startY + finalHeaderHeight,
    margin: { left: 10, right: 10 },
    body: [
      [
        { content: 'DASAR HUKUM:', styles: { fontStyle: 'bold', valign: 'middle' } },
        { content: 'KUALIFIKASI PELAKSANA:', styles: { fontStyle: 'bold', valign: 'middle' } },
      ],
      [
        { content: numberedList(payload.legal_basis), styles: { valign: 'top' } },
        { content: bulletList(payload.executor_qualifications), styles: { valign: 'top' } },
      ],
      [
        { content: 'KETERKAITAN:', styles: { fontStyle: 'bold', valign: 'middle' } },
        { content: 'PERALATAN/PERLENGKAPAN:', styles: { fontStyle: 'bold', valign: 'middle' } },
      ],
      [
        { content: numberedList(payload.related_documents), styles: { valign: 'top' } },
        { content: numberedList(payload.equipment), styles: { valign: 'top' } },
      ],
      [
        { content: 'PERINGATAN:', styles: { fontStyle: 'bold', valign: 'middle' } },
        { content: 'PENCATATAN DAN PENDATAAN:', styles: { fontStyle: 'bold', valign: 'middle' } },
      ],
      [
        { content: numberedList(payload.warnings), styles: { valign: 'top' } },
        { content: numberedList(payload.recording), styles: { valign: 'top' } },
      ],
    ],
    theme: 'plain',
    styles: {
      font: regularFont,
      fontStyle: 'normal',
      fontSize: 9,
      cellPadding: 3,
      lineColor: PURE_BLACK,
      lineWidth: SHAPE_STROKE_MM,
      valign: 'top',
      overflow: 'linebreak',
      textColor: PURE_BLACK,
    },
    columnStyles: {
      0: { cellWidth: (pageWidth - 20) / 2 },
      1: { cellWidth: (pageWidth - 20) / 2 },
    },
    tableWidth: pageWidth - 20,
  });
  return doc.lastAutoTable?.finalY ?? (startY + 5);
};

const drawDiamond = (x, y, size, label = '') => {
  const vSize = size * 0.8;
  doc.setDrawColor(...PURE_BLACK);
  doc.setFillColor(...SHAPE_FILL);
  doc.setLineWidth(SHAPE_STROKE_MM);
  doc.lines(
    [[size, -vSize], [size, vSize], [-size, vSize], [-size, -vSize]],
    x - size,
    y,
    [1, 1],
    'FD',
    true
  );

  if (label) {
    doc.setTextColor(...PURE_BLACK);
    doc.setFont(boldFont, 'bold');
    doc.setFontSize(6.3);
    const textLines = doc.splitTextToSize(label, size * 1.5);
    const lineHeight = 2.4;
    const totalHeight = textLines.length * lineHeight;
    const startY = y - (totalHeight / 2) + (lineHeight / 2);
    textLines.forEach((line, index) => {
      doc.text(line, x, startY + (index * lineHeight), { align: 'center', baseline: 'middle' });
    });
    doc.setTextColor(...PURE_BLACK);
  }
};

const drawStartEndShape = (x, y, size, label) => {
  doc.setDrawColor(...PURE_BLACK);
  doc.setFillColor(...SHAPE_FILL);
  doc.setLineWidth(SHAPE_STROKE_MM);
  doc.roundedRect(x - size, y - (size / 2), size * 2, size, 3, 3, 'FD');
  doc.setTextColor(...SHAPE_TEXT_WHITE);
  doc.setFont(boldFont, 'bold');
  doc.setFontSize(6.8);
  doc.text(label, x, y, { align: 'center', baseline: 'middle' });
  doc.setTextColor(...PURE_BLACK);
};

const drawProcessShape = (x, y, size) => {
  doc.setDrawColor(...PURE_BLACK);
  doc.setFillColor(...SHAPE_FILL);
  doc.setLineWidth(SHAPE_STROKE_MM);
  doc.rect(x - size, y - (size / 2), size * 2, size, 'FD');
};

const drawArrow = (x, y, direction) => {
  const s = 1.0;
  const t = 0.7;
  doc.setDrawColor(...PURE_BLACK);
  doc.setFillColor(...PURE_BLACK);
  doc.setLineWidth(LINE_WIDTH_MM);
  if (direction === 'down') {
    const tip = [x, y + ARROW_PENETRATE_MM];
    const tail = [x, tip[1] - s];
    doc.triangle(tip[0], tip[1], tail[0] - t, tail[1], tail[0] + t, tail[1], 'DF');
  }
  if (direction === 'up') {
    const tip = [x, y - ARROW_PENETRATE_MM];
    const tail = [x, tip[1] + s];
    doc.triangle(tip[0], tip[1], tail[0] - t, tail[1], tail[0] + t, tail[1], 'DF');
  }
  if (direction === 'right') {
    const tip = [x + ARROW_PENETRATE_MM, y];
    const tail = [tip[0] - s, y];
    doc.triangle(tip[0], tip[1], tail[0], tail[1] - t, tail[0], tail[1] + t, 'DF');
  }
  if (direction === 'left') {
    const tip = [x - ARROW_PENETRATE_MM, y];
    const tail = [tip[0] + s, y];
    doc.triangle(tip[0], tip[1], tail[0], tail[1] - t, tail[0], tail[1] + t, 'DF');
  }
};

const drawBranchLabel = (label, x, y, side) => {
  doc.setFontSize(9.5);
  doc.setFont(boldFont, 'bold');
  let offsetX = 2.5;
  let offsetY = 2.0;
  if (side === 'exact') { offsetX = 0; offsetY = 0; }
  else if (side === 'right') { offsetX = 2.2; offsetY = 2.2; }
  else if (side === 'left') { offsetX = -4.2; offsetY = 2.2; }
  else if (side === 'bottom') { offsetX = 2.0; offsetY = 3.4; }
  else if (side === 'top') { offsetX = 2.0; offsetY = -1.4; }
  doc.setTextColor(...PURE_BLACK);
  doc.text(label, x + offsetX, y + offsetY, { align: 'center', baseline: 'middle' });
  doc.setTextColor(...PURE_BLACK);
  doc.setFont(regularFont, 'normal');
};

const setLineStyle = () => {
  doc.setDrawColor(...PURE_BLACK);
  doc.setLineWidth(LINE_WIDTH_MM);
};

const drawActivityTableAndFlows = (overrideStartY = null) => {
  const minSpaceForActivityStart = 80;
  const bottomMargin = 10;

  let actualStartY = 10;
  if (overrideStartY !== null && (pageHeight - overrideStartY - bottomMargin) >= minSpaceForActivityStart) {
    actualStartY = overrideStartY;
  } else {
    doc.addPage();
  }

  const startY = actualStartY;

  const activities = Array.isArray(payload.activities) ? payload.activities : [];

  const rawExecutors = Array.isArray(payload.executors) && payload.executors.length > 0
    ? payload.executors
    : [{ key: 'executor', label: 'Pelaksana' }];

  const usedKeys = [];
  for (const row of activities) {
    const nodes = Array.isArray(row.flow_nodes) ? row.flow_nodes : [];
    for (const n of nodes) {
      const k = String(n.executor_key || '').trim();
      if (k && !usedKeys.includes(k)) usedKeys.push(k);
    }
  }
  let executors = rawExecutors;
  if (usedKeys.length > 0) {
    const byKey = {};
    for (const e of rawExecutors) byKey[String(e.key)] = e;
    executors = usedKeys.map((key) => byKey[key] || { key: String(key), label: String(key) });
  }

  const cellCoordinates = {};
  const roleMap = executors.map((executor) => executor.key);
  const getRoleIndex = (role) => roleMap.indexOf(role);
  const hasNumericTarget = (value) => Number.isInteger(Number(value)) && Number(value) > 0;
  const firstTargetMeta = (activityIndex, executorKey = '') => {
    if (activityIndex < 0 || !activities[activityIndex]) return null;
    const targetNodes = Array.isArray(activities[activityIndex].flow_nodes) ? activities[activityIndex].flow_nodes : [];
    const normalizedExecutorKey = String(executorKey || '').trim();
    const targetFirst = normalizedExecutorKey
      ? (targetNodes.find((node) => node.executor_key === normalizedExecutorKey) || targetNodes[0])
      : targetNodes[0];
    if (!targetFirst) return null;
    const targetExecutor = executors.find((executor) => executor.key === targetFirst.executor_key);
    return {
      node: targetFirst,
      executorLabel: targetExecutor?.label || targetFirst.executor_key,
      key: `${activityIndex}-${targetFirst.executor_key}`,
    };
  };

  const waktuSpans = {};
  let currentWaktu = '';
  let spanStartIdx = -1;
  let currentSpanCount = 0;
  const MAX_WAKTU_SPAN = 3;

  activities.forEach((activity, index) => {
    const waktu = String(activity.duration || '').trim();
    if (waktu === '') {
      waktuSpans[index] = 1;
      currentWaktu = '';
      spanStartIdx = -1;
      currentSpanCount = 0;
      return;
    }

    const isSameGroup = waktu === currentWaktu && spanStartIdx !== -1;
    const isSpanRoom = isSameGroup && currentSpanCount < MAX_WAKTU_SPAN;

    if (isSpanRoom) {
      waktuSpans[spanStartIdx] += 1;
      waktuSpans[index] = 0;
      currentSpanCount += 1;
      return;
    }

    currentWaktu = waktu;
    spanStartIdx = index;
    waktuSpans[index] = 1;
    currentSpanCount = 1;
  });

  const bodyData = activities.map((activity, index) => {
    const row = [
      index + 1,
      activity.name || '-',
      ...executors.map(() => ''),
      numberedList(activity.quality_requirements || []),
    ];

    if ((waktuSpans[index] ?? 1) > 0) {
      row.push({
        content: activity.duration || '-',
        rowSpan: waktuSpans[index],
        styles: {
          halign: 'center',
          valign: 'middle',
          fontSize: 8,
          fontStyle: 'bold',
          lineColor: [0, 0, 0],
          lineWidth: 0.1,
        },
      });
    }

    row.push(numberedList(activity.outputs || []));
    row.push(activity.notes || '');
    return row;
  });

  const bodyCellMap = {
    no: 0,
    kegiatan: 1,
    pelaksanaStart: 2,
    kelengkapan: 2 + executors.length,
    waktu: 3 + executors.length,
    output: 4 + executors.length,
    keterangan: 5 + executors.length,
  };

  const headTop = [
    { content: 'No', rowSpan: 2 },
    { content: 'Kegiatan', rowSpan: 2 },
    { content: 'Pelaksana', colSpan: executors.length, styles: { halign: 'center' } },
    { content: 'Mutu Baku', colSpan: 3, styles: { halign: 'center' } },
    { content: 'Keterangan', rowSpan: 2 },
  ];

  const headSecond = [
    ...executors.map((executor) => executor.label),
    'Kelengkapan',
    'Waktu',
    'Output',
  ];

  const availableWidth = pageWidth - 20;
  const noWidth = 10;
  const qualityWidth = 29;
  const durationWidth = 18;
  const outputWidth = 28;
  const notesWidth = 20;
  const remainingWidth = availableWidth - noWidth - qualityWidth - durationWidth - outputWidth - notesWidth;
  const minKegiatanWidth = executors.length >= 5 ? 38 : 48;
  const executorSharePct = 0.55;
  let executorWidth = Math.min(28, Math.max(18, (remainingWidth * executorSharePct) / Math.max(executors.length, 1)));
  let kegiatanWidth = remainingWidth - (executorWidth * executors.length);

  if (kegiatanWidth < minKegiatanWidth) {
    executorWidth = Math.max(16, (remainingWidth - minKegiatanWidth) / Math.max(executors.length, 1));
    kegiatanWidth = remainingWidth - (executorWidth * executors.length);
  }

  const columnStyles = {
    0: { cellWidth: noWidth, halign: 'center' },
    1: { cellWidth: kegiatanWidth },
  };

  executors.forEach((_, index) => {
    columnStyles[2 + index] = { cellWidth: executorWidth };
  });

  columnStyles[bodyCellMap.kelengkapan] = { cellWidth: qualityWidth };
  columnStyles[bodyCellMap.waktu] = { cellWidth: durationWidth, halign: 'center' };
  columnStyles[bodyCellMap.output] = { cellWidth: outputWidth };
  columnStyles[bodyCellMap.keterangan] = { cellWidth: notesWidth };

  autoTable(doc, {
    startY: 10,
    head: [headTop, headSecond],
    body: bodyData,
    theme: 'plain',
    styles: {
      font: regularFont,
      fontStyle: 'normal',
      fontSize: 7,
      cellPadding: 1,
      lineColor: PURE_BLACK,
      lineWidth: SHAPE_STROKE_MM,
      valign: 'middle',
      minCellHeight: 14,
      textColor: PURE_BLACK,
    },
    headStyles: {
      font: boldFont,
      fontStyle: 'bold',
      fillColor: 240,
      textColor: PURE_BLACK,
      halign: 'center',
      fontSize: 8,
    },
    columnStyles,
    margin: { left: 10, right: 10, top: 10, bottom: 10 },
    tableWidth: availableWidth,
    didDrawCell: (data) => {
      if (data.section === 'body' && data.column.index >= bodyCellMap.pelaksanaStart && data.column.index < bodyCellMap.kelengkapan) {
        const activityIndex = data.row.index;
        const executor = executors[data.column.index - bodyCellMap.pelaksanaStart];
        const key = `${activityIndex}-${executor.key}`;

        cellCoordinates[key] = {
          x: data.cell.x,
          y: data.cell.y,
          w: data.cell.width,
          h: data.cell.height,
          page: doc.getNumberOfPages(),
        };

        const activity = activities[activityIndex];
        const nodes = Array.isArray(activity?.flow_nodes) ? activity.flow_nodes : [];
        const node = nodes.find((item) => item.executor_key === executor.key);

        if (!node) return;

        const cx = data.cell.x + (data.cell.width / 2);
        const cy = data.cell.y + (data.cell.height / 2);

        if (node.type === 'start') {
          drawStartEndShape(cx, cy, NODE_SIZE, 'Start');
        } else if (node.type === 'end') {
          drawStartEndShape(cx, cy, NODE_SIZE, 'End');
        } else if (node.type === 'process') {
          drawProcessShape(cx, cy, NODE_SIZE);
        } else if (node.type === 'decision') {
          drawDiamond(cx, cy, NODE_SIZE * DIAMOND_SCALE, node.label || '');
        }
      }
    },
  });

  const totalPages = doc.getNumberOfPages();

  const slotTrackers = {};
  const ensureSlotTracker = (rIdx) => {
    if (!slotTrackers[rIdx]) {
      const nodesR = Array.isArray(activities[rIdx]?.flow_nodes) ? activities[rIdx].flow_nodes : [];
      const t = {};
      nodesR.forEach((n) => {
        t[n.executor_key] = {
          top: { count: 0, positions: { mid: false, upper: false, lower: false } },
          right: { count: 0, positions: { mid: false, upper: false, lower: false } },
          bottom: { count: 0, positions: { mid: false, upper: false, lower: false } },
          left: { count: 0, positions: { mid: false, upper: false, lower: false } },
        };
      });
      slotTrackers[rIdx] = t;
    }
    return slotTrackers[rIdx];
  };

  const acquireSlot = (rIdx, execKey, side) => {
    const track = ensureSlotTracker(rIdx);
    const t = (track[execKey] = track[execKey] || {
      top: { count: 0, positions: { mid: false, upper: false, lower: false } },
      right: { count: 0, positions: { mid: false, upper: false, lower: false } },
      bottom: { count: 0, positions: { mid: false, upper: false, lower: false } },
      left: { count: 0, positions: { mid: false, upper: false, lower: false } },
    });
    const s = t[side];
    s.count++;
    if (s.count === 1 || !s.positions.mid) {
      s.positions.mid = true;
      return 'mid';
    }
    if (!s.positions.upper) {
      s.positions.upper = true;
      return 'upper';
    }
    s.positions.lower = true;
    return 'lower';
  };

  const pointOnSide = (side, slot, cx, cy, offsets) => {
    const delta = 2.3;
    if (side === 'right') {
      let y = cy;
      if (slot === 'upper') y = cy - delta;
      else if (slot === 'lower') y = cy + delta;
      return { x: cx + offsets.right, y };
    }
    if (side === 'left') {
      let y = cy;
      if (slot === 'upper') y = cy - delta;
      else if (slot === 'lower') y = cy + delta;
      return { x: cx - offsets.left, y };
    }
    if (side === 'top') {
      let x = cx;
      if (slot === 'upper') x = cx - delta;
      else if (slot === 'lower') x = cx + delta;
      return { x, y: cy - offsets.top };
    }
    let x = cx;
    if (slot === 'upper') x = cx - delta;
    else if (slot === 'lower') x = cx + delta;
    return { x, y: cy + offsets.bottom };
  };

  const arrowDirForEntry = (entrySide) => {
    if (entrySide === 'left') return 'right';
    if (entrySide === 'right') return 'left';
    if (entrySide === 'top') return 'down';
    return 'up';
  };

  const drawConnectorV2 = (
    fromRIdx, fromExecKey, toRIdx, toExecKey,
    exitSide, entrySide,
    branchLabel = null
  ) => {
    const fromKeyC = `${fromRIdx}-${fromExecKey}`;
    const toKeyC = `${toRIdx}-${toExecKey}`;
    const fromC = cellCoordinates[fromKeyC];
    const toC = cellCoordinates[toKeyC];
    if (!fromC || !toC) return;

    const samePage = fromC.page === toC.page;
    if (!samePage) return;

    doc.setPage(fromC.page);
    setLineStyle();

    const fromNodeType = (activities[fromRIdx]?.flow_nodes || []).find((n) => n.executor_key === fromExecKey)?.type || 'process';
    const toNodeType = (activities[toRIdx]?.flow_nodes || []).find((n) => n.executor_key === toExecKey)?.type || 'process';
    const fromOffsetsC = shapeEdgeOffsets(fromNodeType);
    const toOffsetsC = shapeEdgeOffsets(toNodeType);

    const fxC = fromC.x + (fromC.w / 2);
    const fyC = fromC.y + (fromC.h / 2);
    const txC = toC.x + (toC.w / 2);
    const tyC = toC.y + (toC.h / 2);

    const exSlot = acquireSlot(fromRIdx, fromExecKey, exitSide);
    const enSlot = acquireSlot(toRIdx, toExecKey, entrySide);
    const exitP = pointOnSide(exitSide, exSlot, fxC, fyC, fromOffsetsC);
    const entryP = pointOnSide(entrySide, enSlot, txC, tyC, toOffsetsC);

    const dirSign = { right: 1, left: -1, top: -1, bottom: 1 };
    const isVExit = exitSide === 'top' || exitSide === 'bottom';
    const isVEntry = entrySide === 'top' || entrySide === 'bottom';
    const isShortStepCase = (!isVExit && isVEntry) || (isVExit && !isVEntry);
    const straightStep = isShortStepCase ? 1.8 : 2.8;
    const approach = isShortStepCase ? 2.0 : 3.2;
    let stepX = exitP.x;
    let stepY = exitP.y;
    if (!isVExit) stepX += dirSign[exitSide] * straightStep;
    else stepY += dirSign[exitSide] * straightStep;
    // SNAKE: Pastikan stepX/stepY TIDAK dekat border cell (1mm zona aman sekitar garis tabel)
    const fromCellRight = fromC.x + fromC.w;
    const fromCellLeft  = fromC.x;
    const fromCellBot   = fromC.y + fromC.h;
    const fromCellTop   = fromC.y;
    if (!isVExit && exitSide === 'right' && stepX >= (fromCellRight - 0.8) && stepX <= (fromCellRight + 1.2)) { stepX = fromCellRight + 1.8; }
    if (!isVExit && exitSide === 'left'  && stepX <= (fromCellLeft  + 0.8) && stepX >= (fromCellLeft  - 1.2)) { stepX = fromCellLeft  - 1.8; }
    if (isVExit  && exitSide === 'bottom'&& stepY >= (fromCellBot   - 0.8) && stepY <= (fromCellBot   + 1.2)) { stepY = fromCellBot   + 1.8; }
    if (isVExit  && exitSide === 'top'   && stepY <= (fromCellTop   + 0.8) && stepY >= (fromCellTop   - 1.2)) { stepY = fromCellTop   - 1.8; }

    // ---- Branch Label: Menjauhi shape, garis konektor, dan border cell ----
    if (branchLabel) {
      let lx = exitP.x, ly = exitP.y;
      const fromCellRight = fromC.x + fromC.w;
      const fromCellLeft  = fromC.x;
      const fromCellBot   = fromC.y + fromC.h;
      if (!isVExit) {
        lx = exitP.x + (dirSign[exitSide] * 6.4);
        const maxSafeX = fromCellRight + 3.6;
        const minSafeX = fromCellLeft - 3.2;
        if (lx > maxSafeX) lx = maxSafeX;
        if (lx < minSafeX) lx = minSafeX;
        ly = exitP.y + 3.5;
      } else if (exitSide === 'bottom') {
        lx = exitP.x + 4.5;
        ly = exitP.y + 1.8;
        const maxSafeY = fromCellBot - 2.0;
        if (ly > maxSafeY) ly = maxSafeY;
      } else {
        lx = exitP.x + 2.5;
        ly = (exitP.y + stepY) / 2;
      }
      drawBranchLabel(branchLabel, lx, ly, 'exact');
    }

    const sameRow = fromRIdx === toRIdx;
    const sameCol = fromExecKey === toExecKey;

    const L = (x1, y1, x2, y2) => {
      if (Math.abs(x1 - x2) < 0.01 && Math.abs(y1 - y2) < 0.01) return;
      // Anti-diagonal safeguard: jika x BEDA DAN y BEDA, split jadi horizontal + vertical (elbow)
      if (Math.abs(x1 - x2) > 0.01 && Math.abs(y1 - y2) > 0.01) {
        doc.line(x1, y1, x2, y1);
        doc.line(x2, y1, x2, y2);
        return;
      }
      doc.line(x1, y1, x2, y2);
    };

    // Pre-shape point: di sumbu axis entry, mundur approach
    //   left/right entry → y SAMA entryP.y (supaya horizontal terakhir)
    //   top/bottom entry → x SAMA entryP.x (supaya vertikal terakhir)
    let preShapeX = entryP.x;
    let preShapeY = entryP.y;
    if (entrySide === 'left') { preShapeX = entryP.x - approach; preShapeY = entryP.y; }
    if (entrySide === 'right') { preShapeX = entryP.x + approach; preShapeY = entryP.y; }
    if (entrySide === 'top') { preShapeY = entryP.y - approach; preShapeX = entryP.x; }
    if (entrySide === 'bottom') { preShapeY = entryP.y + approach; preShapeX = entryP.x; }
    // SNAKE approach: preShapeX/Y juga harus menjauhi border target cell agar ELBOW approach tidak tepat di garis tabel
    const toCellRight = toC.x + toC.w;
    const toCellLeft  = toC.x;
    const toCellBot   = toC.y + toC.h;
    const toCellTop   = toC.y;
    if (!isVEntry && entrySide === 'right' && preShapeX >= (toCellRight - 0.8) && preShapeX <= (toCellRight + 1.2)) { preShapeX = toCellRight + 1.8; }
    if (!isVEntry && entrySide === 'left'  && preShapeX <= (toCellLeft  + 0.8) && preShapeX >= (toCellLeft  - 1.2)) { preShapeX = toCellLeft  - 1.8; }
    if (isVEntry  && entrySide === 'bottom'&& preShapeY >= (toCellBot   - 0.8) && preShapeY <= (toCellBot   + 1.2)) { preShapeY = toCellBot   + 1.8; }
    if (isVEntry  && entrySide === 'top'   && preShapeY <= (toCellTop   + 0.8) && preShapeY >= (toCellTop   - 1.2)) { preShapeY = toCellTop   - 1.8; }

    if (sameRow && sameCol) {
      L(exitP.x, exitP.y, stepX, stepY);
      if (!isVExit && !isVEntry) {
        // HH same-col
        L(stepX, stepY, stepX, entryP.y);
        L(stepX, entryP.y, preShapeX, entryP.y);
        L(preShapeX, entryP.y, entryP.x, entryP.y);
      } else if (isVExit && isVEntry) {
        // VV same-col
        L(stepX, stepY, entryP.x, stepY);
        L(entryP.x, stepY, entryP.x, preShapeY);
        L(entryP.x, preShapeY, entryP.x, entryP.y);
      } else if (!isVExit && isVEntry) {
        // HV same-col: exit left/right → entry top/bottom (Process→Decision)
        // VERTIKAL DULU ke preShapeY, baru HORIZONTAL ke entryP.x, lalu vertikal terakhir
        L(stepX, stepY, stepX, preShapeY);
        if (Math.abs(stepX - entryP.x) > 0.015) L(stepX, preShapeY, entryP.x, preShapeY);
        L(entryP.x, preShapeY, entryP.x, entryP.y);
      } else {
        // VH same-col: exit top/bottom → entry left/right
        // HORIZONTAL DULU ke preShapeX, baru VERTIKAL ke entryP.y, lalu horizontal terakhir
        L(stepX, stepY, preShapeX, stepY);
        if (Math.abs(stepY - entryP.y) > 0.015) L(preShapeX, stepY, preShapeX, entryP.y);
        L(preShapeX, entryP.y, entryP.x, entryP.y);
      }
      drawArrow(entryP.x, entryP.y, arrowDirForEntry(entrySide));
      return;
    }

    if (!isVExit && !isVEntry) {
      // HH: exit left/right → entry left/right
      const laneY = exitP.y;
      L(exitP.x, exitP.y, stepX, laneY);
      if (Math.abs(stepX - preShapeX) > 0.1) L(stepX, laneY, preShapeX, laneY);
      // Belok VERTICAL dulu ke y=entryP.y (pada approach x point preShapeX, tepat di belakang shape)
      L(preShapeX, laneY, preShapeX, entryP.y);
      // Horizontal PENDEK TERAKHIR menuju edge shape left/right (y SAMA: guaranteed orthogonal)
      L(preShapeX, entryP.y, entryP.x, entryP.y);
      drawArrow(entryP.x, entryP.y, arrowDirForEntry(entrySide));
      return;
    }

    if (isVExit && !isVEntry) {
      // VH: exit top/bottom → entry left/right
      const laneY = stepY;
      L(exitP.x, exitP.y, stepX, stepY);
      L(stepX, stepY, stepX, laneY);
      if (Math.abs(stepX - preShapeX) > 0.1) L(stepX, laneY, preShapeX, laneY);
      L(preShapeX, laneY, preShapeX, entryP.y);
      L(preShapeX, entryP.y, entryP.x, entryP.y);
      drawArrow(entryP.x, entryP.y, arrowDirForEntry(entrySide));
      return;
    }

    if (!isVExit && isVEntry) {
      // HV: exit left/right → entry top/bottom
      L(exitP.x, exitP.y, stepX, stepY);
      const turnX = stepX;
      if (entrySide === 'top' || entrySide === 'bottom') {
        L(stepX, stepY, turnX, preShapeY);
        if (Math.abs(turnX - entryP.x) > 0.15) L(turnX, preShapeY, entryP.x, preShapeY);
        L(entryP.x, preShapeY, entryP.x, entryP.y);
      } else {
        const laneY = entryP.y;
        L(stepX, stepY, turnX, laneY);
        if (Math.abs(turnX - entryP.x) > 0.1) L(turnX, laneY, entryP.x, laneY);
        L(entryP.x, laneY, entryP.x, entryP.y);
      }
      drawArrow(entryP.x, entryP.y, arrowDirForEntry(entrySide));
      return;
    }

    // isVExit && isVEntry
    const laneY = stepY;
    L(exitP.x, exitP.y, stepX, stepY);
    L(stepX, stepY, stepX, laneY);
    if (Math.abs(stepX - entryP.x) > 0.1) L(stepX, laneY, entryP.x, laneY);
    if (entrySide === 'top' || entrySide === 'bottom') {
      const preY = preShapeY;
      if (Math.abs(laneY - preY) > 0.3) L(entryP.x, laneY, entryP.x, preY);
      L(entryP.x, preY, entryP.x, entryP.y);
    } else {
      L(entryP.x, laneY, entryP.x, entryP.y);
    }
    drawArrow(entryP.x, entryP.y, arrowDirForEntry(entrySide));
  };

  const drawSamePageFlow = () => {
    activities.forEach((activity, rowIndex) => {
      ensureSlotTracker(rowIndex);
    });

    // Mark cross-row entry/exit for first/last node per row
    activities.forEach((activity, rowIndex) => {
      const nodes = Array.isArray(activity.flow_nodes) ? activity.flow_nodes : [];
      if (nodes.length === 0) return;
      const firstN = nodes[0];
      const lastN = nodes[nodes.length - 1];

      if (rowIndex > 0) {
        const prevAct = activities[rowIndex - 1];
        const prevNodes = Array.isArray(prevAct?.flow_nodes) ? prevAct.flow_nodes : [];
        if (prevNodes.length > 0) {
          const prevLast = prevNodes[prevNodes.length - 1];
          const hasExplicitYes = prevLast.type === 'decision' && hasNumericTarget(prevLast.yes_target);
          if (!hasExplicitYes) {
            acquireSlot(rowIndex - 1, prevLast.executor_key, 'bottom');
            acquireSlot(rowIndex, firstN.executor_key, 'top');
          }
        }
      }
    });

    const pickEntrySide = (fromIdx, toIdx, toNext, rowHasDec) => {
      if (!toNext) return 'top';
      if (fromIdx === toIdx) return 'top';
      const dist = Math.abs(fromIdx - toIdx);
      if (dist <= 1 && !rowHasDec) return 'top';
      return (toIdx < fromIdx) ? 'right' : 'left';
    };

    const buildSideRule = (
      fromRoleIdx, toRoleIdx, fromType, toType,
      sameRow, sameActivityHasPD, processColsThisRow = [], decisionColsThisRow = [],
      isNoBranch = false, partnerInfo = null,
      toNextActivity = false,
    ) => {
      if (toType === 'decision') {
        return [toRoleIdx > fromRoleIdx ? 'right' : (toRoleIdx < fromRoleIdx ? 'left' : 'bottom'), 'top'];
      }
      if (sameActivityHasPD && fromType === 'decision' && partnerInfo !== null) {
        const processLeft = partnerInfo.processLeftOfDecision === true;
        const inSameRowProcesses = processColsThisRow.includes(toRoleIdx);
        if (inSameRowProcesses && sameRow) {
          if (processLeft) {
            return [isNoBranch ? 'bottom' : 'right',
                    isNoBranch ? 'bottom' : (toRoleIdx > fromRoleIdx ? 'left' : 'right')];
          }
          return [isNoBranch ? 'bottom' : 'left',
                  isNoBranch ? 'bottom' : (toRoleIdx > fromRoleIdx ? 'left' : 'right')];
        }
        if (!inSameRowProcesses && isNoBranch && !sameRow) {
          return [processLeft ? 'right' : 'left', 'top'];
        }
      }
      if (fromType === 'decision') {
        const exit = toRoleIdx > fromRoleIdx ? 'right' : (toRoleIdx < fromRoleIdx ? 'left' : 'bottom');
        const entry = (exit === 'right') ? 'left' : (exit === 'left') ? 'right' : 'top';
        const res = [exit, entry];
        if (toNextActivity) res[1] = 'top';
        return res;
      }
      if (toRoleIdx > fromRoleIdx) {
        const res = ['right', 'left'];
        if (toNextActivity) res[1] = 'top';
        return res;
      }
      if (toRoleIdx < fromRoleIdx) {
        const res = ['left', 'right'];
        if (toNextActivity) res[1] = 'top';
        return res;
      }
      const res = ['bottom', 'top'];
      if (toNextActivity) res[1] = 'top';
      return res;
    };

    const findDecisionPartnerNode = (nodes, decisionExecKey) => {
      const processNodes = nodes.filter((n) => ['process','start','end'].includes(n.type));
      const decisionNode = nodes.find((n) => n.executor_key === decisionExecKey);
      if (!decisionNode) return null;
      const decisionCol = getRoleIndex(decisionExecKey);
      let nearestProcess = null;
      let nearestDist = null;
      let nearestCol = null;
      for (const p of processNodes) {
        const pc = getRoleIndex(p.executor_key);
        const d = Math.abs(pc - decisionCol);
        if (nearestDist === null || d < nearestDist) {
          nearestDist = d;
          nearestProcess = p;
          nearestCol = pc;
        }
      }
      if (nearestProcess === null) return null;
      return {
        process: nearestProcess,
        processExecKey: String(nearestProcess.executor_key),
        processCol: nearestCol,
        processLeftOfDecision: nearestCol < decisionCol,
        decisionCol,
      };
    };

    activities.forEach((activity, rowIndex) => {
      const nodes = Array.isArray(activity.flow_nodes) ? activity.flow_nodes : [];

      const hasProcessInThisRow = nodes.some((n) => ['process','start','end'].includes(n.type));
      const hasDecisionInThisRow = nodes.some((n) => n.type === 'decision');
      const sameActivityHasPD = hasProcessInThisRow && hasDecisionInThisRow;
      const processColsThisRow = nodes
        .filter((n) => ['process','start','end'].includes(n.type))
        .map((n) => getRoleIndex(n.executor_key));
      const decisionColsThisRow = nodes
        .filter((n) => n.type === 'decision')
        .map((n) => getRoleIndex(n.executor_key));

      for (let nodeIndex = 0; nodeIndex < nodes.length; nodeIndex += 1) {
        const currentNode = nodes[nodeIndex];
        const isDecision = currentNode.type === 'decision';
        const hasExplicitYesTarget = isDecision && hasNumericTarget(currentNode.yes_target);
        const hasExplicitNoTarget = isDecision && hasNumericTarget(currentNode.no_target);

        if (isDecision && hasExplicitYesTarget) {
          const yTargetIdx = Number(currentNode.yes_target) - 1;
          const yExec = String(currentNode.yes_target_executor_key || '');
          const yTargetMeta = firstTargetMeta(yTargetIdx, yExec);
          if (yTargetMeta) {
            const toKey = `${yTargetIdx}-${yTargetMeta.node.executor_key}`;
            if (cellCoordinates[toKey]) {
              const toRoleIdx = getRoleIndex(yTargetMeta.node.executor_key);
              const fromRoleIdx = getRoleIndex(currentNode.executor_key);
              const sameRow = yTargetIdx === rowIndex;
              const toType = yTargetMeta.node.type;
              const fromType = currentNode.type;

              const tIdx = hasExplicitNoTarget ? (Number(currentNode.no_target) - 1) : null;
              const tExecKey = hasExplicitNoTarget ? String(currentNode.no_target_executor_key || '') : null;
              const tIsSameActivity = (tIdx !== null && tIdx === rowIndex);
              const tToPrior = (tIdx !== null && !tIsSameActivity && tIdx < rowIndex);
              let partner = null;
              if (sameActivityHasPD) partner = findDecisionPartnerNode(nodes, currentNode.executor_key);
              let tBackToPartnerProcess = false;
              if (partner && tIsSameActivity && tExecKey === partner.processExecKey) {
                tBackToPartnerProcess = true;
              }

              let [yExit, yEntry] = buildSideRule(
                fromRoleIdx, toRoleIdx, fromType, toType,
                sameRow, sameActivityHasPD, processColsThisRow, decisionColsThisRow,
                false, partner
              );
              let [tExit, tEntry] = [null, null];
              if (hasExplicitNoTarget && tIdx !== null) {
                const tMeta = firstTargetMeta(tIdx, tExecKey);
                if (tMeta) {
                  const tToRoleIdx = getRoleIndex(tMeta.node.executor_key);
                  const tFromRoleIdx = fromRoleIdx;
                  const tSameRow = tIdx === rowIndex;
                  const tToType = tMeta.node.type;
                  [tExit, tEntry] = buildSideRule(
                    tFromRoleIdx, tToRoleIdx, fromType, tToType,
                    tSameRow, sameActivityHasPD, processColsThisRow, decisionColsThisRow,
                    true, partner
                  );
                }
              }

              if (sameActivityHasPD && partner) {
                if (tBackToPartnerProcess) {
                  // Point 2 / 4: T BALIK ke Process (partner) → T dari BOTTOM decision
                  // Y dari KANAN (Process LEFT) atau KIRI (Process RIGHT)
                  if (partner.processLeftOfDecision) {
                    yExit = 'right';
                  } else {
                    yExit = 'left';
                  }
                  tExit = 'bottom';
                  tEntry = 'bottom';
                } else if (tToPrior) {
                  // Point 3 / 5: T ke KEGIATAN SEBELUMNYA → T dari KANAN (P-left) atau KIRI (P-right)
                  // Y dari BOTTOM ke kegiatan berikutnya
                  if (partner.processLeftOfDecision) {
                    tExit = 'right';
                  } else {
                    tExit = 'left';
                  }
                  tEntry = 'top';
                  yExit = 'bottom';
                  yEntry = 'top';
                } else {
                  // T ke kegiatan BERIKUTNYA atau target lain → default pola simetris
                  if (partner.processLeftOfDecision) {
                    tExit = 'left';
                  } else {
                    tExit = 'right';
                  }
                  tEntry = 'top';
                  yExit = 'bottom';
                  yEntry = 'top';
                }

                // ATURAN UTAMA: Prioritaskan TOP untuk row di bawah, FALLBACK ke SIDE entry (kanan/kiri) jika berpotensi tabrakan
                // (jarak kolom > 1 ATAU row ini punya Decision node yang pakai horizontal lane)
                const rowHasDec = decisionColsThisRow.length > 0;
                if (yTargetIdx > rowIndex) {
                  yEntry = pickEntrySide(fromRoleIdx, toRoleIdx, true, rowHasDec);
                }
                if (hasExplicitNoTarget && tIdx !== null && tIdx > rowIndex && tEntry !== null) {
                  const tToRoleIdx = firstTargetMeta(tIdx, tExecKey) ? getRoleIndex(firstTargetMeta(tIdx, tExecKey).node.executor_key) : toRoleIdx;
                  tEntry = pickEntrySide(fromRoleIdx, tToRoleIdx, true, rowHasDec);
                }
                // Same-row Y ke Process: gunakan LEFT/RIGHT entry (bukan top/bottom kecuali partner-back)
                if (sameRow && toType !== 'decision') {
                  if (!(tBackToPartnerProcess && false)) {
                    yEntry = (toRoleIdx > fromRoleIdx) ? 'left' : 'right';
                  }
                }
              } else {
                // BUKAN same-activity PD-row
                const rowHasDec2 = decisionColsThisRow.length > 0;
                if (yTargetIdx > rowIndex) {
                  yEntry = pickEntrySide(fromRoleIdx, toRoleIdx, true, rowHasDec2);
                }
                if (hasExplicitNoTarget && tIdx !== null && tIdx > rowIndex && tEntry !== null) {
                  const tToRoleIdx2 = firstTargetMeta(tIdx, tExecKey) ? getRoleIndex(firstTargetMeta(tIdx, tExecKey).node.executor_key) : toRoleIdx;
                  tEntry = pickEntrySide(fromRoleIdx, tToRoleIdx2, true, rowHasDec2);
                }
                if (yTargetIdx === rowIndex && toType !== 'decision') {
                  yEntry = (toRoleIdx > fromRoleIdx) ? 'left' : 'right';
                }
              }

              drawConnectorV2(rowIndex, currentNode.executor_key, yTargetIdx, yTargetMeta.node.executor_key, yExit, yEntry, 'Y');
            }
          }
        }

        if (isDecision && hasExplicitNoTarget) {
          const tTargetIdx = Number(currentNode.no_target) - 1;
          const tExec = String(currentNode.no_target_executor_key || '');
          const tTargetMeta = firstTargetMeta(tTargetIdx, tExec);
          if (tTargetMeta) {
            const toKey = `${tTargetIdx}-${tTargetMeta.node.executor_key}`;
            if (cellCoordinates[toKey]) {
              const toRoleIdx = getRoleIndex(tTargetMeta.node.executor_key);
              const fromRoleIdx = getRoleIndex(currentNode.executor_key);
              const sameRow = tTargetIdx === rowIndex;
              const toType = tTargetMeta.node.type;
              const fromType = currentNode.type;

              let partner = null;
              if (sameActivityHasPD) partner = findDecisionPartnerNode(nodes, currentNode.executor_key);
              let tBackToPartnerProcess = false;
              if (partner && sameRow && tExec === partner.processExecKey) {
                tBackToPartnerProcess = true;
              }

              let [tExit, tEntry] = buildSideRule(
                fromRoleIdx, toRoleIdx, fromType, toType,
                sameRow, sameActivityHasPD, processColsThisRow, decisionColsThisRow,
                true, partner
              );

              const rowHasDecT = decisionColsThisRow.length > 0;
              if (sameActivityHasPD && partner) {
                if (tBackToPartnerProcess) {
                  // POINT 2/4: T BALIK ke Process → T dari BOTTOM decision, entry di BOTTOM Process
                  tExit = 'bottom';
                  tEntry = 'bottom';
                } else if (!sameRow && tTargetIdx < rowIndex) {
                  // POINT 3/5: T ke LUAR KEGIATAN SEBELUMNYA
                  tExit = partner.processLeftOfDecision ? 'right' : 'left';
                  tEntry = 'top';
                } else if (!sameRow && tTargetIdx > rowIndex) {
                  // T ke row BAWAH: coba TOP dulu, fallback ke SIDE jika berpotensi tabrakan
                  tExit = partner.processLeftOfDecision ? 'left' : 'right';
                  tEntry = pickEntrySide(fromRoleIdx, toRoleIdx, true, rowHasDecT);
                } else if (sameRow && toType !== 'decision') {
                  // T ke Process DI KEGIATAN YANG SAMA (bukan partner-back): entry LEFT/RIGHT
                  tEntry = (toRoleIdx > fromRoleIdx) ? 'left' : 'right';
                }
              } else if (!sameActivityHasPD) {
                const track = ensureSlotTracker(rowIndex);
                const fromCell = track[currentNode.executor_key];
                if (fromCell && fromCell[tExit] && fromCell[tExit].count > 0) {
                  const alts = ['right', 'left', 'bottom', 'top'];
                  for (const alt of alts) {
                    if (!fromCell[alt] || fromCell[alt].count === 0) { tExit = alt; break; }
                  }
                }
                if (tTargetIdx > rowIndex) {
                  tEntry = pickEntrySide(fromRoleIdx, toRoleIdx, true, rowHasDecT);
                } else if (sameRow && toType !== 'decision') {
                  tEntry = (toRoleIdx > fromRoleIdx) ? 'left' : 'right';
                } else if (tExit === 'top' || tExit === 'bottom') {
                  tEntry = 'top';
                } else {
                  tEntry = tExit === 'right' ? 'left' : 'right';
                }
              }

              // Fallback tabrakan final untuk cross-row: gunakan pickEntrySide
              if (!sameRow && tTargetIdx > rowIndex) {
                tEntry = pickEntrySide(fromRoleIdx, toRoleIdx, true, rowHasDecT);
              }

              drawConnectorV2(rowIndex, currentNode.executor_key, tTargetIdx, tTargetMeta.node.executor_key, tExit, tEntry, 'T');
            }
          }
        }

        if (nodeIndex < nodes.length - 1) {
          const nextNode = nodes[nodeIndex + 1];
          if (currentNode.executor_key === nextNode.executor_key) continue;
          if (isDecision && hasExplicitYesTarget) continue;

          const fromKeyC = `${rowIndex}-${currentNode.executor_key}`;
          const toKeyC = `${rowIndex}-${nextNode.executor_key}`;
          const fromC = cellCoordinates[fromKeyC];
          const toC = cellCoordinates[toKeyC];
          if (!fromC || !toC) continue;
          if (fromC.page !== toC.page) continue;

          const toRoleIdx = getRoleIndex(nextNode.executor_key);
          const fromRoleIdx = getRoleIndex(currentNode.executor_key);
          const nextType = nextNode.type;
          const curType = currentNode.type;

          let [exitSide, entrySide] = buildSideRule(
            fromRoleIdx, toRoleIdx, curType, nextType,
            true, sameActivityHasPD, processColsThisRow, decisionColsThisRow,
            false, null
          );

          if (nextType !== 'decision' && sameActivityHasPD) {
            entrySide = (toRoleIdx > fromRoleIdx) ? 'left' : 'right';
          }

          const label = curType === 'decision' ? 'Y' : null;
          drawConnectorV2(rowIndex, currentNode.executor_key, rowIndex, nextNode.executor_key, exitSide, entrySide, label);
        }
      }

      if (rowIndex < activities.length - 1) {
        const nodes = Array.isArray(activity.flow_nodes) ? activity.flow_nodes : [];
        const nextActivity = activities[rowIndex + 1];
        const nextNodes = Array.isArray(nextActivity.flow_nodes) ? nextActivity.flow_nodes : [];
        if (nodes.length === 0 || nextNodes.length === 0) return;

        const lastNode = nodes[nodes.length - 1];
        const firstNextNode = nextNodes[0];
        const fromKeyC = `${rowIndex}-${lastNode.executor_key}`;
        const toKeyC = `${rowIndex + 1}-${firstNextNode.executor_key}`;
        const fromC = cellCoordinates[fromKeyC];
        const toC = cellCoordinates[toKeyC];
        if (!fromC || !toC) return;

        const hasExplicitYesTarget = lastNode.type === 'decision' && hasNumericTarget(lastNode.yes_target);
        if (hasExplicitYesTarget) return;
        if (fromC.page !== toC.page) return;

        const fx = fromC.x + (fromC.w / 2);
        const fy = fromC.y + (fromC.h / 2);
        const tx = toC.x + (toC.w / 2);
        const ty = toC.y + (toC.h / 2);
        const fromOffsets = shapeEdgeOffsets(lastNode.type);
        const toOffsets = shapeEdgeOffsets(firstNextNode.type);
        const startYEdge = fy + fromOffsets.bottom;
        const endYEdge = ty - toOffsets.top;
        const rowBottomY = fromC.y + fromC.h - 2.5;
        const sourceRoleIdx = getRoleIndex(lastNode.executor_key);
        const targetRoleIdx = getRoleIndex(firstNextNode.executor_key);

        doc.setPage(fromC.page);
        setLineStyle();

        const distDefaultConn = Math.abs(sourceRoleIdx - targetRoleIdx);
        const rowHasDecNow = decisionColsThisRow.length > 0;
        const useSideEntryDefault = (sourceRoleIdx !== targetRoleIdx) && (distDefaultConn > 1 || rowHasDecNow);

        if (lastNode.type === 'decision') {
          const exitSideDec = targetRoleIdx >= sourceRoleIdx ? 'right' : 'left';
          const labelXOffset = exitSideDec === 'right' ? 2.8 : -2.8;
          const lx = fx + labelXOffset;
          const ly = startYEdge + 1.8;
          drawBranchLabel('Y', lx, ly, 'exact');
        }

        if (!useSideEntryDefault) {
          if (lastNode.executor_key === firstNextNode.executor_key) {
            const preShapeY = endYEdge - 2.4;
            doc.line(fx, startYEdge, fx, preShapeY);
            doc.line(fx, preShapeY, fx, endYEdge + ARROW_PENETRATE_MM);
            drawArrow(fx, endYEdge + ARROW_PENETRATE_MM, 'down');
          } else {
            doc.line(fx, startYEdge, fx, rowBottomY);
            if (Math.abs(tx - fx) > 0.1) {
              doc.line(fx, rowBottomY, tx, rowBottomY);
            }
            const preShapeY = endYEdge - 2.4;
            doc.line(tx, rowBottomY, tx, preShapeY);
            doc.line(tx, preShapeY, tx, endYEdge + ARROW_PENETRATE_MM);
            drawArrow(tx, endYEdge + ARROW_PENETRATE_MM, 'down');
          }
        } else {
          const defEntrySide = pickEntrySide(sourceRoleIdx, targetRoleIdx, true, rowHasDecNow);
          drawConnectorV2(
            rowIndex, lastNode.executor_key,
            rowIndex + 1, firstNextNode.executor_key,
            'bottom', defEntrySide, null
          );
        }
      }
    });
  };

  const drawCrossPageFlow = () => {
    activities.forEach((activity, rowIndex) => {
      if (rowIndex >= activities.length - 1) return;

      const nodes = Array.isArray(activity.flow_nodes) ? activity.flow_nodes : [];
      const nextActivity = activities[rowIndex + 1];
      const nextNodes = Array.isArray(nextActivity.flow_nodes) ? nextActivity.flow_nodes : [];
      if (nodes.length === 0 || nextNodes.length === 0) return;

      const lastNode = nodes[nodes.length - 1];
      const firstNextNode = nextNodes[0];
      const fromKey = `${rowIndex}-${lastNode.executor_key}`;
      const toKey = `${rowIndex + 1}-${firstNextNode.executor_key}`;
      const from = cellCoordinates[fromKey];
      const to = cellCoordinates[toKey];
      if (!from || !to) return;

      if (from.page === to.page) return;

      const hasExplicitYesTarget = lastNode.type === 'decision' && hasNumericTarget(lastNode.yes_target);
      if (hasExplicitYesTarget) return;

      acquireSlot(rowIndex, lastNode.executor_key, 'bottom');
      acquireSlot(rowIndex + 1, firstNextNode.executor_key, 'top');

      const fx = from.x + (from.w / 2);
      const fy = from.y + (from.h / 2);
      const tx = to.x + (to.w / 2);
      const ty = to.y + (to.h / 2);
      const fromOffsets = shapeEdgeOffsets(lastNode.type);
      const toOffsets = shapeEdgeOffsets(firstNextNode.type);
      const startYEdge = fy + fromOffsets.bottom;
      const endYEdge = ty - toOffsets.top;
      const fromCellBottom = from.y + from.h;
      const toCellTop = to.y;
      const sourceRoleIdx = getRoleIndex(lastNode.executor_key);
      const targetRoleIdx = getRoleIndex(firstNextNode.executor_key);

      doc.setPage(from.page);
      setLineStyle();
      if (lastNode.type === 'decision') {
        const sourceRoleIdx2 = getRoleIndex(lastNode.executor_key);
        const targetRoleIdx2 = getRoleIndex(firstNextNode.executor_key);
        const exitSide2 = targetRoleIdx2 >= sourceRoleIdx2 ? 'right' : 'left';
        const labelXOffset2 = exitSide2 === 'right' ? 2.8 : -2.8;
        const lx = fx + labelXOffset2;
        const ly = startYEdge + 1.8;
        drawBranchLabel('Y', lx, ly, 'exact');
      }

      if (lastNode.executor_key === firstNextNode.executor_key) {
        doc.line(fx, startYEdge, fx, fromCellBottom);
        doc.setPage(to.page);
        setLineStyle();
        const preShapeY = endYEdge - 2.4;
        doc.line(tx, toCellTop, tx, preShapeY);
        doc.line(tx, preShapeY, tx, endYEdge + ARROW_PENETRATE_MM);
        drawArrow(tx, endYEdge + ARROW_PENETRATE_MM, 'down');
      } else {
        const crossLaneBottom = from.y + from.h - 2.5;
        const crossLaneTop = to.y + 2.5;
        doc.line(fx, startYEdge, fx, crossLaneBottom);
        if (Math.abs(tx - fx) > 0.1) {
          doc.line(fx, crossLaneBottom, fx, fromCellBottom);
        } else {
          doc.line(fx, crossLaneBottom, fx, fromCellBottom);
        }

        doc.setPage(to.page);
        setLineStyle();
        const preShapeY = endYEdge - 2.4;
        if (Math.abs(tx - fx) > 0.1) {
          doc.line(tx, toCellTop, tx, crossLaneTop);
          doc.line(tx, crossLaneTop, tx, preShapeY);
        } else {
          doc.line(tx, toCellTop, tx, preShapeY);
        }
        doc.line(tx, preShapeY, tx, endYEdge + ARROW_PENETRATE_MM);
        drawArrow(tx, endYEdge + ARROW_PENETRATE_MM, 'down');
      }
    });

    activities.forEach((activity, rowIndex) => {
      const nodes = Array.isArray(activity.flow_nodes) ? activity.flow_nodes : [];
      for (let nodeIndex = 0; nodeIndex < nodes.length; nodeIndex += 1) {
        const currentNode = nodes[nodeIndex];
        if (currentNode.type !== 'decision') continue;

        const fromKey = `${rowIndex}-${currentNode.executor_key}`;
        const from = cellCoordinates[fromKey];
        if (!from) continue;

        const fromOffsets = shapeEdgeOffsets(currentNode.type);
        const fx = from.x + (from.w / 2);
        const fy = from.y + (from.h / 2);
        const sourceRoleIdx = getRoleIndex(currentNode.executor_key);

        const branchTargets = [
          { label: 'Y', targetIndex: Number(currentNode.yes_target || 0) - 1, targetExecutorKey: currentNode.yes_target_executor_key || '' },
          { label: 'T', targetIndex: Number(currentNode.no_target || 0) - 1, targetExecutorKey: currentNode.no_target_executor_key || '' },
        ];

        branchTargets.forEach((branch) => {
          if (!hasNumericTarget(branch.targetIndex + 1)) return;
          const targetMeta = firstTargetMeta(branch.targetIndex, branch.targetExecutorKey);
          if (!targetMeta) return;
          const to = cellCoordinates[targetMeta.key];
          if (!to) return;
          if (from.page === to.page) return;

          const targetExec = targetMeta.node.executor_key;
          const targetRoleIdx = getRoleIndex(targetExec);
          const sameRow = branch.targetIndex === rowIndex;
          let exitSide = targetRoleIdx > sourceRoleIdx ? 'right' : (targetRoleIdx < sourceRoleIdx ? 'left' : 'bottom');
          const track = ensureSlotTracker(rowIndex);
          const fromCell = track[currentNode.executor_key];
          if (fromCell && fromCell[exitSide] && fromCell[exitSide].count > 0) {
            for (const alt of ['right', 'left', 'bottom', 'top']) {
              if (!fromCell[alt] || fromCell[alt].count === 0) { exitSide = alt; break; }
            }
          }
          acquireSlot(rowIndex, currentNode.executor_key, exitSide);
          acquireSlot(branch.targetIndex, targetExec, 'top');

          const tx = to.x + (to.w / 2);
          const ty = to.y + (to.h / 2);
          const targetNode = targetMeta.node;
          const targetOffsets = shapeEdgeOffsets(targetNode.type);
          const targetTop = ty - targetOffsets.top;
          const fromCellBottom = from.y + from.h;
          const toCellTop = to.y;

          const isVerticalExit = exitSide === 'top' || exitSide === 'bottom';
          const startP = pointOnSide(exitSide, 'mid', fx, fy, fromOffsets);
          const straightStep = 2.2;
          const dirSign = { right: 1, left: -1, top: -1, bottom: 1 };
          let stepX = startP.x;
          let stepY = startP.y;
          if (!isVerticalExit) stepX += dirSign[exitSide] * straightStep;
          else stepY += dirSign[exitSide] * straightStep;
          // SNAKE: stepX/stepY menjauhi border cell from
          const fCellRight = from.x + from.w;
          const fCellLeft  = from.x;
          const fCellBot    = from.y + from.h;
          const fCellTop    = from.y;
          if (!isVerticalExit && exitSide === 'right' && stepX >= (fCellRight - 0.8) && stepX <= (fCellRight + 1.2)) { stepX = fCellRight + 1.8; }
          if (!isVerticalExit && exitSide === 'left'  && stepX <= (fCellLeft  + 0.8) && stepX >= (fCellLeft  - 1.2)) { stepX = fCellLeft  - 1.8; }
          if (isVerticalExit  && exitSide === 'bottom'&& stepY >= (fCellBot   - 0.8) && stepY <= (fCellBot   + 1.2)) { stepY = fCellBot   + 1.8; }
          if (isVerticalExit  && exitSide === 'top'   && stepY <= (fCellTop   + 0.8) && stepY >= (fCellTop   - 1.2)) { stepY = fCellTop   - 1.8; }

          doc.setPage(from.page);
          setLineStyle();
          // Label posisi dekat exit shape, menjauhi border cell dan garis konektor
          let lx = startP.x, ly = startP.y;
          const fCRight = from.x + from.w;
          const fCLeft  = from.x;
          const fCBot   = from.y + from.h;
          if (!isVerticalExit) {
            lx = startP.x + (dirSign[exitSide] * 6.4);
            const maxSafeX = fCRight + 3.6;
            const minSafeX = fCLeft - 3.2;
            if (lx > maxSafeX) lx = maxSafeX;
            if (lx < minSafeX) lx = minSafeX;
            ly = startP.y + 3.5;
          } else {
            lx = startP.x + 2.5;
            ly = startP.y + 1.8;
            const maxSafeY = fCBot - 2.0;
            if (ly > maxSafeY) ly = maxSafeY;
          }
          drawBranchLabel(branch.label, lx, ly, 'exact');

          doc.line(startP.x, startP.y, stepX, stepY);
          const laneY = Math.min(stepY, fromCellBottom - 2);
          doc.line(stepX, stepY, stepX, laneY);
          const crossTargetX = exitSide === 'left' || (exitSide !== 'right' && targetRoleIdx < sourceRoleIdx)
            ? Math.min(stepX, tx + 5)
            : Math.max(stepX, tx - 5);
          if (Math.abs(stepX - crossTargetX) > 0.1) {
            doc.line(stepX, laneY, crossTargetX, laneY);
          }
          doc.line(crossTargetX, laneY, crossTargetX, fromCellBottom);

          doc.setPage(to.page);
          setLineStyle();
          const enterX = Math.abs(tx - crossTargetX) < 0.1 ? tx : tx;
          const preShapeY = targetTop - 3.2;
          doc.line(enterX, toCellTop, enterX, preShapeY);
          doc.line(enterX, preShapeY, enterX, targetTop + ARROW_PENETRATE_MM);
          drawArrow(enterX, targetTop + ARROW_PENETRATE_MM, 'down');
        });
      }
    });
  };

  drawSamePageFlow();
  drawCrossPageFlow();
};

drawPageOne();
drawActivityTableAndFlows();

fs.mkdirSync(path.dirname(outputPath), { recursive: true });
fs.writeFileSync(outputPath, Buffer.from(doc.output('arraybuffer')));
