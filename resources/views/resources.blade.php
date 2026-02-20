@extends('layouts.landing')

@section('title', 'Ressources | Medkey')
@section('header_class', 'page-header inner-page-header header-basic')

@section('content')
    <style>
        .resources-page {
            background: var(--clr-dark-blue);
        }
        .resources-page .sec-heading .pre-title,
        .resources-page .sec-heading .title {
            color: var(--clr-white);
        }
        .resources-filter {
            display: flex;
            gap: 1.2rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }
        .resources-filter .filter-item {
            color: rgba(255, 255, 255, 0.92);
            font-weight: 500;
        }
        .resources-filter .filter-item.active {
            color: var(--clr-main);
        }
        .resources-grid .resource-card {
            border: 1px solid rgba(255, 255, 255, 0.13);
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.02);
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .resources-grid .resource-card .card-top {
            padding: 1.5rem 1.3rem;
            text-align: center;
        }
        .resources-grid .resource-card .icon-badge {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(9, 175, 244, 0.12);
            margin-bottom: 1rem;
        }
        .resources-grid .resource-card .icon-badge i {
            font-size: 1.9rem;
            color: var(--clr-main);
        }
        .resources-grid .resource-card .card-title {
            color: var(--clr-white);
            margin-bottom: 0;
        }
        .resources-grid .resource-card .card-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding: 0.95rem 1.3rem;
            text-align: center;
        }
        .resources-grid .resource-card .read-link {
            color: var(--clr-main);
            font-weight: 600;
        }
    </style>

    <section class="resources-page mega-section" id="resources-page">
        <div class="container">
            <div class="sec-heading centered mb-4">
                <div class="content-area">
                    <h2 class="title">Nos Ressources</h2>
                </div>
            </div>

            <div class="resources-filter">
                <span class="filter-item active">• Tout</span>
                <span class="filter-item">Guides &amp; Manuels</span>
                <span class="filter-item">Tutoriels Vidéo</span>
                <a class="filter-item" href="{{ route('faq') }}">FAQ</a>
            </div>

            <div class="row g-4 resources-grid">
                <div class="col-12 col-md-6 col-lg-4">
                    <article class="resource-card">
                        <div class="card-top">
                            <div class="icon-badge"><i class="bi bi-gear"></i></div>
                            <h3 class="card-title">Guide de l'Administrateur</h3>
                        </div>
                        <div class="card-bottom"><a class="read-link" href="{{ route('contact') }}">En savoir plus <i class="bi bi-arrow-right"></i></a></div>
                    </article>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <article class="resource-card">
                        <div class="card-top">
                            <div class="icon-badge"><i class="bi bi-journal-text"></i></div>
                            <h3 class="card-title">Manuel du Personnel soignant</h3>
                        </div>
                        <div class="card-bottom"><a class="read-link" href="{{ route('contact') }}">En savoir plus <i class="bi bi-arrow-right"></i></a></div>
                    </article>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <article class="resource-card">
                        <div class="card-top">
                            <div class="icon-badge"><i class="bi bi-people"></i></div>
                            <h3 class="card-title">Guide du Responsable d'établissement</h3>
                        </div>
                        <div class="card-bottom"><a class="read-link" href="{{ route('contact') }}">En savoir plus <i class="bi bi-arrow-right"></i></a></div>
                    </article>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <article class="resource-card">
                        <div class="card-top">
                            <div class="icon-badge"><i class="bi bi-play-circle"></i></div>
                            <h3 class="card-title">Vidéo : Inscriptions</h3>
                        </div>
                        <div class="card-bottom"><a class="read-link" href="{{ route('contact') }}">En savoir plus <i class="bi bi-arrow-right"></i></a></div>
                    </article>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <article class="resource-card">
                        <div class="card-top">
                            <div class="icon-badge"><i class="bi bi-code-slash"></i></div>
                            <h3 class="card-title">Documentation API</h3>
                        </div>
                        <div class="card-bottom"><a class="read-link" href="{{ route('contact') }}">En savoir plus <i class="bi bi-arrow-right"></i></a></div>
                    </article>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <article class="resource-card">
                        <div class="card-top">
                            <div class="icon-badge"><i class="bi bi-headset"></i></div>
                            <h3 class="card-title">Assistance Technique</h3>
                        </div>
                        <div class="card-bottom"><a class="read-link" href="{{ route('faq') }}">En savoir plus <i class="bi bi-arrow-right"></i></a></div>
                    </article>
                </div>
            </div>
        </div>
    </section>
@endsection
