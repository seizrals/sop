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
const shapeFill = [122, 122, 122];
const shapeStroke = [65, 65, 65];

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

const drawPageOne = () => {
  const startX = 10;
  const startY = 10;
  const outerW = pageWidth - 20;
  const midX = startX + (outerW / 2);
  const rightX = pageWidth - 10;
  const rowH = 7;

  doc.setDrawColor(0);
  doc.setLineWidth(0.1);

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
      doc.setLineWidth(0.2);
      doc.line(valueCenterX - (textWidth / 2), y + 34, valueCenterX + (textWidth / 2), y + 34);

      setRegular();
      doc.text(`NIP. ${payload.approval_nip || '-'}`, valueCenterX, y + 38, { align: 'center' });
      doc.setLineWidth(0.1);
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
      lineColor: 0,
      lineWidth: 0.1,
      valign: 'top',
      overflow: 'linebreak',
      textColor: 0,
    },
    columnStyles: {
      0: { cellWidth: (pageWidth - 20) / 2 },
      1: { cellWidth: (pageWidth - 20) / 2 },
    },
    tableWidth: pageWidth - 20,
  });
};

const drawDiamond = (x, y, size, label = '') => {
  const vSize = size * 0.8;
  doc.setDrawColor(...shapeStroke);
  doc.setFillColor(...shapeFill);
  doc.lines(
    [[size, -vSize], [size, vSize], [-size, vSize], [-size, -vSize]],
    x - size,
    y,
    [1, 1],
    'FD',
    true
  );

  if (label) {
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(6);
    const textLines = doc.splitTextToSize(label, size * 1.55);
    const lineHeight = 2.5;
    const totalHeight = textLines.length * lineHeight;
    const startY = y - (totalHeight / 2) + (lineHeight / 2);
    textLines.forEach((line, index) => {
      doc.text(line, x, startY + (index * lineHeight), { align: 'center', baseline: 'middle' });
    });
    doc.setTextColor(0, 0, 0);
  }
};

