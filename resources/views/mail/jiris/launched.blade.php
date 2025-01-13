<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Jiri est lancé !</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #eee;
            background-color: #f9f9f9;
        }
        h1 {
            color: #007bff;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Le Jiri est lancé !</h1>
    <p>Bonjour, {{ $userName }} !</p>
    <p>Nous avons le plaisir de vous annoncer que le {{ $jiriName }} est maintenant lancé.</p>
    <p>Vous pouvez maintenant accéder à la plateforme et démarrer les évaluations.</p>
    <p>Cordialement,</p>
    <p>L'équipe Jiri</p>
</div>
</body>
</html>
