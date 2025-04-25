                </td>
            </tr>
        </table>
        <footer class="bg-light text-center text-lg-start mt-auto">
            <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.05);">
                © 2023 School Dashboard - Nigerian Education System
            </div>
        </footer>
        
        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Custom JS -->
        <script>
            // Auto close alert messages after 5 seconds
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    var alerts = document.querySelectorAll('.alert-flash');
                    alerts.forEach(function(alert) {
                        if (bootstrap.Alert) {
                            var bsAlert = new bootstrap.Alert(alert);
                            bsAlert.close();
                        } else {
                            alert.style.display = 'none';
                        }
                    });
                }, 5000);
                
                // Log layout status for debugging
                console.log('School Dashboard v3 - Table layout initialized');
            });
        </script>
    </body>
</html> 