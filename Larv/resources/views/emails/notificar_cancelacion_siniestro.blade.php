<!doctype html>
<html lang="es">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Solicitud de Cancelación de Siniestro</title>
    <style media="all" type="text/css">
        @media only screen and (max-width: 640px) {

            .main p,
            .main td,
            .main span {
                font-size: 16px !important;
            }

            .wrapper {
                padding: 8px !important;
            }

            .content {
                padding: 0 !important;
            }

            .container {
                padding: 0 !important;
                padding-top: 8px !important;
                width: 100% !important;
            }

            .main {
                border-left-width: 0 !important;
                border-radius: 0 !important;
                border-right-width: 0 !important;
            }
        }
    </style>
</head>

<body
    style="font-family: Helvetica, sans-serif; -webkit-font-smoothing: antialiased; font-size: 16px; line-height: 1.3; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; background-color: #f4f5f6; margin: 0; padding: 0;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body"
        style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #f4f5f6; width: 100%;"
        width="100%" bgcolor="#f4f5f6">
        <tr>
            <td style="font-family: Helvetica, sans-serif; font-size: 16px; vertical-align: top;" valign="top">&nbsp;
            </td>
            <td class="container"
                style="font-family: Helvetica, sans-serif; font-size: 16px; vertical-align: top; max-width: 600px; padding: 0; padding-top: 24px; width: 600px; margin: 0 auto;"
                width="600" valign="top">
                <div class="content"
                    style="box-sizing: border-box; display: block; margin: 0 auto; max-width: 600px; padding: 0;">
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="main"
                        style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; background: #ffffff; border: 1px solid #eaebed; border-radius: 16px; width: 100%;"
                        width="100%">
                        <tr>
                            <td class="wrapper"
                                style="font-family: Helvetica, sans-serif; font-size: 16px; vertical-align: top; box-sizing: border-box; padding: 24px;"
                                valign="top">
                                <h1
                                    style="font-family: Helvetica, sans-serif; font-size: 22px; font-weight: bold; margin: 0 0 16px 0; color: #d32f2f;">
                                    Solicitud de Cancelación de Siniestro</h1>
                                <p
                                    style="font-family: Helvetica, sans-serif; font-size: 16px; font-weight: normal; margin: 0; margin-bottom: 16px;">
                                    Se ha recibido una solicitud para cancelar el siguiente siniestro:
                                </p>
                                <hr style="border: 0; border-bottom: 1px solid #eaebed; margin: 20px 0;">
                                <p style="font-family: Helvetica, sans-serif; font-size: 14px; margin: 5px 0;">
                                    <strong>Número de Orden:</strong> {{ $siniestro->numero_orden }}</p>
                                <p style="font-family: Helvetica, sans-serif; font-size: 14px; margin: 5px 0;">
                                    <strong>Número de Siniestro:</strong> {{ $siniestro->numero_siniestro }}</p>
                                <p style="font-family: Helvetica, sans-serif; font-size: 14px; margin: 5px 0;">
                                    <strong>VIN:</strong> {{ $siniestro->vehiculoInfo->vin ?? 'N/A' }}</p>
                                <p style="font-family: Helvetica, sans-serif; font-size: 14px; margin: 5px 0;">
                                    <strong>Vehículo:</strong>
                                    {{ ($siniestro->vehiculoInfo->marca ?? '') . ' ' . ($siniestro->vehiculoInfo->vehiculo ?? '') }}
                                </p>
                                <p style="font-family: Helvetica, sans-serif; font-size: 14px; margin: 5px 0;">
                                    <strong>Aseguradora:</strong> {{ $siniestro->vehiculoInfo->aseguradora ?? 'N/A' }}
                                </p>
                                <hr style="border: 0; border-bottom: 1px solid #eaebed; margin: 20px 0;">
                                <p
                                    style="font-family: Helvetica, sans-serif; font-size: 16px; font-weight: bold; margin: 0; margin-bottom: 8px;">
                                    Motivo de la solicitud:</p>
                                <div
                                    style="background-color: #fff3e0; padding: 15px; border-radius: 8px; border-left: 4px solid #ff9800; font-style: italic;">
                                    {{ $motivo }}
                                </div>
                                <p
                                    style="font-family: Helvetica, sans-serif; font-size: 14px; font-weight: normal; margin-top: 20px; color: #666;">
                                    Solicitado por: {{ Auth::user()->name }} ({{ Auth::user()->email }})
                                </p>
                            </td>
                        </tr>
                    </table>
                    <div class="footer" style="clear: both; padding-top: 24px; text-align: center; width: 100%;">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0"
                            style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;"
                            width="100%">
                            <tr>
                                <td class="content-block"
                                    style="font-family: Helvetica, sans-serif; vertical-align: top; color: #9a9ea6; font-size: 12px; text-align: center;"
                                    valign="top" align="center">
                                    <span class="apple-link"
                                        style="color: #9a9ea6; font-size: 12px; text-align: center;">
                                        © {{ date('Y') }} GestionSiniestros | Notificación Automática
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
            <td style="font-family: Helvetica, sans-serif; font-size: 16px; vertical-align: top;" valign="top">&nbsp;
            </td>
        </tr>
    </table>
</body>

</html>
