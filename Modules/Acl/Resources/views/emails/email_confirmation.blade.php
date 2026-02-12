<style type="text/css">
    span{
        font-weight: bold;
    }
</style>
<body>
    <p>{{ __('Bonjour') }},</p>
    <p>
        {{ __($aliasAcl.'::premier.notifier.lien_temporaire.texte_1') }}.
    </p>
    <p>
        {{ __($aliasAcl.'::premier.notifier.lien_temporaire.texte_2', ['duree_lien_temp' => $duree_url_temporaire]) }}.
    </p>
    <p>
        <a href="{{ $frontend_url_temp }}">{{ __($aliasAcl.'::premier.notifier.lien_temporaire.texte_3') }}</a>
    </p>
</body>