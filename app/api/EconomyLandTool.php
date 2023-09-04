<?php
declare(strict_types=1);

namespace Mcp\Api;

class EconomyLandTool extends \Mcp\RequestHandler
{
    public function handleRequest(): void
    {
        $membership_levels = array(
            'levels' => array(
            'id' => "00000000-0000-0000-0000-000000000000",
            'description' => "some level"));
    
          $landUse = array(
            'upgrade' => false,
            'action'  => "");
    
          $currency = array(
            'estimatedCost' =>  "200.00");     // convert_to_real($amount));
    
          $membership = array(
            'upgrade' => false,
            'action'  => "",
            'levels'  => $membership_levels);
    
          $response_xml = xmlrpc_encode(array(
             'success'    => true,
             'currency'   => $currency,
             'membership' => $membership,
             'landUse'    => $landUse,
             'currency'   => $currency,
             'confirm'    => "200.00"));
    
           header("Content-type: text/xml");
           print $response_xml;
    }
}
