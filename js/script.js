/**
 * FARM TOOLS RENTAL PLATFORM - CLIENT JAVASCRIPT
 * Interactive validations, total price calculation, catalog filter,
 * and Shopping Cart (localStorage-backed)
 */

/* ============================================================
   SHOPPING CART - State Management & Helpers
   ============================================================ */

const CART_KEY = 'farmtools_cart';

function getCart() {
  try {
    return JSON.parse(localStorage.getItem(CART_KEY)) || [];
  } catch (e) {
    return [];
  }
}

function saveCart(cart) {
  localStorage.setItem(CART_KEY, JSON.stringify(cart));
}

function getCartImageUrl(imgName) {
  if (!imgName || imgName === 'null' || imgName === 'undefined' || imgName === '') {
    imgName = 'default_equipment.jpg';
  }
  // Strip any leading slashes or prefixes like ../ or images/equipment/
  const cleanName = imgName.replace(/^(\.\.\/)+/, '').replace(/^images\/equipment\//, '').replace(/^\/+/, '');
  
  // Detect if current page is inside /farmer/ or /admin/ subfolder
  const path = window.location.pathname;
  const isSubFolder = path.includes('/farmer/') || path.includes('/admin/');
  const prefix = isSubFolder ? '../images/equipment/' : 'images/equipment/';
  
  return prefix + cleanName;
}

function addToCart(id, name, price, image) {
  const cart = getCart();
  const existing = cart.find(item => item.id === id);
  // Clean image name
  const cleanImage = image ? image.replace(/^(\.\.\/)+/, '').replace(/^images\/equipment\//, '').replace(/^\/+/, '') : 'default_equipment.jpg';
  
  if (existing) {
    existing.qty += 1;
    if (!existing.image || existing.image === 'default_equipment.jpg') {
      existing.image = cleanImage;
    }
  } else {
    cart.push({ id, name, price: parseFloat(price), image: cleanImage, qty: 1 });
  }
  saveCart(cart);
  updateCartBadge();
  renderCartItems();
  showCartToast(name + ' added to cart!');
}

function removeFromCart(id) {
  let cart = getCart().filter(item => item.id !== id);
  saveCart(cart);
  updateCartBadge();
  renderCartItems();
}

function changeQty(id, delta) {
  const cart = getCart();
  const item = cart.find(i => i.id === id);
  if (!item) return;
  item.qty = Math.max(1, item.qty + delta);
  saveCart(cart);
  updateCartBadge();
  renderCartItems();
}

function clearCart() {
  saveCart([]);
  updateCartBadge();
  renderCartItems();
}

function updateCartBadge() {
  const cart = getCart();
  const total = cart.reduce((sum, item) => sum + item.qty, 0);
  document.querySelectorAll('#cartBadge').forEach(badge => {
    badge.textContent = total;
    badge.style.display = total > 0 ? 'inline-block' : 'inline-block';
  });
}

function renderCartItems() {
  const cart = getCart();
  const container = document.getElementById('cartItemsContainer');
  const totalEl = document.getElementById('cartTotalPrice');
  if (!container) return;

  if (cart.length === 0) {
    container.innerHTML = `
      <div class="cart-empty-msg">
        <i class="fas fa-shopping-cart"></i>
        <p>Your cart is empty.</p>
        <p style="font-size:0.85rem; margin-top:0.5rem;">Browse equipment and click <strong>Add to Cart</strong> to get started.</p>
      </div>`;
    if (totalEl) totalEl.textContent = '₹ 0.00';
    return;
  }

  let html = '';
  let grandTotal = 0;

  cart.forEach(item => {
    const subtotal = item.price * item.qty;
    grandTotal += subtotal;
    const imgSrc = getCartImageUrl(item.image);
    const fallbackSrc = getCartImageUrl('default_equipment.jpg');

    html += `
      <div class="cart-item" data-cart-id="${item.id}">
        <img src="${imgSrc}" alt="${item.name}" class="cart-item-img"
             onerror="this.onerror=null; this.src='${fallbackSrc}'">
        <div class="cart-item-info">
          <div class="cart-item-title">${item.name}</div>
          <div class="cart-item-price">₹ ${item.price.toLocaleString('en-IN', { minimumFractionDigits: 2 })} / day</div>
          <div class="cart-item-qty-wrap">
            <button class="qty-btn" onclick="changeQty('${item.id}', -1)" aria-label="Decrease quantity">−</button>
            <span class="cart-item-qty">${item.qty}</span>
            <button class="qty-btn" onclick="changeQty('${item.id}', 1)" aria-label="Increase quantity">+</button>
          </div>
          <div class="cart-item-subtotal">Subtotal: ₹ ${subtotal.toLocaleString('en-IN', { minimumFractionDigits: 2 })} / day</div>
        </div>
        <button class="cart-item-remove" onclick="removeFromCart('${item.id}')" aria-label="Remove item">
          <i class="fas fa-trash-alt"></i>
        </button>
      </div>`;
  });

  container.innerHTML = html;
  if (totalEl) totalEl.textContent = '₹ ' + grandTotal.toLocaleString('en-IN', { minimumFractionDigits: 2 });
}

function openCart() {
  const modal = document.getElementById('cartModal');
  if (modal) {
    renderCartItems();
    modal.classList.add('active');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }
}

function closeCart() {
  const modal = document.getElementById('cartModal');
  if (modal) {
    modal.classList.remove('active');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }
}

let toastTimer = null;
function showCartToast(msg) {
  const toast = document.getElementById('cartToast');
  const toastMsg = document.getElementById('cartToastMsg');
  if (!toast) return;
  if (toastMsg) toastMsg.textContent = msg;
  toast.classList.add('show');
  if (toastTimer) clearTimeout(toastTimer);
  toastTimer = setTimeout(() => toast.classList.remove('show'), 2800);
}

/* ============================================================
   DOM READY - Wire up all interactions
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {

  // ── Mobile Nav Menu Toggle ────────────────────────────────
  const navToggle = document.getElementById('navToggle');
  const navMenu = document.getElementById('navMenu');
  if (navToggle && navMenu) {
    navToggle.addEventListener('click', function () {
      navMenu.classList.toggle('show');
    });
  }

  // ── Equipment Catalog Filter ─────────────────────────────
  const searchInput = document.getElementById('catalogSearch');
  const categoryFilter = document.getElementById('categoryFilter');
  const equipmentCards = document.querySelectorAll('.equipment-card-item');

  function filterEquipment() {
    const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedCategory = categoryFilter ? categoryFilter.value.toLowerCase() : '';
    equipmentCards.forEach(card => {
      const name = (card.getAttribute('data-name') || '').toLowerCase();
      const category = (card.getAttribute('data-category') || '').toLowerCase();
      const show = name.includes(query) && (selectedCategory === '' || category === selectedCategory);
      card.style.display = show ? 'flex' : 'none';
    });
  }

  if (searchInput) searchInput.addEventListener('input', filterEquipment);
  if (categoryFilter) categoryFilter.addEventListener('change', filterEquipment);

  // ── Booking Form - Live Total Calculation & Date Constraints ──
  const startDateInput = document.getElementById('start_date');
  const endDateInput = document.getElementById('end_date');
  const daysOutput = document.getElementById('calc_days');
  const totalOutput = document.getElementById('calc_total');
  const priceInput = document.getElementById('price_per_day');

  if (startDateInput && endDateInput && priceInput) {
    const today = new Date().toISOString().split('T')[0];
    startDateInput.setAttribute('min', today);

    function updateBookingCalculation() {
      const startDateVal = startDateInput.value;
      const endDateVal = endDateInput.value;
      const dailyPrice = parseFloat(priceInput.value) || 0;
      if (startDateVal) endDateInput.setAttribute('min', startDateVal);
      if (startDateVal && endDateVal) {
        const start = new Date(startDateVal);
        const end = new Date(endDateVal);
        if (end >= start) {
          const diffDays = Math.ceil(Math.abs(end - start) / (1000 * 60 * 60 * 24)) + 1;
          const totalAmount = diffDays * dailyPrice;
          if (daysOutput) daysOutput.innerText = diffDays;
          if (totalOutput) totalOutput.innerText = '₹ ' + totalAmount.toLocaleString('en-IN', { minimumFractionDigits: 2 });
          const hiddenDays = document.getElementById('days_hidden');
          const hiddenTotal = document.getElementById('total_amount_hidden');
          if (hiddenDays) hiddenDays.value = diffDays;
          if (hiddenTotal) hiddenTotal.value = totalAmount;
        } else {
          if (daysOutput) daysOutput.innerText = '0';
          if (totalOutput) totalOutput.innerText = '₹ 0.00';
        }
      }
    }

    startDateInput.addEventListener('change', updateBookingCalculation);
    endDateInput.addEventListener('change', updateBookingCalculation);
    updateBookingCalculation();
  }

  // ── Registration Password Match Validation ────────────────
  const regForm = document.getElementById('registerForm');
  if (regForm) {
    regForm.addEventListener('submit', function (e) {
      const pass = document.getElementById('password').value;
      const confirmPass = document.getElementById('confirm_password').value;
      if (pass !== confirmPass) {
        e.preventDefault();
        alert('Passwords do not match! Please check and try again.');
        return false;
      }
    });
  }

  // ── Shopping Cart: Add to Cart buttons ───────────────────
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.add-to-cart-btn');
    if (!btn) return;
    const id    = btn.getAttribute('data-id');
    const name  = btn.getAttribute('data-name');
    const price = btn.getAttribute('data-price');
    const image = btn.getAttribute('data-image');
    if (!id || !name || !price) return;
    addToCart(id, name, price, image);

    // Brief visual pulse on button
    btn.classList.add('btn-pulse');
    setTimeout(() => btn.classList.remove('btn-pulse'), 400);
  });

  // ── Cart Toggle (nav icon) ────────────────────────────────
  const cartToggleBtn = document.getElementById('cartToggleBtn');
  if (cartToggleBtn) {
    cartToggleBtn.addEventListener('click', function (e) {
      e.preventDefault();
      openCart();
    });
  }

  // ── Close Cart buttons ────────────────────────────────────
  const closeCartBtn = document.getElementById('closeCartBtn');
  if (closeCartBtn) closeCartBtn.addEventListener('click', closeCart);

  const closeCartFooterBtn = document.getElementById('closeCartFooterBtn');
  if (closeCartFooterBtn) closeCartFooterBtn.addEventListener('click', closeCart);

  // ── Close on overlay click ────────────────────────────────
  const cartOverlay = document.getElementById('cartOverlay');
  if (cartOverlay) cartOverlay.addEventListener('click', closeCart);

  // ── Close on ESC key ─────────────────────────────────────
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeCart();
  });

  // ── Clear Cart button ─────────────────────────────────────
  const clearCartBtn = document.getElementById('clearCartBtn');
  if (clearCartBtn) clearCartBtn.addEventListener('click', clearCart);

  // ── Initialize badge on page load ────────────────────────
  updateCartBadge();
});
