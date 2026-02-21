<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Validation de votre compte MedKey</title>
  <style>
    body { font-family: Arial, sans-serif; background-color: #f2f4f8; color: #2b3443; }
    .container { max-width: 620px; margin: 0 auto; background: #ffffff; border-radius: 8px; padding: 24px; }
    h2 { margin-top: 0; color: #173b73; }
    p { line-height: 1.45; }
    .btn {
      display: inline-block;
      margin-top: 12px;
      padding: 11px 18px;
      background-color: #0a64c9;
      color: #ffffff !important;
      text-decoration: none;
      border-radius: 6px;
      font-weight: 600;
    }
    .hint { font-size: 13px; color: #62708a; margin-top: 18px; }
  </style>
</head>
<body>
  <div class="container">
    <h2>Bienvenue sur MedKey</h2>
    <p>Bonjour <strong>{{ $user->name }} {{ $user->prenom }}</strong>,</p>
    <p>
      Votre espace hospitalier a été créé avec succès.
      Pour valider votre compte et définir vos identifiants de connexion, cliquez sur le bouton ci-dessous.
    </p>

    <p>
      <a class="btn" href="{{ $setupLink }}">Valider mon compte et définir mon mot de passe</a>
    </p>

    <p class="hint">
      Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet email.
    </p>
    <p class="hint">Equipe MedKey</p>
  </div>
</body>
</html>
