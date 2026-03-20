# Sample Files untuk REST Client Testing

Beberapa endpoint memerlukan upload file (gambar / PDF).
Letakkan file sample di folder ini sebelum menjalankan request yang membutuhkan file.

## File yang Dibutuhkan

| Nama File      | Digunakan Untuk                                     | Format            | Maks Ukuran |
| -------------- | --------------------------------------------------- | ----------------- | ----------- |
| `logo.jpg`     | Logo sekolah, foto KTP UMKM, foto profil siswa      | JPEG/PNG/GIF/WebP | 2 MB        |
| `document.pdf` | Izin operasional sekolah, SK Kemenkumham perusahaan | PDF               | 4 MB        |

## Cara Cepat Membuat File Sample

### Windows (PowerShell)

```powershell
# Buat file dummy JPEG (1x1 pixel putih — cukup untuk lolos validasi image)
[System.IO.File]::WriteAllBytes(
  "$PWD\logo.jpg",
  [System.Convert]::FromBase64String(
    "/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QAFAABAAAAAAAAAAAAAAAAAAAACf/EABQQAQAAAAAAAAAAAAAAAAAAAAD/xAAUAQEAAAAAAAAAAAAAAAAAAAAA/8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAwDAQACEQMRAD8AJQAB/9k="
  )
)

# Buat file PDF minimal yang valid
[System.IO.File]::WriteAllBytes(
  "$PWD\document.pdf",
  [System.Text.Encoding]::ASCII.GetBytes(
    "%PDF-1.4`n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj 2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj 3 0 obj<</Type/Page/MediaBox[0 0 3 3]>>endobj`nxref`n0 4`n0000000000 65535 f`n0000000009 00000 n`n0000000058 00000 n`n0000000115 00000 n`ntrailer<</Size 4/Root 1 0 R>>`nstartxref`n190`n%%EOF"
  )
)

Write-Host "File sample berhasil dibuat di: $PWD"
```

### Atau gunakan file asli

Cukup salin sembarang file `.jpg` dan `.pdf` yang Anda miliki ke folder ini,
lalu ganti namanya menjadi `logo.jpg` dan `document.pdf`.

---

> **Catatan:** File-file di folder ini hanya untuk keperluan testing lokal
> dan tidak perlu di-commit ke repository.
