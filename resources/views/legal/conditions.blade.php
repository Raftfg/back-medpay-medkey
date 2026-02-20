@extends('layouts.landing')

@section('title', "Conditions d'utilisation | Medkey")
@section('header_class', 'page-header inner-page-header header-basic')

@section('content')
    <section class="d-flex align-items-center page-hero inner-page-hero">
        <div class="overlay-photo-image-bg" data-bg-img="/images/landing/hero/inner-page-hero.jpg" data-bg-opacity=".75"></div>
        <div class="container">
            <div class="hero-text-area centerd">
                <h1 class="hero-title">Conditions d'utilisation</h1>
                <nav aria-label="breadcrumb"><ul class="breadcrumb"><li class="breadcrumb-item"><a class="breadcrumb-link" href="{{ route('home') }}">Accueil</a></li><li class="breadcrumb-item active">Conditions</li></ul></nav>
            </div>
        </div>
    </section>

    <section class="mega-section">
        <div class="container">
            <div class="row">
                <div class="col-12 col-lg-8 mx-auto">
                    <h3>1. Définitions</h3><p>Plateforme, Établissement, Administrateur, Professionnel de santé et Patient sont définis selon le cadre contractuel Medkey.</p>
                    <h3>2. Accès au service</h3><p>L’accès est réservé aux utilisateurs autorisés par l’établissement client.</p>
                    <h3>3. Période d’essai</h3><p>Une période d’essai de 90 jours sans carte bancaire peut être proposée selon l’offre en vigueur.</p>
                    <h3>4. Abonnements et facturation</h3><p>Les plans peuvent être adaptés aux typologies Clinique, Hôpital, CHU et Réseau.</p>
                    <h3>5. Utilisation acceptable</h3><p>L’utilisateur s’engage à respecter les obligations réglementaires de santé et le secret médical.</p>
                    <h3>6. Responsabilité des données de santé</h3><p>L’établissement reste responsable de traitement au sens du RGPD ; Medkey intervient comme sous-traitant technique.</p>
                    <h3>7. Disponibilité et SLA</h3><p>Un engagement de disponibilité est défini contractuellement avec mécanismes de supervision et continuité de service.</p>
                    <h3>8. Résiliation</h3><p>Chaque partie peut résilier dans les conditions prévues au contrat et à la réglementation applicable.</p>
                    <h3>9. Limitation de responsabilité</h3><p>La responsabilité est encadrée contractuellement et proportionnée aux obligations de chaque partie.</p>
                    <h3>10. Modifications des CGU</h3><p>Medkey peut mettre à jour les présentes conditions en informant les clients concernés.</p>
                    <h3>11. Droit applicable</h3><p>Le droit applicable est celui prévu contractuellement entre Medkey et l’établissement client.</p>
                    <a class="btn-solid mt-4" href="{{ route('contact') }}">Nous contacter</a>
                </div>
            </div>
        </div>
    </section>
@endsection
