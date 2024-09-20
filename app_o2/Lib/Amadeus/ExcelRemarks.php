<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 8/20/19
 * Time: 2:08 PM
 */

namespace App\Lib\Amadeus;


use App\Lib\Amadeus\Flight\Collections\CustomRemarksCollection;

class ExcelRemarks
{

    public $codes = [
        'RM INV-EMAIL:',
        'RM*IDINV',
        'RM*IDVCHR',
        'RM LPO ',
        'RM APP:',
        'RM LPO2:',
        'RM EMP:',
        'RM SUB1:',
        'RM CCD:',
        'RM CCD2:',
        'RM TRC:',
        'RM NCOD1:',
        'RM NCOD2:',
        'RM NCOD3:',
        'RM NCOD4:',
        'RM NCOD5:',
        'RM NCOD6:',
        'RM NCOD7:',
        'RM NCOD8:',
        'RM NCOD9:',
        'RM NCOD10:',
        'RM NCOD11:',
        'RM NCOD12:',
        'RM NCOD13:',
        'RM NCOD14:',
        'RM NCOD15:',
        'RM NCOD16:',
        'RM NCOD17:',
        'RM NCOD18:',
        'RM NCOD19:',
        'RM NCOD20:',
        'RIFFILE',
        'RM AFSMKT',
        'RM AFSSEL',
        'RM AFSLF',
        'RM AFSCOD1',
        'RM AFSCOD2',
        'RM RBKMKT',
        'RM RBKSEL',
        'RM RBKCOD1',
        'RM AFS WVR COD:',
        'RM AFS WVR AMOUNT :',
        'RM EXLOWNCC',
        'RIFIDRF',
        'RM RQDATE:',
        'RM PAYMOB:'

    ];

