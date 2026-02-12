{{-- @TODO : Bien formater cette vue --}}

<h1>Bonjour {{ $demandeur->user->full_name }}</h1>

<p>
    Voici le nouveau statut de votre demande : {{ $demandeur->demandeur_statut->titre }}
</p>
<hr>
Équipe SIGAL