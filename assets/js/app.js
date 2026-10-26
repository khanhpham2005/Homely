document.addEventListener('DOMContentLoaded', function () {
  var toggle = document.querySelector('.menu-toggle');
  var links = document.querySelector('.nav-links');
  if (toggle && links) toggle.addEventListener('click', function () { links.classList.toggle('open'); });
  document.querySelectorAll('form[data-validate]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      var valid = true;
      form.querySelectorAll('[required]').forEach(function (field) {
        field.style.borderColor = field.value.trim() ? '' : '#c0392b';
        if (!field.value.trim()) valid = false;
      });
      var email = form.querySelector('input[type="email"]');
      if (email && email.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
        email.style.borderColor = '#c0392b'; valid = false;
      }
      if (!valid) { event.preventDefault(); alert('Vui lòng kiểm tra các trường bắt buộc.'); }
    });
  });
});

