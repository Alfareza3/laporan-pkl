<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($title) ? $title : 'Laporan PKL' ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    html, body { height: 100%; margin: 0; display: flex; flex-direction: column; }
    main { flex: 1 0 auto; }
    footer { flex-shrink: 0; }
  </style>
</head>
<body>
  <?php include 'navbar.php'; ?>
  <main class="container py-4">
