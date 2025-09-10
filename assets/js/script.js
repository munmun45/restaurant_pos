/**
 * Custom JavaScript for Restaurant POS System
 */

// Document ready function
$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Image preview for file upload
    $('#image').on('change', function() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Category filter for menu items
    $('.category-filter').on('click', function(e) {
        e.preventDefault();
        var category = $(this).data('category');
        
        if (category === 'all') {
            $('.menu-item').show();
        } else {
            $('.menu-item').hide();
            $('.menu-item[data-category="' + category + '"]').show();
        }
        
        // Update active state
        $('.category-filter').removeClass('active');
        $(this).addClass('active');
    });
    
    // Add to order button
    $('.btn-add-to-order').on('click', function() {
        var itemId = $(this).data('item-id');
        var itemName = $(this).data('item-name');
        var itemPrice = $(this).data('item-price');
        
        // Here you would add the item to the order
        // For now, just show an alert
        alert(itemName + ' added to order!');
        
        // In a real application, you would use AJAX to add the item to the order
        // $.post('add_to_order.php', { item_id: itemId, quantity: 1 }, function(response) {
        //     // Handle response
        // });
    });
    
    // Delete confirmation
    $('.btn-delete').on('click', function(e) {
        if (!confirm('Are you sure you want to delete this item?')) {
            e.preventDefault();
        }
    });
    
    // Form validation
    (function() {
        'use strict';
        
        // Fetch all forms we want to apply validation styles to
        var forms = document.querySelectorAll('.needs-validation');
        
        // Loop over them and prevent submission
        Array.prototype.slice.call(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                form.classList.add('was-validated');
            }, false);
        });
    })();
});