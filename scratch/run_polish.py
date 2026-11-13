import docx
import io
import os
import zipfile
from docx.shared import Pt, Inches, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT
from docx.oxml import parse_xml, OxmlElement
from docx.oxml.ns import nsdecls, qn

NAVY = RGBColor(15, 39, 74)        # #0f274a
DARK_TEXT = RGBColor(30, 41, 59)   # #1e293b
MUTED = RGBColor(100, 116, 139)    # #64748b

doc_path = r"c:\laragon\www\cuci-in\docs\Jobsheet_P10_White_Box_Testing_CUCIIN.docx"

print("Loading document...")
with open(doc_path, "rb") as f:
    doc = docx.Document(io.BytesIO(f.read()))

print(f"Loaded successfully. Paragraphs: {len(doc.paragraphs)}, Tables: {len(doc.tables)}")

# 1. Clean whitespace in image tables (T07, T08, T10, T11)
for t_idx in [7, 8, 10, 11]:
    t = doc.tables[t_idx]
    cell = t.rows[0].cells[0]
    for p in cell.paragraphs:
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        p.paragraph_format.space_before = Pt(4)
        p.paragraph_format.space_after = Pt(4)
        for r in p.runs:
            if not r._r.xpath('.//a:blip/@r:embed'):
                r.text = ""

# 2. Re-order Image 2 in Langkah 2: move image before caption 2.2
p32 = doc.paragraphs[32]
p33 = doc.paragraphs[33]
if p33._p.xpath('.//a:blip/@r:embed') and 'Tangkapan Layar 2.2' in p32.text:
    p32._p.addprevious(p33._p)
    p33.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p33.paragraph_format.space_before = Pt(6)
    p33.paragraph_format.space_after = Pt(2)
    print("Re-ordered Image 2 before Caption 2.2")

# 3. Langkah 4 Captions: set caption 4.1 for Image 4 and caption 4.2 for Image 5
p48 = doc.paragraphs[48]
p51 = doc.paragraphs[51]
p48.text = "Tangkapan Layar 4.1: Berkas Pengujian Unit tests/Unit/ContohWhiteBoxTest.php di VS Code"
p51.text = "Tangkapan Layar 4.2: Hasil Eksekusi PHPUnit ContohWhiteBoxTest (Status PASS)"
print("Updated Langkah 4 captions (4.1 and 4.2)")

