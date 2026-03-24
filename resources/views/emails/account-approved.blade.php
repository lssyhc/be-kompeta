<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Akun Anda Telah Disetujui</title>
    </head>

    <body
        style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 0; -webkit-font-smoothing: antialiased;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0"
            style="background-color: #f8fafc; padding: 40px 0;">
            <tr>
                <td align="center">
                    <table width="600" cellpadding="0" cellspacing="0" border="0"
                        style="background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); margin: 0 auto; overflow: hidden; max-width: 90%;">

                        <tr>
                            <td align="center" style="background-color: #2563eb; padding: 32px 0;">
                                <h1
                                    style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 600; letter-spacing: 0.5px;">
                                    Kompeta</h1>
                            </td>
                        </tr>

                        <tr>
                            <td style="padding: 40px 48px;">
                                <h2
                                    style="color: #1e293b; font-size: 22px; font-weight: 600; margin-top: 0; margin-bottom: 24px;">
                                    Halo, {{ $user->name }}!</h2>

                                <p style="color: #475569; font-size: 16px; line-height: 1.6; margin-bottom: 24px;">
                                    Selamat! Pendaftaran akun Anda sebagai
                                    <strong style="color: #0f172a; font-weight: 600;">
                                        @if ($user->role === 'sekolah')
                                            Sekolah
                                        @elseif($user->role === 'mitra' && $user->mitra_type === 'perusahaan')
                                            Mitra Perusahaan
                                        @elseif($user->role === 'mitra' && $user->mitra_type === 'umkm')
                                            Mitra UMKM
                                        @else
                                            Mitra
                                        @endif
                                    </strong>
                                    telah <span style="color: #16a34a; font-weight: 600;">berhasil disetujui</span> oleh
                                    Administrator Kompeta.
                                </p>

                                <p style="color: #475569; font-size: 16px; line-height: 1.6; margin-bottom: 36px;">
                                    Saat ini akun Anda telah berstatus aktif. Anda dapat langsung masuk <em>login</em> ke
                                    <em>dashboard</em> platform kami untuk melengkapi profil dan mulai menggunakan seluruh
                                    layanan serta fitur yang tersedia.
                                </p>

                                <table width="100%" cellpadding="0" cellspacing="0" border="0"
                                    style="margin-bottom: 36px;">
                                    <tr>
                                        <td align="center">
                                            <a href="{{ config('app.frontend_url') }}/login"
                                                style="display: inline-block; background-color: #2563eb; color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-size: 16px; font-weight: 600; text-align: center;">Masuk
                                                ke Dashboard</a>
                                        </td>
                                    </tr>
                                </table>

                                <p style="color: #475569; font-size: 16px; line-height: 1.6; margin-bottom: 0;">
                                    Terima kasih telah bergabung dengan kami,<br>
                                    <strong style="color: #0f172a;">Tim Administrator Kompeta</strong>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <td align="center"
                                style="background-color: #f1f5f9; padding: 24px 48px; border-top: 1px solid #e2e8f0;">
                                <p style="color: #64748b; font-size: 14px; margin: 0; margin-bottom: 8px;">
                                    &copy; {{ date('Y') }} Kompeta. Hak cipta dilindungi undang-undang.
                                </p>
                                <p style="color: #94a3b8; font-size: 12px; margin: 0;">
                                    Email ini dihasilkan secara otomatis dari sistem. Mohon untuk tidak membalas email
                                    ini.
                                </p>
                            </td>
                        </tr>

                    </table>
                </td>
            </tr>
        </table>
    </body>

</html>
