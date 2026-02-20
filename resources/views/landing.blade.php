@extends('layouts.landing')

@section('title', 'Medkey | SIH Cloud')
@section('meta_description', 'Medkey, système d’information hospitalier cloud multi-tenant pour tous types d’hôpitaux.')
@section('header_class', 'page-header content-always-light header-basic')

@section('content')
    <style>
        #services .row > [class*="col-"] {
            display: flex;
        }

        #services .service-box {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: all 0.28s ease;
        }

        #services .service-box:hover {
            background-color: rgba(56, 189, 248, 0.16);
            border-color: #38bdf8;
            transform: translateY(-6px);
            box-shadow: 0 10px 24px rgba(56, 189, 248, 0.25);
        }

        #services .service-icon {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 0.9rem;
            text-align: center;
        }

        #services .service-icon i {
            font-size: 2.15rem;
            line-height: 1;
        }

        .medical-services {
            background: #f4f6f8;
        }

        .medical-services .sec-heading .pre-title,
        .medical-services .sec-heading .title {
            color: #2f3542 !important;
        }

        .medical-services-grid .service-item {
            text-align: center;
            margin-bottom: 1.75rem;
        }

        .medical-services-grid .icon-wrap {
            width: 84px;
            height: 84px;
            border-radius: 50%;
            margin: 0 auto 0.85rem;
            background: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 18px rgba(13, 24, 87, 0.12);
        }

        .medical-services-grid .icon-wrap i {
            font-size: 2rem;
            color: #20b2aa;
            line-height: 1;
        }

        .medical-services-grid .service-label {
            font-size: 1.08rem;
            font-weight: 600;
            color: #2f3542;
            text-transform: uppercase;
        }

        #pricing-1 .row > [class*="col-"] {
            display: flex;
        }

        #pricing-1 .price-plan {
            display: flex;
        }

        #pricing-1 .plan {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        #pricing-1 .plan-head {
            min-height: 190px;
        }

        #pricing-1 .plan-price .price {
            font-size: 2.25rem;
            line-height: 1.15;
            white-space: nowrap;
            display: inline-flex;
            align-items: flex-start;
            gap: 0.2rem;
        }

        #pricing-1 .plan-price .currency-symbol {
            font-size: 0.95rem;
            margin-left: 0;
            position: static;
            top: auto;
            right: auto;
            vertical-align: super;
            line-height: 1;
        }

        #pricing-1 .plan-details {
            flex: 1 1 auto;
            display: flex;
        }

        #pricing-1 .plan-list {
            width: 100%;
            margin-bottom: 0;
        }

        #pricing-1 .plan-feat {
            line-height: 1.45;
            margin-bottom: 0.45rem;
        }

        #pricing-1 .plan-feat .feat-text {
            display: block;
            overflow-wrap: anywhere;
        }

        #pricing-1 .plan-cta {
            margin-top: auto;
            padding-top: 0.6rem;
        }

        @media (max-width: 1199.98px) {
            #pricing-1 .plan-head {
                min-height: 175px;
            }
        }

        #take-action .btn-solid {
            white-space: nowrap;
        }
    </style>

    <section class="page-hero hero-swiper-slider slide-effect d-flex align-items-center" id="page-hero">
        <div class="particles-js bubels" id="particles-js"></div>
        <div class="slider swiper-container">
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <div class="slide-bg-img" data-bg-img="/images/landing/hero/pilo.jpeg">
                        <div class="overlay-gradient-color"></div>
                    </div>
                    <div class="container">
                        <div class="hero-text-area content-always-light">
                            <div class="row g-0">
                                <div class="col-12 col-lg-8">
                                    <div class="pre-title">Transformation Digitale de la Santé</div>
                                    <h1 class="slide-title">Pilotez votre établissement avec un SIH intelligent, fiable et évolutif <span class="featured-text">intelligent</span></h1>
                                    <p class="slide-subtitle">edKey est une plateforme de système d’information hospitalier moderne qui centralise la gestion des patients, des soins, des flux et des ressources.
                                    Gagnez en efficacité opérationnelle, améliorez la qualité des soins et sécurisez vos données médicales — le tout sur une seule interface.</p>
                                    <div class="cta-links-area">
                                        <a class="btn-solid cta-link cta-link-primary" href="{{ \Illuminate\Support\Facades\Route::has('register') ? route('register') : '/register' }}">Commencer gratuitement</a>
                                        <a class="btn-outline cta-link" href="{{ route('contact') }}">Demander une démonstration</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="slide-bg-img" data-bg-img="/images/landing/hero/made.jpg">
                        <div class="overlay-gradient-color"></div>
                    </div>
                    <div class="container">
                        <div class="hero-text-area content-always-light">
                            <div class="row g-0">
                                <div class="col-12 col-lg-8">
                                    <div class="pre-title">Dossier Patient Unifié & Intelligent</div>
                                    <h2 class="slide-title">Des dossiers médicaux accessibles, complets et hautement sécurisés<span class="featured-text"></h2>
                                    <p class="slide-subtitle">MedKey centralise l’ensemble des informations médicales du patient dans un dossier unique, structuré et conforme aux exigences de confidentialité.
                                    Chaque professionnel de santé accède instantanément aux données essentielles — antécédents, consultations, prescriptions — pour une prise en charge rapide, coordonnée et sans rupture.</p>
                                    <div class="cta-links-area">
                                        <a class="btn-solid cta-link cta-link-primary" href="{{ \Illuminate\Support\Facades\Route::has('register') ? route('register') : '/register' }}">Découvrir la solution</a>
                                        <a class="btn-outline cta-link" href="{{ route('contact') }}">Demander une démonstration</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="slide-bg-img" data-bg-img="/images/landing/hero/hero-bg-3.jpg">
                        <div class="overlay-gradient-color"></div>
                    </div>
                    <div class="container">
                        <div class="hero-text-area content-always-light">
                            <div class="row g-0">
                                <div class="col-12 col-lg-8">
                                    <div class="pre-title">Pilotage Hospitalier Intelligent</div>
                                    <h2 class="slide-title">Des décisions cliniques et stratégiques éclairées par la <span class="featured-text">data</span></h2>
                                    <p class="slide-subtitle">MedKey transforme vos données hospitalières en indicateurs clairs et actionnables.
                                    Suivez l’activité médicale, la performance des services, la disponibilité des ressources et les alertes critiques en temps réel pour piloter votre établissement avec précision, anticipation et sérénité.</p>
                                    <div class="cta-links-area">
                                        <a class="btn-solid cta-link cta-link-primary" href="{{ \Illuminate\Support\Facades\Route::has('register') ? route('register') : '/register' }}">Commencer gratuitement</a>
                                        <a class="btn-outline cta-link" href="{{ route('contact') }}">Demander une démonstration</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="swiper-slide">
                    <div class="slide-bg-img" data-bg-img="/images/landing/hero/hero-bg-1.jpg">
                        <div class="overlay-gradient-color"></div>
                    </div>
                    <div class="container">
                        <div class="hero-text-area content-always-light">
                            <div class="row g-0">
                                <div class="col-12 col-lg-8">
                                    <div class="pre-title">Santé Numérique</div>
                                    <h1 class="slide-title">Gérez votre établissement avec un SIH <span class="featured-text">intelligent</span></h1>
                                    <p class="slide-subtitle">Medkey centralise la gestion des patients, des soins et des ressources de votre établissement de santé en une seule plateforme intégrée et sécurisée.</p>
                                    <div class="cta-links-area">
                                        <a class="btn-solid cta-link cta-link-primary" href="{{ \Illuminate\Support\Facades\Route::has('register') ? route('register') : '/register' }}">Commencer gratuitement</a>
                                        <a class="btn-outline cta-link" href="{{ route('contact') }}">Demander une démonstration</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="slides-state h-align  ">
          <div class="slide-num curent-slide  "></div>
          <!--Add Pagination-->
          <div class="swiper-pagination"></div>
          <div class="slide-num slides-count  "></div>
        </div>
            <div class="slider-stacked-arrows">
          <div class="swiper-button-prev   ">
            <div class="left-arrow"><i class="bi bi-chevron-left icon "></i>
            </div>
          </div>
          <div class="swiper-button-next  ">
            <div class="right-arrow"><i class="bi bi-chevron-right icon "></i>
            </div>
          </div>
        </div>
        </div>
    </section>

    <section class="services services-boxed mega-section" id="services">
        <div class="container">
            <div class="sec-heading centered">
                <div class="content-area">
                    <span class="pre-title wow fadeInUp" data-wow-delay=".2s">Nos Fonctionnalités</span>
                    <h2 class="title wow fadeInUp" data-wow-delay=".4s">Modules SIH <span class="hollow-text">essentiels</span></h2>
                </div>
            </div>
            <div class="row gx-4 gy-4">
                <div class="col-12 col-md-6 col-lg-4"><div class="service-box wow fadeInUp" data-wow-delay=".1s"><div class="service-icon"><i class="flaticon-profile"></i></div><h3 class="service-title">Dossier Patient Électronique</h3><p class="service-text">Centralisez l’historique médical complet de chaque patient : consultations, ordonnances, résultats d’analyses et imagerie.</p></div></div>
                <div class="col-12 col-md-6 col-lg-4"><div class="service-box wow fadeInUp" data-wow-delay=".2s"><div class="service-icon"><i class="flaticon-web-development"></i></div><h3 class="service-title">Gestion des Admissions</h3><p class="service-text">Simplifiez l’accueil et le circuit patient, de la préadmission jusqu’à la sortie, avec suivi en temps réel des lits.</p></div></div>
                <div class="col-12 col-md-6 col-lg-4"><div class="service-box wow fadeInUp" data-wow-delay=".3s"><div class="service-icon"><i class="flaticon-strategy"></i></div><h3 class="service-title">Planification des Soins</h3><p class="service-text">Organisez les plannings infirmiers et médicaux, gérez les gardes et optimisez l’affectation des ressources soignantes.</p></div></div>
                <div class="col-12 col-md-6 col-lg-4"><div class="service-box wow fadeInUp" data-wow-delay=".4s"><div class="service-icon"><i class="flaticon-nanotechnology"></i></div><h3 class="service-title">Gestion des Stocks Médicaux</h3><p class="service-text">Suivez les stocks de médicaments et de consommables, gérez les commandes et évitez les ruptures critiques.</p></div></div>
                <div class="col-12 col-md-6 col-lg-4"><div class="service-box wow fadeInUp" data-wow-delay=".5s"><div class="service-icon"><i class="flaticon-content-management"></i></div><h3 class="service-title">Facturation &amp; Cotation</h3><p class="service-text">Automatisez la facturation des actes, la cotation CCAM/NGAP et les transmissions aux organismes payeurs.</p></div></div>
                <div class="col-12 col-md-6 col-lg-4"><div class="service-box wow fadeInUp" data-wow-delay=".6s"><div class="service-icon"><i class="flaticon-aim"></i></div><h3 class="service-title">Rapports &amp; Indicateurs Qualité</h3><p class="service-text">Tableaux de bord DMS, taux d’occupation, indicateurs IPAQSS — pilotez la performance de votre établissement.</p></div></div>
            </div>
        </div>
    </section>

    <section class="about-us mega-section" id="about">
        <div class="container">
            <div class="content-block">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-6">
                        <span class="pre-title wow fadeInUp">À propos de nous</span>
                        <h2 class="title wow fadeInUp">La référence en <span class="hollow-text">gestion</span> de l’information hospitalière depuis <span class="featured-text">2018</span></h2>
                        <p class="about-text">Medkey accompagne les établissements de santé dans leur transition numérique en combinant expertise médicale et technologie cloud. Notre SIH s’adapte aux cliniques, hôpitaux et réseaux de soins.</p>
                        <div class="info-items-list">
                            <div class="row">
                                <div class="col-12 col-md-6"><div class="info-item"><i class="flaticon-medal info-icon"></i><div class="info-content"><h5>Hébergement HDS</h5><p>Données de santé hébergées chez un prestataire certifié HDS, conforme RGPD.</p></div></div></div>
                                <div class="col-12 col-md-6"><div class="info-item"><i class="flaticon-game-console info-icon"></i><div class="info-content"><h5>Disponible 24h/24</h5><p>SLA 99,9% de disponibilité, indispensable pour les soins critiques.</p></div></div></div>
                                <div class="col-12 col-md-6"><div class="info-item"><i class="flaticon-map info-icon"></i><div class="info-content"><h5>Déployé dans 8 pays</h5><p>Solution adaptée aux contextes réglementaires africains et européens.</p></div></div></div>
                                <div class="col-12 col-md-6"><div class="info-item"><i class="flaticon-technical-support-1 info-icon"></i><div class="info-content"><h5>Support médical dédié</h5><p>Équipe formée aux enjeux de santé, à votre disposition.</p></div></div></div>
                            </div>
                        </div>
                        <a class="btn-solid" href="{{ route('contact') }}">Nous contacter</a>
                    </div>
                    <div class="col-12 col-lg-6"><div class="img-area"><img class="img-fluid" src="/images/landing/about/1.png" alt="À propos Medkey"></div></div>
                </div>
            </div>
            <div class="content-block mt-5 pt-5">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-6 order-2 order-lg-1"><div class="img-area"><img class="img-fluid" src="/images/landing/about/2.png" alt="Pourquoi Medkey"></div></div>
                    <div class="col-12 col-lg-6 order-1 order-lg-2">
                        <span class="pre-title wow fadeInUp">Pourquoi nous choisir</span>
                        <h2 class="title wow fadeInUp">Pourquoi les établissements de santé font confiance à <span class="featured-text">Medkey</span></h2>
                        <p class="about-text">Nous concevons notre SIH avec des professionnels de santé pour répondre aux réalités du terrain, pas aux contraintes technologiques.</p>
                        <ul class="list-unstyled">
                            <li class="mb-3"><strong>01. Interopérabilité native</strong> — Compatible HL7 FHIR et DICOM pour s’intégrer avec votre matériel médical et vos laboratoires.</li>
                            <li class="mb-3"><strong>02. Déploiement en 72h</strong> — Migration des données et formation de vos équipes incluses dès le démarrage.</li>
                            <li><strong>03. Réduction des erreurs médicales</strong> — Alertes d’interactions médicamenteuses et traçabilité complète des prescriptions.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="stats stats-counter mega-section js-stats-counter" id="stats">
        <div class="container">
            <div class="row">
                <div class="col-12 col-md-6 col-lg-3"><div class="stat-box"><i class="flaticon-project-management stat-icon"></i><div class="stat-content"><span class="counter" data-from="0" data-to="3500" data-speed="3000" data-refresh-interval="50"></span><span class="sign">+</span><p class="stat-title">Patients traités</p></div></div></div>
                <div class="col-12 col-md-6 col-lg-3"><div class="stat-box"><i class="flaticon-profile stat-icon"></i><div class="stat-content"><span class="counter" data-from="0" data-to="85" data-speed="3000" data-refresh-interval="50"></span><span class="sign">%</span><p class="stat-title">Taux d’occupation des lits</p></div></div></div>
                <div class="col-12 col-md-6 col-lg-3"><div class="stat-box"><i class="flaticon-aim stat-icon"></i><div class="stat-content"><span class="counter" data-from="0" data-to="120" data-speed="3000" data-refresh-interval="50"></span><span class="sign">+</span><p class="stat-title">Nombre de chirurgies</p></div></div></div>
                <div class="col-12 col-md-6 col-lg-3"><div class="stat-box"><i class="flaticon-strategy stat-icon"></i><div class="stat-content"><span class="counter" data-from="0" data-to="95" data-speed="3000" data-refresh-interval="50"></span><span class="sign">%</span><p class="stat-title">Satisfaction des patients</p></div></div></div>
            </div>
        </div>
    </section>

   

    <section class="medical-services mega-section" id="medical-services">
        <div class="container">
            <div class="sec-heading centered">
                <div class="content-area">
                    <h2 class="title">Services médicaux</h2>
                </div>
            </div>
            <div class="row medical-services-grid justify-content-center">
                <div class="col-6 col-md-4 col-lg-2 service-item">
                    <div class="icon-wrap"><i class="bi bi-heart-pulse-fill"></i></div>
                    <h3 class="service-label">Maternité</h3>
                </div>
                <div class="col-6 col-md-4 col-lg-2 service-item">
                    <div class="icon-wrap"><i class="bi bi-person-fill"></i></div>
                    <h3 class="service-label">Pédiatrie</h3>
                </div>
                <div class="col-6 col-md-4 col-lg-2 service-item">
                    <div class="icon-wrap"><i class="bi bi-bandaid-fill"></i></div>
                    <h3 class="service-label">Chirurgie</h3>
                </div>
                <div class="col-6 col-md-4 col-lg-2 service-item">
                    <div class="icon-wrap"><i class="bi bi-image-fill"></i></div>
                    <h3 class="service-label">Imagerie</h3>
                </div>
                <div class="col-6 col-md-4 col-lg-2 service-item">
                    <div class="icon-wrap"><i class="bi bi-eyedropper"></i></div>
                    <h3 class="service-label">Laboratoire</h3>
                </div>
                <div class="col-6 col-md-4 col-lg-2 service-item">
                    <div class="icon-wrap"><i class="bi bi-hospital-fill"></i></div>
                    <h3 class="service-label">Infirmerie</h3>
                </div>
            </div>
        </div>
    </section>

    <section class="pricing mega-section" id="pricing-1">
        <div class="container">
            <div class="sec-heading">
                <div class="content-area">
                    <span class="pre-title">Nos offres</span>
                    <h2 class="title"><span class="hollow-text">Tarifs</span> adaptés aux établissements de santé</h2>
                    <p class="subtitle">Des plans pensés pour le contexte SIH Medkey, de la structure en démarrage au réseau hospitalier multi-sites.</p>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-6 col-xl-3 mx-auto price-plan">
                    <div class="plan">
                        <div class="plan-head">
                            <i class="flaticon-nft-1 plan-icon"></i>
                            <h4 class="plane-name">Plan Gratuit</h4>
                            <div class="plan-price">
                                <h3 class="price">0<sup class="currency-symbol">FCFA</sup></h3>
                                <span class="per">par mois</span>
                            </div>
                        </div>
                        <div class="plan-details">
                            <ul class="plan-list">
                                <li class="plan-feat"><span class="feat-text">Gestion des Patients</span></li>
                                <li class="plan-feat"><span class="feat-text">Gestion Mouvement Patient</span></li>
                                <li class="plan-feat"><span class="feat-text">Gestion Pharmacies</span></li>
                                <li class="plan-feat"><span class="feat-text">Gestion de la caisse</span></li>
                                <li class="plan-feat"><span class="feat-text">Support standard</span></li>
                            </ul>
                        </div>
                        <div class="plan-cta"><a class="cta-btn btn-outline" href="{{ \Illuminate\Support\Facades\Route::has('register') ? route('register') : '/register' }}">Commencer</a></div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-3 mx-auto price-plan">
                    <div class="plan">
                        <div class="plan-head">
                            <i class="flaticon-virtual-reality plan-icon"></i>
                            <h4 class="plane-name">Plan Clinique</h4>
                            <div class="plan-price">
                                <h3 class="price">49<sup class="currency-symbol">FCFA</sup></h3>
                                <span class="per">par mois</span>
                            </div>
                        </div>
                        <div class="plan-details">
                            <ul class="plan-list">
                                <li class="plan-feat"><span class="feat-text">Rendez-vous & consultations</span></li>
                                <li class="plan-feat"><span class="feat-text">Hospitalisation & lits</span></li>
                                <li class="plan-feat"><span class="feat-text">Facturation, paiements & remboursements</span></li>
                                <li class="plan-feat"><span class="feat-text">Stock & administration des produits</span></li>
                                <li class="plan-feat"><span class="feat-text">Tableaux de bord opérationnels</span></li>
                            </ul>
                        </div>
                        <div class="plan-cta"><a class="cta-btn btn-outline" href="{{ \Illuminate\Support\Facades\Route::has('register') ? route('register') : '/register' }}">Choisir ce plan</a></div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-3 mx-auto price-plan">
                    <div class="plan featured">
                        <div class="plan-head">
                            <i class="flaticon-box plan-icon"></i>
                            <h4 class="plane-name">Plan Hôpital</h4>
                            <div class="plan-price">
                                <h3 class="price">99<sup class="currency-symbol">FCFA</sup></h3>
                                <span class="per">par mois</span>
                            </div>
                        </div>
                        <div class="plan-details">
                            <ul class="plan-list">
                                <li class="plan-feat"><span class="feat-text">Tous les modules cliniques & administratifs</span></li>
                                <li class="plan-feat"><span class="feat-text">Annuaire professionnel & suivi des activités</span></li>
                                <li class="plan-feat"><span class="feat-text">Recouvrement & contrôle financier avancé</span></li>
                                <li class="plan-feat"><span class="feat-text">Traçabilité, alertes & notifications</span></li>
                                <li class="plan-feat"><span class="feat-text">Support prioritaire</span></li>
                            </ul>
                        </div>
                        <div class="plan-cta"><a class="cta-btn btn-outline" href="{{ \Illuminate\Support\Facades\Route::has('register') ? route('register') : '/register' }}">Choisir ce plan</a></div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-3 mx-auto price-plan">
                    <div class="plan">
                        <div class="plan-head">
                            <i class="flaticon-basic-shapes plan-icon"></i>
                            <h4 class="plane-name">Plan Réseau / CHU</h4>
                            <div class="plan-price">
                                <h3 class="price">Sur devis</h3>
                                <span class="per">multi-sites</span>
                            </div>
                        </div>
                        <div class="plan-details">
                            <ul class="plan-list">
                                <li class="plan-feat"><span class="feat-text">Déploiement multi-établissements</span></li>
                                <li class="plan-feat"><span class="feat-text">Paramétrage avancé par site</span></li>
                                <li class="plan-feat"><span class="feat-text">Interopérabilité & reporting consolidé</span></li>
                                <li class="plan-feat"><span class="feat-text">Gouvernance des accès centralisée</span></li>
                                <li class="plan-feat"><span class="feat-text">Accompagnement projet dédié</span></li>
                            </ul>
                        </div>
                        <div class="plan-cta"><a class="cta-btn btn-outline" href="{{ route('contact') }}">Nous contacter</a></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="testimonials testimonials-1-col has-dark-bg mega-section" id="testimonials-img-bg">
        <div class="overlay-photo-image-bg parallax" data-bg-img="/images/landing/sections-bg-images/1.jpg" data-bg-opacity=".25"></div>
        <div class="container">
            <div class="sec-heading centered"><div class="content-area"><span class="pre-title">testimonials</span><h2 class="title">customers <span class="hollow-text">testmonials</span></h2></div></div>
            <div class="swiper-container">
                <div class="swiper-wrapper">
                    <div class="swiper-slide"><div class="testmonial-card"><div class="testimonial-content"><div class="customer-img"><img class="img-fluid" src="/images/landing/testimonials/1.png" alt="Dr Jean-Pierre Matumona"></div><div class="customer-testimonial"><p class="testimonial-text">Medkey a transformé notre gestion des urgences. Le suivi des patients en temps réel a réduit nos délais de prise en charge de 40%.</p><div class="customer-info"><h6 class="customer-name">Dr. Jean-Pierre Matumona</h6><span class="customer-role">Chef des Urgences — Clinique Ngaliema, Kinshasa</span></div></div></div></div></div>
                    <div class="swiper-slide"><div class="testmonial-card"><div class="testimonial-content"><div class="customer-img"><img class="img-fluid" src="/images/landing/testimonials/2.png" alt="Isabelle Moreira"></div><div class="customer-testimonial"><p class="testimonial-text">L’intégration avec notre laboratoire s’est faite sans friction. Les résultats arrivent directement dans le dossier patient, sans ressaisie.</p><div class="customer-info"><h6 class="customer-name">Isabelle Moreira</h6><span class="customer-role">Directrice des Soins — Hôpital Général de Référence</span></div></div></div></div></div>
                    <div class="swiper-slide"><div class="testmonial-card"><div class="testimonial-content"><div class="customer-img"><img class="img-fluid" src="/images/landing/testimonials/3.png" alt="Dr Fatou Diallo"></div><div class="customer-testimonial"><p class="testimonial-text">La conformité RGPD et l’hébergement HDS étaient non-négociables. Medkey répond à toutes nos exigences réglementaires.</p><div class="customer-info"><h6 class="customer-name">Dr. Fatou Diallo</h6><span class="customer-role">Directrice Médicale — Groupe Santé Sahel</span></div></div></div></div></div>
                </div>
            </div>
        </div>
    </section>

   

    <section class="take-action elf-section has-dark-bg" id="take-action">
        <div class="overlay-photo-image-bg" data-bg-img="/images/landing/sections-bg-images/2.jpg" data-bg-opacity=".2"></div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-12 col-lg-9">
                    <h2 class="title">
                        Prêt à faire entrer votre établissement dans une nouvelle ère de la santé digitale ?
                    </h2>
                    <p class="subtitle">
                        Déployez MedKey en quelques minutes et transformez la gestion de votre établissement.
                        <strong>Accès gratuit pendant 90 jours</strong>, sans engagement et sans carte bancaire.
                    </p>
                </div>
                <div class="col-12 col-lg-3 text-lg-end">
                    <a class="btn-solid" href="{{ \Illuminate\Support\Facades\Route::has('register') ? route('register') : '/register' }}">Commencer gratuitement</a>
                </div>
            </div>
        </div>
    </section>
@endsection
