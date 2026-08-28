/**
 * FARM TOOLS RENTAL PLATFORM - CLIENT JAVASCRIPT
 * Interactive validations, total price calculation, catalog filter
 */

document.addEventListener('DOMContentLoaded', function () {
  // Mobile Nav Menu Toggle
  const navToggle = document.getElementById('navToggle');
  const navMenu = document.getElementById('navMenu');

  if (navToggle && navMenu) {
    navToggle.addEventListener('click', function () {
      navMenu.classList.toggle('show');
    });
  }

  // Equipment Catalog Filter
  const searchInput = document.getElementById('catalogSearch');
  const categoryFilter = document.getElementById('categoryFilter');
  const equipmentCards = document.querySelectorAll('.equipment-card-item');

  function filterEquipment() {
    const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedCategory = categoryFilter ? categoryFilter.value.toLowerCase() : '';

    equipmentCards.forEach(card => {
      const name = card.getAttribute('data-name') || '';
      const category = card.getAttribute('data-category') || '';
      const matchesSearch = name.toLowerCase().includes(query);
      const matchesCategory = selectedCategory === '' || category.toLowerCase() === selectedCategory;

      if (matchesSearch && matchesCategory) {
        card.style.display = 'flex';
      } else {
        card.style.display = 'none';
      }
    });
  }

  if (searchInput) searchInput.addEventListener('input', filterEquipment);
  if (categoryFilter) categoryFilter.addEventListener('change', filterEquipment);

  // Booking Form - Live Total Calculation & Date Constraints
  const startDateInput = document.getElementById('start_date');
  const endDateInput = document.getElementById('end_date');
  const daysOutput = document.getElementById('calc_days');
  const totalOutput = document.getElementById('calc_total');
  const priceInput = document.getElementById('price_per_day');

  if (startDateInput && endDateInput && priceInput) {
    // Set minimum start date to today
    const today = new Date().toISOString().split('T')[0];
    startDateInput.setAttribute('min', today);

    function updateBookingCalculation() {
      const startDateVal = startDateInput.value;
      const endDateVal = endDateInput.value;
      const dailyPrice = parseFloat(priceInput.value) || 0;

      if (startDateVal) {
        endDateInput.setAttribute('min', startDateVal);
      }

      if (startDateVal && endDateVal) {
        const start = new Date(startDateVal);
        const end = new Date(endDateVal);

        if (end >= start) {
          const diffTime = Math.abs(end - start);
          const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; // Inclusive of start day
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

  // Registration Password Match Validation
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
});
