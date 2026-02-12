{{-- @TODO : Bien formater cette vue --}}

<h1>Bonjour {{ $demandeAssignee->employe->user->full_name }}</h1>

<p>
    Ce courriel pour vous notifier qu'une nouvelle demande vient de vous être affectée.
</p>
<p>Voici la description de ce que vous avez à faire : </br> {{ $demandeAssignee->description_travail }}</p>
<p>
    Veuillez trouver ci-dessous les détails de la demande : 
</p>
<hr>
{{ $demandeAssignee->demande->date_demande }}
<hr>
{{ $demandeAssignee->demande->description }}
<hr>
{{ $demandeAssignee->demande->bourse->titre }}
<hr>
{{ $demandeAssignee->demande->demande_statut->titre }}
<hr>
{{ $demandeAssignee->demande->demande_decision->titre }}
<hr>
Équipe SIGAL