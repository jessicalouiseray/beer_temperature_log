<?php declare(strict_types=1);

namespace steinmb\Logger;

use RuntimeException;

final class BrewersFriendHandler implements HandlerInterface
{
    private const API_BREWSESSIONS = 'https://api.brewersfriend.com/v1/brewsessions';
    private const API_STREAM = 'https://log.brewersfriend.com/stream';
    private const API_FERMENTATION = 'https://api.brewersfriend.com/v1/fermentation';
    private $messages = [];
    private $lastMessage = '';
    private $token;
    private $sessionId;
    private $ch;
    private $jsonDecode;

    public function __construct(string $sessionId, string $token, JsonDecode $jsonDecode)
    {
        $this->token = $token;
        $this->sessionId = $sessionId;
        $this->jsonDecode = $jsonDecode;
    }

    public function read(): string
    {
        $brewesssion = $this->brewSession();
        $batchCode = $brewesssion["brewsessions"][0]["batchcode"];
        $recipeTitle = $brewesssion["brewsessions"][0]["recipe_title"];
        $styleName = $brewesssion["brewsessions"][0]["recipe"]["stylename"];
        $currentTemp = $brewesssion["brewsessions"][0]["current_stats"]["temp"];

        $fermentation = $this->fermentation();
        $content = "$batchCode, $recipeTitle, $currentTemp";
        echo $content . ' ºC' . PHP_EOL;
        return $content;
    }

    private function fermentation()
    {
        $this->curlInit(self::API_FERMENTATION . '/' . $this->sessionId);
        $request = $this->curl();
        return $this->jsonDecode->decode($request);
    }

    private function brewSession(): array
    {
        $this->curlInit(self::API_BREWSESSIONS . '/' . $this->sessionId);
        $request = $this->curl();
        return $this->jsonDecode->decode($request);
    }

    public function write(string $message)
    {
        $sample = explode(', ', $message);
        $this->curlInit(self::API_STREAM . '/' . $this->token);
        $payload = json_encode([
            'name' => 'aptest-' . $sample[1],
            'device_source' => 'DS18B20 Sensor',
            'temp' => $sample[2],
            'temp_unit' => 'C',
        ], JSON_THROW_ON_ERROR);
        curl_setopt($this->ch, CURLOPT_POST, 1);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, [
            'X-API-Key: ' . $this->token,
            'Content-Type: application/json',
        ]);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $payload);
        $request = $this->curl();
        $result = $this->jsonDecode->decode($request);
        $this->messages[] = $message;
        $this->lastMessage = $message;
    }

    private function curlInit(string $url)
    {
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, ['X-API-Key: ' . $this->token]);
//        curl_setopt($this->ch, CURLOPT_VERBOSE, true);
    }

    private function curl()
    {
        $retries = 5;
        $closeAfterDone = false;

        while ($retries--) {
            $curlResponse = curl_exec($this->ch);
            if ($curlResponse === false) {
                $curlErrno = curl_errno($this->ch);

                if (false === in_array($curlErrno, self::$retrievableErrorCodes, true) || !$retries) {
                    $curlError = curl_error($this->ch);

                    if ($closeAfterDone) {
                        $this->close();
                    }

                    throw new RuntimeException(
                        'Curl failed' . $curlErrno . ' ' . $curlError);
                }

                continue;
            }

            if ($closeAfterDone) {
                $this->close();
            }

            return $curlResponse;
        }

        return false;
    }

    public function lastEntry(): string
    {
        return $this->lastMessage;
    }

    public function close()
    {
    }
}