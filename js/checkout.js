var sidenav = document.querySelector(".side-navbar");

const loginlink = document.getElementById("loginlink");
const registerlink = document.getElementById("registerlink");
const loginForm = document.getElementById("loginForm");
const registerForm = document.getElementById("registerForm");


loginlink.addEventListener('click',function(){
  loginForm.style.display="none";
  registerForm.style.display="block";
})
registerlink.addEventListener('click',function(){
  loginForm.style.display="block";
  registerForm.style.display="none";
})





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

// Validate UPI ID format
function validateUPI(upiId) {
    const upiRegex = /^[a-zA-Z0-9.\-_]{2,256}@[a-zA-Z]{2,64}$/;
    return upiRegex.test(upiId);
}

// Validate phone number (10 digits, starting with 6–9)
function isValidPhone(phone) {
    const phoneRegex = /^[6-9]\d{9}$/;
    return phoneRegex.test(phone);
}



function registerUser(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const email = formData.get('email');

    // ✅ Check email format before sending
    if (!isValidEmail(email)) {
        showRegisterMessage("❌ Please enter a valid email address.");
        return false;
    }

    fetch('../php/register.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.data())
    .then(data => {
        // Display the response message in the register form
        showRegisterMessage(text);
        if (data.toLowerCase().includes('Registration successful. Please login') || data.toLowerCase().includes('please login')) {
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




const fetchJSON = (url , options = {}) =>
  fetch(url, { credentials: 'include', ...options }).then(r => r.json());


const panes = {
  1: document.getElementById('step1'),
  2: document.getElementById('step2'),
  3: document.getElementById('step3')
};
const stepTabs = document.querySelectorAll('.step');

function showStep(n) {
  Object.values(panes).forEach(p => p.classList.remove('active'));
  panes[n].classList.add('active');
  stepTabs.forEach(t => t.classList.toggle('active', t.dataset.step == n));
}


let selectedAddressId = null;

async function loadAddresses() {
  const list = document.getElementById('savedAddresses');
  list.innerHTML = '<p>Loading addresses…</p>';
  const data = await fetchJSON('../php/get_addresses.php');
  list.innerHTML = '';

  if (!data || data.length === 0) {
    list.innerHTML = '<p>No saved addresses yet. Please add one.</p>';
    document.getElementById('toSummary').disabled = true;
    return;
  }

  data.forEach(a => {
    const card = document.createElement('div');
    card.className = 'addr-card';
    card.dataset.id = a.id;
    card.innerHTML = `
      <!--<div><strong>${a.full_name}</strong> ${a.is_default ? '(Default)' : ''}</div>
      <div>${a.phone}</div>
      <div>${a.address_line1}</div>
      <div>${a.address_line2 || ''}</div>
      <div>${a.city}, ${a.state} ${a.postal_code}</div>-->
      <div 
  class="p-4 mb-3 rounded-lg shadow-sm border transition hover:shadow-md"
  style="background-color: var(--color-gray-50); color: var(--color-text-dark); border-color: var(--color-tertiary-green);"
>
  <div class="flex items-center justify-between">
    <strong class="text-lg font-semibold">${a.full_name}</strong>
    ${a.is_default ? `<span class="ml-2 px-2 py-1 text-xs rounded-md" 
      style="background-color: var(--color-primary-green); color: var(--color-white);">
      Default
    </span>` : ""}
  </div>
  
  <div class="text-sm mt-1" style="color: var(--color-text-light);">${a.phone}</div>
  <div class="text-sm">${a.address_line1}</div>
  <div class="text-sm">${a.address_line2 || ''}</div>
  <div class="text-sm">${a.city}, ${a.state} ${a.postal_code}</div>
</div>

      <div class="addr-actions">
        <!--<button type="button" class="select-btn">Select</button>
        <button type="button" class="edit-btn">Edit</button>
        <button type="button" class="del-btn">Delete</button>-->
      <div class="flex gap-3 flex-wrap">
  <!-- Select Button -->
  <button 
    type="button" 
    class="select-btn px-4 py-2 rounded-lg font-medium text-white 
           transition duration-300 ease-in-out transform hover:scale-100"
    style="background-color: var(--color-primary-green);"
    onmouseover="this.style.backgroundColor='var(--color-secondary-green)'"
    onmouseout="this.style.backgroundColor='var(--color-primary-green)'">
    Select
  </button>

  <!-- Edit Button -->
  <button 
    type="button" 
    class="edit-btn px-4 py-2 rounded-lg font-medium text-white 
           transition duration-300 ease-in-out transform hover:scale-100"
    style="background-color: var(--color-primary-green);"
    onmouseover="this.style.backgroundColor='var(--color-secondary-green)'"
    onmouseout="this.style.backgroundColor='var(--color-primary-green)'">
    Edit
  </button>

  <!-- Delete Button -->
  <button 
    type="button" 
    class="del-btn px-4 py-2 rounded-lg font-medium text-white 
           transition duration-300 ease-in-out transform hover:scale-100"
    style="background-color: var(--color-primary-green);"
    onmouseover="this.style.backgroundColor='var(--color-secondary-green)'"
    onmouseout="this.style.backgroundColor='var(--color-primary-green)'">
    Delete
  </button>
</div>


      </div>
    `;
    card.querySelector('.select-btn').onclick = () => {
      document.querySelectorAll('.addr-card').forEach(c => c.classList.remove('selected'));
      card.classList.add('selected');
      selectedAddressId = a.id;
      document.getElementById('toSummary').disabled = false;
    };
    card.querySelector('.edit-btn').onclick = () => fillAddressForm(a);
    card.querySelector('.del-btn').onclick = async () => {
      if (!confirm('Delete this address?')) return;
      await fetchJSON('../php/delete_address.php', {
        method: 'POST',
        body: new URLSearchParams({ id: a.id })
      });
      await loadAddresses();
    };
    list.appendChild(card);
    if (a.is_default && !selectedAddressId) {
      // auto-select default
      card.querySelector('.select-btn').click();
    }
  });
}

function fillAddressForm(a) {
  document.getElementById('addr_id').value = a.id;
  document.getElementById('full_name').value = a.full_name;
  document.getElementById('phone').value = a.phone;
  document.getElementById('address_line1').value = a.address_line1;
  document.getElementById('address_line2').value = a.address_line2 || '';
  document.getElementById('city').value = a.city;
  document.getElementById('state').value = a.state;
  document.getElementById('postal_code').value = a.postal_code;
  document.getElementById('is_default').checked = !!a.is_default;
}

// ✅ Phone number validation helper (10–15 digits, optional +)
function isValidPhone(phone) {
    const re = /^\+?\d{10,15}$/;
    return re.test(phone);
}

document.getElementById('addressForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  const id = fd.get('id');
  const url = id ? '../php/update_address.php' : '../php/save_address.php';
  const res = await fetchJSON(url, { method: 'POST', body: fd });
  alert(res.message || (res.success ? 'Saved' : 'Failed'));
  e.target.reset();
  document.getElementById('addr_id').value = '';
  await loadAddresses();
});

document.getElementById('resetAddr').onclick = () => {
  document.getElementById('addressForm').reset();
  document.getElementById('addr_id').value = '';
};


document.getElementById('toSummary').onclick = async () => {
  if (!selectedAddressId) return alert('Please select an address');
  await loadCheckoutItems();
  showStep(2);
};


let totals = { subtotal: 0, delivery: 0, total: 0 };

async function loadCheckoutItems() {
  const box = document.getElementById('orderItems');
  box.innerHTML = '<p>Loading items…</p>';
  const items = await fetchJSON('../php/get_checkout_items.php');

  box.innerHTML = '';
  if (!items || items.length === 0) {
    box.innerHTML = '<p>Your checkout is empty.</p>';
    document.getElementById('subtotal').textContent = '0';
    document.getElementById('delivery').textContent = '0';
    document.getElementById('grandTotal').textContent = '0';
    return;
  }

  let subtotal = 0;
  items.forEach(it => {
    const line = document.createElement('div');
    line.className = 'order-line';
    const img = it.product_image || 'default.jpg';
    line.innerHTML = `
      <img src="${img}" alt="">
      <div>
        <div><strong>${it.product_name}</strong></div>
        <div>Size: ${it.package_size || '-'}</div>
        <div>Qty: ${it.quantity}</div>
      </div>
      <div>₹${(it.price * it.quantity).toFixed(2)}</div>
    `;
    box.appendChild(line);
    subtotal += it.price * it.quantity;
  });

  const delivery = subtotal >= 999 ? 0 : 40; // free delivery above 999
  const total = subtotal + delivery;

  totals = { subtotal, delivery, total };
  document.getElementById('subtotal').textContent = subtotal.toFixed(2);
  document.getElementById('delivery').textContent = delivery.toFixed(2);
  document.getElementById('grandTotal').textContent = total.toFixed(2);
}


document.querySelectorAll('.back').forEach(b => {
  b.addEventListener('click', () => showStep(Number(b.dataset.back)));
});


document.getElementById('toPayment').onclick = () => showStep(3);
document.getElementById('placeOrderBtn').onclick = async (e) => {
  const btn = e.target;
  btn.disabled = true;
  btn.textContent = "Placing Order..."; // show waiting state

  // Get selected payment method
  let method = document.querySelector('input[name="payMethod"]:checked')?.value;

  // If nothing selected, stop here
  if (!method) {
    alert("⚠️ Please select a payment method");
    btn.disabled = false;
    btn.textContent = "Place Order";
    return;
  }
  
    //now change 
  const upi_id = document.getElementById('upiId')?.value.trim();
  const phone = document.getElementById('payment_phone')?.value.trim();

  // Only check if UPI is selected
  /*if (method === 'UPI') {
    if (!upi_id || !phone) {
      e.preventDefault(); // Stop form submission
      alert("Please enter both your UPI ID and Phone Number.");
      btn.disabled = false;
      btn.textContent = "Place Order";
      return; // Stop the function here
    } */

  if (method === 'UPI') {
  if (!validateUPI(upi_id)) {
    e.preventDefault();
    alert("❌ Invalid UPI ID format (example: name@upi)");
    btn.disabled = false;
    btn.textContent = "Place Order";
    return;
  }
  if (!isValidPhone(phone)) {
    e.preventDefault();
    alert("❌ Invalid Phone Number");
    btn.disabled = false;
    btn.textContent = "Place Order";
    return;
  }
}

    
   
   //my change


  // Extra: validation for Card fields
  if (method === 'Card') {
    if (!document.getElementById('cardNumber').value.trim()) {
      alert('Enter card number'); 
      btn.disabled = false; btn.textContent = "Place Order"; return;
    }
    if (!document.getElementById('cardExp').value.trim()) {
      alert('Enter expiry (MM/YY)');
      btn.disabled = false; btn.textContent = "Place Order"; return;
    }
    if (!document.getElementById('cardCVV').value.trim()) {
      alert('Enter CVV');
      btn.disabled = false; btn.textContent = "Place Order"; return;
    }
  }

  // Validate address selection
  if (!selectedAddressId) {
    alert('Select an address');
    btn.disabled = false; 
    btn.textContent = "Place Order"; 
    return;
  }

  try {
    const upi_Id = document.getElementById('upiId').value.trim();
    const phone_Number = document.getElementById('payment_phone').value.trim();
    const res = await fetchJSON('../php/place_order.php', {
      method: 'POST',
      body: new URLSearchParams({
        address_id: selectedAddressId,
        payment_method: method,
        upiId: upi_Id,
        payment_phone: phone_Number
      })
    });

    if (!res.success) {
      alert(res.message || 'Order failed');
      btn.disabled = false; 
      btn.textContent = "Place Order"; 
      return;
    }

    alert('✅ Order placed Successfully');
    window.location.href = 'orders_thankyou.html';
  } catch (err) {
    alert("Error placing order: " + err.message);
    btn.disabled = false; 
    btn.textContent = "Place Order"; 
  }
};
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
