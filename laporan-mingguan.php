<?php
include 'config/koneksi.php';
include 'layout/header.php';

// ---------- Helper ----------
function safe_filename($name) {
    $ext = pathinfo($name, PATHINFO_EXTENSION);
    $base = pathinfo($name, PATHINFO_FILENAME);
    $base = preg_replace('/[^a-zA-Z0-9-_]/', '_', $base);
    return time() . '_' . substr($base, 0, 40) . ($ext ? '.' . strtolower($ext) : '');
}

$uploadDir = 'assets/uploads/';
if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }

// ---------- CREATE ----------
if (isset($_POST['tambah'])) {
    $minggu_ke = (int)$_POST['minggu_ke'];
    $ringkasan = $koneksi->real_escape_string($_POST['ringkasan']);
    $foto = [null, null, null];

    for ($i=1; $i<=3; $i++) {
        if (!empty($_FILES["foto$i"]['name'])) {
            $allowed = ['jpg','jpeg','png','webp'];
            $ext = strtolower(pathinfo($_FILES["foto$i"]['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $nama_file = safe_filename($_FILES["foto$i"]['name']);
                $target = $uploadDir . $nama_file;
                if (move_uploaded_file($_FILES["foto$i"]['tmp_name'], $target)) {
                    $foto[$i-1] = $nama_file;
                }
            }
        }
    }

    $koneksi->query("INSERT INTO laporan_mingguan (minggu_ke, ringkasan, foto1, foto2, foto3)
                     VALUES ($minggu_ke, '$ringkasan',
                             " . ($foto[0] ? "'$foto[0]'" : "NULL") . ",
                             " . ($foto[1] ? "'$foto[1]'" : "NULL") . ",
                             " . ($foto[2] ? "'$foto[2]'" : "NULL") . ")");
    header("Location: laporan-mingguan.php");
    exit;
}

// ---------- DELETE ----------
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $q = $koneksi->query("SELECT foto1,foto2,foto3 FROM laporan_mingguan WHERE id=$id");
    if ($q && $r = $q->fetch_assoc()) {
        foreach (['foto1','foto2','foto3'] as $f) {
            if (!empty($r[$f]) && file_exists($uploadDir.$r[$f])) {
                @unlink($uploadDir.$r[$f]);
            }
        }
    }
    $koneksi->query("DELETE FROM laporan_mingguan WHERE id=$id");
    header("Location: laporan-mingguan.php");
    exit;
}

// ---------- UPDATE ----------
if (isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $minggu_ke = (int)$_POST['minggu_ke'];
    $ringkasan = $koneksi->real_escape_string($_POST['ringkasan']);

    $setFoto = [];
    for ($i=1; $i<=3; $i++) {
        $foto_lama = $_POST["foto{$i}_lama"] ?? null;
        $foto = $foto_lama;

        if (!empty($_FILES["foto$i"]['name'])) {
            $allowed = ['jpg','jpeg','png','webp'];
            $ext = strtolower(pathinfo($_FILES["foto$i"]['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                if (!empty($foto_lama) && file_exists($uploadDir.$foto_lama)) {
                    @unlink($uploadDir.$foto_lama);
                }
                $nama_file = safe_filename($_FILES["foto$i"]['name']);
                $target = $uploadDir . $nama_file;
                if (move_uploaded_file($_FILES["foto$i"]['tmp_name'], $target)) {
                    $foto = $nama_file;
                }
            }
        }
        $setFoto[] = "foto$i=" . ($foto ? "'$foto'" : "NULL");
    }

    $sql = "UPDATE laporan_mingguan
            SET minggu_ke=$minggu_ke, ringkasan='$ringkasan', " . implode(',', $setFoto) . "
            WHERE id=$id";
    $koneksi->query($sql);
    header("Location: laporan-mingguan.php");
    exit;
}

// ---------- FILTER ----------
$where = " WHERE 1=1 ";
if (!empty($_GET['minggu_ke'])) {
    $mk = (int)$_GET['minggu_ke'];
    $where .= " AND minggu_ke=$mk ";
}
if (!empty($_GET['keyword'])) {
    $kw = $koneksi->real_escape_string($_GET['keyword']);
    $where .= " AND ringkasan LIKE '%$kw%' ";
}

$data = $koneksi->query("SELECT * FROM laporan_mingguan $where ORDER BY minggu_ke ASC, id ASC");
?>

<h2 class="mb-4 text-title">📆 Laporan Mingguan</h2>

<!-- Form Tambah -->
<form method="post" enctype="multipart/form-data" class="row g-2 mb-4">
  <div class="col-md-2">
    <input type="number" name="minggu_ke" class="form-control" placeholder="Minggu ke" required>
  </div>
  <div class="col-md-4">
    <input type="text" name="ringkasan" class="form-control" placeholder="Ringkasan kegiatan" required>
  </div>
  <div class="col-md-2"><input type="file" name="foto1" class="form-control"></div>
  <div class="col-md-2"><input type="file" name="foto2" class="form-control"></div>
  <div class="col-md-2"><input type="file" name="foto3" class="form-control"></div>
  <div class="col-md-12 mt-2">
    <button name="tambah" class="btn btn-success">Tambah</button>
  </div>
</form>

<!-- Form Filter / Pencarian -->
<form method="get" class="row g-2 mb-4">
  <div class="col-md-2">
    <input type="number" name="minggu_ke" class="form-control" placeholder="Minggu ke"
           value="<?= htmlspecialchars($_GET['minggu_ke'] ?? '') ?>">
  </div>
  <div class="col-md-4">
    <input type="text" name="keyword" class="form-control" placeholder="Cari ringkasan..."
           value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
  </div>
  <div class="col-md-2">
    <button type="submit" class="btn btn-primary w-100">Cari</button>
  </div>
  <div class="col-md-2">
    <a href="laporan-mingguan.php" class="btn btn-secondary w-100">Reset</a>
  </div>
</form>


<!-- Tabel -->
<div class="table-responsive">
  <table class="table table-bordered align-middle">
    <thead>
      <tr>
        <th>No</th>
        <th>Minggu ke</th>
        <th>Ringkasan</th>
        <th>Foto</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php $no=1; while($row = $data->fetch_assoc()): ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= (int)$row['minggu_ke'] ?></td>
        <td><?= nl2br(htmlspecialchars($row['ringkasan'])) ?></td>
        <td>
          <?php for($i=1;$i<=3;$i++): ?>
            <?php if (!empty($row["foto$i"])): ?>
              <img src="assets/uploads/<?= htmlspecialchars($row["foto$i"]) ?>"
                   class="img-thumbnail m-1"
                   style="width:60px;cursor:pointer"
                   data-bs-toggle="modal"
                   data-bs-target="#fotoModal"
                   data-foto="assets/uploads/<?= htmlspecialchars($row["foto$i"]) ?>">
            <?php endif; ?>
          <?php endfor; ?>
        </td>
        <td>
          <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#edit<?= $row['id'] ?>">Edit</button>
          <a href="?hapus=<?= $row['id'] ?>" onclick="return confirm('Hapus data ini?')" class="btn btn-danger btn-sm">Hapus</a>
        </td>
      </tr>

      <!-- Modal Edit -->
      <div class="modal fade" id="edit<?= $row['id'] ?>" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <form method="post" enctype="multipart/form-data">
              <div class="modal-header">
                <h5 class="modal-title">Edit Laporan Mingguan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <?php for($i=1;$i<=3;$i++): ?>
                  <input type="hidden" name="foto<?= $i ?>_lama" value="<?= htmlspecialchars($row["foto$i"]) ?>">
                <?php endfor; ?>
                <div class="mb-2">
                  <label>Minggu ke</label>
                  <input type="number" name="minggu_ke" value="<?= (int)$row['minggu_ke'] ?>" class="form-control" required>
                </div>
                <div class="mb-2">
                  <label>Ringkasan</label>
                  <textarea name="ringkasan" class="form-control" rows="3" required><?= htmlspecialchars($row['ringkasan']) ?></textarea>
                </div>
                <?php for($i=1;$i<=3;$i++): ?>
                  <div class="mb-2">
                    <label>Foto <?= $i ?> (opsional)</label>
                    <input type="file" name="foto<?= $i ?>" class="form-control">
                    <?php if (!empty($row["foto$i"])): ?>
                      <small class="text-muted">Saat ini: <?= htmlspecialchars($row["foto$i"]) ?></small>
                    <?php endif; ?>
                  </div>
                <?php endfor; ?>
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
</div>

<!-- Modal Preview Foto -->
<div class="modal fade" id="fotoModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-dark text-center">
      <div class="modal-body">
        <img id="fotoPreview" src="" class="img-fluid rounded" alt="Preview Foto">
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var fotoModal = document.getElementById('fotoModal');
  if (fotoModal) {
    fotoModal.addEventListener('show.bs.modal', function (event) {
      var img = event.relatedTarget;
      if (!img) return;
      var src = img.getAttribute('data-foto');
      document.getElementById('fotoPreview').src = src;
    });
  }
});
</script>

<?php include 'layout/footer.php'; ?>
