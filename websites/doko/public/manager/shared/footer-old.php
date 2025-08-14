    </div> <!-- End main-content -->

    <footer style="background-color: #333; color: white; text-align: center; padding: 20px; margin-top: auto;">
        <div style="max-width: 1200px; margin: 0 auto;">
            <p>&copy; <?php echo date('Y'); ?> DOKO Grocery E-commerce Manager Panel. All rights reserved.</p>
            <p style="font-size: 0.9rem; margin-top: 10px; opacity: 0.8;">
                Logged in as: <?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?> 
                (<?php echo ucfirst($currentUser['role']); ?>)
            </p>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        // Common manager panel functions
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
        
        // Confirm delete actions
        $('.delete-btn, .btn-delete').on('click', function(e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
                return false;
            }
        });
        
        // Format numbers in tables
        $('.format-number').each(function() {
            let number = parseFloat($(this).text());
            if (!isNaN(number)) {
                $(this).text(number.toLocaleString());
            }
        });
        
        // Format currency in tables
        $('.format-currency').each(function() {
            let number = parseFloat($(this).text());
            if (!isNaN(number)) {
                $(this).text('$' + number.toFixed(2));
            }
        });
    </script>
</body>
</html>
