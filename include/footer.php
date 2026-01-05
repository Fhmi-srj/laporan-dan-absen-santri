<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmLogout() {
        Swal.fire({
            title: 'Logout?',
            text: 'Apakah Anda yakin ingin keluar dari sistem?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="fas fa-sign-out-alt me-1"></i> Ya, Logout',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const isAdmin = window.location.pathname.includes('/admin/');
                window.location.href = isAdmin ? '../logout.php' : 'logout.php';
            }
        });
    }

    // Sortable Table Functionality
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.table-sortable').forEach(function (table) {
            const headers = table.querySelectorAll('thead th:not(.no-sort)');

            headers.forEach(function (header, index) {
                header.addEventListener('click', function () {
                    const tbody = table.querySelector('tbody');
                    const rows = Array.from(tbody.querySelectorAll('tr'));
                    const isAsc = header.classList.contains('sort-asc');

                    // Remove sort classes from all headers
                    headers.forEach(h => h.classList.remove('sort-asc', 'sort-desc'));

                    // Add appropriate class
                    header.classList.add(isAsc ? 'sort-desc' : 'sort-asc');

                    // Sort rows
                    rows.sort(function (a, b) {
                        const aValue = a.cells[index]?.textContent.trim() || '';
                        const bValue = b.cells[index]?.textContent.trim() || '';

                        // Try to parse as number
                        const aNum = parseFloat(aValue.replace(/[^\d.-]/g, ''));
                        const bNum = parseFloat(bValue.replace(/[^\d.-]/g, ''));

                        if (!isNaN(aNum) && !isNaN(bNum)) {
                            return isAsc ? bNum - aNum : aNum - bNum;
                        }

                        // Try to parse as date (dd/mm/yyyy or dd-mm-yyyy format)
                        const dateRegex = /(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/;
                        const aDate = aValue.match(dateRegex);
                        const bDate = bValue.match(dateRegex);

                        if (aDate && bDate) {
                            const aTime = new Date(aDate[3], aDate[2] - 1, aDate[1]).getTime();
                            const bTime = new Date(bDate[3], bDate[2] - 1, bDate[1]).getTime();
                            return isAsc ? bTime - aTime : aTime - bTime;
                        }

                        // String comparison
                        return isAsc ? bValue.localeCompare(aValue, 'id') : aValue.localeCompare(bValue, 'id');
                    });

                    // Re-append sorted rows
                    rows.forEach(row => tbody.appendChild(row));
                });
            });
        });
    });
</script>
<?php if (isset($extraScripts))
    echo $extraScripts; ?>
</body>

</html>