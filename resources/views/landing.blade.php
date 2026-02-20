@extends('layouts.landing')

@section('title', 'Medkey | SIH Cloud')
@section('meta_description', 'Medkey, système d’information hospitalier cloud multi-tenant pour tous types d’hôpitaux.')
@section('header_class', 'page-header content-always-light header-basic')

@section('content')
    <section class="page-hero hero-swiper-slider slide-effect d-flex align-items-center" id="page-hero">
        <div class="particles-js bubels" id="particles-js"></div>
        <div class="slider swiper-container">
            <div class="swiper-wrapper">
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
                                        <a class="btn-outline cta-link" href="{{ route('contact') }}">Nous contacter</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="slide-bg-img" data-bg-img="/images/landing/hero/hero-bg-2.jpg">
                        <div class="overlay-gradient-color"></div>
                    </div>
                    <div class="container">
                        <div class="hero-text-area content-always-light">
                            <div class="row g-0">
                                <div class="col-12 col-lg-8">
                                    <div class="pre-title">Dossier Patient Centralisé</div>
                                    <h2 class="slide-title">Des dossiers médicaux <span class="featured-text">accessibles</span>, complets et sécurisés</h2>
                                    <p class="slide-subtitle">Chaque professionnel de santé accède en temps réel aux informations patients dont il a besoin, au bon moment, depuis n’importe quel poste.</p>
                                    <div class="cta-links-area">
                                        <a class="btn-solid cta-link cta-link-primary" href="{{ \Illuminate\Support\Facades\Route::has('register') ? route('register') : '/register' }}">Découvrir la solution</a>
                                        <a class="btn-outline cta-link" href="{{ route('contact') }}">Nous contacter</a>
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
                                    <div class="pre-title">Pilotage en Temps Réel</div>
                                    <h2 class="slide-title">Des décisions cliniques éclairées par la <span class="featured-text">data</span></h2>
                                    <p class="slide-subtitle">Tableaux de bord hospitaliers, indicateurs qualité, alertes en temps réel — tout pour piloter votre établissement de santé avec précision.</p>
                                    <div class="cta-links-area">
                                        <a class="btn-solid cta-link cta-link-primary" href="{{ \Illuminate\Support\Facades\Route::has('register') ? route('register') : '/register' }}">Voir les fonctionnalités</a>
                                        <a class="btn-outline cta-link" href="{{ route('contact') }}">Nous contacter</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="swiper-button-prev"><i class="bi bi-arrow-left icon"></i></div>
            <div class="swiper-button-next"><i class="bi bi-arrow-right icon"></i></div>
        </div>
    </section>

    <section class="services services-boxed mega-section" id="services">
        <div class="container">
            <div class="sec-heading centered">
                <div class="content-area">
                    <span class="pre-title wow fadeInUp" data-wow-delay=".2s">Fonctionnalités</span>
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

    <section class="portfolio portfolio-grid mega-section" id="portfolio">
        <div class="container">
            <div class="sec-heading centered">
                <div class="content-area">
                    <span class="pre-title">Nos modules</span>
                    <h2 class="title">Un SIH pour chaque <span class="hollow-text">service</span></h2>
                </div>
            </div>
            <div class="portfolio-btn-list wow fadeInUp" data-wow-delay=".2s">
                <button class="portfolio-btn active" data-filter="*">All</button>
                <button class="portfolio-btn" data-filter=".mobile">Urgences</button>
                <button class="portfolio-btn" data-filter=".web">Pharmacie</button>
                <button class="portfolio-btn" data-filter=".data">Bloc Opératoire</button>
                <button class="portfolio-btn" data-filter=".hosting">Laboratoire</button>
            </div>
            <div class="portfolio-group wow fadeIn" data-wow-delay=".4s">
                <div class="row">
                    <div class="col-12 col-md-6 col-lg-4 portfolio-item mobile"><div class="item"><img src="/images/landing/portfolio/1.jpg" alt="Gestion des urgences"><div class="item-info"><h3>Gestion des Urgences</h3></div></div></div>
                    <div class="col-12 col-md-6 col-lg-4 portfolio-item web"><div class="item"><img src="/images/landing/portfolio/2.jpg" alt="Pharmacie"><div class="item-info"><h3>Pharmacie Hospitalière</h3></div></div></div>
                    <div class="col-12 col-md-6 col-lg-4 portfolio-item data"><div class="item"><img src="/images/landing/portfolio/3.jpg" alt="Bloc opératoire"><div class="item-info"><h3>Bloc Opératoire &amp; Anesthésie</h3></div></div></div>
                    <div class="col-12 col-md-6 col-lg-4 portfolio-item hosting"><div class="item"><img src="/images/landing/portfolio/4.jpg" alt="Laboratoire"><div class="item-info"><h3>Laboratoire d’Analyses</h3></div></div></div>
                    <div class="col-12 col-md-6 col-lg-4 portfolio-item hosting"><div class="item"><img src="/images/landing/portfolio/5.jpg" alt="Radiologie"><div class="item-info"><h3>Radiologie &amp; Imagerie</h3></div></div></div>
                    <div class="col-12 col-md-6 col-lg-4 portfolio-item mobile"><div class="item"><img src="/images/landing/portfolio/6.jpg" alt="Administration"><div class="item-info"><h3>Administration &amp; Facturation</h3></div></div></div>
                </div>
            </div>
        </div>
    </section>

    <section class="our-clients mega-section">
        <div class="container">
            <div class="sec-heading centered"><div class="content-area"><span class="pre-title">Partenaires</span><h2 class="title">Ils nous font <span class="hollow-text">confiance</span></h2></div></div>
            <div class="swiper-container">
                <div class="swiper-wrapper clients-logo-wrapper">
                    @for ($i = 1; $i <= 7; $i++)
                        <div class="swiper-slide">
                            <div class="client-logo"><img class="img-fluid logo" loading="lazy" src="/images/landing/clients-logos/{{ $i }}-white.png" alt="Logo client {{ $i }}"></div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    </section>

    <section class="pricing mega-section" id="pricing-1">
        <div class="container">
            <div class="sec-heading">
                <div class="content-area"><span class="pre-title">pricing plans</span><h2 class="title"><span class="hollow-text">affordable</span> pricing plans</h2></div>
            </div>
            <div class="row">
                <div class="col-12 col-md-6 col-xl-3 mx-auto price-plan"><div class="plan"><div class="plan-head"><i class="flaticon-nft-1 plan-icon"></i><h4 class="plane-name">free plan</h4><div class="plan-price"><h3 class="price">00<sup class="currency-symbol">$</sup></h3><span class="per">per project</span></div></div><div class="plan-details"><ul class="plan-list"><li class="plan-feat"><span class="feat-text">150 Lorem, ipsum dolor.</span></li><li class="plan-feat"><span class="feat-text">20 Lorem ipsum dolor sit.</span></li><li class="plan-feat"><span class="feat-text">Lorem ipsum dolor sit.</span></li><li class="plan-feat"><span class="feat-text">free Lorem ipsum dolor.</span></li><li class="plan-feat"><span class="feat-text">added Lorem ipsum dolor.</span></li></ul></div><div class="plan-cta"><a class="cta-btn btn-outline" href="{{ \Illuminate\Support\Facades\Route::has('register') ? route('register') : '/register' }}">select plan</a></div></div></div>
                <div class="col-12 col-md-6 col-xl-3 mx-auto price-plan"><div class="plan"><div class="plan-head"><i class="flaticon-virtual-reality plan-icon"></i><h4 class="plane-name">standerd plan</h4><div class="plan-price"><h3 class="price">85<sup class="currency-symbol">$</sup></h3><span class="per">per project</span></div></div><div class="plan-details"><ul class="plan-list"><li class="plan-feat"><span class="feat-text">150 Lorem, ipsum dolor.</span></li><li class="plan-feat"><span class="feat-text">20 Lorem ipsum dolor sit.</span></li><li class="plan-feat"><span class="feat-text">Lorem ipsum dolor sit.</span></li><li class="plan-feat"><span class="feat-text">free Lorem ipsum dolor.</span></li><li class="plan-feat"><span class="feat-text">added Lorem ipsum dolor.</span></li></ul></div><div class="plan-cta"><a class="cta-btn btn-outline" href="{{ \Illuminate\Support\Facades\Route::has('register') ? route('register') : '/register' }}">select plan</a></div></div></div>
                <div class="col-12 col-md-6 col-xl-3 mx-auto price-plan"><div class="plan featured"><div class="plan-head"><i class="flaticon-box plan-icon"></i><h4 class="plane-name">pro plan</h4><div class="plan-price"><h3 class="price">150<sup class="currency-symbol">$</sup></h3><span class="per">per project</span></div></div><div class="plan-details"><ul class="plan-list"><li class="plan-feat"><span class="feat-text">150 Lorem, ipsum dolor.</span></li><li class="plan-feat"><span class="feat-text">20 Lorem ipsum dolor sit.</span></li><li class="plan-feat"><span class="feat-text">Lorem ipsum dolor sit.</span></li><li class="plan-feat"><span class="feat-text">free Lorem ipsum dolor.</span></li><li class="plan-feat"><span class="feat-text">added Lorem ipsum dolor.</span></li></ul></div><div class="plan-cta"><a class="cta-btn btn-outline" href="{{ \Illuminate\Support\Facades\Route::has('register') ? route('register') : '/register' }}">select plan</a></div></div></div>
                <div class="col-12 col-md-6 col-xl-3 mx-auto price-plan"><div class="plan"><div class="plan-head"><i class="flaticon-basic-shapes plan-icon"></i><h4 class="plane-name">ultimate plan</h4><div class="plan-price"><h3 class="price">210<sup class="currency-symbol">$</sup></h3><span class="per">per project</span></div></div><div class="plan-details"><ul class="plan-list"><li class="plan-feat"><span class="feat-text">150 Lorem, ipsum dolor.</span></li><li class="plan-feat"><span class="feat-text">20 Lorem ipsum dolor sit.</span></li><li class="plan-feat"><span class="feat-text">Lorem ipsum dolor sit.</span></li><li class="plan-feat"><span class="feat-text">free Lorem ipsum dolor.</span></li><li class="plan-feat"><span class="feat-text">added Lorem ipsum dolor.</span></li></ul></div><div class="plan-cta"><a class="cta-btn btn-outline" href="{{ route('contact') }}">contact us</a></div></div></div>
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

    <section class="blog blog-home mega-section" id="blog">
        <div class="container">
            <div class="sec-heading">
                <div class="content-area"><span class="pre-title">blog</span><h2 class="title">latest <span class="hollow-text">news</span></h2></div>
            </div>
            <div class="row">
                <div class="col-12 col-lg-4"><div class="post-box"><a class="post-link" href="#0"><div class="post-img-wrapper"><img class="post-img" src="/images/landing/blog/1.jpg" alt=""><span class="post-date"><span class="day">05</span>oct 2022</span></div></a><div class="post-summary"><div class="post-info"><a class="info post-cat" href="#0"><i class="bi bi-bookmark icon"></i>hosting</a><a class="info post-author" href="#0"><i class="bi bi-person icon"></i>Allan Moore</a></div><div class="post-text"><h2 class="post-title">How litespeed technology works to speed up your site</h2><p class="post-excerpt">Lorem ipsum dolor sit, amet consectetur adipisicing elit.Iure nulla dolorem, voluptates molestiae</p></div></div></div></div>
                <div class="col-12 col-lg-4"><div class="post-box"><a class="post-link" href="#0"><div class="post-img-wrapper"><img class="post-img" src="/images/landing/blog/2.jpg" alt=""><span class="post-date"><span class="day">15</span>sep 2022</span></div></a><div class="post-summary"><div class="post-info"><a class="info post-cat" href="#0"><i class="bi bi-bookmark icon"></i>web dev</a><a class="info post-author" href="#0"><i class="bi bi-person icon"></i>mhmd amin</a></div><div class="post-text"><h2 class="post-title">give your website a new look and feel with themes</h2><p class="post-excerpt">Lorem ipsum dolor sit, amet consectetur adipisicing elit.Iure nulla dolorem, voluptates molestiae</p></div></div></div></div>
                <div class="col-12 col-lg-4"><div class="post-box"><a class="post-link" href="#0"><div class="post-img-wrapper"><img class="post-img" src="/images/landing/blog/3.jpg" alt=""><span class="post-date"><span class="day">27</span>aug 2022</span></div></a><div class="post-summary"><div class="post-info"><a class="info post-cat" href="#0"><i class="bi bi-bookmark icon"></i>SEO</a><a class="info post-author" href="#0"><i class="bi bi-person icon"></i>yusuf amin</a></div><div class="post-text"><h2 class="post-title">the role of domain names in SEO world explained</h2><p class="post-excerpt">Lorem ipsum dolor sit, amet consectetur adipisicing elit.Iure nulla dolorem, voluptates molestiae</p></div></div></div></div>
            </div>
        </div>
    </section>

    <section class="take-action elf-section has-dark-bg" id="take-action">
        <div class="overlay-photo-image-bg" data-bg-img="/images/landing/sections-bg-images/2.jpg" data-bg-opacity=".2"></div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-12 col-lg-9"><h2 class="title">Prêt à moderniser votre système d’information hospitalier ?</h2><p class="subtitle">Commencez gratuitement pendant 90 jours. Aucune carte bancaire requise.</p></div>
                <div class="col-12 col-lg-3 text-lg-end"><a class="btn-solid" href="{{ \Illuminate\Support\Facades\Route::has('register') ? route('register') : '/register' }}">Commencer gratuitement</a></div>
            </div>
        </div>
    </section>
@endsection