const drawPageTwo = () => {
  doc.addPage();

  const executors = Array.isArray(payload.executors) && payload.executors.length > 0
    ? payload.executors
    : [{ key: 'executor', label: 'Pelaksana' }];
  const activities = Array.isArray(payload.activities) ? payload.activities : [];
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

  activities.forEach((activity, index) => {
    const waktu = String(activity.duration || '').trim();
    if (waktu === '') {
      waktuSpans[index] = 1;
      currentWaktu = '';
      spanStartIdx = -1;
      return;
    }

    if (waktu === currentWaktu) {
      waktuSpans[spanStartIdx] += 1;
      waktuSpans[index] = 0;
      return;
    }

    currentWaktu = waktu;
    spanStartIdx = index;
    waktuSpans[index] = 1;
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
        styles: { halign: 'center', valign: 'middle' },
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
  const qualityWidth = 28;
  const durationWidth = 18;
  const outputWidth = 26;
  const notesWidth = 28;
  const remainingWidth = availableWidth - noWidth - qualityWidth - durationWidth - outputWidth - notesWidth;
  const minKegiatanWidth = executors.length >= 5 ? 44 : 58;
  let executorWidth = Math.min(24, Math.max(14, (remainingWidth * 0.42) / Math.max(executors.length, 1)));
  let kegiatanWidth = remainingWidth - (executorWidth * executors.length);

  if (kegiatanWidth < minKegiatanWidth) {
    executorWidth = Math.max(12, (remainingWidth - minKegiatanWidth) / Math.max(executors.length, 1));
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
      lineColor: 0,
      lineWidth: 0.1,
      valign: 'middle',
      minCellHeight: 14,
      textColor: 0,
    },
    headStyles: {
      font: boldFont,
      fontStyle: 'bold',
      fillColor: 240,
      textColor: 0,
      halign: 'center',
      fontSize: 8,
    },
    columnStyles,
    margin: { left: 10, right: 10 },
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
        const size = 6;
        doc.setDrawColor(...shapeStroke);
        doc.setFillColor(...shapeFill);

        if (node.type === 'start') {
          doc.roundedRect(cx - size, cy - (size / 2), size * 2, size, 2, 2, 'FD');
          doc.setTextColor(255, 255, 255);
          doc.text('Start', cx, cy, { align: 'center', baseline: 'middle' });
          doc.setTextColor(0, 0, 0);
        } else if (node.type === 'end') {
          doc.roundedRect(cx - size, cy - (size / 2), size * 2, size, 2, 2, 'FD');
          doc.setTextColor(255, 255, 255);
          doc.text('End', cx, cy, { align: 'center', baseline: 'middle' });
          doc.setTextColor(0, 0, 0);
        } else if (node.type === 'process') {
          doc.rect(cx - size, cy - (size / 2), size * 2, size, 'FD');
        } else if (node.type === 'decision') {
          drawDiamond(cx, cy, size * 1.2, node.label || '');
        }
      }
    },
  });

  doc.setDrawColor(0);
  doc.setLineWidth(0.2);

  const drawArrow = (x, y, direction) => {
    const s = 1.2;
    doc.setFillColor(0, 0, 0);
    if (direction === 'down') doc.triangle(x, y, x - (s / 2), y - s, x + (s / 2), y - s, 'F');
    if (direction === 'up') doc.triangle(x, y, x - (s / 2), y + s, x + (s / 2), y + s, 'F');
    if (direction === 'right') doc.triangle(x, y, x - s, y - (s / 2), x - s, y + (s / 2), 'F');
    if (direction === 'left') doc.triangle(x, y, x + s, y - (s / 2), x + s, y + (s / 2), 'F');
  };

  const drawBranchLabel = (label, x, y, side) => {
    doc.setFontSize(9);
    doc.setFont(boldFont, 'bold');
    const offsetX = side === 'right' ? 4.2 : -5.2;
    const offsetY = -2.2;
    doc.setTextColor(0, 90, 170);
    doc.text(label, x + offsetX, y + offsetY, { align: 'center', baseline: 'middle' });
    doc.setTextColor(0, 0, 0);
    doc.setFont(regularFont, 'normal');
  };

  activities.forEach((activity, rowIndex) => {
    const nodes = Array.isArray(activity.flow_nodes) ? activity.flow_nodes : [];

    for (let nodeIndex = 0; nodeIndex < nodes.length; nodeIndex += 1) {
      const currentNode = nodes[nodeIndex];
      const fromKey = `${rowIndex}-${currentNode.executor_key}`;
      const from = cellCoordinates[fromKey];
      if (!from) continue;

      doc.setPage(from.page);
      const fx = from.x + (from.w / 2);
      const fy = from.y + (from.h / 2);
      const size = 6;
      const diamondScale = 1.2;

      if (currentNode.type === 'decision') {
        const hasExplicitYesTarget = hasNumericTarget(currentNode.yes_target);
        const hasExplicitNoTarget = hasNumericTarget(currentNode.no_target);
        const branchTargets = [
          { label: 'Y', targetIndex: Number(currentNode.yes_target || 0) - 1, side: 'right', targetExecutorKey: currentNode.yes_target_executor_key || '' },
          { label: 'T', targetIndex: Number(currentNode.no_target || 0) - 1, side: 'left', targetExecutorKey: currentNode.no_target_executor_key || '' },
        ];

        branchTargets.forEach((branch) => {
          const targetMeta = firstTargetMeta(branch.targetIndex, branch.targetExecutorKey);
          if (!targetMeta) return;
          const to = cellCoordinates[targetMeta.key];
          if (!to) return;

          doc.setPage(from.page);
          const tx = to.x + (to.w / 2);
          const ty = to.y + (to.h / 2);
          const startX = branch.side === 'right' ? fx + (size * diamondScale) : fx - (size * diamondScale);
          const startY = fy;
          const targetNode = targetMeta.node;
          const targetTop = ty - ((targetNode.type === 'decision') ? (size * diamondScale * 0.8) : (size / 2));

          drawBranchLabel(branch.label, startX, startY - 0.5, branch.side);

          if (from.page === to.page) {
            if (branch.targetIndex === rowIndex && branch.side === 'left') {
              const laneX = Math.min(startX - 3, tx - 8);
              const targetLeftX = tx - ((targetNode.type === 'decision') ? (size * diamondScale) : size);
              const entryY = ty + 2;
              doc.line(startX, startY, laneX, startY);
              doc.line(laneX, startY, laneX, entryY);
              doc.line(laneX, entryY, targetLeftX, entryY);
              drawArrow(targetLeftX, entryY, 'right');
            } else if (branch.targetIndex >= rowIndex) {
              const midY = ty - 8;
              doc.line(startX, startY, startX, midY);
              doc.line(startX, midY, tx, midY);
              doc.line(tx, midY, tx, targetTop);
              drawArrow(tx, targetTop, 'down');
            } else {
              const laneX = Math.min(startX - 3, tx - 8);
              const targetLeftX = tx - ((targetNode.type === 'decision') ? (size * diamondScale) : size);
              const entryY = ty + (branch.label === 'T' ? 2 : 0);
              doc.line(startX, startY, laneX, startY);
              doc.line(laneX, startY, laneX, entryY);
              doc.line(laneX, entryY, targetLeftX, entryY);
              drawArrow(targetLeftX, entryY, 'right');
            }
          }
        });
      }

      if (nodeIndex < nodes.length - 1) {
        const nextNode = nodes[nodeIndex + 1];
        const toKey = `${rowIndex}-${nextNode.executor_key}`;
        const to = cellCoordinates[toKey];
        if (to && from.page === to.page) {
          const hasExplicitYesTarget = currentNode.type === 'decision' && hasNumericTarget(currentNode.yes_target);
          if (hasExplicitYesTarget) {
            continue;
          }

          doc.setPage(from.page);
          const tx = to.x + (to.w / 2);
          const ty = to.y + (to.h / 2);

          let sX = fx + size;
          let eX = tx - size;
          if (currentNode.type === 'decision') sX = fx + (size * diamondScale);
          if (nextNode.type === 'decision') {
            const diamondV = size * diamondScale * 0.8;
            const targetTopY = ty - diamondV;
            const laneY = Math.min(fy, targetTopY) - 6;
            doc.line(sX, fy, sX, laneY);
            doc.line(sX, laneY, tx, laneY);
            doc.line(tx, laneY, tx, targetTopY);
            if (currentNode.type === 'decision') {
              drawBranchLabel('Y', sX, fy, 'right');
            }
            drawArrow(tx, targetTopY, 'down');
          } else {
            const isTargetLastNode = (nodeIndex + 1) === (nodes.length - 1);
            const incomingOffset = (isTargetLastNode && rowIndex < activities.length - 1) ? -2 : 0;
            doc.line(sX, fy + incomingOffset, eX, ty + incomingOffset);
            if (currentNode.type === 'decision') {
              drawBranchLabel('Y', sX, fy + incomingOffset, 'right');
            }
            drawArrow(eX, ty + incomingOffset, 'right');
          }
        }
      }
    }

    if (rowIndex < activities.length - 1) {
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

      const fx = from.x + (from.w / 2);
      const fy = from.y + (from.h / 2);
      const tx = to.x + (to.w / 2);
      const ty = to.y + (to.h / 2);
      const size = 6;
      const diamondScale = 1.2;
      const diamondVScale = diamondScale * 0.8;
      const startYEdge = (lastNode.type === 'decision') ? fy + (size * diamondVScale) : fy + (size / 2);
      const endYEdge = (firstNextNode.type === 'decision') ? ty - (size * diamondVScale) : ty - (size / 2);
      const sourceRoleIdx = getRoleIndex(lastNode.executor_key);
      const targetRoleIdx = getRoleIndex(firstNextNode.executor_key);
      const rowBottomY = from.y + from.h;
      const hasExplicitYesTarget = lastNode.type === 'decision' && hasNumericTarget(lastNode.yes_target);

      if (from.page === to.page) {
        doc.setPage(from.page);
        if (lastNode.type === 'decision' && !hasExplicitYesTarget) {
          drawBranchLabel('Y', fx, startYEdge + 1, 'right');
        }

        if (hasExplicitYesTarget) {
          return;
        } else if (lastNode.executor_key === firstNextNode.executor_key) {
          doc.line(fx, startYEdge, fx, endYEdge);
          drawArrow(fx, endYEdge, 'down');
        } else if (lastNode.type === 'decision') {
          const targetSideX = targetRoleIdx > sourceRoleIdx
            ? tx - ((firstNextNode.type === 'decision') ? (size * diamondScale) : size)
            : tx + ((firstNextNode.type === 'decision') ? (size * diamondScale) : size);
          const arrowDirection = targetRoleIdx > sourceRoleIdx ? 'right' : 'left';
          doc.line(fx, startYEdge, fx, ty);
          doc.line(fx, ty, targetSideX, ty);
          drawArrow(targetSideX, ty, arrowDirection);
        } else if (firstNextNode.type === 'decision') {
          const channelY = rowBottomY - 1 - (sourceRoleIdx * 0.5);
          doc.line(fx, startYEdge, fx, channelY);
          doc.line(fx, channelY, tx, channelY);
          doc.line(tx, channelY, tx, endYEdge);
          drawArrow(tx, endYEdge, 'down');
        } else {
          const sourceSideX = targetRoleIdx > sourceRoleIdx ? fx + size : fx - size;
          doc.line(sourceSideX, fy, tx, fy);
          doc.line(tx, fy, tx, endYEdge);
          drawArrow(tx, endYEdge, 'down');
        }
      }
    }
  });
};

drawPageOne();
drawPageTwo();

fs.mkdirSync(path.dirname(outputPath), { recursive: true });
fs.writeFileSync(outputPath, Buffer.from(doc.output('arraybuffer')));
