<?php
class API {
    /**
     * the doc info will be generated automatically into service info page.
     * @params 
     * @return
     */
    public function some_method($parameter, $option = "foo")
    {
        echo 'this is output.';

        // throw new RuntimeException('this is exception.', 567);

        return ['hello' => 'world'];
    }

    protected function client_can_not_see() {
    }
}

$service = new Yar_Server(new API());
$service->handle();