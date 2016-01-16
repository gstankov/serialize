# Serialize

Serialize PDF file for printing, like tickets or some other purpose that needs serialization.

Requirements:

- Apache, PHP
- Write permissions for "generate/class/tfpdf/font/unifont" (tFPDF needs to generate font metrics files)
- Tested on shared hosting provider

Features:

- Upload existing PDF file to use as a template
- Resizing of the template file
- "Number of copies" or range selection (range is usefull to split ranges when having great number of copies)
- Two output methods: "nest" to one file or output as multi-page file
- Custom sn. positioning and custom prefix
- Sn. font sizing

Utilizes:

- AngularJs and ZurbFoundation for frontend
- ClassUpload for uploading files
- tFPDF and FPDI for PDF generation

Working example hosted at http://gstankov.x10.mx/serialize/ (u: bla, p: bla)

Has some issues, check issues.
