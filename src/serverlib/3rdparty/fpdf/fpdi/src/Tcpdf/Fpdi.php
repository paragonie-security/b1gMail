<?php

/**
 * This file is part of FPDI
 *
 * @package   setasign\Fpdi
 * @copyright Copyright (c) 2023 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */

namespace setasign\Fpdi\Tcpdf;

use setasign\Fpdi\FpdiException;
use setasign\Fpdi\FpdiTrait;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use setasign\Fpdi\PdfParser\Filter\AsciiHex;
use setasign\Fpdi\PdfParser\PdfParserException;
use setasign\Fpdi\PdfParser\Type\PdfArray;
use setasign\Fpdi\PdfParser\Type\PdfDictionary;
use setasign\Fpdi\PdfParser\Type\PdfHexString;
use setasign\Fpdi\PdfParser\Type\PdfIndirectObject;
use setasign\Fpdi\PdfParser\Type\PdfName;
use setasign\Fpdi\PdfParser\Type\PdfNull;
use setasign\Fpdi\PdfParser\Type\PdfNumeric;
use setasign\Fpdi\PdfParser\Type\PdfStream;
use setasign\Fpdi\PdfParser\Type\PdfString;
use setasign\Fpdi\PdfParser\Type\PdfType;
use setasign\Fpdi\PdfParser\Type\PdfTypeException;

/**
 * Class Fpdi
 *
 * This class let you import pages of existing PDF documents into a reusable structure for TCPDF.
 *
 * @method _encrypt_data(int $n, string $s) string
 */
class Fpdi extends \TCPDF
{
    use FpdiTrait {
        writePdfType as fpdiWritePdfType;
        useImportedPage as fpdiUseImportedPage;
    }

    /**
     * FPDI version
     *
     * @string
     */
    const VERSION = '2.5.0';

    /**
     * A counter for template ids.
     *
     * @var int
     */
    protected $templateId = 0;

    /**
     * The currently used object number.
     *
     * @var int|null
     */
    protected $currentObjectNumber;






















}
