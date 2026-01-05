/**
 * Document Checker Component
 * Shows visual checklist of document upload status
 */

const DocumentChecker = {
    // Required documents configuration
    documents: [
        { field: 'file_kk', name: 'Kartu Keluarga (KK)', required: true },
        { field: 'file_ktp_ortu', name: 'KTP Orang Tua', required: true },
        { field: 'file_akta', name: 'Akta Kelahiran', required: true },
        { field: 'file_ijazah', name: 'Ijazah', required: false },
        { field: 'file_sertifikat', name: 'Sertifikat Prestasi', required: false }
    ],

    /**
     * Generate document status HTML
     * @param {Object} data - User data with file fields
     * @param {boolean} compact - Use compact display
     * @returns {string} - HTML string
     */
    generateHTML: function (data, compact = false) {
        let completed = 0;
        let requiredCompleted = 0;
        let requiredTotal = 0;

        const items = this.documents.map(doc => {
            const hasFile = data[doc.field] && data[doc.field].trim() !== '';
            if (hasFile) completed++;
            if (doc.required) {
                requiredTotal++;
                if (hasFile) requiredCompleted++;
            }

            const icon = hasFile ? 'fa-check-circle text-green-500' :
                (doc.required ? 'fa-times-circle text-red-400' : 'fa-circle text-gray-300');
            const textClass = hasFile ? 'text-gray-700' : 'text-gray-400';
            const badge = doc.required ? '<span class="text-red-500 text-xs">*</span>' : '';

            if (compact) {
                return `<div class="flex items-center gap-2 py-1">
                    <i class="fas ${icon} text-sm"></i>
                    <span class="text-xs ${textClass}">${doc.name}${badge}</span>
                </div>`;
            }

            return `<div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                <div class="flex items-center gap-2">
                    <i class="fas ${icon}"></i>
                    <span class="text-sm ${textClass}">${doc.name}${badge}</span>
                </div>
                <span class="text-xs ${hasFile ? 'text-green-600' : 'text-gray-400'}">
                    ${hasFile ? 'Terupload' : 'Belum'}
                </span>
            </div>`;
        }).join('');

        const percentage = requiredTotal > 0 ? Math.round((requiredCompleted / requiredTotal) * 100) : 0;
        const progressColor = percentage === 100 ? 'bg-green-500' : (percentage >= 50 ? 'bg-yellow-500' : 'bg-red-500');

        if (compact) {
            return `<div class="space-y-1">${items}</div>`;
        }

        return `
            <div class="mb-3">
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600">Kelengkapan Dokumen Wajib</span>
                    <span class="font-semibold ${percentage === 100 ? 'text-green-600' : 'text-gray-700'}">${percentage}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="${progressColor} h-2 rounded-full transition-all duration-300" style="width: ${percentage}%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">${requiredCompleted} dari ${requiredTotal} dokumen wajib</p>
            </div>
            <div class="space-y-0">${items}</div>
        `;
    },

    /**
     * Render document checker to element
     * @param {string|HTMLElement} target - Target element or selector
     * @param {Object} data - User data
     * @param {boolean} compact - Compact mode
     */
    render: function (target, data, compact = false) {
        const element = typeof target === 'string' ? document.querySelector(target) : target;
        if (element) {
            element.innerHTML = this.generateHTML(data, compact);
        }
    },

    /**
     * Get document status summary
     * @param {Object} data - User data
     * @returns {Object} - Status summary
     */
    getStatus: function (data) {
        let total = 0, completed = 0, requiredTotal = 0, requiredCompleted = 0;

        this.documents.forEach(doc => {
            total++;
            const hasFile = data[doc.field] && data[doc.field].trim() !== '';
            if (hasFile) completed++;
            if (doc.required) {
                requiredTotal++;
                if (hasFile) requiredCompleted++;
            }
        });

        return {
            total,
            completed,
            requiredTotal,
            requiredCompleted,
            percentage: requiredTotal > 0 ? Math.round((requiredCompleted / requiredTotal) * 100) : 0,
            isComplete: requiredCompleted === requiredTotal
        };
    }
};

// Export for use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DocumentChecker;
}
