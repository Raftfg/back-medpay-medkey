<!DOCTYPE html>
<html>
<head>
  <title>Réinitialisation de mot de passe</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f2f2f2;
    }

    h2 {
      color: #333333;
    }

    p {
      color: #666666;
    }

    a {
      display: inline-block;
      margin-top: 10px;
      padding: 10px 20px;
      background-color: #0b5d3f;
      color: #ffffff;
      text-decoration: none;
      border-radius: 4px;
    }

    a:hover {
      background-color: #144e35;
    }
  </style>
</head>
<body>
  <h2>Réinitialisation de mot de passe</h2>

  <p>Bonjour <strong>{{$user->name}} {{$user->prenom}}</strong>,</p>

  <p>Vous avez reçu cet e-mail car nous avons reçu une demande de réinitialisation de mot de passe pour votre compte.</p>

  

  <p><a href="{{$resetLink}}">Réinitialiser le mot de passe</a></p>

  <p>Si vous n'avez pas demandé la réinitialisation de mot de passe, aucune autre action n'est requise.</p>

  {{-- <p>Le lien de réinitialisation expirera dans {{$expires}} minutes.</p> --}}

  <p>Merci,</p>
  <p>L'équipe MedKey</p>

  <!-- Inclure ce script dans la page de réinitialisation du mot de passe -->
<script>
  document.addEventListener("DOMContentLoaded", function () {
      // Extraction de l'adresse e-mail du lien
      const urlParams = new URLSearchParams(window.location.search);
      const email = urlParams.get('email');

      // Pré-remplir le champ d'adresse e-mail si une adresse e-mail est présente dans le lien
      if (email) {
          document.getElementById('emailInput').value = decodeURIComponent(email);
      }
  });
</script>
</body>


</html>
