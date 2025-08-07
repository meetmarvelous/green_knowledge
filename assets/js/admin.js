document.addEventListener('DOMContentLoaded', function() {
  // Initialize tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Toggle sidebar on mobile
  document.getElementById('sidebarToggle').addEventListener('click', function() {
      document.querySelector('.admin-sidebar').classList.toggle('active');
  });

  // Confirm before destructive actions
  document.querySelectorAll('a[data-confirm]').forEach(function(el) {
      el.addEventListener('click', function(e) {
          if (!confirm(this.getAttribute('data-confirm'))) {
              e.preventDefault();
          }
      });
  });

  // Auto-dismiss alerts
  setTimeout(function() {
      var alerts = document.querySelectorAll('.alert');
      alerts.forEach(function(alert) {
          new bootstrap.Alert(alert).close();
      });
  }, 5000);
});