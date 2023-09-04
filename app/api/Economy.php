<?php

declare(strict_types=1);

namespace Mcp\Api;

class Economy extends \Mcp\RequestHandler
{

    private const SYSURL = ""; // ???

    # Copyright (c)Melanie Thielker and Teravus Ovares (http://opensimulator.org/)
    #
    #  Redistribution and use in source and binary forms, with or without
    #  modification, are permitted provided that the following conditions are met:
    #      * Redistributions of source code must retain the above copyright
    #        notice, this list of conditions and the following disclaimer.
    #      * Redistributions in binary form must reproduce the above copyright
    #        notice, this list of conditions and the following disclaimer in the
    #        documentation and/or other materials provided with the distribution.
    #      * Neither the name of the OpenSim Project nor the
    #        names of its contributors may be used to endorse or promote products
    #        derived from this software without specific prior written permission.
    #
    #  THIS SOFTWARE IS PROVIDED BY THE DEVELOPERS ``AS IS'' AND ANY
    #  EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
    #  WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
    #  DISCLAIMED. IN NO EVENT SHALL THE CONTRIBUTORS BE LIABLE FOR ANY
    #  DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
    #  (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
    #  LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
    #  ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
    #  (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
    #  SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
    #

    # updated for Robust installations: BlueWall 2011
    # further minor changes by justincc (http://justincc.org)
    # Adapted to use PDO and the MCP API by a 4Creative contributor
    public function handleRequest(): void
    {
        # XMLRPC
        $xmlrpc_server = xmlrpc_server_create();
        xmlrpc_server_register_method($xmlrpc_server, "preflightBuyLandPrep", function($method_name, $params, $app_data) {
            $confirmvalue = "";
            $req = $params[0];
            $agentid = $req['agentId'];
            $sessionid = $req['secureSessionId'];
            $amount = $req['currencyBuy'];
            $billableArea = $req['billableArea'];

            $id = $this->validateUser($agentid, $sessionid);

            if ($id) {
                $membership_levels = array(
                    'levels' => array(
                        'id' => "00000000-0000-0000-0000-000000000000",
                        'description' => "some level"
                    )
                );

                $landUse = array(
                    'upgrade' => false,
                    'action'  => "" . $this::SYSURL . ""
                );

                $currency = array(
                    'estimatedCost' =>  "200.00"
                );     // convert_to_real($amount));

                $membership = array(
                    'upgrade' => false,
                    'action'  => "" . $this::SYSURL . "",
                    'levels'  => $membership_levels
                );

                $response_xml = xmlrpc_encode(array(
                    'success'    => true,
                    'currency'   => $currency,
                    'membership' => $membership,
                    'landUse'    => $landUse,
                    'currency'   => $currency,
                    'confirm'    => $confirmvalue
                ));

                header("Content-type: text/xml");
                print $response_xml;
            } else {
                header("Content-type: text/xml");
                $response_xml = xmlrpc_encode(array(
                    'success' => false,
                    'errorMessage' => "\n\nUnable to Authenticate\n\nClick URL for more info.",
                    'errorURI' => "" . $this::SYSURL . ""
                ));

                print $response_xml;
            }

            return "";
        });

        $request_xml = file_get_contents('php://input');
        xmlrpc_server_call_method($xmlrpc_server, $request_xml, '');
        xmlrpc_server_destroy($xmlrpc_server);
    }

    private function validateUser($agent_id, $s_session_id)
    {
        $stmt = $this->app->db()->prepare("SELECT UserID FROM Presence WHERE UserID=? AND SecureSessionID = ?");
        $stmt->execute(array($agent_id, $s_session_id));

        if ($stmt->rowCount() == 0) {
            return false;
        }

        $res = $stmt->fetch();
        return $res['UserID'];
    }
}
