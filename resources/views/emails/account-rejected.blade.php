<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Status Pendaftaran Akun</title>
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
                            <td align="center" style="background-color: #ef4444; padding: 32px 0;">
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
                                    Kami telah melakukan peninjauan terhadap aplikasi pendaftaran Anda sebagai 
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
                                    </strong>.
                                </p>

                                <p style="color: #475569; font-size: 16px; line-height: 1.6; margin-bottom: 24px;">
                                    Mohon maaf, saat ini kami <strong style="color: #dc2626;">belum dapat menyetujui</strong> aplikasi pendaftaran Anda.
                                    @if ($reason)
                                        Berikut adalah alasan penolakan dari Tim Kompeta:
                                    @else
                                        Hal ini biasanya dikarenakan adanya ketidaksesuaian data, dokumen pendukung yang kurang lengkap, atau belum sesuai dengan kriteria yang ditetapkan oleh platform Kompeta saat ini.
                                    @endif
                                </p>

                                @if ($reason)
                                    <div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 16px 20px; border-radius: 0 8px 8px 0; margin-bottom: 24px;">
                                        <p style="color: #991b1b; font-size: 15px; line-height: 1.6; margin: 0; font-style: italic;">
                                            "{{ $reason }}"
                                        </p>
                                    </div>
                                @endif

                                <p style="color: #475569; font-size: 16px; line-height: 1.6; margin-bottom: 36px;">
                                    Anda dapat mencoba mendaftar kembali di lain waktu setelah memastikan seluruh dokumen dan data yang diperlukan telah lengkap dan sesuai. Jika Anda memiliki pertanyaan lebih lanjut, silakan hubungi tim bantuan platform kami.
                                </p>

                                <p style="color: #475569; font-size: 16px; line-height: 1.6; margin-bottom: 0;">
                                    Terima kasih atas minat Anda untuk bergabung bersama kami,<br>
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
