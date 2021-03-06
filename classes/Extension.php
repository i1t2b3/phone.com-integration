<?php

class Extension extends AbstractCall {

    public function get($extensionId) {
        $response = $this->get_client()->get('extensions/'.$extensionId);
        $this->print_response($response);
    }

    public function listContactGroups($extensionId, $limit=25, $offset=0) {
        $response = $this->get_client()->get('extensions/'.$extensionId.'/contact-groups?limit='.$limit.'&offset='.$offset);
//        $this->print_response($response);
        return json_decode($response->getBody()->getContents(), 1);
    }

    public function isCallerPresentInAddressBookAndGroup($extensionId, $contactGroupId, $callerPhoneNumber) {
        $response = $this->isCallerPresentInGroup($extensionId, $contactGroupId, $callerPhoneNumber);
        if (200 != $response->getStatusCode()) {
            throw new Exception($response->getReasonPhrase());
        }

        $data = json_decode($response->getBody()->getContents(), 1);
        if (empty($data)) {
            throw new Exception('Could not convert the JSON: ' . substr($response->getBody()->getContents(), 0, 200));
        }

        return ('success' == $data['message'] && 1 == $data['status']);
    }

    public function saveCallerToAddressBookAndGroup($extensionId, $contactGroupId, $contactGroupName, $callerPhoneNumber) {
        $response = $this->addContact($extensionId, $contactGroupId, $contactGroupName, $callerPhoneNumber);
        if (!in_array($response->getStatusCode(), array(200, 201))) {
            throw new Exception($response->getReasonPhrase());
        }

        $data = json_decode($response->getBody()->getContents(), 1);
        if (empty($data)) {
            throw new Exception('Could not convert the JSON: ' . $response->getBody()->getContents());
        }
        return $data;
    }


    public function isCallerPresentInGroup($extensionId, $groupId, $callerPhoneNumber) {
        $response = $this->get_client2()->get('check-if-contact-exists?account_id='.ACCOUNT_ID.'&extension_id='
            . $extensionId
            . '&phone='
            . $callerPhoneNumber
            . '&group_id='
            . $groupId,
          array('timeout' => 180)
        );
        return $response;
    }


    public function getCallerInExtension($extensionId, $callerPhoneNumber) {
        $response = $this->get_client2()->get('check-if-contact-exists?account_id='.ACCOUNT_ID.'&extension_id='
            . $extensionId
            . '&phone='
            . $callerPhoneNumber,
          array('timeout' => 180)
        );
        return $response;
    }


    public function getContactsOfGroup($extensionId, $groupId, $count=50, $offset=0) {
        $response = $this->get_client()->get('extensions/'
            . $extensionId
            . '/contacts?limit='.$count.'&offset='.$offset.'&filters%5Bgroup_id%5D='
            . $groupId,
          array('timeout' => 180)
        );
        return $response;
    }

    public function addContact($extensionId, $contactGroupId, $contactGroupName, $callerPhoneNumber) {
        return $this->get_client()->post(
            'extensions/'.$extensionId.'/contacts',
            array(
                'timeout' => 180,
                'body' => json_encode(array(
                    "phone_numbers" => array(
                        array(
                            "type" => "business",
                            "number" => $callerPhoneNumber,
                            "normalized" => $callerPhoneNumber
                        )
                    ),

                    'group' => array(
                        'id'   => $contactGroupId,
                        'name' => $contactGroupName
                    )

                ))
            )
        );
    }

    public function deleteContact($extensionId, $contactId) {
        return $this->get_client()->delete(
            'extensions/'.$extensionId.'/contacts/'.$contactId,
            array('timeout' => 180)
        );
    }

    public function deleteGroup($extensionId, $groupId) {
        return $this->get_client()->delete(
            'extensions/'.$extensionId.'/contact-groups/'.$groupId,
            array('timeout' => 180)
        );
    }

    public function getMappingByShortExtension($short) {
        $stmt = query('SELECT * FROM mapping WHERE from_extension = ' . $short);
        $extensionMapping = $stmt->fetchAll();
        if (empty($extensionMapping)) {
            throw new Exception("Not found any mappings for extension: " . $extensionFrom);
        }
        return $extensionMapping;
    }

