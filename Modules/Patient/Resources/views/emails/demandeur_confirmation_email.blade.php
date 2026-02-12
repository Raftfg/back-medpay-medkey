
<p>{{ __('Bonjour') }} {{ $user->full_name }},</p>
<p>
    Veuillez trouver ci-dessous, le lien de confirmation de votre adresse courriel. En cliquant sur celui-ci, vous pourrez confirmer votre courriel.
</p>
<p>
    Ce lien est valide pendant : {{ $duree_url_temporaire }}.
</p>

<p>
    <a href="{{ $frontend_url_temp }}">Cliquez ici pour confirmer</a>
</p>

<hr>
Équipe SIGAL