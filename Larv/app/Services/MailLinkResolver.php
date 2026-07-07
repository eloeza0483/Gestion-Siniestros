<?php

namespace App\Services;

use App\Models\Perfile;
use App\Models\Presupuesto;

class MailLinkResolver
{
    public function resolvePerfilCotizacion(Presupuesto $presupuesto): string
    {
        $presupuesto->loadMissing('siniestros');

        return $this->resolvePerfilDesdeSiniestro($presupuesto) ?? 'refacciones';
    }

    public function buildCotizacionLink(Presupuesto $presupuesto): string
    {
        $perfil = $this->resolvePerfilCotizacion($presupuesto);

        return url($perfil . '/vales/asignar') . '?folio=' . urlencode($presupuesto->numero_presupuesto);
    }

    public function resolvePerfilVale(Presupuesto $presupuesto): string
    {
        $presupuesto->loadMissing('siniestros');

        $proveedor = strtoupper(trim((string) $presupuesto->proveedor));
        $perfilPorFolio = $this->resolvePerfilDesdeNumeroPresupuesto($presupuesto->numero_presupuesto);

        if ($proveedor === 'CHEVROLET') {
            return 'refacciones';
        }

        return $perfilPorFolio
            ?? $this->resolvePerfilDesdeSiniestro($presupuesto)
            ?? 'refacciones';
    }

    public function buildValeLink(Presupuesto $presupuesto, string|int $numeroVale): string
    {
        $perfil = $this->resolvePerfilVale($presupuesto);

        return url($perfil . '/vales/ver') . '?numVale=' . urlencode((string) $numeroVale);
    }

    public function buildAlbaranLink(Presupuesto $presupuesto, string|int $numeroAlbaran): string
    {
        $perfil = $this->resolvePerfilAlbaran($presupuesto);

        return url($perfil . '/albaranes/ver') . '?numAlbaran=' . urlencode((string) $numeroAlbaran);
    }

    public function resolvePerfilAlbaran(Presupuesto $presupuesto): string
    {
        $presupuesto->loadMissing('siniestros.vehiculoInfo');

        $tallerVehiculo = strtoupper(trim((string) ($presupuesto->siniestros->vehiculoInfo->taller ?? '')));
        $proveedor = strtoupper(trim((string) $presupuesto->proveedor));

        if ($tallerVehiculo === 'AUTOCAR PENSIONES') {
            return 'autocar_pensiones';
        }

        if ($tallerVehiculo === 'AUTOCAR PERIFERICO') {
            return 'autocar_periferico';
        }

        if ($proveedor === 'CHEVROLET') {
            return 'refacciones';
        }

        return $this->resolvePerfilDesdeSiniestro($presupuesto)
            ?? $this->resolvePerfilDesdeNumeroPresupuesto($presupuesto->numero_presupuesto)
            ?? 'refacciones';
    }

    protected function resolvePerfilDesdeSiniestro(Presupuesto $presupuesto): ?string
    {
        $perfilId = $presupuesto->siniestros->perfil_id ?? null;

        if (!$perfilId) {
            return null;
        }

        $perfil = Perfile::find($perfilId);

        if (!$perfil || empty($perfil->nombre)) {
            return null;
        }

        return strtolower(str_replace(' ', '_', $perfil->nombre));
    }

    protected function resolvePerfilDesdeNumeroPresupuesto(?string $numeroPresupuesto): ?string
    {
        $folio = strtoupper(trim((string) $numeroPresupuesto));

        if (str_starts_with($folio, 'REF')) {
            return 'refacciones';
        }

        if (str_starts_with($folio, 'APN')) {
            return 'autocar_pensiones';
        }

        if (str_starts_with($folio, 'APR') || str_starts_with($folio, 'APF')) {
            return 'autocar_periferico';
        }

        return null;
    }
}
