{{-- @TODO : Bien formater cette vue --}}

<h1>Bonjour {{ $demande->demandeur->user->full_name }}</h1>

<p>
    Voici le nouveau statut de votre demande : 
</p>
<p>
    Veuillez trouver ci-dessous les détails de la demande : 
</p>
<hr>
{{ $demande->date_demande }}
<hr>
{{ $demande->description }}
<hr>
{{ $demande->bourse->titre }}
<hr>
{{ $demande->demande_statut->titre }}
<hr>
{{ $demande->demande_decision->titre }}
<hr>
Équipe SIGAL
