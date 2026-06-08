  <header class="uc-header">
    <nav class="uc-navbar container-fluid">
      <!-- Логотип и название -->
      <div class="uc-navbar__brand">
        <a href="/" class="uc-brand-link" aria-label="UC-SHOP">
          <alt="UC SHOP" class="uc-brand-logo">
          <span class="uc-brand-text">UC SHOP</span>
        </a>
      </div>

      <!-- Меню для Desktop -->
      <div class="uc-navbar__menu uc-navbar__menu--desktop">
        <a href="/#faq-section" class="uc-menu-link">📚 КАК КУПИТЬ</a>
        <a href="https://t.me/pubgm_uc_reviews" target="_blank" rel="noopener" class="uc-menu-link">⭐ ОТЗЫВЫ</a>
        <a href="/contacts" class="uc-menu-link">📞 КОНТАКТЫ</a>
      </div>

      <!-- Правые кнопки -->
      <div class="uc-navbar__actions">
        <a href="https://t.me/ucshop_air" target="_blank" rel="noopener" class="uc-btn-telegram"
           aria-label="Поддержка в Telegram" title="Написать в поддержку">
          <span class="uc-btn-telegram__icon">💬</span>
          <span class="uc-btn-telegram__text">ПОДДЕРЖКА</span>
        </a>

        <!-- Бургер-меню для Mobile -->
        <button class="uc-burger-menu" id="burger-toggle" aria-label="Меню">
          <span></span>
          <span></span>
          <span></span>
        </button>
      </div>
    </nav>

    <!-- Mobile меню (скрыто по умолчанию) -->
    <div class="uc-mobile-menu" id="mobile-menu">
      <a href="/#faq-section" class="uc-mobile-menu__link">📚 КАК КУПИТЬ</a>
      <a href="https://t.me/pubgm_uc_reviews" target="_blank" rel="noopener" class="uc-mobile-menu__link">⭐ ОТЗЫВЫ</a>
      <a href="/contacts" class="uc-mobile-menu__link">📞 КОНТАКТЫ</a>
      <hr class="uc-mobile-menu__divider">
      <a href="https://t.me/ucshop_air" target="_blank" rel="noopener" class="uc-mobile-menu__link uc-mobile-menu__link--secondary">
        📱 Telegram Support
      </a>
    </div>
  </header>

  <script>
    // Простой toggle для мобильного меню
    document.addEventListener('DOMContentLoaded', function() {
      const burgerToggle = document.getElementById('burger-toggle');
      const mobileMenu = document.getElementById('mobile-menu');

      if (burgerToggle && mobileMenu) {
        burgerToggle.addEventListener('click', function(e) {
          e.preventDefault();
          mobileMenu.classList.toggle('uc-mobile-menu--active');
          burgerToggle.classList.toggle('uc-burger-menu--active');
        });

        // Закрыть меню при клике на ссылку
        const links = mobileMenu.querySelectorAll('a');
        links.forEach(link => {
          link.addEventListener('click', function() {
            mobileMenu.classList.remove('uc-mobile-menu--active');
            burgerToggle.classList.remove('uc-burger-menu--active');
          });
        });
      }

    });
  </script>

