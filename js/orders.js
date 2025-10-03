var sidenav = document.querySelector(".side-navbar");
var search = document.getElementById("search");

function showNavbar()
{
    sidenav.style.left="0%"
}

function closeNavbar()
{
    sidenav.style.left="-60%"
}

function goToPage(page){
    window.location.href=page;
}
function goToBack(){
    window.location.href="product.html";
}




function toggleLoginForm() {
  const loginForm = document.getElementById('loginForm');
  loginForm.style.display = (loginForm.style.display === 'block') ? 'none' : 'block';
}

// ✅ Email validation helper
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email.toLowerCase());
}


function registerUser(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const email = formData.get('email');

    // ✅ Check email format before sending
    if (!isValidEmail(email)) {
        alert("❌ Please enter a valid email address.");
        return false;
    }

    fetch('../php/register.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        // Display the response message in the register form
        showRegisterMessage(data);
        if (data.toLowerCase().includes('Registration successful. Please login.') || data.toLowerCase().includes('please login')) {
            form.reset();
            // show login form, hide register form (if you want)
            if (document.getElementById('loginForm')) document.getElementById('loginForm').style.display = 'block';
            if (document.getElementById('registerForm')) document.getElementById('registerForm').style.display = 'none';
        }
    })
    .catch(error => {
        showRegisterMessage('Registration failed. Please try again.');
    });

    return false; // Prevent default form submission
}

// Helper function to show message
function showRegisterMessage(message) {
    let msgDiv = document.getElementById('register-message');
    if (!msgDiv) {
        msgDiv = document.createElement('div');
        msgDiv.id = 'register-message';
        msgDiv.style.color = 'red';
        document.querySelector('#registerForm form').prepend(msgDiv);
    }
    msgDiv.innerHTML = message;
}


// Handle login success with SQL Server
function loginUser(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    fetch('../php/login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            localStorage.setItem('loggedInUser', data.username);
              localStorage.setItem('profilePic', data.profile_pic || 'default-profile.png');
            document.getElementById('loginForm').style.display = 'none';
            showProfileBox();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        alert('Login failed. Please try again.');
        console.error(error);
    });
    return false;
}


// Toggle profile menu or login form
document.getElementById('userMenuToggle').addEventListener('click', function() {
    if (localStorage.getItem('loggedInUser')) {
        toggleProfileBox();
    } else {
        toggleLoginForm();
    }
});

function toggleProfileBox() {
    const box = document.getElementById('profileBox');
    box.style.display = (box.style.display === 'block') ? 'none' : 'block';
}

// Show profile with username
function showProfileBox() {
    const username = localStorage.getItem('loggedInUser') || 'Guest';
    const profilePic = 'default-profile.png';
    document.getElementById('profileUsername').textContent = username;
    document.querySelector('#profileBox .profile-pic').src = profilePic;
}

function logoutUser() {
    fetch('../php/logout.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                localStorage.removeItem('loggedInUser');
                document.getElementById('profileBox').style.display = 'none';
                alert(data.message); // ✅ popup shows
                window.location.href = "shop.html"; // ✅ redirect after OK
            } else {
                alert("Logout failed. Try again.");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("Something went wrong.");
        });
}


// On page load, check login
window.onload = function() {
    fetch('../php/checkLogin.php')
        .then(res => res.json())
        .then(data => {
            if (data.loggedIn) {
                localStorage.setItem('loggedInUser', data.username);
                showProfileBox();
            }
        });
};

/*function searchOrders(event) {
    event.preventDefault(); 

    const dateInput = document.getElementById('orderDate').value; // YYYY-MM-DD

    loadOrders(dateInput); 
}*/

function searchOrders(event) {
    event.preventDefault();

    const fromDate = document.getElementById('fromDate').value;
    const toDate = document.getElementById('toDate').value;

    if (!fromDate || !toDate) {
        alert("Please select both From and To dates.");
        return;
    }

    loadOrders(fromDate, toDate);
}


