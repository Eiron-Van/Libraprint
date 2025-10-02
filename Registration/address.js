const cityInput = document.getElementById('city');
const cityDropdown = document.getElementById('city-dropdown');
const barangayDropdown = document.getElementById('barangay-dropdown');
const selectedCitySpan = document.getElementById('selectedCity');
const selectedBarangaySpan = document.getElementById('selectedBarangay');
const barangayInput = document.getElementById('barangay');
    
  let barangayMap = {};
    // Load JSON mapping
    fetch('barangayMap.json')
      .then(res => res.json())
      .then(data => {
        barangayMap = data;
        //populateCityOptions();
      })
      .catch(err => {
        console.error('Failed to load barangayMap.json:', err);
      });
    function resolveCityKey(inputValue) {
      const trimmed = (inputValue || '').trim().toLowerCase();
      if (!trimmed) return '';
      const cities = Object.keys(barangayMap);
      // Exact (case-insensitive) match first
      const exact = cities.find(c => c.toLowerCase() === trimmed);
      if (exact) return exact;
      // Starts with
      const starts = cities.find(c => c.toLowerCase().startsWith(trimmed));
      if (starts) return starts;
      // Includes
      const includes = cities.find(c => c.toLowerCase().includes(trimmed));
      return includes || '';
    }

    function populateCityOptions(filter = '') {
      cityDropdown.innerHTML = '';
      const cities = Object.keys(barangayMap).filter(city =>
        city.toLowerCase().includes((filter || '').toLowerCase())
      );

      cities.forEach(city => {
        const cityElement = document.createElement('div');
        cityElement.className = 'px-4 py-2 text-gray-800 cursor-pointer hover:bg-gray-100';
        cityElement.textContent = city;
        cityElement.addEventListener('click', () => {
          cityInput.value = city;
          if (selectedCitySpan) selectedCitySpan.textContent = city;
          cityDropdown.classList.add('hidden');
          // Clear barangay when city changes
          barangayInput.value = '';
          populateBarangayOptions(city);
        });
        cityDropdown.appendChild(cityElement);
      });
      cityDropdown.classList.toggle('hidden', cities.length === 0);
    }

    function populateBarangayOptions(cityInputValue, filter = '') {
      barangayDropdown.innerHTML = '';
      const cityKey = resolveCityKey(cityInputValue);
      const barangaysRaw = cityKey ? (barangayMap[cityKey] || []) : [];
      const filterLc = (filter || '').toLowerCase();
      const barangays = filterLc
        ? barangaysRaw.filter(b => (b || '').toLowerCase().includes(filterLc))
        : barangaysRaw;

      barangayInput.disabled = barangays.length === 0;
      if (barangays.length === 0) {
        barangayDropdown.classList.add('hidden');
        return;
      }

      barangays.forEach(brgy => {
        const barangayElement = document.createElement('div');
        barangayElement.className = 'px-4 py-2 text-gray-800 cursor-pointer hover:bg-gray-100';
        barangayElement.textContent = brgy;
        barangayElement.addEventListener('click', () => {
          barangayInput.value = brgy;
          if (selectedBarangaySpan) selectedBarangaySpan.textContent = brgy;
          barangayDropdown.classList.add('hidden');
        });
        barangayDropdown.appendChild(barangayElement);
      });
      barangayDropdown.classList.remove('hidden');
    }

    cityInput.addEventListener('focus', () => {
      populateCityOptions(cityInput.value);
    });
    cityInput.addEventListener('input', () => {
      populateCityOptions(cityInput.value);
      // Clear barangay suggestions while city text is changing
      barangayInput.value = '';
      barangayDropdown.classList.add('hidden');
    });
    cityInput.addEventListener('blur', () => {
      // Normalize typed city to canonical key if possible
      const resolved = resolveCityKey(cityInput.value);
      if (resolved) {
        cityInput.value = resolved;
      }
    });

    barangayInput.addEventListener('focus', () => {
      if (cityInput.value) {
        populateBarangayOptions(cityInput.value, barangayInput.value);
      }
    });
    barangayInput.addEventListener('input', () => {
      if (cityInput.value) {
        populateBarangayOptions(cityInput.value, barangayInput.value);
      }
    });

    document.addEventListener('click', (e) => {
      const clickedInsideCity = cityDropdown.contains(e.target) || e.target === cityInput;
      const clickedInsideBarangay = barangayDropdown.contains(e.target) || e.target === barangayInput;
      if (!clickedInsideCity) {
        cityDropdown.classList.add('hidden');
      }
      if (!clickedInsideBarangay) {
        barangayDropdown.classList.add('hidden');
      }
    });