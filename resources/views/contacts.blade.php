@extends('app')
@section('title', 'Контакты — UC-SHOP')
@section('content')

<section class="uc-contacts-section">
  <div class="container">

    <h1 class="uc-section-title">Контакты</h1>
    <p class="uc-section-subtitle">Мы на связи 24/7 — выбери удобный способ</p>

    <div class="uc-contacts-grid">

      {{-- БЛОК: Telegram-каналы --}}
      <div class="uc-contacts-card">
        <div class="uc-contacts-card__icon">📱</div>
        <h2 class="uc-contacts-card__title">Telegram</h2>
        <ul class="uc-contacts-list">
          <li>
            <span class="uc-contacts-list__label">Официальный канал</span>
            <a href="https://t.me/ucshop" target="_blank" rel="noopener" class="uc-contacts-list__link">@ucshop</a>
          </li>
          <li>
            <span class="uc-contacts-list__label">Отзывы и чат</span>
            <a href="https://t.me/pubgm_uc_reviews" target="_blank" rel="noopener" class="uc-contacts-list__link">@pubgm_uc_reviews</a>
          </li>
          <li>
            <span class="uc-contacts-list__label">Форум</span>
            <a href="https://t.me/ucshop_forum" target="_blank" rel="noopener" class="uc-contacts-list__link">@ucshop_forum</a>
          </li>
          <li>
            <span class="uc-contacts-list__label">Email</span>
            <a href="mailto:info@uc-shop.ru" class="uc-contacts-list__link">info@uc-shop.ru</a>
          </li>
        </ul>
      </div>

      {{-- БЛОК: Поддержка --}}
      <div class="uc-contacts-card">
        <div class="uc-contacts-card__icon">🎧</div>
        <h2 class="uc-contacts-card__title">Поддержка</h2>
        <ul class="uc-contacts-list">
          <li>
            <span class="uc-contacts-list__label">Администратор</span>
            <a href="https://t.me/ucshop_air" target="_blank" rel="noopener" class="uc-contacts-list__link">@ucshop_air</a>
          </li>
          <li>
            <span class="uc-contacts-list__label">Оператор</span>
            <a href="https://t.me/ucshop_air" target="_blank" rel="noopener" class="uc-contacts-list__link">@ucshop_air</a>
          </li>
        </ul>
        <a href="https://t.me/ucshop_air" target="_blank" rel="noopener" class="uc-btn-contact-telegram">
          💬 Написать в поддержку
        </a>
      </div>

      {{-- БЛОК: Форма --}}
      <div class="uc-contacts-card uc-contacts-card--form">
        <div class="uc-contacts-card__icon">✉️</div>
        <h2 class="uc-contacts-card__title">Форма обратной связи</h2>
        <form action="{{ route('contact-form') }}" method="post" class="uc-contact-form">
          @csrf
          <div class="uc-contact-form__group">
            <label for="subject" class="uc-contact-form__label">Тема обращения</label>
            <input type="text" id="subject" name="subject" class="uc-contact-form__input" placeholder="Например: не пришли UC" required>
          </div>
          <div class="uc-contact-form__group">
            <label for="name" class="uc-contact-form__label">Имя</label>
            <input type="text" id="name" name="name" class="uc-contact-form__input" placeholder="Ваше имя" required>
          </div>
          <div class="uc-contact-form__group">
            <label for="email" class="uc-contact-form__label">Email</label>
            <input type="email" id="email" name="email" class="uc-contact-form__input" placeholder="your@email.com" required>
          </div>
          <div class="uc-contact-form__group">
            <label for="message" class="uc-contact-form__label">Сообщение</label>
            <textarea id="message" name="message" class="uc-contact-form__input uc-contact-form__textarea" rows="4" placeholder="Опишите проблему..." required></textarea>
          </div>
          <button type="submit" class="uc-contact-form__submit">Отправить</button>
        </form>
      </div>

    </div>
  </div>
</section>

@endsection