const fetchJSON = (url, options={}) =>
  fetch(url, { credentials:'include', ...options }).then(r => r.json());

/*async function loadOrders() {
  const box = document.getElementById('ordersList');
  box.innerHTML = "<p>Loading orders…</p>";
  const orders = await fetchJSON('php/get_orders.php');

  box.innerHTML = "";
  if (!orders || orders.length === 0) {
    box.innerHTML = "<p>You have no orders yet.</p>";
    return;
  }

  orders.forEach(o => {
    const div = document.createElement('div');
    div.className = 'order-card';
    div.innerHTML = `
      <div><strong>Order #${o.id}</strong> — ${new Date(o.created_at).toLocaleString()}</div>
      <div>Status: ${o.order_status} | Payment: ${o.payment_status} (${o.payment_method})</div>
      <div>Total: ₹${o.total_amount}</div>
      <div class="order-actions">
        <button onclick="viewItems(${o.id})">View Items</button>
        ${o.order_status.toLowerCase() === 'Placed' || o.order_status.toLowerCase() === 'processing' 
        ? `<button onclick="cancelOrder(${o.id})" class="cancel-btn">Cancel Order</button>`:''}
      </div>
    `;
    box.appendChild(div);
  });
}*/
async function loadOrders(from = '',to='') {
    const box = document.getElementById('ordersList');
    box.innerHTML = "<p>Loading orders…</p>";

    let url = '../php/get_orders.php';
    if (from && to) url += `?from=${from}&to=${to}`; // Add date filter

    const orders = await fetchJSON(url);

    box.innerHTML = "";
    if (!orders || orders.length === 0) {
        box.innerHTML = `<p>No orders found between ${from} and ${to}.</p>`;
        return;
    }

    orders.forEach(o => {
        const div = document.createElement('div');
        div.className = 'order-card';
        div.innerHTML = `
            <div><strong>Order #${o.id}</strong> —${new Date(o.created_at).toLocaleString()}</div>
            <div>Status: ${o.order_status} | Payment: ${o.payment_status} (${o.payment_method})</div>
            <div>Delivery: ${o.delivery_status}</div>
            <div>Reason: ${o.cancelled_reason}</div>
            <div>Total: ₹${o.total_amount}</div>
            <div class="order-actions flex flex-col sm:flex-row gap-3 mt-3">

            <button onclick="viewItems(${o.id})" class="px-4 py-2 rounded-md font-medium text-white transition duration-300 ease-in-out transform hover:scale-105 w-full sm:w-auto"
    style="background-color: var(--color-primary-green);"
    onmouseover="this.style.backgroundColor='var(--color-secondary-green)'"
    onmouseout="this.style.backgroundColor='var(--color-primary-green)'">
    View Items</button>
            ${o.delivery_status.toLowerCase() !== 'delivered' && o.order_status.toLowerCase() !== 'cancelled'
            ? `<button onclick="cancelOrder(${o.id})"  class="cancel-btn px-4 py-2 rounded-md font-medium text-white transition duration-300 ease-in-out transform hover:scale-105 w-full sm:w-auto"
    style="background-color: var(--color-primary-green);"
    onmouseover="this.style.backgroundColor='var(--color-secondary-green)'"
    onmouseout="this.style.backgroundColor='var(--color-primary-green)'">
        Cancel Order</button>`:''}
            </div>
        `;
        box.appendChild(div);
    });
}
/*function resetSearch() {
    const dateInput = document.getElementById('orderDate');
    dateInput.value = ''; // Clear the input
    loadOrders(); // Reload all orders without filter
}*/
function resetSearch() {
    document.getElementById('fromDate').value = '';
    document.getElementById('toDate').value = '';
    loadOrders(); // Load all orders again
}



