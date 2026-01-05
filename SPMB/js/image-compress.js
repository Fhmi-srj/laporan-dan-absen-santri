/**
 * Image Compression Utility
 * Compresses images before upload to reduce file size
 */

const ImageCompressor = {
    // Default options
    options: {
        maxWidth: 1200,
        maxHeight: 1200,
        quality: 0.8,
        mimeType: 'image/jpeg'
    },

    /**
     * Compress an image file
     * @param {File} file - The image file to compress
     * @param {Object} customOptions - Custom compression options
     * @returns {Promise<Blob>} - Compressed image as Blob
     */
    compress: function (file, customOptions = {}) {
        return new Promise((resolve, reject) => {
            // Merge options
            const opts = { ...this.options, ...customOptions };

            // Check if file is an image
            if (!file.type.startsWith('image/')) {
                resolve(file); // Return original file if not an image
                return;
            }

            // Skip compression for small files (< 500KB)
            if (file.size < 500 * 1024) {
                resolve(file);
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    let { width, height } = img;

                    // Calculate new dimensions
                    if (width > opts.maxWidth) {
                        height = (height * opts.maxWidth) / width;
                        width = opts.maxWidth;
                    }
                    if (height > opts.maxHeight) {
                        width = (width * opts.maxHeight) / height;
                        height = opts.maxHeight;
                    }

                    canvas.width = width;
                    canvas.height = height;

                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    // Convert to blob
                    canvas.toBlob(
                        (blob) => {
                            if (blob) {
                                // Create new file with original name
                                const compressedFile = new File([blob], file.name, {
                                    type: opts.mimeType,
                                    lastModified: Date.now()
                                });

                                console.log(`Image compressed: ${(file.size / 1024).toFixed(1)}KB -> ${(blob.size / 1024).toFixed(1)}KB`);
                                resolve(compressedFile);
                            } else {
                                resolve(file); // Return original if compression fails
                            }
                        },
                        opts.mimeType,
                        opts.quality
                    );
                };
                img.onerror = () => resolve(file);
                img.src = e.target.result;
            };
            reader.onerror = () => resolve(file);
            reader.readAsDataURL(file);
        });
    },

    /**
     * Compress multiple files
     * @param {FileList|Array} files - Files to compress
     * @param {Object} options - Compression options
     * @returns {Promise<Array>} - Array of compressed files
     */
    compressMultiple: async function (files, options = {}) {
        const compressed = [];
        for (const file of files) {
            const result = await this.compress(file, options);
            compressed.push(result);
        }
        return compressed;
    },

    /**
     * Attach auto-compression to file input
     * @param {HTMLInputElement} input - File input element
     * @param {Object} options - Compression options
     */
    attachToInput: function (input, options = {}) {
        input.addEventListener('change', async (e) => {
            if (!e.target.files.length) return;

            const file = e.target.files[0];
            if (!file.type.startsWith('image/')) return;

            // Show loading indicator
            const parent = input.closest('.file-upload') || input.parentElement;
            const originalText = parent.querySelector('p')?.textContent;
            if (parent.querySelector('p')) {
                parent.querySelector('p').textContent = 'Mengompres gambar...';
            }

            try {
                const compressed = await this.compress(file, options);

                // Create new FileList with compressed file
                const dt = new DataTransfer();
                dt.items.add(compressed);
                input.files = dt.files;

                // Update label
                if (parent.querySelector('p')) {
                    const savings = ((1 - compressed.size / file.size) * 100).toFixed(0);
                    parent.querySelector('p').textContent = `${compressed.name} (dikompres ${savings}%)`;
                }

                // Add success class
                if (parent.classList.contains('file-upload')) {
                    parent.classList.add('has-file');
                }
            } catch (err) {
                console.error('Compression failed:', err);
                if (parent.querySelector('p')) {
                    parent.querySelector('p').textContent = originalText || file.name;
                }
            }
        });
    }
};

// Auto-initialize on DOM ready
document.addEventListener('DOMContentLoaded', function () {
    // Attach to all file inputs with data-compress attribute
    document.querySelectorAll('input[type="file"][data-compress]').forEach(input => {
        ImageCompressor.attachToInput(input);
    });
});
