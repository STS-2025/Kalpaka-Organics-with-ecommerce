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


//CART js
function  loadCart()
   {
    fetch('../php/get_cart.php', { credentials: 'include' })
    .then(res => res.json())
    .then(items => {
        const cartBox = document.getElementById('cartItems');
        const totalBox = document.getElementById('cartTotal');
        const cartCountEl = document.getElementById('cart-count');
        cartBox.innerHTML = '';

        if (items.length === 0) {
            cartBox.innerHTML = "<p>Your cart is empty.</p>";
            totalBox.textContent = "Total: ₹0";
            cartCountEl.textContent = 0;
            return;
        }

        let total = 0;
        let productCount = 0;
        items.forEach(item => {
            let itemTotal = item.price * item.quantity;
            total += itemTotal;
            productCount++;
            cartBox.innerHTML += `
                <div class="cart-item">
    <div class="cart-items-container">
        <img src="${item.product_image || 'default.jpg'}" alt="${item.product_name}">
        
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

    <!-- ✅ details now outside, below the flex row -->
    <div class="cart-details">
        <h4>${item.product_name}</h4>
        <small>Size: ${item.package_size || '-'}</small>
    </div>
</div>

            `;
        });

        totalBox.textContent = "Total: ₹" + total.toFixed(2);
        cartCountEl.textContent = productCount;
    })
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


/*
async function goCheckoutFromCart() {
  const res = await fetch('../php/checkout_from_cart.php', { method:'POST', credentials:'include' }).then(r=>r.json());
 /* if (!res.success) return alert(res.message || 'Could not prepare checkout');
  window.location.href = 'checkout.html';
}*

if (!res.success && res.out_of_stock) {
    let msg = "The following items are out of stock:\n\n";
    res.out_of_stock.forEach(item => {
        msg += `${item.product_name} (${item.package_size}) - Available: ${item.available}, Requested: ${item.requested}\n`;
    });
    alert(msg);
}
}*/

async function goCheckoutFromCart() {
    const res = await fetch('../php/checkout_from_cart.php', { 
        method:'POST', 
        credentials:'include' 
    }).then(r => r.json());

    if (!res.success && res.out_of_stock) {
    let msg = "The following items are out of stock:\n\n";
    res.out_of_stock.forEach(item => {
        msg += `${item.product_name} (${item.package_size}) - Available: ${item.available}, Requested: ${item.requested}\n`;
    });
    alert(msg);
} else if (res.success) {
    window.location.href = 'checkout.html';
}
}