async function viewItems(orderId) {
  const data = await fetchJSON('../php/get_order_items.php?order_id=' + orderId);
  const box = document.getElementById('orderItems');
  box.innerHTML = "";

  // Ensure items are always an array
  const items = Array.isArray(data.items) ? data.items : [];

  items.forEach(it => {
    const line = document.createElement('div');
    line.className = "order-line";
    line.innerHTML = `
      <img src="${it.product_image || 'default.jpg'}">
      <div>
        <div><strong>${it.product_name}</strong></div>
        <div>Size: ${it.package_size || '-'}</div>
        <div>Qty: ${it.quantity}</div>
      </div>
      <div>₹${(it.price * it.quantity).toFixed(2)}</div>
    `;
    box.appendChild(line);
  });

  // Add totals if available
  if (data.subtotal !== undefined) {
    const summary = document.createElement('div');
    summary.className = "order-summary";
    summary.innerHTML = `
      <hr>
      <div>Subtotal: ₹${Number(data.subtotal).toFixed(2)}</div>
      <div>Delivery Fee: ₹${Number(data.delivery_fee).toFixed(2)}</div>
      <div><strong>Total: ₹${Number(data.total_amount).toFixed(2)}</strong></div>
    `;
    box.appendChild(summary);
  }

  document.getElementById('orderItemsBox').classList.remove('hidden');
}

document.getElementById('closeModal').onclick = () =>
  document.getElementById('orderItemsBox').classList.add('hidden');

loadOrders();


function cancelOrder(orderId) {
  if (!confirm("Are you sure you want to cancel this order?")) return;

  fetch('../php/cancel_order.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `order_id=${orderId}`
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert("✅ Order cancelled successfully.");
      loadOrders(); // refresh orders list
    } else {
      alert("❌ " + (data.message || "Failed to cancel order."));
    }
  })
  .catch(err => {
    console.error("Cancel failed:", err);
    alert("Something went wrong. Please try again.");
  });
}





function loadCart() {
    fetch('../php/get_cart.php', { credentials: 'include' })
    .then(res => res.json())
    .then(items => {
        const cartBox = document.getElementById('cartItems');
        const totalBox = document.getElementById('cartTotal');
        const cartCountEl = document.getElementById('cart-count');

        // ✅ Always update count (works on all pages)
        if (cartCountEl) {
            cartCountEl.textContent = items.length;
        }

        // ✅ Only run full cart rendering if cartBox/totalBox exist
        if (!cartBox || !totalBox) return;

        cartBox.innerHTML = '';
        if (items.length === 0) {
            cartBox.innerHTML = "<p>Your cart is empty.</p>";
            totalBox.textContent = "Total: ₹0";
            return;
        }

        let total = 0;
        items.forEach(item => {
            let itemTotal = item.price * item.quantity;
            total += itemTotal;
            cartBox.innerHTML += `
                <div class="cart-item">
                    <img src="${item.product_image || 'default.jpg'}" alt="${item.product_name}">
                    <div class="cart-details">
                        <h4>${item.product_name}</h4>
                        <small>Size: ${item.package_size || '-'} </small>
                    </div>
                    <div class="qty-control">
                        <button onclick="updateQty(${item.id}, -1)">-</button>
                        ${item.quantity}
                        <button class="plus" onclick="updateQty(${item.id}, 1)">+</button>
                    </div>
                    <div class="cart-price">₹${Number(item.price).toFixed(2)}</div>
                    <button class="remove-btn" onclick="removeItem(${item.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
        });

        totalBox.textContent = "Total: ₹" + total.toFixed(2);
    })
    .catch(err => console.error("Cart load failed:", err));
}


function updateQty(id, change) {
    fetch('../php/update_cart.php', {
        method: 'POST',
        body: new URLSearchParams({ id, change })
    })
    .then(res => res.json())
    .then(() => loadCart());
}

function removeItem(id) {
    fetch('../php/remove_cart.php', {
        method: 'POST',
        body: new URLSearchParams({ id })
    })
    .then(res => res.json())
    .then(() => loadCart());
}
// Load cart on page start
loadCart();
