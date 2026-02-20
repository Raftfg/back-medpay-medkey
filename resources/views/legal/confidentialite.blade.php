@extends('layouts.landing')

@section('title', 'Politique de confidentialité | Medkey')
@section('header_class', 'page-header inner-page-header header-basic')

@section('content')
    <section class="d-flex align-items-center page-hero inner-page-hero">
        <div class="overlay-photo-image-bg" data-bg-img="/images/landing/hero/inner-page-hero.jpg" data-bg-opacity=".75"></div>
        <div class="container">
            <div class="hero-text-area centerd">
                <h1 class="hero-title">Politique de confidentialité</h1>
                <nav aria-label="breadcrumb"><ul class="breadcrumb"><li class="breadcrumb-item"><a class="breadcrumb-link" href="{{ route('home') }}">Accueil</a></li><li class="breadcrumb-item active">Confidentialité</li></ul></nav>
            </div>
        </div>
    </section>

    <section class="mega-section">
        <div class="container">
            <div class="row">
                <div class="col-12 col-lg-8 mx-auto">
                    <h3>1. Responsable du traitement</h3>
                    <p>Les établissements de santé clients sont responsables de traitement des données patients. Medkey agit en qualité de sous-traitant technique.</p>
                    <h3>2. Données collectées</h3>
                    <p>Données d’identité des professionnels de santé, données médicales traitées pour le compte des établissements, et données de connexion au service.</p>
                    <h3>3. Finalités</h3>
                    <p>Fourniture du SIH, gestion des soins, coordination des services, pilotage opérationnel, sécurisation des accès et amélioration continue de la plateforme.</p>
                    <h3>4. Base légale</h3>
                    <p>Traitements fondés sur l’exécution contractuelle et, pour les données de santé, sur les dispositions applicables du RGPD (dont l’article 9).</p>
                    <h3>5. Durée de conservation</h3>
                    <p>Les données sont conservées selon les durées légales et les politiques définies avec l’établissement client.</p>
                    <h3>6. Hébergement HDS et isolation multi-tenant</h3>
                    <p>Les données sont hébergées sur une infrastructure certifiée HDS avec isolation logique stricte par établissement.</p>
                    <h3>7. Partage des données</h3>
                    <p>Aucune vente de données. Les transferts se limitent aux sous-traitants autorisés et nécessaires à l’exploitation du service.</p>
                    <h3>8. Vos droits</h3>
                    <p>Droits d’accès, rectification, effacement, limitation, portabilité et opposition, à exercer via l’établissement de santé responsable de traitement.</p>
                    <h3>9. Modifications</h3>
                    <p>La présente politique peut évoluer pour refléter les changements réglementaires, techniques et fonctionnels.</p>
                    <a class="btn-solid mt-4" href="{{ route('contact') }}">Nous contacter</a>
                </div>
            </div>
        </div>
    </section>
@endsection
