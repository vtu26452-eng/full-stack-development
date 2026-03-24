// Form validation
document.addEventListener('DOMContentLoaded', function() {
    // Product form validation
    const productForm = document.getElementById('productForm');
    if(productForm) {
        productForm.addEventListener('submit', function(e) {
            const sku = document.getElementById('sku').value;
            const name = document.getElementById('name').value;
            const unitPrice = document.getElementById('unit_price').value;
            const sellingPrice = document.getElementById('selling_price').value;

            if(!sku || !name || !unitPrice || !sellingPrice) {
                e.preventDefault();
                alert('Please fill in all required fields');
                return false;
            }

            if(parseFloat(unitPrice) > parseFloat(sellingPrice)) {
                if(!confirm('Unit price is higher than selling price. Continue anyway?')) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    }

    // Stock movement form validation
    const stockForm = document.getElementById('stockInForm');
    if(stockForm) {
        stockForm.addEventListener('submit', function(e) {
            const quantity = document.getElementById('quantity').value;
            
            if(quantity <= 0) {
                e.preventDefault();
                alert('Quantity must be greater than 0');
                return false;
            }
        });
    }

    // Real-time search functionality
    const searchInput = document