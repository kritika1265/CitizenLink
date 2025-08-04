</div> <!-- End main-content -->
        </div> <!-- End content -->
    </div> <!-- End wrapper -->
    
    <!-- Footer -->
    <footer class="admin-footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; <?php echo date('Y'); ?> CitizenLink. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-end">
                    <p>Version 1.0 | <a href="#" target="_blank">Documentation</a> | <a href="#" target="_blank">Support</a></p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- jQuery -->
    <?php if (!defined('BASE_URL')) define('BASE_URL', '/'); ?>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/jquery.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="<?php echo BASE_URL; ?>/public/assets/js/bootstrap.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/dataTables.bootstrap4.min.js"></script>
    
    <!-- Admin JS -->
    <script src="<?php echo BASE_URL; ?>/public/assets/js/admin.js"></script>
    
    <script>
        $(document).ready(function() {
            // Sidebar toggle
            $('#sidebarCollapse').on('click', function() {
                $('#sidebar').toggleClass('active');
            });
            
            // Initialize DataTables
            $('.data-table').DataTable({
                "pageLength": 25,
                "ordering": true,
                "searching": true,
                "lengthChange": true,
                "info": true,
                "autoWidth": false,
                "responsive": true
            });
            
            // Auto hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
            
            // Confirmation for delete actions
            $('.delete-btn').on('click', function(e) {
                if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                    e.preventDefault();
                    return false;
                }
            });
            
            // Status change confirmation
            $('.status-change').on('click', function(e) {
                var action = $(this).data('action') || 'change status';
                if (!confirm('Are you sure you want to ' + action + '?')) {
                    e.preventDefault();
                    return false;
                }
            });
            
            // Real-time search
            $('.live-search').on('keyup', function() {
                var searchTerm = $(this).val().toLowerCase();
                var targetTable = $(this).data('target');
                
                $(targetTable + ' tbody tr').each(function() {
                    var rowText = $(this).text().toLowerCase();
                    if (rowText.indexOf(searchTerm) === -1) {
                        $(this).hide();
                    } else {
                        $(this).show();
                    }
                });
            });
            
            // Form validation
            $('.admin-form').on('submit', function(e) {
                var isValid = true;
                var firstInvalidField = null;
                
                // Check required fields
                $(this).find('input[required], select[required], textarea[required]').each(function() {
                    if (!$(this).val()) {
                        isValid = false;
                        $(this).addClass('is-invalid');
                        if (!firstInvalidField) {
                            firstInvalidField = $(this);
                        }
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });
                
                // Check email format
                $(this).find('input[type="email"]').each(function() {
                    var email = $(this).val();
                    if (email && !isValidEmail(email)) {
                        isValid = false;
                        $(this).addClass('is-invalid');
                        if (!firstInvalidField) {
                            firstInvalidField = $(this);
                        }
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    if (firstInvalidField) {
                        firstInvalidField.focus();
                    }
                    showAlert('Please fill in all required fields correctly.', 'danger');
                }
            });
            
            // Password strength indicator
            $('input[type="password"][data-strength]').on('keyup', function() {
                var password = $(this).val();
                var strength = checkPasswordStrength(password);
                var indicator = $(this).siblings('.password-strength');
                
                if (indicator.length === 0) {
                    $(this).after('<div class="password-strength mt-1"></div>');
                    indicator = $(this).siblings('.password-strength');
                }
                
                indicator.removeClass('weak medium strong')
                         .addClass(strength.class)
                         .html('<small>' + strength.text + '</small>');
            });
        });
        
        // Utility functions
        function isValidEmail(email) {
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        
        function checkPasswordStrength(password) {
            var strength = { class: 'weak', text: 'Weak' };
            
            if (password.length >= 8) {
                var hasUpper = /[A-Z]/.test(password);
                var hasLower = /[a-z]/.test(password);
                var hasNumber = /\d/.test(password);
                var hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);
                
                var score = hasUpper + hasLower + hasNumber + hasSpecial;
                
                if (score >= 3) {
                    strength = { class: 'strong', text: 'Strong' };
                } else if (score >= 2) {
                    strength = { class: 'medium', text: 'Medium' };
                }
            }
            
            return strength;
        }
        
        function showAlert(message, type = 'info') {
            var alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                           message +
                           '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                           '</div>';
            
            $('.main-content').prepend(alertHtml);
            
            setTimeout(function() {
                $('.alert').first().fadeOut('slow');
            }, 5000);
        }
        
        // AJAX helper function
        function makeAjaxRequest(url, data, successCallback, errorCallback) {
            $.ajax({
                url: url,
                method: 'POST',
                data: data,
                dataType: 'json',
                success: function(response) {
                    if (successCallback && typeof successCallback === 'function') {
                        successCallback(response);
                    }
                },
                error: function(xhr, status, error) {
                    if (errorCallback && typeof errorCallback === 'function') {
                        errorCallback(xhr, status, error);
                    } else {
                        showAlert('An error occurred. Please try again.', 'danger');
                    }
                }
            });
        }
    </script>
    
    <?php if (isset($additionalJS)): ?>
        <?php echo $additionalJS; ?>
    <?php endif; ?>
</body>
</html>