<h2>Nouveau message depuis le formulaire de contact Medkey</h2>

<p><strong>Nom :</strong> {{ $contactMessage['name'] }}</p>
<p><strong>E-mail :</strong> {{ $contactMessage['email'] }}</p>
<p><strong>Sujet :</strong> {{ $contactMessage['subject'] }}</p>
<p><strong>Message :</strong></p>
<p>{{ $contactMessage['message'] }}</p>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouveau message de contact</title>
</head>
<body>
    <h2>Nouveau message de contact Medkey</h2>
    <p><strong>Nom :</strong> {{ $contactMessage['name'] }}</p>
    <p><strong>Email :</strong> {{ $contactMessage['email'] }}</p>
    <p><strong>Sujet :</strong> {{ $contactMessage['subject'] }}</p>
    <p><strong>Message :</strong></p>
    <p>{{ $contactMessage['message'] }}</p>
</body>
</html>
