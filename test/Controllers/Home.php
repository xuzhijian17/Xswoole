<?php

class Home {

	public function index($request, $response) {
		var_dump($request, $response);
        echo "#### index ####".PHP_EOL;
        //echo $request->getData();
        echo "########".PHP_EOL.PHP_EOL;

        $response->end("index".PHP_EOL);
    }
	
	public function task($request, $response) {
		var_dump($request, $response);
        echo "#### task ####".PHP_EOL;
        echo $request->getData();
        echo "########".PHP_EOL.PHP_EOL;

        $response->end("task".PHP_EOL);
    }
}