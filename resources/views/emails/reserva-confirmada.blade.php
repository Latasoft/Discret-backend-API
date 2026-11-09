<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Comprobante de reserva DISCRET</title>
</head>
<body style="margin:0;padding:0;background:#070b14;color:#ffffff;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#070b14;padding:28px 14px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:680px;background:#101827;border:1px solid rgba(236,72,153,0.36);border-radius:22px;overflow:hidden;box-shadow:0 24px 70px rgba(0,0,0,0.42);">
                    <tr>
                        <td style="padding:32px 28px;text-align:center;background:#0b1020;">
                            <div style="margin:0 auto 14px;font-size:34px;letter-spacing:10px;font-weight:300;color:#ff4fa3;text-shadow:0 0 16px rgba(236,72,153,0.82),0 0 28px rgba(236,72,153,0.42);line-height:1;">DISCRET</div>
                            <div style="font-size:11px;letter-spacing:5px;color:#ffffff;font-weight:700;">
                                DISCRECIÓN · CONFORT · PRIVACIDAD
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:30px 30px 10px;background:linear-gradient(180deg, rgba(236,72,153,0.12), rgba(16,24,39,0));">
                            <p style="margin:0 0 12px;color:#ff7fbd;font-size:12px;font-weight:900;letter-spacing:2px;text-transform:uppercase;">
                                Comprobante de reserva
                            </p>

                            <h1 style="margin:0;color:#ffffff;font-size:30px;line-height:1.18;">
                                {{ $nombreCliente }}, tu reserva está confirmada
                            </h1>

                            <p style="margin:16px 0 0;color:#dce6f7;font-size:16px;line-height:1.7;">
                                Presenta el ID de reserva o el QR en portería al momento de ingresar. El número de habitación sera asignado internamente por administración según disponibilidad.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 30px 8px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#0b1220;border:1px solid rgba(236,72,153,0.22);border-radius:18px;">
                                <tr>
                                    <td style="padding:18px 18px 6px;">
                                        <p style="margin:0;color:#ff7fbd;font-size:12px;font-weight:900;letter-spacing:1.4px;text-transform:uppercase;">ID de reserva</p>
                                        <p style="margin:8px 0 0;color:#ffffff;font-size:24px;font-weight:900;letter-spacing:1px;">{{ $codigoReserva }}</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 18px 18px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td width="50%" style="padding:10px;background:#070d18;border:1px solid rgba(148,163,184,0.12);border-radius:12px;">
                                                    <span style="display:block;color:#94a3b8;font-size:12px;">Tipo de habitación</span>
                                                    <strong style="display:block;margin-top:5px;color:#ffffff;font-size:15px;">{{ $habitación }}</strong>
                                                </td>
                                                <td width="12"></td>
                                                <td width="50%" style="padding:10px;background:#070d18;border:1px solid rgba(148,163,184,0.12);border-radius:12px;">
                                                    <span style="display:block;color:#94a3b8;font-size:12px;">Personas</span>
                                                    <strong style="display:block;margin-top:5px;color:#ffffff;font-size:15px;">{{ $cantidadPersonas }}</strong>
                                                </td>
                                            </tr>
                                            <tr><td height="12" colspan="3"></td></tr>
                                            <tr>
                                                <td width="50%" style="padding:10px;background:#070d18;border:1px solid rgba(148,163,184,0.12);border-radius:12px;">
                                                    <span style="display:block;color:#94a3b8;font-size:12px;">Entrada</span>
                                                    <strong style="display:block;margin-top:5px;color:#ffffff;font-size:15px;">{{ $fechaEntrada }}</strong>
                                                </td>
                                                <td width="12"></td>
                                                <td width="50%" style="padding:10px;background:#070d18;border:1px solid rgba(148,163,184,0.12);border-radius:12px;">
                                                    <span style="display:block;color:#94a3b8;font-size:12px;">Salida</span>
                                                    <strong style="display:block;margin-top:5px;color:#ffffff;font-size:15px;">{{ $fechaSalida }}</strong>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:20px 30px;">
                            <div style="display:inline-block;padding:14px;background:#ffffff;border-radius:18px;border:1px solid rgba(236,72,153,0.45);box-shadow:0 0 30px rgba(236,72,153,0.26);">
                                <img src="{{ $qrUrl }}" width="180" height="180" alt="QR de ingreso DISCRET" style="display:block;border-radius:10px;">
                            </div>
                            <p style="margin:14px 0 0;color:#94a3b8;font-size:13px;line-height:1.6;">
                                Este QR es personal y corresponde a tu comprobante de ingreso.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:8px 30px 32px;">
                            <a href="{{ $misReservasUrl }}" style="display:inline-block;padding:16px 28px;border-radius:14px;background:#ec4899;color:#ffffff;text-decoration:none;font-size:16px;font-weight:900;box-shadow:0 14px 34px rgba(236,72,153,0.32);">
                                Ver mis reservas
                            </a>

                            <p style="margin:20px 0 0;color:#94a3b8;font-size:13px;line-height:1.6;">
                                Si el botón no funciona, copia y pega este enlace:<br>
                                <span style="color:#ffb3d6;word-break:break-all;">{{ $misReservasUrl }}</span>
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
