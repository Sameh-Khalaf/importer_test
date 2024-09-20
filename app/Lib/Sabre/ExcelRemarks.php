<?php
namespace App\Lib\Sabre;


use App\Lib\Sabre\Flight\Collections\CustomRemarksCollection;
use App\Lib\Sabre\Flight\Collections\InvoiceRemarksCollection;

class ExcelRemarks
{
    public $codes = [
        'Z*U1-',
        'Z*U2-',
        'Z*U3-',
        'Z*U4-',
        'Z*U5-',
        'Z*U6-',
        'Z*U7-',
        'Z*U8-',
        'Z*U9-',
        'Z*U10-',
        'Z*U11-',
        'Z*U12-',
        'Z*U13-',
        'Z*U14-',
        'Z*U15-',
        'Z*U16-',
        'Z*U17-',
        'Z*U18-',
        'Z*U19-',
        'Z*U20-',
        'Z*U21-',
        'Z*U22-',
        'Z*U23-',
        'Z*U24-',
        'Z*U25-',
        'Z*U26-',
        'Z*U27-',
        'Z*U28-',
        'Z*U29-',
        'Z*U30-',
        'Z*U50-',
        'Z*U96-',
        'Z*U52-',
        'Z*U53-',
        'Z*U54-',
        'Z*U91-',
        'Z*U92-',
        'Z*U93-',
        'Z*U94-',
        'Z*U95-',
        'Z*U99-',
        'Z*U100-',
        'Z*FILE-',
        'Z*VCHR',
        'Z*INV',
        'MARKUP-EGP',
        'BCKOFF-',
        'DIP-EGP',
        'DISC-EGP-',
        'SVF-EGP',
        'DK',
        'Z#PDQ.',
        'Z*AFSMKT1-',
        'Z*AFSMKT2-',
        'Z*AFSSEL-',
        'Z*RBKCOD1-',
        'Z*RBKSEL-',
        'Z*RBKMKT-',
        ];
    public $remarksCounter = 0;
    public $hasAutoInvoice = false;
    public $hasAutoVoucher = false;
    public $autoImportOrder = null;
    public $matchCode = null;
    public function parseRMLine($line,CustomRemarksCollection &$customRemarksCollection,InvoiceRemarksCollection  $invoiceRemarksCollection){

        foreach ($this->codes as $singleCode){
            $pos = strpos($line, $singleCode);

            if ($pos !== false) {
                $substring = substr($line, $pos + strlen($singleCode));
                if($singleCode=='Z*INV') $this->hasAutoInvoice=true;
                if($singleCode=='Z*VCHR') $this->hasAutoVoucher=true;
                if($singleCode=='Z*FILE-') $this->autoImportOrder=$substring;
                if($singleCode=='Z#PDQ.') $this->matchCode=$substring;
                $customRemarksCollection->putByIndex('field_name',$singleCode,$this->remarksCounter);
                $customRemarksCollection->putByIndex('remark_text',$substring,$this->remarksCounter);
                $customRemarksCollection->putByIndex('remark',$line,$this->remarksCounter);
                $customRemarksCollection->putByIndex('participants_id',0,$this->remarksCounter);
                $this->remarksCounter++;
            }
        }

    }
}