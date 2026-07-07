<?php

namespace App\Exports;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PresupuestoExport
{
   public $folio;
   public $id;
   public $nameFile;
   public $infoPresupuesto;
   public $taller;
   private $sheet;
   public function __construct()
   {
      $this->folio = 'New';
      $this->nameFile = 'NewFile';
      $this->infoPresupuesto;
      $this->taller;
      $this->sheet;
   }

   private function InfoVerticalCell(array $cells)
   {
      $sheet = $this->sheet;
      foreach ($cells as $cell => $val) {
         $sheet->setCellValue($cell, $val);
      }
   }

   private function autoAjuste()
   {
      foreach (range('A', 'G') as $column) {
         $this->sheet->getColumnDimension($column)->setAutoSize(true);
      }
   }


   public function exportar()
   {
      $spreadsheet = new Spreadsheet();
      $spreadsheet->getProperties()->setCreator('Sistema GestionSiniestros')->setLastModifiedBy('Sistema GestionSiniestros')->setTitle($this->nameFile);
      $sheet = $spreadsheet->getActiveSheet();
      $this->sheet = $sheet;

      // #### TITULO
      $sheet->setCellValue('A1', $this->nameFile);
      $sheet->mergeCells('A1:G1');
      // $sheet->getStyle('A1:D1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
      $estilo = [
         'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
         'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
         'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '000000']],
      ];
      $sheet->getStyle('A1')->applyFromArray($estilo);

      // ###### IMAGEN (TEMPORALMENTE COMENTADA PARA DEBUG)
      $drawing = new Drawing;
      $drawing->setName('PhpSpreadsheet logo');
      $drawing->setDescription('PhpSpreadsheet logo');
      $drawing->setPath('assets/img/logos_report.png');
      $drawing->setCoordinates('A7');
      //setOffsetX works properly
      $drawing->setOffsetY(5);
      $drawing->setOffsetX(15);
      //set width, height
      $drawing->setWidth(90);
      $drawing->setHeight(50);
      $drawing->setWorksheet($spreadsheet->getActiveSheet());



      // ####### INFO SINIESTRO
      $infoSiniestro = $this->infoPresupuesto->siniestros ?? null;
      $datosVehiculo = $infoSiniestro->vehiculoInfo ?? null;
      $this->InfoVerticalCell([
         'A2' => "Numero de orden:",
         'B2' => $infoSiniestro->numero_orden ?? '',
         'A3' => "Numero Siniestro:",
         'B3' => $infoSiniestro->numero_siniestro ?? '',
         'A4' => "Aseguradora:",
         'B4' => $datosVehiculo->aseguradora ?? '',
         'D2' => "Vehiculo:",
         'E2' => $datosVehiculo->vehiculo ?? '',
         'D3' => "Marca:",
         'E3' => $datosVehiculo->marca ?? '',
         'D4' => "Modelo:",
         'E4' => $datosVehiculo->modelo ?? '',
         'D5' => "VIN:",
         'E5' => $datosVehiculo->vin ?? '',
      ]);
      $styleInfoSiniestro = [
         'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
         'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '002060']]
         // 'fill' => ['fillType'=> Fill::FILL_SOLID ,'color'=>['rgb'=>'404040']]
      ];
      $sheet->getStyle('A2:G6')->applyFromArray($styleInfoSiniestro);
      $sheet->getStyle('B1:B5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
      $sheet->getStyle('E1:E5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

      // ####### COLOCACION DEL TEXTO AZUL
      $sheet->getStyle('C8')->getAlignment()->setWrapText(true);
      $sheet->getRowDimension(8)->setRowHeight(35);

      if (strpos($this->folio, "APR") !== false) {
         $styleInfoAzul = [
            'alignment' => array(
               'horizontal' => Alignment::HORIZONTAL_CENTER,
               'vertical' => Alignment::VERTICAL_CENTER,
            ),
            'font' => ['bold' => true, 'color' => ['rgb' => '0000CC'], 'size' => 9]
         ];
         $sheet->mergeCells('C7:G7');
         $sheet->mergeCells('C8:G8');
         $this->InfoVerticalCell([
            'C7' => "CDR: CENTRO DE COLISIÓN CERTIFICADO (CHEVROLET PERIFÉRICO)",
            'C8' => "DOMICILIO: ANILLO PERIFÉRICO  LIC.MANUEL BERZUZA TABLAJE CATASTRAL 18631 C.P 97300 MÉRIDA YUCATÁN"
         ]);

         $sheet->getStyle('C7:C8')->applyFromArray($styleInfoAzul);
         $sheet->getStyle("C7:G8")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
      }



      // #### Cabecera del la info del presupuesto
      $this->InfoVerticalCell([
         'A9' => "Numero de parte",
         'B9' => "Descripción",
         'C9' => "Cantidad",
         'D9' => "Importe Unitario",
         'E9' => "Importe Total",
         'F9' => "Costo Publico",
         'G9' => "Existencia",
      ]);
      $styleDetallePzas = [
         'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
         'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
         'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '002060']],
      ];
      $sheet->getStyle('A9:G9')->applyFromArray($styleDetallePzas);
      $i = 9;
      $detalleVehiculo = $this->infoPresupuesto->piezas;
      foreach ($detalleVehiculo as $val) {
         $i++;
         $this->InfoVerticalCell([
            "A$i" => $val->numero_parte,
            "B$i" => $val->descripcion,
            "C$i" => $val->numero_pzas_presupuesto,
            "D$i" => $val->importe_unitario ? "$" . number_format($val->importe_unitario, 2) : '',
            "E$i" => $val->importe_total ? "$" . number_format($val->importe_total, 2) : '',
            "G$i" => $val->existencia ?? '',
         ]);
      }
      $sheet->getStyle("A9:G$i")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
      $sheet->getStyle("A9:G$i")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
      $this->autoAjuste();

      // GENERAR EL ARCHIVO
      $writer = new Xlsx($spreadsheet);

      // Crear archivo temporal
      $tempFile = tempnam(sys_get_temp_dir(), 'presupuesto_') . '.xlsx';
      $writer->save($tempFile);

      // Retornar el path del archivo temporal
      return $tempFile;
   }
}
