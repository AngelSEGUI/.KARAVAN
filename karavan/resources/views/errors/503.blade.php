{{-- Page autonome : n'utilise ni la base de données ni la session (qui peuvent être la cause de l'erreur) --}}
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Erreur — Karavan</title>
    <link rel="stylesheet" href="{{ asset('css/themes/' . config('karavan.theme', 'festival') . '.css') }}">
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
</head>
<body style="padding: 0">
    <div class="container">
        <div class="empty empty-page">
            <h1>Karavan fait une pause</h1>
            <p>Le site est en maintenance, il revient très vite.</p>
            <a href="{{ url('/') }}" class="btn btn-primary">Retour à l'accueil</a>
        </div>
    </div>
</body>
</html>
