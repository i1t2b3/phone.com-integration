<?php

class Listener extends AbstractCall {

    public function listAll() {
        $response = $this->get_client()->get('listeners');
        return json_decode($response->getBody()->getContents(), 1);
    }

    public function addListener() {
        $response = $this->get_client()->post(
            'listeners',
            array(
                'body' => '{
                "type" : "callback", 
                "event_type" : "call.update", 
                "callbacks" : [{ 
                    "role" : "main", 
                    "url": "http://run.pfmtools.com/ani-route/index.php?page=incoming", 
                    "verb": "POST" 
                    }]
                }'
            )
        );
        $this->print_response($response);
    }

    function deleteListener($id) {
        $response = $this->get_client()->delete('listeners/' . $id);
        $this->print_response($response);
    }
}