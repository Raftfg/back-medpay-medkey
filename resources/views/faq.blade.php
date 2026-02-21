@extends('layouts.landing')

@section('title', 'FAQ | Medkey')
@section('header_class', 'page-header inner-page-header header-basic')

@section('content')
    <style>
        .support-page {
            background: var(--clr-dark-blue);
        }
        .support-page .support-card {
            border: 1px solid rgba(255, 255, 255, 0.28);
            border-radius: 14px;
            padding: 1.6rem 1.3rem;
            height: 100%;
            background: rgba(255, 255, 255, 0.02);
        }
        .support-page .support-card .icon-wrap i {
            font-size: 1.8rem;
            color: var(--clr-main);
        }
        .support-page .support-card h4 {
            color: var(--clr-white);
            margin-top: 0.9rem;
            margin-bottom: 0.7rem;
        }
        .support-page .support-card p {
            color: rgba(255, 255, 255, 0.88);
        }
        .support-page .support-link {
            color: var(--clr-main);
            font-weight: 600;
        }
        .support-page .faq-side-card {
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 14px;
            padding: 1.2rem;
            background: rgba(255, 255, 255, 0.03);
        }
        .support-page .faq-side-item {
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding: 0.7rem 0;
        }
        .support-page .faq-side-item:last-child {
            border-bottom: 0;
        }
        .support-page .faq-side-item h6 {
            color: var(--clr-white);
            margin-bottom: 0.4rem;
        }
        .support-page .faq-side-item p {
            color: rgba(255, 255, 255, 0.86);
            margin-bottom: 0;
            font-size: 0.95rem;
        }
        .support-page .contact-side-card {
            border-radius: 14px;
            padding: 1.35rem;
            background: #1f6feb;
            margin-top: 1rem;
        }
        .support-page .contact-side-card h5,
        .support-page .contact-side-card p {
            color: var(--clr-white);
        }
        .support-page .contact-list .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 0.7rem;
            color: rgba(255, 255, 255, 0.92);
            margin-bottom: 0.9rem;
        }
        .support-page .contact-list .contact-item i {
            color: var(--clr-main);
            font-size: 1.15rem;
            margin-top: 0.15rem;
        }
    </style>

    <section class="support-page mega-section" id="faq">
        <div class="container">
            <div class="row g-4">
                <div class="col-12 col-lg-8">
                    <div class="sec-heading">
                        <div class="content-area">
                            <span class="pre-title">Assistance</span>
                            <h2 class="title">Comment pouvons-nous vous aider ?</h2>
                            <p class="subtitle">Notre équipe d'assistance dédiée est là pour vous garantir une utilisation optimale de la plateforme MedKey. Que vous ayez besoin d'une aide technique ou de conseils d'utilisation, nous sommes à votre disposition.</p>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <div class="support-card">
                                <div class="icon-wrap"><i class="bi bi-headset"></i></div>
                                <h4>Assistance Technique</h4>
                                <p>Rencontrez-vous un problème technique ? Nos experts interviennent rapidement pour résoudre vos blocages.</p>
                                <a class="support-link" href="{{ route('contact') }}">Ouvrir un ticket <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="support-card">
                                <div class="icon-wrap"><i class="bi bi-book"></i></div>
                                <h4>Base de Connaissances</h4>
                                <p>Consultez nos guides détaillés et tutoriels vidéo pour maîtriser toutes les fonctionnalités de MedKey.</p>
                                <a class="support-link" href="{{ route('faq') }}">Voir les ressources <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>

                    <h3 class="mb-3">Autres moyens de nous contacter</h3>
                    <div class="row contact-list">
                        <div class="col-12 col-md-6">
                            <div class="contact-item">
                                <i class="bi bi-telephone"></i>
                                <div>
                                    <strong>Téléphone (Urgence)</strong><br>
                                    <a href="tel:+2290195621919">+229 01 95 62 19 19</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="contact-item">
                                <i class="bi bi-envelope"></i>
                                <div>
                                    <strong>Email Direct</strong><br>
                                    <a href="mailto:akasi-commercial@akasigroup.com">akasi-commercial@akasigroup.com</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="faq-side-card">
                        <h4>Questions Fréquentes</h4>
                        <div class="faq-side-item">
                            <h6>Vos services sont-ils faciles à utiliser ?</h6>
                            <p>Oui, MedKey est conçu avec une interface intuitive pour les médecins, infirmiers et agents administratifs.</p>
                        </div>
                        <div class="faq-side-item">
                            <h6>Recevrai-je des mises à jour futures ?</h6>
                            <p>Absolument. Nous mettons régulièrement la plateforme à jour avec de nouvelles fonctionnalités.</p>
                        </div>
                        <div class="faq-side-item">
                            <h6>Le service fonctionne-t-il dans mon pays ?</h6>
                            <p>Oui, notre solution cloud est accessible partout avec une connexion Internet stable.</p>
                        </div>
                        <a class="btn-solid w-100 mt-3" href="{{ route('faq') }}">Voir toute la FAQ</a>
                    </div>

                    <div class="contact-side-card text-center">
                        <i class="bi bi-chat-dots" style="font-size: 1.8rem;"></i>
                        <h5 class="mt-2">Besoin d'une démo ?</h5>
                        <p>Demandez une présentation personnalisée pour votre établissement.</p>
                        <a class="btn-outline w-100" href="{{ route('contact') }}">Contactez-nous</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
