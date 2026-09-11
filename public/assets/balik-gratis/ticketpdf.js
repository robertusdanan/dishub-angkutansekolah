
function bgLoadImage(src) {
  return new Promise((resolve, reject) => {
    const img = new Image();
    img.onload = () => resolve(img);
    img.onerror = () => reject(new Error(`Gagal memuat image: ${src}`));
    img.src = src;
  });
}

/**
 * Generate tiket PDF.
 * @param {object} tiketData { nomor_tiket, nama, no_hp, nik, no_kk, kategori, tahun, qrCanvas }
 * @returns {Promise<Blob>}
 */
async function generateTiketPDF(tiketData) {
  const { jsPDF } = window.jspdf;
  const pdf = new jsPDF({ orientation: "landscape", unit: "mm", format: "a6" });

  const W = pdf.internal.pageSize.getWidth();
  const H = pdf.internal.pageSize.getHeight();

  const COLOR_BG = [10, 31, 68];
  const COLOR_PRIMARY = [26, 86, 219];
  const COLOR_PRIMARY_DARK = [10, 31, 68];
  const COLOR_TEXT = [15, 23, 42];
  const COLOR_MUTED = [100, 116, 139];
  const COLOR_LINE = [226, 232, 240];
  const COLOR_CARD = [255, 255, 255];
  const COLOR_ACCENT = [235, 243, 255];
  const COLOR_GOLD = [255, 244, 224];
  const COLOR_GOLD_TEXT = [124, 71, 3];

  pdf.setFillColor(...COLOR_BG);
  pdf.rect(0, 0, W, H, "F");
  pdf.setFillColor(13, 42, 92);
  pdf.circle(W - 15, 12, 22, "F");
  pdf.setFillColor(20, 55, 110);
  pdf.circle(-8, H - 12, 18, "F");

  const cardX = 7, cardY = 7, cardW = W - 14, cardH = H - 14;
  pdf.setFillColor(...COLOR_CARD);
  pdf.roundedRect(cardX, cardY, cardW, cardH, 3, 3, "F");
  pdf.setFillColor(...COLOR_PRIMARY);
  pdf.rect(cardX, cardY, cardW, 2, "F");

  const paddingX = cardX + 10;
  let cursorY = cardY + 12;

  pdf.setFont("helvetica", "bold");
  pdf.setFontSize(12);
  pdf.setTextColor(...COLOR_TEXT);
  pdf.text("TIKET BALIK GRATIS", paddingX, cursorY);

  cursorY += 5;
  pdf.setFont("helvetica", "normal");
  pdf.setFontSize(7);
  pdf.setTextColor(...COLOR_MUTED);
  pdf.text(String(tiketData.tahun || ""), paddingX, cursorY);

  cursorY += 7;
  pdf.setFillColor(...COLOR_ACCENT);
  pdf.roundedRect(paddingX, cursorY, cardW - 20, 8, 2, 2, "F");
  pdf.setFont("helvetica", "bold");
  pdf.setFontSize(7);
  pdf.setTextColor(...COLOR_PRIMARY_DARK);
  pdf.text("Tulungagung", paddingX + 3, cursorY + 5.5);
  pdf.setFont("helvetica", "normal");
  pdf.setFontSize(6);
  pdf.text("Surabaya", cardX + cardW - 13, cursorY + 5.5, { align: "right" });

  cursorY += 12;
  const ticketBoxW = cardW - 50;
  pdf.setFillColor(...COLOR_GOLD);
  pdf.roundedRect(paddingX, cursorY, ticketBoxW, 9, 2, 2, "F");
  pdf.setFont("helvetica", "bold");
  pdf.setFontSize(6);
  pdf.setTextColor(...COLOR_GOLD_TEXT);
  pdf.text("NO. TIKET", paddingX + 3, cursorY + 3.5);
  pdf.setFontSize(9);
  pdf.text(tiketData.nomor_tiket || "", paddingX + 3, cursorY + 7.5);

  if (tiketData.qrCanvas) {
    const qrSize = 27;
    const qrX = cardX + cardW - qrSize - 7;
    const qrY = cursorY + 6;
    pdf.setFillColor(248, 250, 252);
    pdf.roundedRect(qrX - 2, qrY - 2, qrSize + 4, qrSize + 4, 2, 2, "F");
    pdf.setDrawColor(203, 213, 225);
    pdf.setLineWidth(0.2);
    pdf.roundedRect(qrX - 2, qrY - 2, qrSize + 4, qrSize + 4, 2, 2, "S");
    pdf.addImage(tiketData.qrCanvas.toDataURL("image/png"), "PNG", qrX, qrY, qrSize, qrSize);
  }

  cursorY += 15;
  pdf.setFont("helvetica", "bold");
  pdf.setFontSize(6);
  pdf.setTextColor(...COLOR_MUTED);
  pdf.text("DETAIL PENUMPANG", paddingX, cursorY);
  cursorY += 6;

  const rowGap = 5.5;
  const labelWidth = 20;
  const dataRows = [
    { label: "Nama", value: tiketData.nama || "–" },
    { label: "No HP", value: tiketData.no_hp || "–" },
    { label: "NIK", value: tiketData.nik || "–" },
    { label: "No KK", value: tiketData.no_kk || "–" },
    { label: "Kategori", value: tiketData.kategori || "–" },
  ];

  dataRows.forEach((row, index) => {
    pdf.setFont("helvetica", "normal");
    pdf.setFontSize(6.5);
    pdf.setTextColor(...COLOR_MUTED);
    pdf.text(row.label, paddingX, cursorY);
    pdf.setFont("helvetica", "bold");
    pdf.setFontSize(7);
    pdf.setTextColor(...COLOR_TEXT);
    let displayValue = String(row.value);
    const maxWidth = cardW - 60;
    if (pdf.getTextWidth(displayValue) > maxWidth) {
      while (pdf.getTextWidth(displayValue + "...") > maxWidth && displayValue.length > 0) {
        displayValue = displayValue.slice(0, -1);
      }
      displayValue += "...";
    }
    pdf.text(displayValue, paddingX + labelWidth, cursorY);
    if (index < dataRows.length - 1) {
      pdf.setDrawColor(...COLOR_LINE);
      pdf.setLineWidth(0.1);
      pdf.line(paddingX, cursorY + 2, cardX + cardW - 40, cursorY + 2);
    }
    cursorY += rowGap;
  });

  const footerY = cardY + cardH - 10;
  pdf.setDrawColor(...COLOR_LINE);
  pdf.setLineWidth(0.15);
  pdf.line(paddingX, footerY, cardX + cardW - 10, footerY);
  pdf.setFont("helvetica", "normal");
  pdf.setFontSize(5.5);
  pdf.setTextColor(...COLOR_MUTED);
  pdf.text("Dinas Perhubungan Kabupaten Tulungagung", W / 2, footerY + 4, { align: "center" });
  pdf.setFontSize(5);
  pdf.text("Tunjukkan tiket ini saat keberangkatan", W / 2, footerY + 7, { align: "center" });

  const logoSize = 8;
  const logoX = cardX + cardW - logoSize - 8;
  const logoY = footerY + 1;
  let logoDishub = null;
  try {
    logoDishub = await bgLoadImage("/assets/dishub.png");
  } catch { /* logo opsional, PDF tetap valid tanpa logo */ }
  if (logoDishub) {
    pdf.addImage(logoDishub, "PNG", logoX, logoY, logoSize, logoSize);
  }

  return pdf.output("blob");
}

/** Generate lalu langsung unduh ke perangkat. */
async function downloadTiketPDF(tiketData) {
  const pdfBlob = await generateTiketPDF(tiketData);
  const url = URL.createObjectURL(pdfBlob);
  const a = document.createElement("a");
  a.href = url;
  a.download = `Tiket-Balik-Gratis-${tiketData.nomor_tiket}.pdf`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}
