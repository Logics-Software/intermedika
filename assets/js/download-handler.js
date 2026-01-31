/**
 * Download Handler - Custom error handling for file downloads
 * Provides user-friendly error messages when downloads fail
 */
class DownloadHandler {
  constructor() {
    this.init();
  }

  init() {
    // Override all download links
    this.attachDownloadHandlers();

    // Monitor for dynamically added download links
    this.observeNewLinks();
  }

  /**
   * Attach download handlers to existing links
   */
  attachDownloadHandlers() {
    const downloadLinks = document.querySelectorAll(
      'a[download], a[href*="/uploads/"], a[href*="download"]'
    );
    downloadLinks.forEach((link) => {
      if (!link.hasAttribute("data-download-handled")) {
        this.attachHandler(link);
        link.setAttribute("data-download-handled", "true");
      }
    });
  }

  /**
   * Attach handler to a single download link
   */
  attachHandler(link) {
    link.addEventListener("click", async (e) => {
      e.preventDefault();

      const url = link.href;
      const filename =
        link.getAttribute("download") || this.getFilenameFromUrl(url);

      try {
        await this.handleDownload(url, filename, link);
      } catch (error) {
        console.error("Download error:", error);
        this.showErrorAlert("Gagal mengunduh file", error.message);
      }
    });
  }

  /**
   * Handle the actual download with error checking
   */
  async handleDownload(url, filename, linkElement) {
    // Show loading state
    this.showLoadingState(linkElement, true);

    try {
      // Check if file exists first
      const response = await fetch(url, { method: "HEAD" });

      if (!response.ok) {
        throw new Error(this.getErrorMessage(response.status, filename));
      }

      // File exists, proceed with download
      const downloadResponse = await fetch(url);

      if (!downloadResponse.ok) {
        throw new Error(
          this.getErrorMessage(downloadResponse.status, filename)
        );
      }

      // Get file blob
      const blob = await downloadResponse.blob();

      // Check if blob is valid
      if (blob.size === 0) {
        throw new Error(`File "${filename}" kosong atau rusak`);
      }

      // Create download link and trigger download
      this.triggerDownload(blob, filename);

      // Show success message
      this.showSuccessAlert(`File "${filename}" berhasil diunduh`);
    } catch (error) {
      // Handle different types of errors
      if (error.name === "TypeError" && error.message.includes("fetch")) {
        this.showErrorAlert(
          "Koneksi Bermasalah",
          "Tidak dapat terhubung ke server. Periksa koneksi internet Anda."
        );
      } else {
        this.showErrorAlert("Gagal Mengunduh File", error.message);
      }
      throw error;
    } finally {
      // Hide loading state
      this.showLoadingState(linkElement, false);
    }
  }

  /**
   * Get appropriate error message based on HTTP status
   */
  getErrorMessage(status, filename) {
    switch (status) {
      case 404:
        return `File "${filename}" tidak ditemukan. File mungkin telah dihapus atau dipindahkan.`;
      case 403:
        return `Akses ditolak untuk file "${filename}". Anda tidak memiliki izin untuk mengunduh file ini.`;
      case 500:
        return `Terjadi kesalahan server saat mengunduh file "${filename}". Silakan coba lagi nanti.`;
      case 503:
        return `Server sedang tidak tersedia. Silakan coba mengunduh file "${filename}" lagi nanti.`;
      case 413:
        return `File "${filename}" terlalu besar untuk diunduh.`;
      case 429:
        return `Terlalu banyak permintaan download. Silakan tunggu sebentar sebelum mengunduh "${filename}".`;
      default:
        return `Gagal mengunduh file "${filename}". Kode error: ${status}`;
    }
  }

  /**
   * Extract filename from URL
   */
  getFilenameFromUrl(url) {
    try {
      const urlObj = new URL(url);
      const pathname = urlObj.pathname;
      const filename = pathname.split("/").pop();
      return filename || "file";
    } catch (e) {
      return "file";
    }
  }

