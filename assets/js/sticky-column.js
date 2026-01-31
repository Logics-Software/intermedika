/**
 * Sticky Column JavaScript - Reusable untuk table dengan sticky column di mobile
 * 
 * Cara penggunaan:
 * 1. Tambahkan class "table-sticky-column" pada container table-responsive
 * 2. Tambahkan class "sticky-col" pada th dan td yang ingin di-sticky
 * 3. Optional: Tambahkan class "hide-first-col" pada container untuk menyembunyikan kolom pertama
 * 
 * Contoh:
 * <div class="table-responsive table-sticky-column hide-first-col" id="myTable">
 *     <table>
 *         <thead>
 *             <tr>
 *                 <th>No</th>
 *                 <th class="sticky-col">Nama Barang</th>
 *                 ...
 *             </tr>
 *         </thead>
 *         <tbody>
 *             <tr>
 *                 <td>1</td>
 *                 <td class="sticky-col">Barang A</td>
 *                 ...
 *             </tr>
 *         </tbody>
 *     </table>
 * </div>
 */

(function() {
    'use strict';
    
    // Map untuk menyimpan scroll handler setiap table
    const scrollHandlers = new Map();
    
    /**
     * Initialize sticky column untuk sebuah table container
     * @param {HTMLElement} tableContainer - Container table dengan class table-sticky-column
     */
    function initStickyColumn(tableContainer) {
        // Hanya untuk mobile
        if (window.innerWidth > 767.98) {
            cleanup(tableContainer);
            return;
        }
        
        if (!tableContainer) return;
        
        const stickyCells = tableContainer.querySelectorAll('.sticky-col');
        if (stickyCells.length === 0) return;
        
        // Pastikan overflow-x: auto
        tableContainer.style.overflowX = 'auto';
        
        // Tambahkan class sticky-active
        stickyCells.forEach(function(cell) {
            cell.classList.add('sticky-active');
        });
        
        // Fungsi untuk update posisi saat scroll
        function handleScroll() {
            const scrollLeft = tableContainer.scrollLeft;
            
            stickyCells.forEach(function(cell) {
                // Update posisi dengan transform
                // Kolom akan bergerak sejauh scroll, sehingga tetap terlihat di posisi 0
                cell.style.transform = 'translateX(' + scrollLeft + 'px)';
            });
        }
        
        // Hapus event listener lama jika ada
        const oldHandler = scrollHandlers.get(tableContainer);
        if (oldHandler) {
            tableContainer.removeEventListener('scroll', oldHandler);
        }
        
        // Simpan handler baru
        scrollHandlers.set(tableContainer, handleScroll);
        tableContainer.addEventListener('scroll', handleScroll, { passive: true });
        
        // Update posisi awal
        handleScroll();
    }
    
    /**
     * Cleanup sticky column untuk sebuah table container
     * @param {HTMLElement} tableContainer - Container table
     */
    function cleanup(tableContainer) {
        if (!tableContainer) return;
        
        const stickyCells = tableContainer.querySelectorAll('.sticky-col');
        stickyCells.forEach(function(cell) {
            cell.classList.remove('sticky-active');
            cell.style.transform = '';
        });
        
        const handler = scrollHandlers.get(tableContainer);
        if (handler) {
            tableContainer.removeEventListener('scroll', handler);
            scrollHandlers.delete(tableContainer);
        }
    }
    
    /**
     * Initialize semua table dengan class table-sticky-column
     */
    function initAllStickyColumns() {
        const tableContainers = document.querySelectorAll('.table-sticky-column');
        tableContainers.forEach(function(container) {
            initStickyColumn(container);
        });
    }
    
    /**
     * Cleanup semua sticky columns
     */
    function cleanupAll() {
        const tableContainers = document.querySelectorAll('.table-sticky-column');
        tableContainers.forEach(function(container) {
            cleanup(container);
        });
    }
    
    // Initialize saat DOM ready
    function runInit() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(initAllStickyColumns, 100);
            });
        } else {
            setTimeout(initAllStickyColumns, 100);
        }
    }
    
    runInit();
    
    // Re-initialize on resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        cleanupAll();
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(initAllStickyColumns, 150);
    });
    
    // Export untuk penggunaan manual jika diperlukan
    window.StickyColumn = {
        init: initStickyColumn,
        initAll: initAllStickyColumns,
        cleanup: cleanup,
        cleanupAll: cleanupAll
    };
})();

