<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="@yield('meta_description', 'Medkey, SIH cloud multi-tenant pour les établissements de santé.')">
    <title>@yield('title', 'Medkey')</title>

    <link rel="icon" href="/images/landing/logo/fav-icon.png">

    <link rel="stylesheet" href="/css/vendors/bootstrap.min.css">
    <link rel="stylesheet" href="/css/vendors/animate.css">
    <link rel="stylesheet" href="/css/vendors/swiper-bundle.min.css">
    <link rel="stylesheet" href="/fonts/flaticon/flaticon.css">
    <link rel="stylesheet" href="/fonts/fontawesome/fontawesome.css">
    <link rel="stylesheet" href="/fonts/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="/css/vendors/jquery.fancybox.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="/css/landing.css">
    <style>
        #page-header .controls-box {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        #page-header .nav-cta-area {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-inline-end: 0.25rem;
        }

        #page-header .nav-cta-area .cta-link {
            white-space: nowrap;
            margin-bottom: 0;
            padding: 0.58rem 1.35rem;
            font-size: 1rem;
            line-height: 1.05;
        }

        #page-header .links-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        #page-header .links-list .menu-item::marker {
            content: "";
        }

        @media (min-width: 1200px) {
            #page-header .menu-wrapper {
                margin-inline-start: 2.5rem;
            }
        }

        #page-header .links-list {
            list-style: none;
            padding-left: 0;
            margin: 0;
        }

        #page-header .header-logo .logo-img {
            max-height: 42px;
            width: auto;
        }

        #page-header .menu-link::before {
            display: none !important;
            content: none !important;
        }

        #page-header.content-always-light.header-basic .nav-cta-area .btn-solid,
        #page-header.is-sticky.header-basic .nav-cta-area .btn-solid {
            background-color: var(--clr-white);
            border-color: var(--clr-white);
            color: var(--clr-main);
        }

        #page-header.content-always-light.header-basic .nav-cta-area .btn-solid:hover,
        #page-header.is-sticky.header-basic .nav-cta-area .btn-solid:hover {
            background-color: rgba(255, 255, 255, 0.92);
            border-color: rgba(255, 255, 255, 0.92);
            color: var(--clr-main);
        }

        #page-header.content-always-light.header-basic .nav-cta-area .btn-outline,
        #page-header.is-sticky.header-basic .nav-cta-area .btn-outline {
            border-color: var(--clr-white);
            color: var(--clr-white);
            background-color: transparent;
        }

        #page-header.content-always-light.header-basic .nav-cta-area .btn-outline:hover,
        #page-header.is-sticky.header-basic .nav-cta-area .btn-outline:hover {
            background-color: var(--clr-white);
            border-color: var(--clr-white);
            color: var(--clr-main);
        }

        @media (max-width: 1199.98px) {
            #page-header .header-logo .logo-img {
                max-height: 38px;
            }

            #page-header .nav-cta-area {
                display: none;
            }
        }

        #page-footer .footer-col {
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            padding: 1.1rem 1.1rem 1rem;
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(2px);
            height: 100%;
        }

        #page-footer .footer-col-title {
            margin-bottom: 0.85rem;
            letter-spacing: 0.2px;
        }

        #page-footer .footer-menu-item {
            margin-bottom: 0.45rem;
        }

        #page-footer .footer-menu-link {
            opacity: 0.92;
            transition: all 0.25s ease;
        }

        #page-footer .footer-menu-link:hover {
            color: var(--clr-main);
            opacity: 1;
            padding-left: 0.18rem;
        }

        #page-footer .contact-info-card {
            margin-bottom: 0.75rem;
        }

        #page-footer .footer-text-about-us {
            opacity: 0.9;
        }

        #page-footer .contact-info-card,
        #page-footer .contact-info-card span,
        #page-footer .contact-info-card a {
            font-size: 0.7rem;
            font-weight: 400;
            line-height: 1.35;
        }
    </style>
