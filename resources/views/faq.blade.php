@extends('layouts.landing')

@section('title', 'FAQ | Medkey')
@section('header_class', 'page-header inner-page-header header-basic')

@section('content')
    <section class="d-flex align-items-center page-hero inner-page-hero" id="page-hero">
        <div class="overlay-photo-image-bg" data-bg-img="/images/landing/hero/inner-page-hero.jpg" data-bg-opacity=".75"></div>
        <div class="container">
            <div class="hero-text-area centerd">
                <h1 class="hero-title">Questions fréquentes</h1>
                <nav aria-label="breadcrumb">
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a class="breadcrumb-link" href="{{ route('home') }}">Accueil</a></li>
                        <li class="breadcrumb-item active">FAQ</li>
                    </ul>
                </nav>
            </div>
        </div>
    </section>

    <section class="faq mega-section" id="faq">
        <div class="container">
            <div class="row gx-4">
                <div class="col-12 col-lg-6">
                    <div class="accordion faq-accordion" id="faq-accordion-left">
                        <div class="card">
                            <div class="card-header" id="faq-heading-1"><button class="faq-btn btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#faq-collapse-1" aria-expanded="true" aria-controls="faq-collapse-1">À quels types d’hôpitaux Medkey s’adresse-t-il ?</button></div>
                            <div class="collapse show" id="faq-collapse-1" aria-labelledby="faq-heading-1" data-bs-parent="#faq-accordion-left"><div class="card-body">Medkey est conçu pour tout type d’hôpital : structures privées, publiques, cliniques spécialisées, centres hospitaliers généraux et réseaux multi-sites.</div></div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="faq-heading-2"><button class="faq-btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-collapse-2" aria-expanded="false" aria-controls="faq-collapse-2">Quels profils peuvent utiliser la plateforme ?</button></div>
                            <div class="collapse" id="faq-collapse-2" aria-labelledby="faq-heading-2" data-bs-parent="#faq-accordion-left"><div class="card-body">Médecins, infirmiers, pharmaciens, secrétaires médicales, caissiers, comptables, administrateurs système et directeurs disposent de parcours adaptés à leurs rôles.</div></div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="faq-heading-3"><button class="faq-btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-collapse-3" aria-expanded="false" aria-controls="faq-collapse-3">Quels modules sont disponibles dans Medkey ?</button></div>
                            <div class="collapse" id="faq-collapse-3" aria-labelledby="faq-heading-3" data-bs-parent="#faq-accordion-left"><div class="card-body">Le SIH couvre les flux cliniques, administratifs et financiers : dossier patient, admissions, pharmacie, facturation, caisse, pilotage, et d’autres modules opérationnels.</div></div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="faq-heading-4"><button class="faq-btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-collapse-4" aria-expanded="false" aria-controls="faq-collapse-4">Medkey est-il multi-services et multi-utilisateurs ?</button></div>
                            <div class="collapse" id="faq-collapse-4" aria-labelledby="faq-heading-4" data-bs-parent="#faq-accordion-left"><div class="card-body">Oui. Chaque service dispose de ses écrans, tout en partageant une base patient unifiée. Les accès concurrentiels sont gérés avec des droits fins par rôle.</div></div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="faq-heading-5"><button class="faq-btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-collapse-5" aria-expanded="false" aria-controls="faq-collapse-5">Comment sont gérés les droits d’accès ?</button></div>
                            <div class="collapse" id="faq-collapse-5" aria-labelledby="faq-heading-5" data-bs-parent="#faq-accordion-left"><div class="card-body">L’administrateur configure les permissions par profil et par module pour garantir confidentialité, traçabilité et séparation des responsabilités.</div></div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="accordion faq-accordion" id="faq-accordion-right">
                        <div class="card">
                            <div class="card-header" id="faq-heading-6"><button class="faq-btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-collapse-6" aria-expanded="false" aria-controls="faq-collapse-6">La plateforme s’intègre-t-elle aux systèmes externes ?</button></div>
                            <div class="collapse" id="faq-collapse-6" aria-labelledby="faq-heading-6" data-bs-parent="#faq-accordion-right"><div class="card-body">Oui. Medkey prend en charge les intégrations avec automates de laboratoire, imagerie (scanner/IRM/radio), assurance et plateformes de surveillance type DHIS2.</div></div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="faq-heading-7"><button class="faq-btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-collapse-7" aria-expanded="false" aria-controls="faq-collapse-7">Comment la sécurité des données est-elle assurée ?</button></div>
                            <div class="collapse" id="faq-collapse-7" aria-labelledby="faq-heading-7" data-bs-parent="#faq-accordion-right"><div class="card-body">La plateforme applique des mécanismes de sécurité renforcés, l’isolation multi-tenant, la journalisation des actions et des sauvegardes adaptées aux données de santé.</div></div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="faq-heading-8"><button class="faq-btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-collapse-8" aria-expanded="false" aria-controls="faq-collapse-8">Peut-on commencer avec une période d’essai ?</button></div>
                            <div class="collapse" id="faq-collapse-8" aria-labelledby="faq-heading-8" data-bs-parent="#faq-accordion-right"><div class="card-body">Oui, vous pouvez démarrer avec une période d’essai de 90 jours pour évaluer les usages cliniques et administratifs avant généralisation.</div></div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="faq-heading-9"><button class="faq-btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-collapse-9" aria-expanded="false" aria-controls="faq-collapse-9">Combien de temps pour un déploiement ?</button></div>
                            <div class="collapse" id="faq-collapse-9" aria-labelledby="faq-heading-9" data-bs-parent="#faq-accordion-right"><div class="card-body">Le déploiement est progressif et cadré : paramétrage, reprise des données, formation des équipes et mise en production selon votre organisation.</div></div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="faq-heading-10"><button class="faq-btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-collapse-10" aria-expanded="false" aria-controls="faq-collapse-10">Quel accompagnement est proposé après mise en service ?</button></div>
                            <div class="collapse" id="faq-collapse-10" aria-labelledby="faq-heading-10" data-bs-parent="#faq-accordion-right"><div class="card-body">Nos équipes assurent assistance fonctionnelle et technique, supervision, et accompagnement des évolutions pour maintenir une qualité de service continue.</div></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5 text-center">
                <h3>Vous n’avez pas trouvé votre réponse ?</h3>
                <a class="btn-solid mt-3" href="{{ route('contact') }}">Nous contacter</a>
            </div>
        </div>
    </section>
@endsection
