<?php

class Queue {

    public function add($shortExtensionFrom, $shortExtensionTo, $contactGroupName, $phoneNumber, $source, $payload) {
        global $db;
        $stmt = query(
            'INSERT IGNORE INTO queue (`extension_from`, `extension_to`, `contact_group`, `phone_number`, `source`, `payload`, `created_at`)
             VALUES (:extension_from, :extension_to, :contact_group, :phone_number, :source, :payload, NOW())',
            array(
                ':extension_from' => $shortExtensionFrom,
                ':extension_to' => $shortExtensionTo,
                ':contact_group' => $contactGroupName,
                ':phone_number' => $phoneNumber,
                ':source' => $source,
                ':payload' => print_r($payload, 1),
            )
        );
        return $db->lastInsertId();
    }

    public function fetch($limit=10) {
        $stmt = query('SELECT * FROM queue WHERE status="queued" LIMIT ' . (int)$limit);
        return $stmt->fetchAll();
    }

    public function setStatusProcessing($id) {
        query('UPDATE queue SET status="processing", error_message=DEFAULT, processing_time=DEFAULT WHERE id=' . $id);
    }

    public function setStatusError($id, $errorMessage, $processingTime=null) {
        query(
            'UPDATE queue SET
                status="error",
                error_message=:error_message,
                processing_time=:processing_time
             WHERE id=' . $id,
            array(
                ':error_message'   => $errorMessage,
                ':processing_time' => $processingTime
            )
        );
    }

    public function setStatusSuccess($id, $processingTime=null) {
        query(
            'UPDATE queue SET
                status="success",
                error_message=DEFAULT,
                processing_time=:processing_time
             WHERE id=' . $id,
            array(
                ':processing_time' => $processingTime
            )
        );
    }

    public function removeRecord($id) {
        query('DELETE FROM queue WHERE id=' . $id);
    }
}