</head>
<body class="@yield('body_class', 'dark-theme')">
    <div class="loading-screen" id="loading-screen">
        <span class="bar top-bar"></span>
        <span class="bar down-bar"></span>
        <span class="progress-line"></span>
        <span class="loading-counter"></span>
    </div>

    <header class="@yield('header_class', 'page-header content-always-light header-basic')" id="page-header">
        <div class="container">
            <nav class="menu-navbar">
                <div class="header-logo">
                    <a class="logo-link" href="{{ route('home') }}">
                        <img class="logo-img light-logo" loading="lazy" src="/images/landing/logo/logo1.png" alt="Medkey">
                        <img class="logo-img dark-logo" loading="lazy" src="/images/landing/logo/logo2.png" alt="Medkey">
                    </a>
                </div>

                <div class="links menu-wrapper">
                    <ul class="list-js links-list">
                        <li class="menu-item">
                            <a class="menu-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}#page-hero">Présentation</a>
                        </li>
                        <li class="menu-item">
                            <a class="menu-link" href="{{ route('home') }}#services">Fonctionnalités</a>
                        </li>
                        <li class="menu-item">
                            <a class="menu-link" href="{{ route('home') }}#pricing-1">Tarifs</a>
                        </li>
                        <li class="menu-item">
                            <a class="menu-link {{ request()->routeIs('resources') ? 'active' : '' }}" href="{{ route('resources') }}">Ressources</a>
                        </li>
                        <li class="menu-item">
                            <a class="menu-link {{ request()->routeIs('faq') ? 'active' : '' }}" href="{{ route('faq') }}">Support</a>
                        </li>
                        <li class="menu-item">
                            <a class="menu-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Contact</a>
                        </li>
                    </ul>
                </div>

                <div class="controls-box">
                    <div class="nav-cta-area">
                        <a class="btn-solid cta-link cta-link-primary nav-cta-btn" href="{{ route('free-access') }}">Accéder gratuitement</a>
                        <a class="btn-outline cta-link nav-cta-btn" href="/login">Connexion</a>
                    </div>
                    <div class="control menu-toggler">
                        <span></span><span></span><span></span>
                    </div>
                    <div class="mode-switcher">
                        <div class="switch-inner go-light" title="Passer en mode clair">
                            <i class="bi bi-sun icon"></i>
                        </div>
                        <div class="switch-inner go-dark" title="Passer en mode sombre">
                            <i class="bi bi-moon icon"></i>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    @yield('content')

    <footer class="page-footer dark-color-footer" id="page-footer">
        <div class="overlay-photo-image-bg" data-bg-img="/images/landing/sections-bg-images/footer-bg-1.jpg" data-bg-opacity=".25"></div>
        <div class="container">
            <div class="row footer-cols">
                <div class="col-12 col-md-8 col-lg-4 footer-col">
                    <img class="img-fluid footer-logo" loading="lazy" src="/images/landing/logo/logo2.png" alt="Medkey">
                    <div class="footer-col-content-wrapper">
                        <p class="footer-text-about-us">
                            Le SIH cloud qui modernise vos établissements de santé, du parcours patient à la performance financière.
                        </p>
                        <div class="sc-wrapper dir-row sc-flat">
                            <ul class="sc-list">
                                <li class="sc-item" title="LinkedIn"><a class="sc-link" href="https://www.linkedin.com/company/akasigroup/" target="_blank" rel="noopener noreferrer"><i class="fab fa-linkedin-in sc-icon"></i></a></li>
                                <li class="sc-item" title="X"><a class="sc-link" href="https://x.com/GroupAkasi" target="_blank" rel="noopener noreferrer"><i class="fab fa-x-twitter sc-icon"></i></a></li>
                                <li class="sc-item" title="Facebook"><a class="sc-link" href="https://www.facebook.com/akasiholding" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook-f sc-icon"></i></a></li>
                                <li class="sc-item" title="YouTube"><a class="sc-link" href="#0"><i class="fab fa-youtube sc-icon"></i></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-2 footer-col">
                    <h2 class="footer-col-title">Navigation</h2>
                    <div class="footer-col-content-wrapper">
                        <ul class="footer-menu">
                            <li class="footer-menu-item"><a class="footer-menu-link" href="{{ route('home') }}#page-hero">Présentation</a></li>
                            <li class="footer-menu-item"><a class="footer-menu-link" href="{{ route('home') }}#services">Fonctionnalités</a></li>
                            <li class="footer-menu-item"><a class="footer-menu-link" href="{{ route('home') }}#pricing-1">Tarifs</a></li>
                            <li class="footer-menu-item"><a class="footer-menu-link" href="{{ route('resources') }}">Ressources</a></li>
                            <li class="footer-menu-item"><a class="footer-menu-link" href="{{ route('faq') }}">Support</a></li>
                            <li class="footer-menu-item"><a class="footer-menu-link" href="{{ route('contact') }}">Contact</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-6 col-lg-3 footer-col">
                    <h2 class="footer-col-title">Légal</h2>
                    <div class="footer-col-content-wrapper">
                        <ul class="footer-menu">
                            <li class="footer-menu-item"><a class="footer-menu-link" href="{{ route('legal.confidentialite') }}">Confidentialité</a></li>
                            <li class="footer-menu-item"><a class="footer-menu-link" href="{{ route('legal.conditions') }}">Conditions</a></li>
                            <li class="footer-menu-item"><a class="footer-menu-link" href="{{ route('legal.mentions') }}">Mentions légales</a></li>
                            <li class="footer-menu-item"><a class="footer-menu-link" href="{{ route('legal.cookies') }}">Cookies</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-12 col-lg-3 footer-col">
                    <h2 class="footer-col-title">Contact</h2>
                    <div class="footer-col-content-wrapper">
                        <div class="contact-info-card">
                            <i class="bi bi-telephone icon"></i>
                            <span>ASHUA NH, USA: <a href="tel:+16038527935">(1 603) 852 79 35</a></span>
                        </div>
                        <div class="contact-info-card">
                            <i class="bi bi-telephone icon"></i>
                            <span>COTONOU, BENIN: <a href="tel:+2290195621919">(229) 01 95 62 19 19</a></span>
                        </div>
                        <div class="contact-info-card">
                            <i class="bi bi-telephone icon"></i>
                            <span>ABIDJAN, COTE D'IVOIRE: <a href="tel:+2250767257112">(225) 07 67 25 71 12</a></span>
                        </div>
                        <div class="contact-info-card">
                            <i class="bi bi-telephone icon"></i>
                            <span>NAIROBI, KENYA: <a href="tel:+254741896511">(254) 741 89 65 11</a></span>
                        </div>
                        <div class="contact-info-card">
                            <i class="bi bi-envelope icon"></i>
                            <a href="mailto:akasi-commercial@akasigroup.com">akasi-commercial@akasigroup.com</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="copyrights">
            <div class="container">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <p class="creadits">&copy; {{ date('Y') }} Medkey. Tous droits réservés.</p>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="terms-links">
                            <a href="/login">Connexion</a> |
                            <a href="{{ \Illuminate\Support\Facades\Route::has('register') ? route('register') : '/register' }}">Inscription</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <div class="back-to-top" id="back-to-top">
        <i class="bi bi-arrow-up icon"></i>
    </div>

    <script src="/js/vendors/jquery-3.6.1.min.js"></script>
    <script src="/js/vendors/appear.min.js"></script>
    <script src="/js/vendors/bootstrap.bundle.min.js"></script>
    <script src="/js/vendors/jquery.countTo.js"></script>
    <script src="/js/vendors/wow.min.js"></script>
    <script src="/js/vendors/swiper-bundle.min.js"></script>
    <script src="/js/vendors/particles.min.js"></script>
    <script src="/js/vendors/vanilla-tilt.min.js"></script>
    <script src="/js/vendors/isotope-min.js"></script>
    <script src="/js/vendors/jquery.fancybox.min.js"></script>
    <script src="/js/landing-main.js"></script>

    <script>
        (function () {
            var body = document.body;
            var switcher = document.querySelector('.mode-switcher');
            var stored = localStorage.getItem('ThemeColor');

            function applyTheme(theme) {
                if (theme === 'dark-theme') {
                    body.classList.add('dark-theme');
                    body.classList.remove('light-theme');
                    if (switcher) {
                        switcher.classList.add('dark-theme');
                        switcher.classList.remove('light-theme');
                    }
                } else {
                    body.classList.remove('dark-theme');
                    body.classList.add('light-theme');
                    if (switcher) {
                        switcher.classList.add('light-theme');
                        switcher.classList.remove('dark-theme');
                    }
                }
                localStorage.setItem('ThemeColor', theme);
            }

            applyTheme(stored === 'light-theme' ? 'light-theme' : 'dark-theme');

            if (switcher) {
                switcher.addEventListener('click', function () {
                    applyTheme(body.classList.contains('dark-theme') ? 'light-theme' : 'dark-theme');
                });
            }
        })();
    </script>

    @stack('scripts')
</body>
</html>