  /**
   * Trigger file download
   */
  triggerDownload(blob, filename) {
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.style.display = "none";
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);
  }

  /**
   * Show loading state on download button
   */
  showLoadingState(element, isLoading) {
    if (isLoading) {
      element.classList.add("downloading");
      element.style.pointerEvents = "none";
      element.style.opacity = "0.6";

      // Add spinner if not exists
      if (!element.querySelector(".spinner-border")) {
        const originalContent = element.innerHTML;
        element.setAttribute("data-original-content", originalContent);
        element.innerHTML =
          '<span class="spinner-border spinner-border-sm me-1"></span>Mengunduh...';
      }
    } else {
      element.classList.remove("downloading");
      element.style.pointerEvents = "";
      element.style.opacity = "";

      // Restore original content
      const originalContent = element.getAttribute("data-original-content");
      if (originalContent) {
        element.innerHTML = originalContent;
        element.removeAttribute("data-original-content");
      }
    }
  }

  /**
   * Show success alert
   */
  showSuccessAlert(message) {
    this.showAlert("success", "Berhasil!", message);
  }

  /**
   * Show error alert
   */
  showErrorAlert(title, message) {
    this.showAlert("error", title, message);
  }

  /**
   * Show custom alert modal
   */
  showAlert(type, title, message) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll(".download-alert-modal");
    existingAlerts.forEach((alert) => alert.remove());

    // Create alert modal
    const alertModal = document.createElement("div");
    alertModal.className = "download-alert-modal";
    alertModal.innerHTML = `
            <div class="download-alert-overlay">
                <div class="download-alert-content ${type}">
                    <div class="download-alert-header">
                        <div class="download-alert-icon">
                            ${
                              type === "success"
                                ? '<svg width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.061L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>'
                                : '<svg width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/></svg>'
                            }
                        </div>
                        <h4 class="download-alert-title">${title}</h4>
                        <button class="download-alert-close" onclick="this.closest('.download-alert-modal').remove()">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/>
                            </svg>
                        </button>
                    </div>
                    <div class="download-alert-body">
                        <p>${message}</p>
                    </div>
                    <div class="download-alert-footer">
                        <button class="btn btn-primary" onclick="this.closest('.download-alert-modal').remove()">
                            OK
                        </button>
                    </div>
                </div>
            </div>
        `;

    // Add styles
    this.addAlertStyles();

    // Add to DOM
    document.body.appendChild(alertModal);

    // Auto remove after 5 seconds for success messages
    if (type === "success") {
      setTimeout(() => {
        if (alertModal.parentNode) {
          alertModal.remove();
        }
      }, 5000);
    }

    // Add click outside to close
    alertModal
      .querySelector(".download-alert-overlay")
      .addEventListener("click", (e) => {
        if (e.target === e.currentTarget) {
          alertModal.remove();
        }
      });
  }

  /**
   * Add CSS styles for alert modal
   */
  addAlertStyles() {
    // Check if external CSS is already loaded
    const existingLink = document.querySelector('link[href*="download-alerts.css"]');
    if (existingLink || document.getElementById("download-alert-styles")) return;

    // Fallback to inline styles if external CSS is not loaded
    const styles = document.createElement("style");
    styles.id = "download-alert-styles";
    styles.textContent = `
            .download-alert-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 9999;
                animation: fadeIn 0.3s ease-out;
            }

            .download-alert-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }

            .download-alert-content {
                background: white;
                border-radius: 8px;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
                max-width: 500px;
                width: 100%;
                animation: slideIn 0.3s ease-out;
            }

            .download-alert-content.success {
                border-top: 4px solid #28a745;
            }

            .download-alert-content.error {
                border-top: 4px solid #dc3545;
            }

            .download-alert-header {
                display: flex;
                align-items: center;
                padding: 20px 20px 10px;
                position: relative;
            }

            .download-alert-icon {
                margin-right: 12px;
                flex-shrink: 0;
            }

            .download-alert-content.success .download-alert-icon {
                color: #28a745;
            }

            .download-alert-content.error .download-alert-icon {
                color: #dc3545;
            }

            .download-alert-title {
                margin: 0;
                font-size: 1.25rem;
                font-weight: 600;
                flex-grow: 1;
            }

            .download-alert-close {
                background: none;
                border: none;
                color: #6c757d;
                cursor: pointer;
                padding: 4px;
                border-radius: 4px;
                transition: all 0.2s;
            }

            .download-alert-close:hover {
                background: #f8f9fa;
                color: #495057;
            }

            .download-alert-body {
                padding: 0 20px 20px;
            }

            .download-alert-body p {
                margin: 0;
                color: #6c757d;
                line-height: 1.5;
            }

            .download-alert-footer {
                padding: 0 20px 20px;
                text-align: right;
            }

            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }

            @keyframes slideIn {
                from { 
                    opacity: 0;
                    transform: translateY(-20px) scale(0.95);
                }
                to { 
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }

            /* Loading state styles */
            .downloading {
                position: relative;
            }

            .downloading::after {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(255, 255, 255, 0.8);
                border-radius: inherit;
                pointer-events: none;
            }
        `;
    document.head.appendChild(styles);
  }

  /**
   * Observe for dynamically added download links
   */
  observeNewLinks() {
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        mutation.addedNodes.forEach((node) => {
          if (node.nodeType === Node.ELEMENT_NODE) {
            // Check if the added node is a download link
            if (
              node.matches &&
              node.matches(
                'a[download], a[href*="/uploads/"], a[href*="download"]'
              )
            ) {
              this.attachHandler(node);
              node.setAttribute("data-download-handled", "true");
            }

            // Check for download links within the added node
            const downloadLinks =
              node.querySelectorAll &&
              node.querySelectorAll(
                'a[download], a[href*="/uploads/"], a[href*="download"]'
              );
            if (downloadLinks) {
              downloadLinks.forEach((link) => {
                if (!link.hasAttribute("data-download-handled")) {
                  this.attachHandler(link);
                  link.setAttribute("data-download-handled", "true");
                }
              });
            }
          }
        });
      });
    });

    observer.observe(document.body, {
      childList: true,
      subtree: true,
    });
  }
}

// Initialize download handler when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
  new DownloadHandler();
});

// Also initialize if DOM is already loaded
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    new DownloadHandler();
  });
} else {
  new DownloadHandler();
}
