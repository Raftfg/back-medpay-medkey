@extends('layouts.landing')

@section('title', 'Mentions légales | Medkey')
@section('header_class', 'page-header inner-page-header header-basic')

@section('content')
    <section class="d-flex align-items-center page-hero inner-page-hero">
        <div class="overlay-photo-image-bg" data-bg-img="/images/landing/hero/inner-page-hero.jpg" data-bg-opacity=".75"></div>
        <div class="container">
            <div class="hero-text-area centerd">
                <h1 class="hero-title">Mentions légales</h1>
                <nav aria-label="breadcrumb"><ul class="breadcrumb"><li class="breadcrumb-item"><a class="breadcrumb-link" href="{{ route('home') }}">Accueil</a></li><li class="breadcrumb-item active">Mentions légales</li></ul></nav>
            </div>
        </div>
    </section>

    <section class="mega-section">
        <div class="container">
            <div class="row">
                <div class="col-12 col-lg-8 mx-auto">
                    <h3>Éditeur du site</h3>
                    <p>Medkey — {{ config('custom.contact_address') }}</p>
                    <p>Email : {{ config('custom.contact_to_address') }}</p>
                    <p>Téléphone : {{ config('custom.contact_phone') }}</p>

                    <h3>Hébergement</h3>
                    <p>Les données de santé sont hébergées sur une infrastructure certifiée HDS adaptée aux exigences de sécurité et de disponibilité hospitalières.</p>

                    <h3>Propriété intellectuelle</h3>
                    <p>L’ensemble des contenus présents sur ce site (textes, visuels, éléments graphiques, marques, logos) est protégé et ne peut être reproduit sans autorisation préalable.</p>

                    <h3>Limitation de responsabilité</h3>
                    <p>Medkey met en œuvre les moyens nécessaires pour assurer l’exactitude des informations publiées, sans garantir l’absence totale d’erreur ou d’indisponibilité temporaire.</p>

                    <h3>Liens hypertextes</h3>
                    <p>Le site peut contenir des liens externes. Medkey n’est pas responsable du contenu de ces sites tiers ni de leur politique de confidentialité.</p>

                    <h3>Droit applicable</h3>
                    <p>Le présent site est soumis au droit applicable dans la juridiction de l’éditeur, sous réserve des règles impératives en matière de protection des données de santé.</p>

                    <a class="btn-solid mt-4" href="{{ route('contact') }}">Nous contacter</a>
                </div>
            </div>
        </div>
    </section>
@endsection
