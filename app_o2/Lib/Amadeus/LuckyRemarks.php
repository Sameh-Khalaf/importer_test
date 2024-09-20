<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 8/20/19
 * Time: 2:08 PM
 */

namespace App\Lib\Amadeus;


use App\Lib\Amadeus\Flight\Collections\CustomRemarksCollection;

class LuckyRemarks
{

    public $codes = [
        'RM FEE',
        'RM INV',
        'RM FILE',
        'RM DISCOUNT',
        'RM VCH'

    ];

    public $codeNames = [
        'FEE',
        'Online_Invoice',
        'FILE',
        'DISCOUNT',
        'VCH'
    ];

    public $remarksCounter = 0;

    public function parseRMLine($line,CustomRemarksCollection &$customRemarksCollection,$participantNumber,&$ownCC = false){

        $line = trim($line);
        //        var_dump(count($this->codeNames),count($this->codes));;die;
        foreach ($this->codes as $k=>$single)
        {
           
            if(strpos($line,$single) !== false)
            {
               
                switch ($single){
                    case "RM FEE":

                        $re = '/([\d].*)/';
                        preg_match($re, $line, $matches);

                        if (isset($matches[1]) && !empty($matches[1])) {
                            $customRemarksCollection->putByIndex('field_name',$this->codeNames[$k],$this->remarksCounter);
                            $customRemarksCollection->putByIndex('remark_text',trim($matches[1]),$this->remarksCounter);
                            $customRemarksCollection->putByIndex('remark',$line,$this->remarksCounter);
                            $customRemarksCollection->putByIndex('participants_id',$participantNumber,$this->remarksCounter);
                            $this->remarksCounter++;
                            
                        }
                        break;
                    case 'RM FILE':
                        $re = '/([\d].*)/';
                        preg_match($re, $line, $matches);
                        if (isset($matches[1]) && !empty($matches[1])) {
                            $customRemarksCollection->putByIndex('field_name',$this->codeNames[$k],$this->remarksCounter);
                            $customRemarksCollection->putByIndex('remark_text',trim($matches[1]),$this->remarksCounter);
                            $customRemarksCollection->putByIndex('remark',$line,$this->remarksCounter);
                            $customRemarksCollection->putByIndex('participants_id',$participantNumber,$this->remarksCounter);
                            $this->remarksCounter++;
                        }
                        break;
                    case 'RM DISCOUNT':
                        $re = '/([\d].*)/';
                        preg_match($re, $line, $matches);

                        if (isset($matches[1]) && !empty($matches[1])) {
                            $customRemarksCollection->putByIndex('field_name',$this->codeNames[$k],$this->remarksCounter);
                            $customRemarksCollection->putByIndex('remark_text',trim($matches[1]),$this->remarksCounter);
                            $customRemarksCollection->putByIndex('remark',$line,$this->remarksCounter);
                            $customRemarksCollection->putByIndex('participants_id',$participantNumber,$this->remarksCounter);
                            $ownCC = true;
                            $this->remarksCounter++;
                        }
                        break;
                    case 'RM INV':
                        
                        $customRemarksCollection->putByIndex('field_name',$this->codeNames[$k],$this->remarksCounter);
                        $customRemarksCollection->putByIndex('remark_text','INV',$this->remarksCounter);
                        $customRemarksCollection->putByIndex('remark',$line,$this->remarksCounter);
                        $customRemarksCollection->putByIndex('participants_id',$participantNumber,$this->remarksCounter);
                        $this->remarksCounter++;
                        break;
                    case 'RM VCH':
                        $customRemarksCollection->putByIndex('field_name',$this->codeNames[$k],$this->remarksCounter);
                        $customRemarksCollection->putByIndex('remark_text','VCH',$this->remarksCounter);
                        $customRemarksCollection->putByIndex('remark',$line,$this->remarksCounter);
                        $customRemarksCollection->putByIndex('participants_id',$participantNumber,$this->remarksCounter);
                        $this->remarksCounter++;
                        break;   
                    default:
                        
                        break;


                }

            }
        }
        if($customRemarksCollection->count() > 4){
        //print_r($customRemarksCollection);die;
        }

    }
}