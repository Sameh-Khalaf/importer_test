<?php
/**
 * Created by PhpStorm.
 * User: aymen-ahmed
 * Date: 8/19/19
 * Time: 1:43 PM
 */

namespace App\Lib\Sabre\Flight\Parsers;

use App\Lib\Sabre\Flight\Collections\CustomRemarksCollection;
use App\Lib\Sabre\Flight\Collections\IdentCollection;
use App\Lib\Sabre\Flight\Collections\InvoiceRemarksCollection;
use App\Lib\Sabre\Flight\Collections\ParticipantsCollection;
use App\Lib\Sabre\Flight\Collections\PriceCollection;
use App\Lib\Sabre\Flight\Collections\SegmentsCollection;
use App\Lib\Sabre\Flight\Collections\TicketDataCollection;
use Matomo\Ini\IniReader;


class VoidParser
{

    private $ticketCounter = -1;

    private $participantCounter = -1;

    private $invoiceRemarksCounter = -1;

    //we need to know start position and end position to update ticketdata in case of
    //conjunctive tickets
    private $ticketDataCollectionUpdateIndexArray = [];

    private $invoiceRemarks = [

    ];
    private $ownCC = false;
    private $validAirlineNumber = '';



    public function parse($file, $agent, IdentCollection &$identCollection, TicketDataCollection &$ticketDataCollection,
                          SegmentsCollection &$segmentsCollection, PriceCollection &$priceCollection,
                          ParticipantsCollection &$participantsCollection, InvoiceRemarksCollection &$invoiceRemarksCollection,
                          CustomRemarksCollection &$customRemarksCollection, $data, $fileDataArray)
    {



        //M0 – Control Void Message Data PAGE#28

        // if(Sab_matchVoidCount($data[0]) !== '01'){
        //     throw new \Exception('Multi void unhandled '.__FILE__.' '.__LINE__);
        // }


        $identCollection->put('crs_id','7');
        $identCollection->put('pnr_id', Sab_matchVoidPnr($data[0]));

        $ticketDataCollection->putByIndex('number',Sab_matchVoidTicketNumber($data[0]),0);
        $ticketDataCollection->putByIndex('ticket_type','2',0);





    }

}