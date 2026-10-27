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
  var searchForm = document.querySelector('[data-live-search]');
  if (searchForm) {
    var searchInput = searchForm.querySelector('input[name="q"]');
    var results = document.querySelector('.search-results');
    var status = document.querySelector('.search-status');
    var timer;
    var escapeHtml = function (value) {
      var div = document.createElement('div');
      div.textContent = value == null ? '' : String(value);
      return div.innerHTML;
    };
    var renderResults = function (products) {
      if (!products.length) {
        results.innerHTML = '<p class="muted">Không tìm thấy kết quả phù hợp.</p>';
        return;
      }
      results.innerHTML = '<div class="grid">' + products.map(function (product) {
        var image = product.image
          ? '<img class="product-image" src="' + escapeHtml(product.image) + '" alt="' + escapeHtml(product.name) + '" loading="lazy">'
          : '<span class="image-placeholder">' + escapeHtml(product.name) + '</span>';
        return '<article class="card"><div class="card-img">' + image + '</div><div class="card-body"><span class="tag">Homely home essentials</span><h3>' + escapeHtml(product.name) + '</h3><p class="muted">' + escapeHtml(product.description) + '</p><div class="price">' + escapeHtml(product.price) + '</div><p><a class="btn" href="' + escapeHtml('/ltw/san-pham/' + product.slug) + '">Xem chi tiết</a></p></div></article>';
      }).join('') + '</div>';
    };
    var search = function () {
      var keyword = searchInput.value.trim();
      status.textContent = 'Đang tìm kiếm...';
      fetch('/ltw/san-pham?live=1&q=' + encodeURIComponent(keyword), { headers: { 'Accept': 'application/json' } })
        .then(function (response) {
          if (!response.ok) throw new Error('search failed');
          return response.json();
        })
        .then(function (data) {
          renderResults(data.products || []);
          status.textContent = data.products && data.products.length && keyword
            ? 'Kết quả cho: ' + keyword
            : '';
        })
        .catch(function () {
          status.textContent = 'Không thể tìm kiếm lúc này. Vui lòng thử lại.';
        });
    };
    searchInput.addEventListener('input', function () {
      clearTimeout(timer);
      timer = setTimeout(search, 250);
    });
    searchForm.addEventListener('submit', function (event) {
      event.preventDefault();
      clearTimeout(timer);
      search();
    });
  }
});
