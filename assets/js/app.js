/**
 * BazarPK - Vanilla JavaScript Engine
 * Handles Cart Drawer, Wishlist, Live Search, & Toast Alerts
 */

const BazarPK = (function () {
  'use strict';

  const STORAGE_CART_KEY = 'bazarpk_cart';
  const STORAGE_WISHLIST_KEY = 'bazarpk_wishlist';
  const FREE_SHIPPING_LIMIT = 3500;

  let cart = [];
  let wishlist = [];

  // Initialize
  function init() {
    loadLocalState();
    bindEvents();
    renderCart();
    updateWishlistUI();
  }

  // Load from LocalStorage
  function loadLocalState() {
    try {
      cart = JSON.parse(localStorage.getItem(STORAGE_CART_KEY)) || [];
      wishlist = JSON.parse(localStorage.getItem(STORAGE_WISHLIST_KEY)) || [];
    } catch (e) {
      cart = [];
      wishlist = [];
    }
  }

  function saveCart() {
    localStorage.setItem(STORAGE_CART_KEY, JSON.stringify(cart));
    updateCartBadges();
    syncCartWithServer();
  }

  function saveWishlist() {
    localStorage.setItem(STORAGE_WISHLIST_KEY, JSON.stringify(wishlist));
    updateWishlistUI();
  }

  // Sync cart with PHP session via lightweight API
  function syncCartWithServer() {
    fetch('api/cart.php?action=sync', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ items: cart })
    }).catch(err => console.debug('Cart sync deferred:', err));
  }

  // Bind UI Events
  function bindEvents() {
    // Cart Drawer Toggle
    document.querySelectorAll('[data-action="open-cart"]').forEach(el => {
      el.addEventListener('click', e => {
        e.preventDefault();
        openCartDrawer();
      });
    });

    document.querySelectorAll('[data-action="close-cart"]').forEach(el => {
      el.addEventListener('click', e => {
        e.preventDefault();
        closeCartDrawer();
      });
    });

    const overlay = document.querySelector('.cart-drawer-overlay');
    if (overlay) {
      overlay.addEventListener('click', closeCartDrawer);
    }

    // Quick Add to Cart buttons
    document.addEventListener('click', function (e) {
      const btn = e.target.closest('[data-action="add-to-cart"]');
      if (btn) {
        e.preventDefault();
        const id = parseInt(btn.dataset.id, 10);
        const name = btn.dataset.name;
        const price = parseFloat(btn.dataset.price);
        const image = btn.dataset.image;
        const variant = btn.dataset.variant || 'Standard';

        addToCart({ id, name, price, image, variant, quantity: 1 });
        showToast(`Added "${name}" to your cart!`, 'success');
        openCartDrawer();
      }

      // Wishlist toggle
      const wishBtn = e.target.closest('[data-action="toggle-wishlist"]');
      if (wishBtn) {
        e.preventDefault();
        const id = parseInt(wishBtn.dataset.id, 10);
        toggleWishlist(id, wishBtn);
      }
    });

    // Live Search
    const searchInput = document.querySelector('.search-input');
    const searchDropdown = document.querySelector('.search-results-dropdown');
    if (searchInput && searchDropdown) {
      let debounceTimeout;
      searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimeout);
        const query = this.value.trim();
        if (query.length < 2) {
          searchDropdown.style.display = 'none';
          searchDropdown.innerHTML = '';
          return;
        }

        debounceTimeout = setTimeout(() => {
          fetch(`api/products.php?action=search&q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
              if (data.success && data.products && data.products.length > 0) {
                let html = '';
                data.products.slice(0, 5).forEach(prod => {
                  html += `
                    <a href="product.php?id=${prod.id}" class="search-result-item">
                      <img src="${prod.main_image || 'assets/images/placeholder.jpg'}" alt="${prod.name}" class="search-result-img">
                      <div style="flex:1;">
                        <div style="font-size:13px; font-weight:600; color:#0f172a;">${escapeHtml(prod.name)}</div>
                        <div style="font-size:13px; font-weight:700; color:#0f766e;">Rs. ${Number(prod.price).toLocaleString()}</div>
                      </div>
                    </a>
                  `;
                });
                searchDropdown.innerHTML = html;
                searchDropdown.style.display = 'block';
              } else {
                searchDropdown.innerHTML = '<div style="padding:14px; font-size:13px; color:#64748b; text-align:center;">No matching products found in Pakistan.</div>';
                searchDropdown.style.display = 'block';
              }
            })
            .catch(() => {
              searchDropdown.style.display = 'none';
            });
        }, 250);
      });

      document.addEventListener('click', function (e) {
        if (!e.target.closest('.header-search')) {
          searchDropdown.style.display = 'none';
        }
      });
    }
  }

  // Cart Functions
  function addToCart(item) {
    const existingIndex = cart.findIndex(c => c.id === item.id && c.variant === item.variant);
    if (existingIndex > -1) {
      cart[existingIndex].quantity += (item.quantity || 1);
    } else {
      cart.push({
        id: item.id,
        name: item.name,
        price: item.price,
        image: item.image,
        variant: item.variant || 'Standard',
        quantity: item.quantity || 1
      });
    }
    saveCart();
    renderCart();
  }

  function updateQuantity(index, delta) {
    if (!cart[index]) return;
    cart[index].quantity += delta;
    if (cart[index].quantity <= 0) {
      cart.splice(index, 1);
    }
    saveCart();
    renderCart();
  }

  function removeFromCart(index) {
    if (!cart[index]) return;
    const removedName = cart[index].name;
    cart.splice(index, 1);
    saveCart();
    renderCart();
    showToast(`Removed "${removedName}" from cart`, 'info');
  }

  function getCartSubtotal() {
    return cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
  }

  function updateCartBadges() {
    const totalCount = cart.reduce((sum, item) => sum + item.quantity, 0);
    document.querySelectorAll('.cart-counter-badge').forEach(el => {
      el.textContent = totalCount;
      el.style.display = totalCount > 0 ? 'flex' : 'none';
    });
  }

  function renderCart() {
    updateCartBadges();
    const container = document.querySelector('.cart-drawer-items');
    const subtotalEl = document.querySelector('.cart-subtotal-amount');
    const shippingBar = document.querySelector('.shipping-progress-fill');
    const shippingText = document.querySelector('.shipping-progress-text');
    const checkoutBtn = document.querySelector('.btn-checkout-drawer');

    if (!container) return;

    const subtotal = getCartSubtotal();
    if (subtotalEl) {
      subtotalEl.textContent = `Rs. ${subtotal.toLocaleString()}`;
    }

    // Free shipping progress bar
    if (shippingBar && shippingText) {
      if (subtotal >= FREE_SHIPPING_LIMIT) {
        shippingBar.style.width = '100%';
        shippingText.innerHTML = `🎉 Congratulations! You have qualified for <strong>FREE Delivery</strong> across Pakistan!`;
      } else {
        const percent = Math.min(100, Math.round((subtotal / FREE_SHIPPING_LIMIT) * 100));
        shippingBar.style.width = `${percent}%`;
        const diff = FREE_SHIPPING_LIMIT - subtotal;
        shippingText.innerHTML = `Add <strong>Rs. ${diff.toLocaleString()}</strong> more to enjoy <strong>FREE Shipping</strong>!`;
      }
    }

    if (cart.length === 0) {
      container.innerHTML = `
        <div style="text-align:center; padding: 48px 16px; color:#64748b;">
          <div style="font-size:44px; margin-bottom:12px;">🛒</div>
          <h4 style="color:#0f172a; margin-bottom:6px;">Your cart is empty</h4>
          <p style="font-size:13px; margin-bottom:18px;">Looks like you haven't added anything yet.</p>
          <a href="shop.php" class="btn-primary" style="display:inline-block; padding:10px 20px; font-size:13px;">Explore Catalog</a>
        </div>
      `;
      if (checkoutBtn) checkoutBtn.style.opacity = '0.5';
      if (checkoutBtn) checkoutBtn.style.pointerEvents = 'none';
      return;
    }

    if (checkoutBtn) {
      checkoutBtn.style.opacity = '1';
      checkoutBtn.style.pointerEvents = 'auto';
    }

    let html = '';
    cart.forEach((item, index) => {
      html += `
        <div class="cart-item">
          <img src="${item.image || 'assets/images/placeholder.jpg'}" alt="${escapeHtml(item.name)}" class="cart-item-img">
          <div class="cart-item-details">
            <div class="cart-item-name">${escapeHtml(item.name)}</div>
            <div class="cart-item-variant">${escapeHtml(item.variant)}</div>
            <div class="cart-item-price">Rs. ${(item.price * item.quantity).toLocaleString()}</div>
            <div class="cart-item-actions">
              <div class="quantity-stepper">
                <button type="button" class="qty-btn" onclick="BazarPK.updateQuantity(${index}, -1)">&minus;</button>
                <input type="text" class="qty-input" value="${item.quantity}" readonly>
                <button type="button" class="qty-btn" onclick="BazarPK.updateQuantity(${index}, 1)">&plus;</button>
              </div>
              <button type="button" class="cart-item-remove" onclick="BazarPK.removeFromCart(${index})">Remove</button>
            </div>
          </div>
        </div>
      `;
    });
    container.innerHTML = html;
  }

  function openCartDrawer() {
    const drawer = document.querySelector('.cart-drawer');
    const overlay = document.querySelector('.cart-drawer-overlay');
    if (drawer && overlay) {
      drawer.classList.add('active');
      overlay.classList.add('active');
      document.body.style.overflow = 'hidden';
    }
  }

  function closeCartDrawer() {
    const drawer = document.querySelector('.cart-drawer');
    const overlay = document.querySelector('.cart-drawer-overlay');
    if (drawer && overlay) {
      drawer.classList.remove('active');
      overlay.classList.remove('active');
      document.body.style.overflow = '';
    }
  }

  // Wishlist Functions
  function toggleWishlist(productId, btnEl) {
    const idx = wishlist.indexOf(productId);
    if (idx > -1) {
      wishlist.splice(idx, 1);
      showToast('Removed item from your wishlist', 'info');
      if (btnEl) btnEl.classList.remove('active');
    } else {
      wishlist.push(productId);
      showToast('Added item to your wishlist ❤️', 'success');
      if (btnEl) btnEl.classList.add('active');
    }
    saveWishlist();
  }

  function updateWishlistUI() {
    const countEl = document.querySelector('.wishlist-counter-badge');
    if (countEl) {
      countEl.textContent = wishlist.length;
      countEl.style.display = wishlist.length > 0 ? 'flex' : 'none';
    }
    document.querySelectorAll('[data-action="toggle-wishlist"]').forEach(btn => {
      const id = parseInt(btn.dataset.id, 10);
      if (wishlist.includes(id)) {
        btn.classList.add('active');
      } else {
        btn.classList.remove('active');
      }
    });
  }

  // Toast Notification System
  function showToast(message, type = 'success') {
    let container = document.querySelector('.toast-container');
    if (!container) {
      container = document.createElement('div');
      container.className = 'toast-container';
      document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    const icon = type === 'success' ? '✓' : (type === 'error' ? '✕' : 'ℹ');
    toast.innerHTML = `<span style="font-weight:700;">${icon}</span> <span>${escapeHtml(message)}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(10px)';
      toast.style.transition = 'all 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, 3200);
  }

  function escapeHtml(text) {
    if (!text) return '';
    return text.toString()
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // Run on DOM Ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  return {
    addToCart,
    updateQuantity,
    removeFromCart,
    openCartDrawer,
    closeCartDrawer,
    toggleWishlist,
    showToast,
    getCart: () => cart
  };
})();
