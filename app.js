// Mengirim data ke server (database)
async function submitReportData(reportObj) {
  try {
    const response = await fetch('api_post_data.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(reportObj)
    });
    const result = await response.json();
    
    if (result.status === 'success') {
      showToast('Data berhasil diunggah ke server!', 'success');
      loadDataFromAPI(); // Panggil ulang data (termasuk untuk publik) agar tabel ter-update
    } else {
      showToast('Gagal: ' + result.message, 'err');
    }
  } catch (err) {
    showToast('Terjadi kesalahan jaringan server.', 'err');
  }
}

// Pastikan fungsi pengambilan data Anda menggunakan api_get_data.php
async function loadDataFromAPI() {
  try {
    const response = await fetch('api_get_data.php');
    const data = await response.json();
    // Render fungsi peta dan tabel laporan publik di sini menggunakan variabel 'data'
  } catch (err) {
    console.error('Gagal mengambil data:', err);
  }
}