    public function fetchExtensionById($id) {
        $stmt = query('SELECT * FROM extensions WHERE extension_id = ' . $id);
        $extensionData = $stmt->fetch();
        if (empty($extensionData)) {
            throw new Exception("Extension not found in the database: " . $id);
        }
        return $extensionData;
    }

    public function fetchExtensionByShort($short) {
        $stmt = query('SELECT * FROM extensions WHERE short_extension = ' . $short);
        $extensionData = $stmt->fetch();
        if (empty($extensionData)) {
            throw new Exception("Short extension not found in the database: " . $short);
        }
        return $extensionData;
    }

    public function reloadAll() {
        $response = $this->get_client()->get('extensions?limit=500');

        if (200 != $response->getStatusCode()) {
            throw new Exception($response->getReasonPhrase());
        }

        $data = json_decode($response->getBody()->getContents(), 1);
        if (empty($data)) {
            throw new Exception('Could not convert the JSON: ' . substr($response->getBody()->getContents(), 0, 200));
        }

        query('TRUNCATE TABLE extensions');
        foreach ($data['items'] as $extensionRecord) {
            query('INSERT INTO extensions VALUES (:id, :short_id, :name)', array(
                ':id' => $extensionRecord['id'],
                ':short_id' => $extensionRecord['extension'],
                ':name' => $extensionRecord['name']
            ));
        };

        query('UPDATE `extensions` SET `existing_route_group_id`=388712 WHERE `extension_id`=1854653');
        query('UPDATE `extensions` SET `existing_route_group_id`=388844 WHERE `extension_id`=1855889');

        query('UPDATE `extensions` SET `existing_route_group_id`=388917 WHERE `extension_id`=1843686');
        query('UPDATE `extensions` SET `existing_route_group_id`=388918 WHERE `extension_id`=1844661');
        query('UPDATE `extensions` SET `existing_route_group_id`=388713 WHERE `extension_id`=1854656');

        query('UPDATE `extensions` SET `existing_route_group_id`=388714 WHERE `extension_id`=1854658');
        query('UPDATE `extensions` SET `existing_route_group_id`=388848 WHERE `extension_id`=1855896');
        query('UPDATE `extensions` SET `existing_route_group_id`=388852 WHERE `extension_id`=1855898');

        query('UPDATE `extensions` SET `existing_route_group_id`=388715 WHERE `extension_id`=1854661');
        query('UPDATE `extensions` SET `existing_route_group_id`=388856 WHERE `extension_id`=1855900');
        query('UPDATE `extensions` SET `existing_route_group_id`=388860 WHERE `extension_id`=1855902');

        query('UPDATE `extensions` SET `existing_route_group_id`=388716 WHERE `extension_id`=1854663');
        query('UPDATE `extensions` SET `existing_route_group_id`=388922 WHERE `extension_id`=1843687');
        query('UPDATE `extensions` SET `existing_route_group_id`=388864 WHERE `extension_id`=1855903');

        query('UPDATE `extensions` SET `existing_route_group_id`=388717 WHERE `extension_id`=1854665');
        query('UPDATE `extensions` SET `existing_route_group_id`=388923 WHERE `extension_id`=1843678');
        query('UPDATE `extensions` SET `existing_route_group_id`=388924 WHERE `extension_id`=1843680');
        query('UPDATE `extensions` SET `existing_route_group_id`=388925 WHERE `extension_id`=1843690');
        

        query('UPDATE `extensions` SET `over_x_minutes_group_id`=389434 WHERE `extension_id`=1843678;');
        query('UPDATE `extensions` SET `over_x_minutes_group_id`=389427 WHERE `extension_id`=1854207;');
    }

    protected function _getMappings($tableName) {
        $mappings = array();
        $stmt = query('SELECT * FROM ' . $tableName);
        $mappingData = $stmt->fetchAll();
        foreach ($mappingData as $mappingRecord) {
            $from = $mappingRecord['from_extension'];
            $to = $mappingRecord['to_extension'];
            $mappings[$from][] = $to;
        }
        return $mappings;
    }

    public function getMappings() {
        return $this->_getMappings('mapping');
    }

    public function getDurationMappings() {
        return $this->_getMappings('mapping_duration');
    }


}