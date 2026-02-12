
<h1>Bonjour {{ $user->full_name }},</h1>

<p>
    Ci-dessous, votre mot de passe temporaire et les instructions pour vous connecter à la plateforme :
</p>
<p>
    Courriel : {{ $user->email }}
    <br>
    Mot de passe temporaire : {{ $motDePasse }}
    <br>
    Lien : {{ config('premier.frontend.url.racine') }}
</p>

