<?php

function getCallersFromLogs($ext, $limit=50) {
    $callLogApi = new CallLog;

    $response = $callLogApi->getForExtension($ext, $limit);

    if (200 != $response->getStatusCode()) {
        throw new Exception($response->getReasonPhrase());
    }

    $data = json_decode($response->getBody()->getContents(), 1);
    if (empty($data)) {
        throw new Exception('Could not convert the JSON: ' . substr($response->getBody()->getContents(), 0, 200));
    }

    return $data['items'];
}

function isCallerPresentInAddressBook($extTo, $callerPhoneNumber) {
    $extensionApi = new Extension;
    
    //$extensionApi->deleteContact(EXTENSION_TO, 2632586);     return true;

    $response = $extensionApi->isCallerPresent($extTo, $callerPhoneNumber);
    if (200 != $response->getStatusCode()) {
        throw new Exception($response->getReasonPhrase());
    }

    $data = json_decode($response->getBody()->getContents(), 1);
    if (empty($data)) {
        throw new Exception('Could not convert the JSON: ' . substr($response->getBody()->getContents(), 0, 200));
    }

    return (int)$data['total'] > 0;
}

function saveCallerToAddressBook($extTo, $contactGroupId, $callerPhoneNumber) {
    $extensionApi = new Extension;
    $response = $extensionApi->addContact($extTo, $contactGroupId, $callerPhoneNumber);
    if (!in_array($response->getStatusCode(), array(200, 201))) {
        throw new Exception($response->getReasonPhrase());
    }

    $data = json_decode($response->getBody()->getContents(), 1);
    if (empty($data)) {
        throw new Exception('Could not convert the JSON: ' . $response->getBody()->getContents());
    }
}

//phpinfo(); exit;
foreach ($mapping as $extensionFrom => $destinationList) {
    try {
        new dBug(array($extensionFrom => $destinationList));
        $callersList = getCallersFromLogs($extensionFrom);
        if (empty($callersList)) {
           continue;
        }

new dBug($callersList);

        foreach($destinationList as $destination) {
            $extensionTo = $destination['extension_to'];
            $contactGroupId = $destination['contact_group'];

            foreach($callersList as $item) {
                $callerPhoneNumber = $item['caller_id'];
                if (in_array(strtolower($callerPhoneNumber), array('private', 'unknown', ''))) {
                    continue;
                }

                if (isCallerPresentInAddressBook($extensionTo, $callerPhoneNumber)) {
                    new dBug(array($callerPhoneNumber => 'already present'));
                    continue;
                }
                saveCallerToAddressBook($extensionTo, $contactGroupId, $callerPhoneNumber);
                    query(
                         'INSERT INTO logs VALUES (NULL, :from, :to, :caller, :status, NULL, NOW())',
                         array(
                             ':from' => $extensionFrom,
                             ':to'   => $extensionTo,
                             ':caller' => $callerPhoneNumber,
                             ':status' => 'success'
                         )
                    );
                    new dBug('Saved');
            }
        }
    }
    catch(Exception $e) {
        query(
                     'INSERT INTO logs VALUES (NULL, :from, :to, :caller, :status, :error, NOW())',
                     array(
                         ':from' => $extensionFrom,
                         ':to'   => $extensionTo,
                         ':caller' => $callerPhoneNumber,
                         ':status' => 'fail',
                         ':error'  => $e->getMessage()
                     )
        );
        new dBug($e->getMessage());
        sendFailEmail('The error message: ' . $e->getMessage());
        continue;
    }
}
