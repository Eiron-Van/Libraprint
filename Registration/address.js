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
    function populateCityOptions(filter = '') {
      cityDropdown.innerHTML = '';
      const cities = Object.keys(barangayMap).filter(city => 
        city.toLowerCase().includes(filter.toLowerCase())
      );

      cities.forEach(city => {
        const cityElement = document.createElement('div');
        cityElement.className = ' px-4 py-2 text-gray-800 cursor-pointer hover:bg-gray-100';
        cityElement.textContent = city;
        cityElement.addEventListener('click', () => {
          cityInput.value = city;
          selectedCitySpan.textContent = city;
          cityDropdown.classList.add('hidden');
          populateBarangayOptions(city);
        });
        cityDropdown.appendChild(cityElement);
      });
      if (cities.length > 0) {
        cityDropdown.classList.remove('hidden');
      } else {
        cityDropdown.classList.add('hidden');
      }
    }
    function populateBarangayOptions(city) {
      barangayDropdown.innerHTML = '';
      const barangays = barangayMap[city] || [];
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
          selectedBarangaySpan.textContent = brgy;
          barangayDropdown.classList.add('hidden');
        });
        barangayDropdown.appendChild(barangayElement);
      });
      barangayDropdown.classList.remove('hidden');
    }
    cityInput.addEventListener('focus', () => {
      populateCityOptions();
    });
    cityInput.addEventListener('input', () => {
      populateCityOptions(cityInput.value);
    });
    barangayInput.addEventListener('focus', () => {
      if (cityInput.value) {
        populateBarangayOptions(cityInput.value);
      }
    });

    document.addEventListener('click', (e) => {
      if (e.target !== cityInput) {
        cityDropdown.classList.add('hidden');
      }
      if (e.target !== barangayInput) {
        barangayDropdown.classList.add('hidden');
      }
    });