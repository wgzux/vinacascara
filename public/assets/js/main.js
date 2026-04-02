// =========================================
// VINA CASCARA - MAIN JAVASCRIPT
// =========================================

// ---- NAVBAR SCROLL EFFECT ----
window.addEventListener('scroll', () => {
  const navbar = document.getElementById('navbar');
  if (navbar) navbar.classList.toggle('scrolled', window.scrollY > 20);
});

// ---- MOBILE NAV ----
function toggleMobileNav() {
  const nav = document.getElementById('navLinks');
  nav.classList.toggle('open');
}

// ---- USER DROPDOWN ----
function toggleUserMenu() {
  document.getElementById('userMenu')?.classList.toggle('show');
}
document.addEventListener('click', (e) => {
  if (!e.target.closest('#userDropdown')) {
    document.getElementById('userMenu')?.classList.remove('show');
  }
});

// ---- CART FUNCTIONS ----
async function addToCart(productId, qty = 1) {
  const btn = document.querySelector(`[data-product-id="${productId}"]`);
  const originalText = btn?.innerHTML;
  if (btn) { btn.disabled = true; btn.innerHTML = '<span>Đang thêm...</span>'; }

  try {
    const form = new FormData();
    form.append('product_id', productId);
    form.append('quantity', qty);

    const res = await fetch('/api/cart/add', { method: 'POST', body: form });
    const data = await res.json();

    if (data.success) {
      updateCartBadge(data.cart_count);
      showToast(data.message, 'success');
    } else {
      showToast(data.message || 'Không thể thêm sản phẩm.', 'error');
    }
  } catch (err) {
    showToast('Lỗi kết nối. Vui lòng thử lại.', 'error');
  } finally {
    if (btn && originalText) { btn.disabled = false; btn.innerHTML = originalText; }
  }
}

async function updateQty(cartId, qty) {
  const form = new FormData();
  form.append('cart_id', cartId);
  form.append('quantity', qty);

  try {
    const res = await fetch('/api/cart/update', { method: 'POST', body: form });
    const data = await res.json();

    if (data.success) {
      updateCartBadge(data.cart_count);
      if (qty <= 0) {
        document.getElementById(`cartItem-${cartId}`)?.remove();
      } else {
        const qtyEl = document.querySelector(`#cartItem-${cartId} .qty-display`);
        if (qtyEl) qtyEl.textContent = qty;
      }
      const sub = document.getElementById('subtotalDisplay');
      const total = document.getElementById('totalDisplay');
      const ship = document.getElementById('shippingDisplay');
      if (sub) sub.textContent = data.subtotal;
      if (total) total.textContent = data.total;
      if (ship) ship.textContent = data.shipping;

      if (data.cart_count === 0) location.reload();
    }
  } catch (err) { showToast('Lỗi kết nối.', 'error'); }
}

async function removeCartItem(cartId) {
  const form = new FormData();
  form.append('cart_id', cartId);

  try {
    const res = await fetch('/api/cart/remove', { method: 'POST', body: form });
    const data = await res.json();
    if (data.success) {
      document.getElementById(`cartItem-${cartId}`)?.remove();
      updateCartBadge(data.cart_count);
      const sub = document.getElementById('subtotalDisplay');
      const total = document.getElementById('totalDisplay');
      if (sub) sub.textContent = data.subtotal;
      if (total) total.textContent = data.total;
      if (data.cart_count === 0) location.reload();
    }
  } catch (err) { showToast('Lỗi kết nối.', 'error'); }
}

function updateCartBadge(count) {
  let badge = document.querySelector('.cart-badge');
  const cartEl = document.querySelector('.nav-cart');
  if (count > 0) {
    if (!badge) {
      badge = document.createElement('span');
      badge.className = 'cart-badge';
      cartEl?.appendChild(badge);
    }
    badge.textContent = count;
    badge.style.animation = 'none';
    badge.offsetHeight; // force reflow
    badge.style.animation = 'cartPop 0.3s cubic-bezier(0.68,-0.55,0.27,1.55)';
  } else {
    badge?.remove();
  }
}

// ---- TOAST NOTIFICATIONS ----
function showToast(message, type = 'success') {
  const container = document.getElementById('toastContainer') || createToastContainer();
  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.innerHTML = `<span>${message}</span><button onclick="this.parentElement.remove()">×</button>`;
  container.appendChild(toast);
  setTimeout(() => toast.style.opacity = '1', 10);
  setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 3500);
}

function createToastContainer() {
  const el = document.createElement('div');
  el.id = 'toastContainer';
  el.style.cssText = 'position:fixed;top:80px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:10px;';
  document.body.appendChild(el);
  return el;
}

// ---- SMOOTH SCROLL ----
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function(e) {
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      e.preventDefault();
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  });
});

// ---- FLASH AUTO DISMISS ----
document.addEventListener('DOMContentLoaded', () => {
  const flash = document.getElementById('flashMsg');
  if (flash) setTimeout(() => { flash.style.opacity = '0'; setTimeout(() => flash?.remove(), 300); }, 5000);
});

// ---- CONTACT FORM AJAX ----
document.getElementById('contactForm')?.addEventListener('submit', async function(e) {
  e.preventDefault();
  const btn = this.querySelector('button[type=submit]');
  btn.disabled = true; btn.textContent = 'Đang gửi...';
  try {
    const res = await fetch('/api/contact.php', { method: 'POST', body: new FormData(this) });
    showToast('Cảm ơn! Chúng tôi sẽ liên hệ bạn sớm.', 'success');
    this.reset();
  } catch (err) {
    showToast('Lỗi gửi form. Vui lòng thử lại.', 'error');
  }
  btn.disabled = false; btn.textContent = 'Gửi yêu cầu tư vấn';
});

// ---- ANIMATIONS ON SCROLL (Intersection Observer) ----
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('animate-in');
      observer.unobserve(entry.target);
    }
  });
}, { threshold: 0.1 });

document.querySelectorAll('.product-card, .testimonial-card, .feature-item')
  .forEach(el => { el.style.opacity = '0'; el.style.transform = 'translateY(20px)'; observer.observe(el); });

// Toast CSS injected dynamically
const toastStyle = document.createElement('style');
toastStyle.textContent = `
.toast { background: white; border-radius: 10px; padding: 14px 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); display: flex; align-items: center; gap: 12px; min-width: 280px; opacity: 0; transition: opacity 0.3s; font-size: 0.875rem; }
.toast button { background: none; border: none; cursor: pointer; font-size: 1.2rem; opacity: 0.5; margin-left: auto; }
.toast-success { border-left: 4px solid #22c55e; }
.toast-error { border-left: 4px solid #ef4444; }
.toast-info { border-left: 4px solid #3b82f6; }
.animate-in { opacity: 1 !important; transform: translateY(0) !important; transition: opacity 0.5s ease, transform 0.5s ease !important; }
@keyframes cartPop { 0% { transform: scale(0.5); } 70% { transform: scale(1.2); } 100% { transform: scale(1); } }
`;
document.head.appendChild(toastStyle);
