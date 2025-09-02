<?php
include 'config/koneksi.php';
include 'layout/header.php';

// Tambah Data
if (isset($_POST['tambah'])) {
    $tanggal = $_POST['tanggal'];
    $kegiatan = $_POST['kegiatan'];
    $koneksi->query("INSERT INTO laporan_harian (tanggal, kegiatan) VALUES ('$tanggal','$kegiatan')");
    header("Location: laporan-harian.php");
    exit;
}

// Hapus Data
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $koneksi->query("DELETE FROM laporan_harian WHERE id=$id");
    header("Location: laporan-harian.php");
    exit;
}

// Update Data
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $tanggal = $_POST['tanggal'];
    $kegiatan = $_POST['kegiatan'];
    $koneksi->query("UPDATE laporan_harian SET tanggal='$tanggal', kegiatan='$kegiatan' WHERE id=$id");
    header("Location: laporan-harian.php");
    exit;
}

// Filter
$where = "";
if (!empty($_GET['tanggal'])) {
    $tgl = $_GET['tanggal'];
    $where .= " AND tanggal='$tgl'";
}
if (!empty($_GET['keyword'])) {
    $key = $_GET['keyword'];
    $where .= " AND kegiatan LIKE '%$key%'";
}
$data = $koneksi->query("SELECT * FROM laporan_harian WHERE 1=1 $where ORDER BY tanggal DESC");
?>

<h2 class="mb-4 text-title">📅 Laporan Harian</h2>

<!-- Form Tambah -->
<form method="post" class="row g-2 mb-4">
  <div class="col-md-3"><input type="date" name="tanggal" class="form-control" required></div>
  <div class="col-md-6"><input type="text" name="kegiatan" class="form-control" placeholder="Kegiatan" required></div>
  <div class="col-md-3"><button name="tambah" class="btn btn-success w-100">Tambah</button></div>
</form>

<!-- Filter -->
<form method="get" class="row g-2 mb-4">
  <div class="col-md-3"><input type="date" name="tanggal" class="form-control"></div>
  <div class="col-md-6"><input type="text" name="keyword" class="form-control" placeholder="Cari kegiatan..."></div>
  <div class="col-md-3"><button class="btn btn-primary w-100">Filter</button></div>
</form>

<table class="table table-bordered align-middle">
  <thead>
    <tr><th>No</th><th>Tanggal</th><th>Kegiatan</th><th>Aksi</th></tr>
  </thead>
  <tbody>
  <?php $no=1; while($row=$data->fetch_assoc()): ?>
    <tr>
      <td><?= $no++ ?></td>
      <td><?= $row['tanggal'] ?></td>
      <td><?= $row['kegiatan'] ?></td>
      <td>
        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#edit<?= $row['id'] ?>">Edit</button>
        <a href="?hapus=<?= $row['id'] ?>" onclick="return confirm('Hapus data ini?')" class="btn btn-danger btn-sm">Hapus</a>
      </td>
    </tr>

    <!-- Modal Edit -->
    <div class="modal fade" id="edit<?= $row['id'] ?>">
      <div class="modal-dialog">
        <div class="modal-content">
          <form method="post">
            <div class="modal-header"><h5>Edit Laporan</h5></div>
            <div class="modal-body">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <input type="date" name="tanggal" value="<?= $row['tanggal'] ?>" class="form-control mb-2" required>
              <textarea name="kegiatan" class="form-control" required><?= $row['kegiatan'] ?></textarea>
            </div>
            <div class="modal-footer">
              <button type="submit" name="update" class="btn btn-success">Simpan</button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  <?php endwhile; ?>
  </tbody>
</table>

<?php include 'layout/footer.php'; ?>
