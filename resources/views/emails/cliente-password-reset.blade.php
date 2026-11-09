<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Restablecer contraseña DISCRET</title>
</head>
<body style="margin:0;background:#070b14;color:#ffffff;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#070b14;padding:28px 14px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:#101827;border:1px solid rgba(236,72,153,0.38);border-radius:22px;overflow:hidden;box-shadow:0 24px 70px rgba(0,0,0,0.42);">
                    <tr>
                        <td style="padding:32px 30px;text-align:center;background:#0b1020;">
                            <div style="margin:0 auto 14px;font-size:34px;letter-spacing:10px;font-weight:300;color:#ff4fa3;text-shadow:0 0 16px rgba(236,72,153,0.82),0 0 28px rgba(236,72,153,0.42);line-height:1;">DISCRET</div>
                            <div style="font-size:11px;letter-spacing:5px;color:#ffffff;font-weight:700;">
                                DISCRECIÓN · CONFORT · PRIVACIDAD
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:30px;background:linear-gradient(180deg, rgba(236,72,153,0.12), rgba(16,24,39,0));">
                            <p style="margin:0 0 12px;color:#ff7fbd;font-size:12px;font-weight:900;letter-spacing:2px;text-transform:uppercase;">
                                Seguridad de cuenta
                            </p>

                            <h1 style="margin:0 0 16px;font-size:30px;line-height:1.16;color:#ffffff;">
                                Restablecer contraseña
                            </h1>

                            <p style="margin:0 0 18px;font-size:16px;line-height:1.7;color:#dce6f7;">
                                Hola {{ $nombreCliente ?: 'cliente' }}, recibimos una solicitud para cambiar la contraseña de tu cuenta DISCRET.
                            </p>

                            <p style="margin:0 0 24px;font-size:15px;line-height:1.65;color:#aebbd0;">
                                Presiona el botón para crear una nueva contraseña. Por seguridad, este enlace vence en 60 minutos.
                            </p>

                            <table role="presentation" cellspacing="0" cellpadding="0" align="center" style="margin:0 auto;">
                                <tr>
                                    <td style="border-radius:14px;background:#ec4899;box-shadow:0 14px 34px rgba(236,72,153,0.32);">
                                        <a href="{{ $resetUrl }}" style="display:inline-block;padding:16px 26px;color:#ffffff;text-decoration:none;font-size:16px;font-weight:900;">
                                            Crear nueva contraseña
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:28px;background:#0b1220;border:1px solid rgba(148,163,184,0.14);border-radius:16px;">
                                <tr>
                                    <td style="padding:16px;color:#cbd5e1;font-size:14px;line-height:1.6;">
                                        Si no solicitaste este cambio, puedes ignorar este correo y tu contraseña actual se mantendrá activa.
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:22px 0 0;color:#94a3b8;font-size:13px;line-height:1.6;">
                                Si el botón no funciona, copia y pega este enlace:<br>
                                <span style="color:#ffb3d6;word-break:break-all;">{{ $resetUrl }}</span>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px 30px;text-align:center;color:#7f8da3;font-size:12px;border-top:1px solid rgba(236,72,153,0.18);">
                            © 2026 Sistema de Reservas para Moteles - {{ $motelNombre }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
