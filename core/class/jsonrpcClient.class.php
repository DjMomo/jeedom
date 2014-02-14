<?php

/**
 * Description of JSONRPC client
 *
 * @author Loïc Gevrey
 */
class jsonrpcClient {
    /*     * ********Attributs******************* */

    private $errorCode = '';
    private $errorMessage = '';
    private $error = '';
    private $result;
    private $rawResult;
    private $apikey = '';
    private $apiAddr;

    /*     * ********Static******************* */

    function __construct($_apiAddr, $_apikey = '') {
        $this->apiAddr = $_apiAddr;
        $this->apikey = $_apikey;
    }

    public function sendRequest($_method, $_params = null, $_timeout = 2) {
        $_params['api'] = $this->apikey;
        $request = array(
            'request' => json_encode(array(
                'jsonrpc' => '2.0',
                'id' => rand(1, 9999),
                'method' => $_method,
                'params' => $_params,
        )));
        $this->rawResult = $this->send($request, $_timeout);

        if ($this->rawResult === false) {
            return false;
        }
        $result = json_decode(trim($this->rawResult), true);

        if (isset($result['result'])) {
            $this->result = $result['result'];
            return true;
        } else {
            if (isset($result['error']['code'])) {
                $this->error = 'Code : ' . $result['error']['code'];
                $this->errorCode = $result['error']['code'];
            }
            if (isset($result['error']['message'])) {
                $this->error .= '<br/>Message : ' . $result['error']['message'];
                $this->errorMessage = $result['error']['message'];
            }
            return false;
        }
    }

    private function send($_request, $_timeout = 2) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiAddr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, $_timeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $_request);
        $output = curl_exec($ch);
        if ($output === false) {
            $this->error = 'Erreur curl sur : ' . $this->apiAddr . '. Détail :' . curl_error($ch);
        }
        curl_close($ch);
        return $output;
    }

    /*     * ********Getteur Setteur******************* */

    public function getError() {
        return $this->error;
    }

    public function getResult() {
        return $this->result;
    }

    public function getRawResult() {
        return $this->rawResult;
    }

    public function getErrorCode() {
        return $this->errorCode;
    }

    public function getErrorMessage() {
        return $this->errorMessage;
    }

}

?>