{{-- Fallback if controller redirect is bypassed --}}
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="refresh" content="0;url={{ route('module_comptoir.pos', ['ouvrir' => 1]) }}">
    <title>Redirection POS…</title>
</head>

<body>
    <p><a href="{{ route('module_comptoir.pos', ['ouvrir' => 1]) }}">Ouvrir le point de vente</a></p>
</body>

</html>
