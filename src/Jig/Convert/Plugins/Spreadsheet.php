<?php

/**
 * This file is part of the Jig package.
 * 
 * Copyright (c) 03-Mar-2013 Dieter Raber <me@dieterraber.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jig\Convert\Plugins;

use Jig\Convert\PluginAbstract;
use Jig\Convert\Interfaces\PluginArrayInterface;
use Jig\Convert\Plugins\Csv;
use PHPExcel\PhpExcelNsWrapper;

/**
 * Spreadsheet
 */
class Spreadsheet extends PluginAbstract implements PluginArrayInterface {

  public function fromArray($resource, array $options = []) {
    $options = array_merge(
      [
      'delimiter'      => ',',
      'enclosure'      => '"',
      'escape'         => '\\',
      'input_encoding' => 'UTF-8',
      'parse_file'     => false,
      'output_format'  => 'xlsx'
      ], $options
    );
    $resource        = parent::getRealResource($resource, $options);
    if (!is_string($resource) || !is_file($resource)) {
      $csv         = new Csv($options);
      $resource    = parent::tempStore(uniqid(), $csv -> fromArray($resource, $options));
    }
    PhpExcelNsWrapper::init();
    $objReader   = new \PHPExcel_Reader_CSV();
    $objReader -> setInputEncoding($options['input_encoding']);
    $objReader -> setDelimiter($options['delimiter']);
    $objReader -> setEnclosure($options['enclosure']);
    $objReader -> setLineEnding(false !== strpos($resource, "\r\n") ? "\r\n" : "\n");
    $objReader -> setSheetIndex(0);
    $objPHPExcel = $objReader -> load($resource);

    // The following statement is borrowed from PHPExcel_IOFactory
    switch (strtolower($options['output_format'])) {
      case 'xlsx':   //	Excel (OfficeOpenXML) Spreadsheet
      case 'xlsm':   //	Excel (OfficeOpenXML) Macro Spreadsheet (macros will be discarded)
      case 'xltx':   //	Excel (OfficeOpenXML) Template
      case 'xltm':   //	Excel (OfficeOpenXML) Macro Template (macros will be discarded)
        $extensionType = 'Excel2007';
        break;
      case 'xls':    //	Excel (BIFF) Spreadsheet
      case 'xlt':    //	Excel (BIFF) Template
        $extensionType = 'Excel5';
        break;
      case 'ods':    //	Open/Libre Offic Calc
      case 'ots':    //	Open/Libre Offic Calc Template
        $extensionType = 'OOCalc';
        break;
      case 'slk':
        $extensionType = 'SYLK';
        break;
      case 'xml':    //	Excel 2003 SpreadSheetML
        $extensionType = 'Excel2003XML';
        break;
      case 'gnumeric':
        $extensionType = 'Gnumeric';
        break;
      case 'htm':
      case 'html':
        $extensionType = 'HTML';
        break;
      case 'csv':
        // Do nothing
        // This must be handled by the CSV class
        break;
      default:
        break;
    }
    if (is_null($extensionType)) {
      throw new JigException('Unable to identify a reader for this file');
    }
    
    $writer      = \PHPExcel_IOFactory::createWriter($objPHPExcel, $extensionType);
    ob_start();
    $writer -> save('php://output');
    $spreadsheet = ob_get_contents();
    ob_end_clean();
    return $spreadsheet;
  }

  public function toArray($resource, array $options = []) {
    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
    $objWriter -> save('testExportFile.csv');
  }

}
