<!doctype html>
<html lang="es">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Cierre de Siniestro (Autocares)</title>
    <style media="all" type="text/css">
        @media only screen and (max-width: 640px) {
            .wrapper {
                padding: 8px !important;
            }

            .container {
                width: 100% !important;
            }
        }
    </style>
</head>

<body style="font-family: Helvetica, sans-serif; background-color: #f4f5f6; margin: 0; padding: 0;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0"
        style="background-color: #f4f5f6; width: 100%;">
        <tr>
            <td align="center">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0"
                    style="max-width: 600px; width: 100%; margin: 24px auto;">
                    <tr>
                        <td style="background: #ffffff; border: 1px solid #eaebed; border-radius: 16px; padding: 24px;">
                            <h1 style="font-size: 22px; font-weight: bold; color: #2e7d32; margin-bottom: 16px;">
                                Notificación de Cierre de Siniestro (Autocares)
                            </h1>
                            <p style="font-size: 16px; color: #333333; line-height: 1.5;">
                                Se ha cerrado formalmente el siguiente siniestro perteneciente al área de
                                <strong>Autocares</strong>:
                            </p>

                            <hr style="border: 0; border-bottom: 1px solid #eaebed; margin: 20px 0;">

                            <table role="presentation" border="0" cellpadding="0" cellspacing="0"
                                style="width: 100%; font-size: 14px; color: #555555;">
                                <tr>
                                    <td style="padding: 4px 0;"><strong>Número de Orden:</strong></td>
                                    <td style="padding: 4px 0;">{{ $siniestro->numero_orden }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 0;"><strong>Número de Siniestro:</strong></td>
                                    <td style="padding: 4px 0;">{{ $siniestro->numero_siniestro }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 0;"><strong>VIN:</strong></td>
                                    <td style="padding: 4px 0;">{{ $siniestro->vehiculoInfo->vin ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 0;"><strong>Vehículo:</strong></td>
                                    <td style="padding: 4px 0;">
                                        {{ ($siniestro->vehiculoInfo->marca ?? '') . ' ' . ($siniestro->vehiculoInfo->vehiculo ?? '') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 0;"><strong>Taller:</strong></td>
                                    <td style="padding: 4px 0;">{{ $siniestro->vehiculoInfo->taller ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 0;"><strong>Aseguradora:</strong></td>
                                    <td style="padding: 4px 0;">{{ $siniestro->vehiculoInfo->aseguradora ?? 'N/A' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 0;"><strong>Fecha de Cierre:</strong></td>
                                    <td style="padding: 4px 0;">{{ now()->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>

                            <hr style="border: 0; border-bottom: 1px solid #eaebed; margin: 20px 0;">

                            <p style="font-size: 14px; color: #666; font-style: italic;">
                                Cerrado por: {{ Auth::user()->name }} ({{ Auth::user()->email }})
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 24px; text-align: center; color: #9a9ea6; font-size: 12px;">
                            © {{ date('Y') }} GestionSiniestros | Notificación Automática
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
