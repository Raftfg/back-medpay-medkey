@extends('layouts.landing')

@section('title', 'Politique de cookies | Medkey')
@section('header_class', 'page-header inner-page-header header-basic')

@section('content')
    <section class="d-flex align-items-center page-hero inner-page-hero">
        <div class="overlay-photo-image-bg" data-bg-img="/images/landing/hero/inner-page-hero.jpg" data-bg-opacity=".75"></div>
        <div class="container">
            <div class="hero-text-area centerd">
                <h1 class="hero-title">Politique de cookies</h1>
                <nav aria-label="breadcrumb"><ul class="breadcrumb"><li class="breadcrumb-item"><a class="breadcrumb-link" href="{{ route('home') }}">Accueil</a></li><li class="breadcrumb-item active">Cookies</li></ul></nav>
            </div>
        </div>
    </section>

    <section class="mega-section">
        <div class="container">
            <div class="row">
                <div class="col-12 col-lg-8 mx-auto">
                    <h3>Définition d’un cookie</h3>
                    <p>Un cookie est un petit fichier stocké par votre navigateur pour assurer le bon fonctionnement du site et mémoriser certaines préférences.</p>

                    <h3>Cookies strictement nécessaires</h3>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead><tr><th>Nom</th><th>Finalité</th></tr></thead>
                            <tbody>
                                <tr><td><code>medkey_session</code></td><td>Maintien de la session utilisateur</td></tr>
                                <tr><td><code>XSRF-TOKEN</code></td><td>Protection CSRF</td></tr>
                                <tr><td><code>remember_web_*</code></td><td>Option de connexion persistante</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <h3>Cookies de préférences</h3>
                    <p>La préférence de thème est stockée côté navigateur via <code>localStorage</code> avec la clé <code>ThemeColor</code>.</p>

                    <h3>Cookies analytiques</h3>
                    <p>Des mesures d’audience peuvent être utilisées pour améliorer l’expérience utilisateur, selon les paramétrages de l’établissement.</p>

                    <h3>Cookies tiers</h3>
                    <p>Le site charge des ressources tierces comme Google Fonts, susceptibles d’impliquer des requêtes externes.</p>

                    <h3>Gestion par navigateur</h3>
                    <p>Vous pouvez gérer vos préférences cookies depuis les paramètres de Chrome, Firefox, Edge ou Safari.</p>

                    <h3>Modifications</h3>
                    <p>Cette politique peut être mise à jour en fonction des évolutions techniques et réglementaires.</p>

                    <a class="btn-solid mt-4" href="{{ route('contact') }}">Nous contacter</a>
                </div>
            </div>
        </div>
    </section>
@endsection
