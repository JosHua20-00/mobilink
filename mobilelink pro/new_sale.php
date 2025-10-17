<?php
// new_sale.php
session_start();
if (!isset($_SESSION['UserID'])) {
    header('Location: login.php');
    exit();
}
include 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Sale - MobileLink Pro</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .pos-container { display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; }
        .form-section, .summary-section { background-color: #fff; padding: 1.5rem; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #D1D5DB; }
        #product_search { margin-top: 1.5rem; }
        #cart_items thead { border-bottom: 2px solid var(--secondary-color, #ccc); }
        .summary-total { font-size: 1.5rem; font-weight: 700; text-align: right; margin-top: 1rem; }
        .btn-complete-sale { width: 100%; padding: 15px; font-size: 1.2rem; margin-top: 1rem; background-color: #10B981; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .btn-delete { background: red; color: white; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="sidebar-header">MobileLink Pro</div>
            <nav>
                <a href="index.php"><i class="fas fa-home"></i> Home</a>
                <a href="inventory.php"><i class="fas fa-box-open"></i> Inventory</a>
                <a href="new_sale.php" class="active"><i class="fas fa-cash-register"></i> Sales</a>
                <a href="customers.php"><i class="fas fa-users"></i> Customers</a>
                <a href="#"><i class="fas fa-chart-bar"></i> Reports</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="header"><h1>New Sale Transaction</h1></header>

            <form id="sale-form" action="process_sale.php" method="POST">
                <div class="pos-container">
                    <div class="form-section">
                        <h3>1. Customer & Product Selection</h3>
                        <div class="form-group">
                            <label for="customer_id">Select Customer</label>
                            <select id="customer_id" name="customer_id" required>
                                <option value="">-- Search for a customer --</option>
                            </select>
                        </div>
                        <div class="form-group" id="product_search">
                            <label for="product_id">Add Product to Cart</label>
                            <select id="product_id">
                                <option value="">-- Search for a product --</option>
                            </select>
                        </div>
                        <hr style="margin: 1.5rem 0;">
                        <h3>2. Items in Cart</h3>
                        <table id="cart_items" style="width: 100%;">
                            <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th><th></th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <div class="summary-section">
                        <h3>3. Sale Summary</h3>
                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select id="payment_method" name="payment_method" required>
                                <option value="Cash">Cash</option>
                                <option value="Card">Card</option>
                                <option value="Mobile Money">Mobile Money</option>
                            </select>
                        </div>
                        <div class="summary-total">
                            <span>TOTAL: ZMW</span>
                            <span id="grand_total">0.00</span>
                        </div>
                        <input type="hidden" name="total_amount" id="total_amount_hidden">
                        <button type="submit" class="btn-complete-sale">Complete Sale</button>
                    </div>
                </div>
            </form>
        </main>
    </div>

<script>
// --- GLOBAL VARIABLES ---
let cart = []; // Array to hold cart items: { id, name, price, quantity }

// --- DOM ELEMENTS ---
const customerSelect = document.getElementById('customer_id');
const productSelect = document.getElementById('product_id');
const cartTbody = document.querySelector('#cart_items tbody');
const grandTotalSpan = document.getElementById('grand_total');
const totalAmountHiddenInput = document.getElementById('total_amount_hidden');
const saleForm = document.getElementById('sale-form');

// --- FUNCTIONS ---

// Fetch customers and populate dropdown
async function fetchCustomers() {
    try {
        const response = await fetch('fetch_customers.php');
        const customers = await response.json();
        customers.forEach(customer => {
            const option = document.createElement('option');
            option.value = customer.CustomerID;
            option.textContent = `${customer.FirstName} ${customer.LastName} (${customer.PhoneNumber})`;
            customerSelect.appendChild(option);
        });
    } catch (error) {
        console.error('Failed to fetch customers:', error);
    }
}

// Fetch products and populate dropdown
async function fetchProducts() {
    try {
        const response = await fetch('fetch_products.php');
        const products = await response.json();
        products.forEach(product => {
            const option = document.createElement('option');
            option.value = product.ProductID;
            option.textContent = `${product.Brand} ${product.Model} (Stock: ${product.StockQuantity})`;
            option.dataset.price = product.SellingPrice;
            option.dataset.name = `${product.Brand} ${product.Model}`;
            option.dataset.stock = product.StockQuantity;
            productSelect.appendChild(option);
        });
    } catch (error) {
        console.error('Failed to fetch products:', error);
    }
}

// Add an item to the cart
function addToCart(productId, productName, productPrice, maxStock) {
    const existingItem = cart.find(item => item.id === productId);

    if (existingItem) {
        if (existingItem.quantity < maxStock) {
            existingItem.quantity++;
        } else {
            alert('Cannot add more than available stock.');
        }
    } else {
        if (maxStock > 0) {
            cart.push({ id: productId, name: productName, price: parseFloat(productPrice), quantity: 1 });
        } else {
            alert('This product is out of stock.');
        }
    }
    renderCart();
}

// Remove an item from the cart
function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    renderCart();
}

// Render the cart in the HTML table
function renderCart() {
    cartTbody.innerHTML = '';
    let total = 0;

    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        total += itemTotal;
        const row = `
            <tr>
                <td>${item.name}</td>
                <td>${item.quantity}</td>
                <td>${item.price.toFixed(2)}</td>
                <td>${itemTotal.toFixed(2)}</td>
                <td><button type="button" class="btn-delete" onclick="removeFromCart(${item.id})">&times;</button></td>
            </tr>`;
        cartTbody.insertAdjacentHTML('beforeend', row);
    });

    grandTotalSpan.textContent = total.toFixed(2);
    totalAmountHiddenInput.value = total.toFixed(2);
}

// --- EVENT LISTENERS ---

// Add product to cart when selected
productSelect.addEventListener('change', (e) => {
    const selectedOption = e.target.options[e.target.selectedIndex];
    if (!selectedOption.value) return;

    const productId = parseInt(selectedOption.value);
    const productName = selectedOption.dataset.name;
    const productPrice = selectedOption.dataset.price;
    const maxStock = parseInt(selectedOption.dataset.stock);

    addToCart(productId, productName, productPrice, maxStock);
    e.target.selectedIndex = 0; // Reset dropdown
});

// Add hidden inputs for cart items before form submission
saleForm.addEventListener('submit', (e) => {
    // Clear any previous hidden inputs
    document.querySelectorAll('input[name="products[]"]').forEach(el => el.remove());

    if (cart.length === 0) {
        e.preventDefault();
        alert('Cannot complete sale with an empty cart.');
        return;
    }
    
    if (!customerSelect.value) {
        e.preventDefault();
        alert('Please select a customer.');
        return;
    }

    cart.forEach(item => {
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'products[]';
        hiddenInput.value = JSON.stringify(item);
        saleForm.appendChild(hiddenInput);
    });
});

// --- INITIALIZATION ---
document.addEventListener('DOMContentLoaded', () => {
    fetchCustomers();
    fetchProducts();
});
</script>
<script src="api_interactions.js"></script>
</body>
</html>
