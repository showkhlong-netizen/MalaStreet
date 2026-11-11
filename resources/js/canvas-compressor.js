/**
 * HTML5 Canvas Image Compression Utility
 * ย่อและบีบอัดรูปภาพด้วย HTML5 Canvas ก่อนอัปโหลด ลดขนาดไฟล์ได้ถึง 90%
 */
export class CanvasCompressor {
    /**
     * @param {File} file - Original image file
     * @param {Object} options - Compression options
     * @param {number} options.maxWidth - Max width in pixels (default 1280)
     * @param {number} options.maxHeight - Max height in pixels (default 1280)
     * @param {number} options.quality - JPEG compression quality 0.0 - 1.0 (default 0.8)
     * @returns {Promise<{blob: Blob, base64: string, originalSize: number, compressedSize: number, reductionPercent: number}>}
     */
    static async compress(file, options = {}) {
        const maxWidth = options.maxWidth || 1280;
        const maxHeight = options.maxHeight || 1280;
        const quality = options.quality !== undefined ? options.quality : 0.80;

        return new Promise((resolve, reject) => {
            if (!file || !file.type.match(/image.*/)) {
                return reject(new Error('ไฟล์ที่เลือกไม่ใช่รูปภาพที่รองรับ'));
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    let width = img.width;
                    let height = img.height;

                    // Calculate aspect ratio downscaling
                    if (width > height) {
                        if (width > maxWidth) {
                            height = Math.round((height * maxWidth) / width);
                            width = maxWidth;
                        }
                    } else {
                        if (height > maxHeight) {
                            width = Math.round((width * maxHeight) / height);
                            height = maxHeight;
                        }
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;

                    const ctx = canvas.getContext('2d');
                    // Fill white background for transparent PNG/WebP before converting to JPEG
                    ctx.fillStyle = '#FFFFFF';
                    ctx.fillRect(0, 0, width, height);
                    ctx.drawImage(img, 0, 0, width, height);

                    const base64 = canvas.toDataURL('image/jpeg', quality);

                    canvas.toBlob((blob) => {
                        const originalSize = file.size;
                        const compressedSize = blob.size;
                        const reductionPercent = Math.max(0, Math.round(((originalSize - compressedSize) / originalSize) * 100));

                        resolve({
                            blob,
                            base64,
                            width,
                            height,
                            originalSize,
                            compressedSize,
                            reductionPercent,
                            formattedOriginal: CanvasCompressor.formatBytes(originalSize),
                            formattedCompressed: CanvasCompressor.formatBytes(compressedSize)
                        });
                    }, 'image/jpeg', quality);
                };
                img.onerror = () => reject(new Error('ไม่สามารถโหลดภาพเข้า Canvas ได้'));
                img.src = e.target.result;
            };
            reader.onerror = () => reject(new Error('เกิดข้อผิดพลาดในการอ่านไฟล์'));
            reader.readAsDataURL(file);
        });
    }

    static formatBytes(bytes, decimals = 1) {
        if (!+bytes) return '0 B';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
    }
}

window.CanvasCompressor = CanvasCompressor;

