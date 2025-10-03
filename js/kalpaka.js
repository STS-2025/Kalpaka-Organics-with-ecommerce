var sidenav = document.querySelector(".side-navbar");
var productContainer = document.getElementById("product");
var search = document.getElementById("search");
var productlist = productContainer.querySelectorAll(".product-1");

const loginlink = document.getElementById("loginlink");
const registerlink = document.getElementById("registerlink");
const loginForm = document.getElementById("loginForm");
const registerForm = document.getElementById("registerForm");

const forgotPasswordLink = document.getElementById("forgotPasswordLink");
const forgotForm = document.getElementById("forgotForm");
const backToLogin = document.getElementById("backToLogin");




loginlink.addEventListener('click',function(){
  loginForm.style.display="none";
  registerForm.style.display="block";
})
registerlink.addEventListener('click',function(){
  loginForm.style.display="block";
  registerForm.style.display="none";
})

//Forgot Password//

forgotPasswordLink.addEventListener('click',function(){
  loginForm.style.display="none";
  forgotForm.style.display="block";
})
backToLogin.addEventListener('click',function(){
  loginForm.style.display="block";
  forgotForm.style.display="none";
})


function forgotPassword(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

        // Get the button element
    const submitBtn = form.querySelector("button[type='submit']");
    const originalText = submitBtn.textContent;

    // Disable button and show "Sending..."
    submitBtn.disabled = true;
    submitBtn.textContent = "Sending...";

    fetch('../php/forgot_password.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(msg => {
        alert(msg); // e.g., "Reset link sent to your email."
        form.reset();
        document.getElementById("forgotForm").style.display = "none";
        document.getElementById("loginForm").style.display = "block";
    })
    
    .catch(err => {
        console.error("Forgot password error:", err);
        alert("Something went wrong.");
    })
    .finally(() => {
        // Re-enable button and restore text
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });

    return false;
}




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


search.addEventListener("keyup",function(e){
    var enterdValue = e.target.value.toUpperCase()

    for( let count=0;count<productlist.length;count++)
    {
        var productName = productlist[count].querySelector("h3").textContent.toUpperCase();

        if(productName.indexOf(enterdValue)<0)
        {
            productlist[count].style.display="none"
        }
        else{
            productlist[count].style.display="block"
        }
    }
})



function toggleLoginForm() {
  const loginForm = document.getElementById('loginForm');
  loginForm.style.display = (loginForm.style.display === 'block') ? 'none' : 'block';
}

// Add this to kalpaka.js


function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email.toLowerCase());
}


document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('registerForm');
  if (form) form.addEventListener('submit', registerUser);
});

function registerUser(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const email = formData.get('email');

    if (!isValidEmail(email)) {
        showRegisterMessage("❌ Please enter a valid email address.");
        return false;
    }

    fetch('../php/register.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // ✅ Show popup then redirect
            alert(data.message);
            window.location.href = "shop.html";
        } else {
            // Show error below form
            showRegisterMessage(data.message || 'Registration failed.');
        }
    })
    .catch(error => {
        showRegisterMessage('Registration failed. Please try again.');
    });

    return false;
}


// Helper function to show message
function showRegisterMessage(message) {
    let msgDiv = document.getElementById('register-message');
    if (!msgDiv) {
        msgDiv = document.createElement('div');
        msgDiv.id = 'register-message';
        msgDiv.style.color = 'red';
        msgDiv.style.marginBottom = '10px';
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
        body: formData,
        credentials: 'include',        // <<< critical for PHP sessions
        cache: 'no-store',
        headers: { 'X-Requested-With': 'fetch' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.href = data.redirect;
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

function openCart() {
    let user = localStorage.getItem("loggedInUser");
    if (user) {
        window.location.href = "cart.html";
    } else {
        alert("Login required to view your cart.");
        window.location.href = "shop.html";
    }
}
function openOrder() {
    let user = localStorage.getItem("loggedInUser");
    if (user) {
        window.location.href = "orders.html";
    } else {
        alert("Login required to view orders.");
        window.location.href = "shop.html";
    }
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
