<?php
require 'config.php';

$id = $_GET['id'];
$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM laporan_harian WHERE id=$id"));

if (isset($_POST['update'])) {
    $tanggal = $_POST['tanggal'];
    $catatan = $_POST['catatan'];
    mysqli_query($conn, "UPDATE laporan_harian SET tanggal='$tanggal', catatan='$catatan' WHERE id=$id");
    header("Location: laporan-harian.php");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit Laporan Harian</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container mt-4">
  <h2>Edit Laporan Harian</h2>
  <form method="POST">
    <div class="mb-3">
      <label>Tanggal</label>
      <input type="date" name="tanggal" class="form-control" value="<?= $data['tanggal'] ?>" required>
    </div>
    <div class="mb-3">
      <label>Catatan Harian</label>
      <textarea name="catatan" class="form-control" rows="3" required><?= $data['catatan'] ?></textarea>
    </div>
    <button type="submit" name="update" class="btn btn-success">Update</button>
    <a href="laporan-harian.php" class="btn btn-secondary">Kembali</a>
  </form>
</div>
</body>
</html>
