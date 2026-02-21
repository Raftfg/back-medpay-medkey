@extends('layouts.landing')

@section('title', 'Contact | Medkey')
@section('header_class', 'page-header inner-page-header header-basic')

@section('content')
    <section class="d-flex align-items-center page-hero inner-page-hero" id="page-hero">
        <div class="overlay-photo-image-bg" data-bg-img="/images/landing/hero/inner-page-hero.jpg" data-bg-opacity=".75"></div>
        <div class="container">
            <div class="hero-text-area centerd">
                <h1 class="hero-title">Contactez-nous</h1>
                <nav aria-label="breadcrumb">
                    <ul class="breadcrumb wow fadeInUp" data-wow-delay=".6s">
                        <li class="breadcrumb-item"><a class="breadcrumb-link" href="{{ route('home') }}">Accueil</a></li>
                        <li class="breadcrumb-item active">Contact</li>
                    </ul>
                </nav>
            </div>
        </div>
    </section>

    <section class="contact-us mega-section pb-0" id="contact-us">
        <div class="container">
            <div class="row gx-4 gy-4 mb-5">
                <div class="col-12 col-md-4">
                    <div class="info-card">
                        <i class="bi bi-envelope info-icon"></i>
                        <h4 class="info-title">Email</h4>
                        <p class="info-text"><a href="mailto:{{ config('custom.contact_to_address') }}">{{ config('custom.contact_to_address') }}</a></p>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="info-card">
                        <i class="bi bi-telephone info-icon"></i>
                        <h4 class="info-title">Téléphone</h4>
                        <p class="info-text"><a href="tel:{{ config('custom.contact_phone_href') }}">{{ config('custom.contact_phone') }}</a></p>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="info-card">
                        <i class="bi bi-geo-alt info-icon"></i>
                        <h4 class="info-title">Adresse</h4>
                        <p class="info-text">{{ config('custom.contact_address') }}</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-lg-10 mx-auto">
                    <form class="main-form" id="contact-form" action="{{ route('contact.store') }}" method="post" novalidate>
                        @csrf
                        <div class="custom-form-area input-boxed">
                            <div class="row g-4">
                                <div class="col-12 col-md-6">
                                    <div class="input-wrapper">
                                        <input class="text-input" id="contact-name" name="name" type="text" required>
                                        <label class="input-label" for="contact-name">Nom *</label>
                                        <span class="b-border"></span>
                                        <small class="error-msg" data-error-for="name"></small>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="input-wrapper">
                                        <input class="text-input" id="contact-email" name="email" type="email" required>
                                        <label class="input-label" for="contact-email">E-Mail *</label>
                                        <span class="b-border"></span>
                                        <small class="error-msg" data-error-for="email"></small>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="input-wrapper">
                                        <input class="text-input" id="contact-subject" name="subject" type="text" required>
                                        <label class="input-label" for="contact-subject">Sujet *</label>
                                        <span class="b-border"></span>
                                        <small class="error-msg" data-error-for="subject"></small>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="input-wrapper">
                                        <textarea class="text-input" id="contact-message" name="message" rows="6" required></textarea>
                                        <label class="input-label" for="contact-message">Votre message *</label>
                                        <span class="b-border"></span>
                                        <small class="error-msg" data-error-for="message"></small>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button class="btn-solid" id="contact-submit" type="submit">Envoyer</button>
                                    <p class="done-msg" id="contact-done-msg">Votre message a bien été envoyé.</p>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        (function () {
            const form = document.getElementById('contact-form');
            const submitBtn = document.getElementById('contact-submit');
            const doneMsg = document.getElementById('contact-done-msg');
            if (!form || !submitBtn || !doneMsg) return;

            const clearErrors = () => {
                form.querySelectorAll('.error-msg').forEach((el) => {
                    el.textContent = '';
                });
            };

            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                clearErrors();
                doneMsg.classList.remove('show');

                submitBtn.disabled = true;
                const oldLabel = submitBtn.textContent;
                submitBtn.textContent = 'Envoi en cours...';

                const formData = new FormData(form);

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: formData,
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        if (data.errors) {
                            Object.entries(data.errors).forEach(([field, messages]) => {
                                const errorEl = form.querySelector(`[data-error-for="${field}"]`);
                                if (errorEl) errorEl.textContent = messages[0];
                            });
                        }
                        throw new Error(data.message || 'Veuillez réessayer.');
                    }

                    form.reset();
                    doneMsg.textContent = data.message || 'Votre message a bien été envoyé.';
                    doneMsg.classList.add('show');
                } catch (error) {
                    if (!doneMsg.classList.contains('show')) {
                        doneMsg.textContent = error.message || 'Veuillez réessayer.';
                        doneMsg.classList.add('show');
                    }
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = oldLabel;
                }
            });
        })();
    </script>
@endpush
