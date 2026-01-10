/**
 * QZ Tray Configuration for Thermal Printer 58mm
 * Printer: IDY01POS-58B
 */

// QZ Tray connection status
let qzConnected = false;
let printerName = 'POS-58';

// Setup security certificate (demo keys)
function setupQzSecurity() {
    if (typeof qz === 'undefined') return;

    // Set certificate promise - load public certificate
    qz.security.setCertificatePromise(function (resolve, reject) {
        fetch('assets/certs/digital-certificate.txt', { cache: 'no-store' })
            .then(function (response) {
                if (response.ok) {
                    return response.text();
                }
                throw new Error('Certificate not found');
            })
            .then(resolve)
            .catch(reject);
    });

    // Set signature promise - use server-side signing for valid signatures
    qz.security.setSignatureAlgorithm("SHA512");
    qz.security.setSignaturePromise(function (toSign) {
        return function (resolve, reject) {
            fetch('api/qz-sign.php', {
                method: 'POST',
                headers: { 'Content-Type': 'text/plain' },
                body: toSign
            })
                .then(function (response) {
                    if (response.ok) {
                        return response.text();
                    }
                    throw new Error('Signing failed: ' + response.status);
                })
                .then(resolve)
                .catch(reject);
        };
    });
}

// Initialize QZ Tray connection
async function initQzTray() {
    if (typeof qz === 'undefined') {
        console.error('QZ Tray library not loaded');
        return false;
    }

    try {
        // Setup security first
        setupQzSecurity();

        if (!qz.websocket.isActive()) {
            await qz.websocket.connect();
        }
        qzConnected = true;
        console.log('QZ Tray connected successfully');
        return true;
    } catch (err) {
        console.error('QZ Tray connection failed:', err);
        qzConnected = false;
        return false;
    }
}

// Check if QZ Tray is connected
function isQzConnected() {
    return qzConnected && qz.websocket.isActive();
}

// Get printer config for 58mm thermal
function getPrinterConfig() {
    return qz.configs.create(printerName, {
        size: { width: 58, height: null },
        units: 'mm',
        margins: { top: 0, left: 0, right: 0, bottom: 0 },
        colorType: 'blackwhite',
        interpolation: 'bicubic'
    });
}

// Format text for 58mm (max ~32 chars per line)
function formatLine(text, maxWidth = 32) {
    if (text.length <= maxWidth) return text;

    const words = text.split(' ');
    const lines = [];
    let currentLine = '';

    words.forEach(word => {
        if ((currentLine + ' ' + word).trim().length <= maxWidth) {
            currentLine = (currentLine + ' ' + word).trim();
        } else {
            if (currentLine) lines.push(currentLine);
            currentLine = word;
        }
    });
    if (currentLine) lines.push(currentLine);

    return lines.join('\n');
}

// Center text
function centerText(text, width = 32) {
    const padding = Math.max(0, Math.floor((width - text.length) / 2));
    return ' '.repeat(padding) + text;
}

// Generate surat izin text for thermal printing
function generateSuratIzinText(data) {
    const divider = '================================';
    const dividerThin = '--------------------------------';

    let text = '';

    // Header
    text += divider + '\n';
    text += centerText('PONDOK PESANTREN') + '\n';
    text += centerText("MAMBA'UL HUDA") + '\n';
    text += centerText('PAJOMBLANGAN') + '\n';
    text += divider + '\n';
    text += centerText('SURAT IZIN SEKOLAH') + '\n';
    text += centerText('NO: ' + data.nomorSurat) + '\n';
    text += dividerThin + '\n\n';

    // Kepada
    text += 'Kepada Yth.\n';
    text += 'Bapak/Ibu Guru ' + (data.tujuanGuru || '') + '\n\n';

    // Salam
    text += "Assalamu'alaikum Wr. Wb.\n\n";

    // Isi
    text += formatLine('Dengan hormat, melalui surat ini kami memberitahukan bahwa:') + '\n\n';

    // Data santri
    text += 'Nama    : ';
    if (data.santriNames && data.santriNames.length > 0) {
        data.santriNames.forEach((name, idx) => {
            if (idx === 0) {
                text += name + '\n';
            } else {
                text += '          ' + name + '\n';
            }
        });
    }

    text += 'Kelas   : ' + (data.kelas || '-') + '\n';
    text += 'Ket     : Izin tidak mengikuti\n';
    text += '          KBM\n';
    text += 'Tanggal : ' + (data.tanggal || '-') + '\n';
    text += 'Alasan  : ' + (data.kategori === 'sakit' ? 'Sakit' : 'Izin Pulang') + '\n\n';

    // Penutup
    text += formatLine('Demikian surat ini kami sampaikan. Atas perhatian Bapak/Ibu, kami ucapkan terima kasih.') + '\n\n';
    text += "Wassalamu'alaikum Wr. Wb.\n\n";

    // TTD
    text += 'Hormat kami,\n\n\n\n';
    text += 'Pengurus Izin\n';
    text += divider + '\n';

    return text;
}

// Print surat izin
async function printSuratIzin(data) {
    if (!isQzConnected()) {
        const connected = await initQzTray();
        if (!connected) {
            throw new Error('QZ Tray tidak terhubung. Pastikan QZ Tray sudah berjalan.');
        }
    }

    const config = getPrinterConfig();
    const text = generateSuratIzinText(data);

    // ESC/POS commands for thermal printer
    const printData = [
        '\x1B\x40',          // Initialize printer
        '\x1B\x61\x00',      // Left align
        text,
        '\x1B\x64\x03',      // Feed 3 lines
        '\x1D\x56\x00'       // Cut paper (if supported)
    ];

    try {
        await qz.print(config, printData);
        console.log('Print successful');
        return true;
    } catch (err) {
        console.error('Print failed:', err);
        throw err;
    }
}

// List available printers
async function listPrinters() {
    if (!isQzConnected()) {
        await initQzTray();
    }

    try {
        const printers = await qz.printers.find();
        console.log('Available printers:', printers);
        return printers;
    } catch (err) {
        console.error('Failed to list printers:', err);
        return [];
    }
}

// Set printer name
function setPrinterName(name) {
    printerName = name;
    console.log('Printer set to:', printerName);
}

// Export functions
window.QzPrint = {
    init: initQzTray,
    isConnected: isQzConnected,
    print: printSuratIzin,
    listPrinters: listPrinters,
    setPrinter: setPrinterName,
    generateText: generateSuratIzinText
};
