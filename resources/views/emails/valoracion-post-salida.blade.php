<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Valoración DISCRET</title>
</head>
<body style="margin:0;padding:0;background:#070b14;color:#ffffff;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#070b14;padding:28px 14px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:#111827;border:1px solid rgba(236,72,153,0.35);border-radius:18px;overflow:hidden;">
                    <tr>
                        <td style="padding:34px 28px;text-align:center;background:radial-gradient(circle at top, rgba(236,72,153,0.24), transparent 46%), #111827;">
                            <div style="font-size:38px;letter-spacing:10px;font-weight:300;color:#ff4fa3;text-shadow:0 0 14px rgba(236,72,153,0.8);">
                                DISCRET
                            </div>
                            <div style="margin-top:8px;font-size:11px;letter-spacing:4px;color:#ffffff;font-weight:700;">
                                DISCRECIÓN · CONFORT · PRIVACIDAD
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:30px 28px 12px;">
                            <p style="margin:0 0 12px;color:#ff7fbd;font-size:13px;font-weight:800;letter-spacing:2px;text-transform:uppercase;">
                                Valoración post-salida
                            </p>

                            <h1 style="margin:0;color:#ffffff;font-size:28px;line-height:1.2;">
                                {{ $nombreCliente }}, cuéntanos como fue tu experiencia
                            </h1>

                            <p style="margin:16px 0 0;color:#cbd5e1;font-size:16px;line-height:1.65;">
                                Tu opinión nos ayuda a mejorar la atención, privacidad y experiencia de cada reserva.
                                La valoración es confidencial y solo será usada por {{ $motelNombre }} para mejorar el servicio.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:12px 28px 6px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#0b1220;border:1px solid rgba(148,163,184,0.18);border-radius:14px;">
                                <tr>
                                    <td style="padding:18px;color:#ffffff;font-size:14px;line-height:1.7;">
                                        <strong style="color:#ff4fa3;">Reserva:</strong> {{ $codigoReserva }}<br>
                                        <strong style="color:#ff4fa3;">Habitación:</strong> {{ $habitación }}<br>
                                        @if ($fechaSalida)
                                            <strong style="color:#ff4fa3;">Salida:</strong> {{ $fechaSalida }}
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:28px;">
                            <a href="{{ $linkValoración }}" style="display:inline-block;padding:17px 28px;border-radius:14px;background:#ec4899;color:#ffffff;text-decoration:none;font-size:16px;font-weight:800;box-shadow:0 14px 32px rgba(236,72,153,0.35);">
                                Valorar mi experiencia
                            </a>

                            <p style="margin:20px 0 0;color:#94a3b8;font-size:13px;line-height:1.6;">
                                Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
                                <span style="color:#ffb3d6;word-break:break-all;">{{ $linkValoración }}</span>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 28px 28px;text-align:center;color:#64748b;font-size:12px;">
                            © 2026 Sistema de Reservas para Moteles - DISCRET
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
