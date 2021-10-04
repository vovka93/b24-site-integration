<?php

namespace Name;

use App\Bitrix24\Bitrix24API;
use App\Bitrix24\Bitrix24APIException;

class Bitrix24 {

  private $bx24;

  function __construct($webhook) {
    $this->bx24 = new Bitrix24API($webhook);
  }

  public function createLead($lead) {
    try {
      $communications = ['PHONE', 'EMAIL'];
      foreach ($communications as $communication) {
        if(array_key_exists($communication, $lead)) {
          $contactID = $this->findContact($communication, $lead[$communication]);
          if($contactID) {
            $lead['CONTACT_ID'] = $contactID;
            break;
          } else {
            $lead[$communication] = [[
              'VALUE'      => $lead[$communication],
              'VALUE_TYPE' => 'WORK'
            ]];
          }
        }
      }
      $this->bx24->addLead($lead);
    } catch (Bitrix24APIException $e) {
      return;
    }
  }

  private function findContact($type, $value) {
    try {
      $generator = $this->bx24->getContactList([$type=>$value], ['ID'], []);
      foreach ($generator as $contacts) {
        foreach($contacts as $contact) {
          return $contact['ID'];
        }
      }
    } catch (Bitrix24APIException $e) {
      return;
    }
  }

}