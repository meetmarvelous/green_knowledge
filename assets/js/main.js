document.addEventListener('DOMContentLoaded', function () {
  // Enable Bootstrap tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Photo upload preview
  document.querySelectorAll('input[type="file"]').forEach(function (input) {
    if (input.multiple) {
      input.addEventListener('change', function (e) {
        const files = e.target.files;
        const previewContainer = document.getElementById('photo-preview-container');

        if (previewContainer) {
          previewContainer.innerHTML = '';

          for (let i = 0; i < files.length; i++) {
            const reader = new FileReader();
            reader.onload = function (event) {
              const preview = document.createElement('div');
              preview.className = 'col-md-3 mb-3';
              preview.innerHTML = `
                              <div class="card">
                                  <img src="${event.target.result}" class="card-img-top" style="height: 120px; object-fit: cover;">
                                  <div class="card-body p-2">
                                      <input type="text" class="form-control form-control-sm" 
                                             placeholder="Caption" name="photo_captions[]">
                                  </div>
                              </div>
                          `;
              previewContainer.appendChild(preview);
            };
            reader.readAsDataURL(files[i]);
          }
        }
      });
    }
  });

  // Confirm before delete actions
  document.querySelectorAll('a[data-confirm]').forEach(function (link) {
    link.addEventListener('click', function (e) {
      if (!confirm(this.getAttribute('data-confirm'))) {
        e.preventDefault();
      }
    });
  });

  // Search form enhancement
  const searchForm = document.querySelector('form[action="search.php"]');
  if (searchForm) {
    searchForm.addEventListener('submit', function (e) {
      const query = this.querySelector('input[name="q"]').value.trim();
      if (query.length < 2) {
        alert('Please enter at least 2 characters to search');
        e.preventDefault();
      }
    });
  }
});


// Hero Slider Functionality (for homepage)
document.addEventListener('DOMContentLoaded', function() {
  // Homepage slider, Only run on homepage
  if (document.querySelector('.hero-slider-container')) {
      const heroSlides = document.querySelectorAll('.hero-slide');
      let currentHeroSlide = 0;
      
      // Initialize slider
      function initHeroSlider() {
          if (heroSlides.length > 0) {
              heroSlides[0].classList.add('active');
              setInterval(nextHeroSlide, 6000); // Change slide every 6 seconds
          }
      }
      
      function nextHeroSlide() {
          heroSlides[currentHeroSlide].classList.remove('active');
          currentHeroSlide = (currentHeroSlide + 1) % heroSlides.length;
          heroSlides[currentHeroSlide].classList.add('active');
      }
      
      initHeroSlider();
  }
});