    public $codeNames = [
        'INV-EMAIL',
        'Online_Invoice',
        'IDVCHR',
        'LPO',
        'APP',
        'LPO2',
        'EMP',
        'SUB1',
        'CCD',
        'CCD2',
        'TRC',
        'NCOD1',
        'NCOD2',
        'NCOD3',
        'NCOD4',
        'NCOD5',
        'NCOD6',
        'NCOD7',
        'NCOD8',
        'NCOD9',
        'NCOD10',
        'NCOD11',
        'NCOD12',
        'NCOD13',
        'NCOD14',
        'NCOD15',
        'NCOD16',
        'NCOD17',
        'NCOD18',
        'NCOD19',
        'NCOD20',
        'RIFFILE',
        'AFSMKT',
        'AFSSEL',
        'AFSLF',
        'AFSCOD1',
        'AFSCOD2',
        'RBKMKT',
        'RBKSEL',
        'RBKCOD1',
        'AFS WVR COD',
        'AFS WVR AMOUNT',
        'EXLOWNCC',
        'RIFIDRF',
        'RQDATE',
        'PAYMOB'
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
                    case "RM LPO ":

                        $re = '/([[:alnum:]]{6})/';
                        preg_match($re, $line, $matches);

                        if (isset($matches[1])) {
                            if (!empty($matches[1])) {
                                $customRemarksCollection->putByIndex('field_name',$this->codeNames[$k],$this->remarksCounter);
                                $customRemarksCollection->putByIndex('remark_text',trim($matches[1]),$this->remarksCounter);
                                $customRemarksCollection->putByIndex('remark',$line,$this->remarksCounter);
                                $customRemarksCollection->putByIndex('participants_id',$participantNumber,$this->remarksCounter);
                                $this->remarksCounter++;
                            }
                        }
                        break;
                    case 'RM*IDVCHR':
                        $customRemarksCollection->putByIndex('field_name',$this->codeNames[$k],$this->remarksCounter);
                        $customRemarksCollection->putByIndex('remark_text','VCHR',$this->remarksCounter);
                        $customRemarksCollection->putByIndex('remark',$line,$this->remarksCounter);
                        $customRemarksCollection->putByIndex('participants_id',$participantNumber,$this->remarksCounter);
                        $this->remarksCounter++;
                    break;

                    case 'RM*IDINV':
                        $customRemarksCollection->putByIndex('field_name',$this->codeNames[$k],$this->remarksCounter);
                        $customRemarksCollection->putByIndex('remark_text','INV',$this->remarksCounter);
                        $customRemarksCollection->putByIndex('remark',$line,$this->remarksCounter);
                        $customRemarksCollection->putByIndex('participants_id',$participantNumber,$this->remarksCounter);
                        $this->remarksCounter++;
                        break;
                    case 'RM EXLOWNCC':
                        $customRemarksCollection->putByIndex('field_name',$this->codeNames[$k],$this->remarksCounter);
                        $customRemarksCollection->putByIndex('remark_text','EXLOWNCC',$this->remarksCounter);
                        $customRemarksCollection->putByIndex('remark',$line,$this->remarksCounter);
                        $customRemarksCollection->putByIndex('participants_id',$participantNumber,$this->remarksCounter);
                        $ownCC = true;
                        $this->remarksCounter++;
                        break;
                    case 'RIFIDRF':
                        $requestName = trim(str_replace('RIFIDRF ','',$line));
                        $customRemarksCollection->putByIndex('field_name',$this->codeNames[$k],$this->remarksCounter);
                        $customRemarksCollection->putByIndex('remark_text',$requestName,$this->remarksCounter);
                        $customRemarksCollection->putByIndex('remark',$line,$this->remarksCounter);
                        $customRemarksCollection->putByIndex('participants_id',$participantNumber,$this->remarksCounter);
                        $this->remarksCounter++;
                        break;
                    case 'RM RBKCOD1':
                        $requestName = trim(str_replace('RM RBKCOD1','',$line));
                        $customRemarksCollection->putByIndex('field_name',$this->codeNames[$k],$this->remarksCounter);
                        $customRemarksCollection->putByIndex('remark_text',$requestName,$this->remarksCounter);
                        $customRemarksCollection->putByIndex('remark',$line,$this->remarksCounter);
                        $customRemarksCollection->putByIndex('participants_id',$participantNumber,$this->remarksCounter);
                        $this->remarksCounter++;
                        break;
                    case 'RM AFSCOD1':
                        $requestName = trim(str_replace('RM AFSCOD1','',$line));
                        $customRemarksCollection->putByIndex('field_name',$this->codeNames[$k],$this->remarksCounter);
                        $customRemarksCollection->putByIndex('remark_text',$requestName,$this->remarksCounter);
                        $customRemarksCollection->putByIndex('remark',$line,$this->remarksCounter);
                        $customRemarksCollection->putByIndex('participants_id',$participantNumber,$this->remarksCounter);
                        $this->remarksCounter++;
                        break;
                    case 'RM RBKSEL':
                        $requestName = trim(str_replace('RM RBKSEL','',$line));
                        $customRemarksCollection->putByIndex('field_name',$this->codeNames[$k],$this->remarksCounter);
                        $customRemarksCollection->putByIndex('remark_text',$requestName,$this->remarksCounter);
                        $customRemarksCollection->putByIndex('remark',$line,$this->remarksCounter);
                        $customRemarksCollection->putByIndex('participants_id',$participantNumber,$this->remarksCounter);
                        $this->remarksCounter++;
                        break;
                    default:
                        $explode = explode(':',$line);

                        if(isset($explode[1]))
                        {
                            if(!empty($explode[1])){
                                $customRemarksCollection->putByIndex('field_name',$this->codeNames[$k],$this->remarksCounter);
                                $customRemarksCollection->putByIndex('remark_text',trim($explode[1]),$this->remarksCounter);
                                $customRemarksCollection->putByIndex('remark',$line,$this->remarksCounter);
                                $customRemarksCollection->putByIndex('participants_id',$participantNumber,$this->remarksCounter);
                                $this->remarksCounter++;
                            }
                        }else{
                            $re = '/([\d].*)/';
                            $tempLine = str_replace($single,'',$line);
                            preg_match($re, $tempLine, $matches);
//                            if($single == 'RM AFSMKT') {
                                if (isset($matches[1])) {
                                    if (!empty($matches[1])) {
//                                        var_dump($this->codeNames[$k],$this->remarksCounter,$line);die;
                                        $customRemarksCollection->putByIndex('field_name',$this->codeNames[$k],$this->remarksCounter);
                                        $customRemarksCollection->putByIndex('remark_text',trim($matches[1]),$this->remarksCounter);
                                        $customRemarksCollection->putByIndex('remark',$line,$this->remarksCounter);
                                        $customRemarksCollection->putByIndex('participants_id',$participantNumber,$this->remarksCounter);
                                        $this->remarksCounter++;
                                    }
                                }else{
                                    $tempLine = str_replace($single,'',$line);
                                    if(!empty($tempLine)) {
                                        $customRemarksCollection->putByIndex('field_name', $this->codeNames[$k], $this->remarksCounter);
                                        $customRemarksCollection->putByIndex('remark_text', trim($tempLine), $this->remarksCounter);
                                        $customRemarksCollection->putByIndex('remark', $line, $this->remarksCounter);
                                        $customRemarksCollection->putByIndex('participants_id', $participantNumber, $this->remarksCounter);
                                        $this->remarksCounter++;
                                    }
                                }
//                                var_dump($line, $single,$customRemarksCollection);die;
//                            }
                        }
                        break;
//
//                    case "RM INV-EMAIL:":
//                        $explode = explode(':',$line);
//                        if(isset($explode[1]))
//                        {
//                            if(!empty($explode[1])){
//                                $customRemarksCollection->putByIndex($this->codeNames[$k],$explode[1],$this->remarksCounter);
//                                $this->remarksCounter++;
//                            }
//                        }
//                        break;
//
//                    case "RM APP:":
//                        $explode = explode(':',$line);
//                        if(isset($explode[1]))
//                        {
//                            if(!empty($explode[1])){
//                                $customRemarksCollection->putByIndex($this->codeNames[$k],$explode[1],$this->remarksCounter);
//                                $this->remarksCounter++;
//                            }
//                        }
//                        break;
//
//                    case "RM LPO2:":
//                        $explode = explode(':',$line);
//                        if(isset($explode[1]))
//                        {
//                            if(!empty($explode[1])){
//                                $customRemarksCollection->putByIndex($this->codeNames[$k],$explode[1],$this->remarksCounter);
//                                $this->remarksCounter++;
//                            }
//                        }
//                        break;

                }

            }
        }
//        print_r($customRemarksCollection);die;

    }
}