# 4. Polish all paragraphs
for p in doc.paragraphs:
    txt = p.text.strip()
    imgs = p._p.xpath('.//a:blip/@r:embed')
    if imgs:
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        continue

    if not txt:
        p.paragraph_format.space_before = Pt(0)
        p.paragraph_format.space_after = Pt(2)
        p.paragraph_format.line_spacing = 1.0
        continue

    # Major Headings
    if p.style.name.startswith("Heading 1") or any(txt.startswith(prefix) for prefix in [
        "Identitas ", "Persyaratan ", "Jadwal ", "Tindakan ", "Lembar ", "Jawaban ", "Panduan ", "Rubrik ", "Checklist "
    ]):
        p.paragraph_format.space_before = Pt(14)
        p.paragraph_format.space_after = Pt(6)
        p.paragraph_format.line_spacing = 1.15
        for r in p.runs:
            r.font.name = "Calibri"
            r.font.bold = True
            r.font.size = Pt(14)
            r.font.color.rgb = NAVY

    # Secondary Headings (Langkah 1..6, Anggota 1..5)
    elif p.style.name.startswith("Heading 2") or (txt.startswith("Langkah ") and "Menit" in txt) or txt.startswith("Anggota "):
        p.paragraph_format.space_before = Pt(12)
        p.paragraph_format.space_after = Pt(4)
        p.paragraph_format.line_spacing = 1.15
        for r in p.runs:
            r.font.name = "Calibri"
            r.font.bold = True
            r.font.size = Pt(13)
            r.font.color.rgb = NAVY

    # Section Sub-labels
    elif any(txt.startswith(prefix) for prefix in [
        "Hasil yang dilihat", "Catatan /", "Hasil Uji Manual", "Tabel Matriks", "Perintah Eksekusi", "Daftar Anggota"
    ]):
        p.paragraph_format.space_before = Pt(8)
        p.paragraph_format.space_after = Pt(3)
        p.paragraph_format.line_spacing = 1.15
        for r in p.runs:
            r.font.name = "Calibri"
            r.font.bold = True
            r.font.size = Pt(11)
            r.font.color.rgb = NAVY

    # Captions
    elif txt.startswith("Tangkapan Layar") or txt.startswith("Gambar "):
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        p.paragraph_format.space_before = Pt(3)
        p.paragraph_format.space_after = Pt(8)
        for r in p.runs:
            r.font.name = "Calibri"
            r.font.italic = True
            r.font.size = Pt(9.5)
            r.font.color.rgb = MUTED

    # Bullet lists
    elif p.style.name == "List Bullet" or txt.startswith("•"):
        p.paragraph_format.space_before = Pt(2)
        p.paragraph_format.space_after = Pt(3)
        p.paragraph_format.line_spacing = 1.15
        for r in p.runs:
            r.font.name = "Calibri"
            r.font.size = Pt(11)
            r.font.color.rgb = DARK_TEXT

    # Title / Subtitle
    elif p.style.name in ["Title", "Subtitle"]:
        p.paragraph_format.line_spacing = 1.15
        for r in p.runs:
            r.font.name = "Calibri"

    # Regular body paragraphs
    else:
        p.paragraph_format.space_before = Pt(0)
        p.paragraph_format.space_after = Pt(4)
        p.paragraph_format.line_spacing = 1.15
        for r in p.runs:
            r.font.name = "Calibri"
            if r.font.size is None:
                r.font.size = Pt(11)
            if r.font.color is None or r.font.color.rgb is None:
                r.font.color.rgb = DARK_TEXT

# 5. Polish Tables
cant_split_xml = '<w:cantSplit xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"/>'
tbl_header_xml = '<w:tblHeader xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"/>'
tc_mar_xml = '''<w:tcMar xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
    <w:top w:w="80" w:type="dxa"/>
    <w:bottom w:w="80" w:type="dxa"/>
    <w:left w:w="120" w:type="dxa"/>
    <w:right w:w="120" w:type="dxa"/>
</w:tcMar>'''

for t_idx, t in enumerate(doc.tables):
    t.alignment = WD_TABLE_ALIGNMENT.CENTER
    for row in t.rows:
        trPr = row._tr.get_or_add_trPr()
        trPr.append(parse_xml(cant_split_xml))

    if len(t.rows) > 1:
        trPr0 = t.rows[0]._tr.get_or_add_trPr()
        trPr0.append(parse_xml(tbl_header_xml))

    for row_idx, row in enumerate(t.rows):
        for cell in row.cells:
            tcPr = cell._tc.get_or_add_tcPr()
            tcPr.append(parse_xml(tc_mar_xml))

            for p in cell.paragraphs:
                p.paragraph_format.space_before = Pt(1)
                p.paragraph_format.space_after = Pt(1)
                p.paragraph_format.line_spacing = 1.1
                for r in p.runs:
                    if t_idx in [6, 12]:  # Terminal code blocks
                        r.font.name = "Consolas"
                        r.font.size = Pt(9.5)
                    elif t_idx not in [7, 8, 10, 11]:  # Regular data tables
                        r.font.name = "Calibri"
                        if row_idx == 0:
                            r.font.bold = True
                            r.font.size = Pt(10.5)
                        else:
                            if r.font.size is None or r.font.size > Pt(10.5):
                                r.font.size = Pt(10.0)

# Save directly to the canonical docx
doc.save(doc_path)
print(f"SUCCESS: Canonical document {doc_path} has been tidied and saved!")

# Verify media count
with zipfile.ZipFile(doc_path, "r") as z:
    media = [m for m in z.namelist() if m.startswith("word/media/")]
    print(f"Verified media files in output: {len(media)}")
    for m in media:
        print(f" - {m} ({z.getinfo(m).file_size} bytes)")

