<?php
declare(strict_types=1);

namespace Mcp\Opensim;

use XMLReader;

class RestConsole
{
    private string $baseUrl;
    private ?string $sessionId;

    public function __construct(string $host, int $port)
    {
        $this->baseUrl = "http://".$host.':'.$port;
    }

    public function startSession(string $username, string $password): bool
    {
        $response = $this->sendRequest('/StartSession/', [
            'USER' => $username,
            'PASS' => $password
        ]);

        if ($this->detectError($response, '/StartSession/')) {
            return false;
        }

        $this->sessionId = $response['SessionID'];
        $this->readResponses();
        return true;
    }

    public function closeSession(): void
    {
        $response = $this->sendRequest('/CloseSession/', [
            'ID' => $this->sessionId
        ]);

        $this->detectError($response, '/CloseSession/');
    }

    public function readResponses(): array
    {
        if ($this->sessionId == null) {
            return array();
        }

        $response = $this->sendRequest('/ReadResponses/'.$this->sessionId);
        if ($this->detectError($response, '/ReadResponses/')) {
            return array();
        }

        return $response['Line'];
    }

    public function sendCommand(string $command): bool
    {
        $response = $this->sendRequest('/SessionCommand/', [
            'ID' => $this->sessionId,
            'COMMAND' => $command
        ]);

        if ($this->detectError($response, '/SessionCommand/')) {
            return false;
        }

        return $response['Result'] == 'OK';
    }

    private function detectError(array|int $response, string $request): bool
    {
        if (gettype($response) == 'integer') {
            error_log('OS RestConsole request '.$this->baseUrl.$request.' failed, status: '.$response);
            return true;
        }

        return false;
    }

    private function sendRequest(string $request, array $data = array()): array|int
    {
        $curl = curl_init($this->baseUrl.$request);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'mcp-restconsole/0.0.1');
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Accept' => 'text/xml'
        ]);

        $postData = '';
        foreach ($data as $key => $val) {
            $postData = $postData.(strlen($postData) > 0 ? '&' : '').$key.'='.$val;
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);

        $res = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($status != 200) {
            return $status;
        }


        return $this->parseXml($res);
    }

    private function parseXml(string $response): array
    {
        $xmlReader = XMLReader::XML($response, "UTF-8");
        $res = array();
        if ($xmlReader->next()) {
            $consoleSession = $xmlReader->expand();
            if ($consoleSession->nodeName == 'ConsoleSession') {
                foreach ($consoleSession->childNodes as $childNode) {
                    $name = $childNode->nodeName;
                    if (isset($res[$name])) {
                        if (gettype($res[$name]) == 'string') {
                            $res[$name] = array($res[$name], $childNode->nodeValue);
                        }
                        else {
                            $res[$name][] = $childNode->nodeValue;
                        }
                    }
                    else {
                        $res[$name] = $childNode->nodeValue;
                    }
                }
            }
        }

        return $res;
    }
}
