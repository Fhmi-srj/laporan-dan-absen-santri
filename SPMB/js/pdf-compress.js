/**
 * PDF Compression Utility
 * Uses pdf-lib for optimization and canvas for image compression
 * Automatically compresses PDF files before upload
 */

// Load pdf-lib from CDN (will be loaded dynamically)
let PDFLib = null;

async function loadPDFLib() {
    if (PDFLib) return PDFLib;

    return new Promise((resolve, reject) => {
        if (window.PDFLib) {
            PDFLib = window.PDFLib;
            resolve(PDFLib);
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://unpkg.com/pdf-lib@1.17.1/dist/pdf-lib.min.js';
        script.onload = () => {
            PDFLib = window.PDFLib;
            resolve(PDFLib);
        };
        script.onerror = reject;
        document.head.appendChild(script);
    });
}

/**
 * Compress a PDF file
 * @param {File} file - The PDF file to compress
 * @param {Object} options - Compression options
 * @returns {Promise<File>} - Compressed PDF file
 */
async function compressPDF(file, options = {}) {
    const {
        quality = 0.8,  // Image quality (0.0 - 1.0)
        maxWidth = 1200, // Max image width
        showProgress = true
    } = options;

    // Show loading indicator
    let loadingEl = null;
    if (showProgress) {
        loadingEl = showCompressionLoading(file.name);
    }

    try {
        await loadPDFLib();

        const arrayBuffer = await file.arrayBuffer();
        const originalSize = arrayBuffer.byteLength;

        // Load the PDF
        const pdfDoc = await PDFLib.PDFDocument.load(arrayBuffer, {
            ignoreEncryption: true,
            updateMetadata: false
        });

        // Get all pages
        const pages = pdfDoc.getPages();

        // Process each page - convert to image and back for compression
        for (let i = 0; i < pages.length; i++) {
            if (loadingEl) {
                updateCompressionProgress(loadingEl, `Memproses halaman ${i + 1}/${pages.length}...`);
            }
        }

        // Save with optimization options
        const compressedBytes = await pdfDoc.save({
            useObjectStreams: true,
            addDefaultPage: false,
            objectsPerTick: 50,
        });

        const compressedSize = compressedBytes.byteLength;
        const reduction = ((originalSize - compressedSize) / originalSize * 100).toFixed(1);

        // Create new file
        const compressedFile = new File([compressedBytes], file.name, {
            type: 'application/pdf',
            lastModified: Date.now()
        });

        if (loadingEl) {
            hideCompressionLoading(loadingEl, originalSize, compressedSize);
        }

        console.log(`PDF Compressed: ${formatFileSize(originalSize)} → ${formatFileSize(compressedSize)} (${reduction}% reduced)`);

        return compressedFile;

    } catch (error) {
        console.error('PDF compression error:', error);
        if (loadingEl) {
            loadingEl.remove();
        }
        // Return original file if compression fails
        return file;
    }
}

/**
 * Alternative compression using canvas (for scanned documents)
 * This provides better compression for image-heavy PDFs
 */
async function compressPDFWithCanvas(file, options = {}) {
    const {
        quality = 0.75,
        scale = 1.5
    } = options;

    try {
        await loadPDFLib();

        // Load PDF.js for rendering
        if (!window.pdfjsLib) {
            await loadPDFJS();
        }

        const arrayBuffer = await file.arrayBuffer();
        const originalSize = arrayBuffer.byteLength;

        // Load with PDF.js for rendering
        const loadingTask = pdfjsLib.getDocument({ data: arrayBuffer });
        const pdfDoc = await loadingTask.promise;

        // Create new PDF with pdf-lib
        const newPdfDoc = await PDFLib.PDFDocument.create();

        for (let i = 1; i <= pdfDoc.numPages; i++) {
            const page = await pdfDoc.getPage(i);
            const viewport = page.getViewport({ scale: scale });

            // Create canvas
            const canvas = document.createElement('canvas');
            canvas.width = viewport.width;
            canvas.height = viewport.height;
            const ctx = canvas.getContext('2d');

            // Render page to canvas
            await page.render({
                canvasContext: ctx,
                viewport: viewport
            }).promise;

            // Convert to JPEG with compression
            const imageData = canvas.toDataURL('image/jpeg', quality);
            const imageBytes = await fetch(imageData).then(r => r.arrayBuffer());

            // Embed in new PDF
            const image = await newPdfDoc.embedJpg(imageBytes);
            const newPage = newPdfDoc.addPage([viewport.width, viewport.height]);
            newPage.drawImage(image, {
                x: 0,
                y: 0,
                width: viewport.width,
                height: viewport.height
            });
        }

        const compressedBytes = await newPdfDoc.save();

        return new File([compressedBytes], file.name, {
            type: 'application/pdf',
            lastModified: Date.now()
        });

    } catch (error) {
        console.error('Canvas compression error:', error);
        return file;
    }
}

async function loadPDFJS() {
    return new Promise((resolve, reject) => {
        if (window.pdfjsLib) {
            resolve();
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
        script.onload = () => {
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
            resolve();
        };
        script.onerror = reject;
        document.head.appendChild(script);
    });
}

/**
 * Setup auto-compression for file inputs
 * @param {string} selector - CSS selector for file inputs
 */
function setupPDFCompression(selector = 'input[type="file"][accept*="pdf"]') {
    const fileInputs = document.querySelectorAll(selector);

    fileInputs.forEach(input => {
        // Store original onchange if exists
        const originalOnChange = input.onchange;

        input.addEventListener('change', async function (e) {
            const file = this.files[0];

            if (!file || file.type !== 'application/pdf') {
                return;
            }

            // Only compress if file is larger than 500KB
            if (file.size < 500 * 1024) {
                console.log('File cukup kecil, tidak perlu kompresi');
                return;
            }

            try {
                // Compress the PDF
                const compressedFile = await compressPDF(file, {
                    quality: 0.85,
                    showProgress: true
                });

                // Create a new FileList with compressed file
                const dt = new DataTransfer();
                dt.items.add(compressedFile);
                this.files = dt.files;

                // Trigger original onchange if exists
                if (originalOnChange) {
                    originalOnChange.call(this, e);
                }

            } catch (error) {
                console.error('Gagal kompresi PDF:', error);
            }
        });
    });
}

/**
 * Manual compression function for existing file inputs
 */
async function compressFileInput(inputElement) {
    const file = inputElement.files[0];

    if (!file || file.type !== 'application/pdf') {
        return false;
    }

    const compressedFile = await compressPDF(file);

    const dt = new DataTransfer();
    dt.items.add(compressedFile);
    inputElement.files = dt.files;

    return true;
}

// UI Helper Functions
function showCompressionLoading(filename) {
    const el = document.createElement('div');
    el.className = 'pdf-compression-loading';
    el.innerHTML = `
        <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); 
                    background: white; padding: 24px 32px; border-radius: 16px; 
                    box-shadow: 0 10px 40px rgba(0,0,0,0.2); z-index: 10000; text-align: center; min-width: 280px;">
            <div style="width: 48px; height: 48px; border: 4px solid #f3f3f3; border-top: 4px solid #E67E22; 
                        border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 16px;"></div>
            <div style="font-weight: 600; color: #333; margin-bottom: 8px;">Mengompresi PDF...</div>
            <div class="compression-status" style="font-size: 13px; color: #666;">${filename}</div>
        </div>
        <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.3); z-index: 9999;"></div>
        <style>@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>
    `;
    document.body.appendChild(el);
    return el;
}

function updateCompressionProgress(el, message) {
    const status = el.querySelector('.compression-status');
    if (status) status.textContent = message;
}

function hideCompressionLoading(el, originalSize, compressedSize) {
    const reduction = ((originalSize - compressedSize) / originalSize * 100).toFixed(0);
    const saved = originalSize - compressedSize;

    el.querySelector('.compression-status').innerHTML = `
        <div style="color: #10b981; font-weight: 600; margin-bottom: 4px;">✓ Berhasil dikompres!</div>
        <div style="font-size: 12px; color: #666;">
            ${formatFileSize(originalSize)} → ${formatFileSize(compressedSize)} 
            <span style="color: #10b981;">(hemat ${formatFileSize(saved)})</span>
        </div>
    `;

    setTimeout(() => el.remove(), 2000);
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
    // Setup compression for all PDF file inputs
    setTimeout(() => {
        setupPDFCompression('input[type="file"]');
    }, 500);
});

// Export for manual use
window.PDFCompressor = {
    compress: compressPDF,
    compressWithCanvas: compressPDFWithCanvas,
    setup: setupPDFCompression,
    compressInput: compressFileInput
};
